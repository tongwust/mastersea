<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Cache;

class User extends Controller{
	
	//获取我的圈子用户信息及项目
	public function get_user_friend_info(){
		$ret = [ 
			"r" => 0,
			"msg" => '查询成功',
			'project_list' => [],
		];
		$user_id = input('user_id');
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( $user_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$user_attention = model('UserAttention');
		$user_project_tag = model('UserProjectTag');
		$res = $user_attention -> getAttenUserinfo();
//		dump($res);
		$user_id_arr = array_column( $res, 'user_id');
		if( count($user_id_arr) == 0){
			return json_encode($ret);
			exit;
		}
		$user_ids_str = implode( ',', $user_id_arr);
		$projects = ($user_ids_str == '')?[]:$user_project_tag -> getUserProjectByUserids($user_ids_str);
		$atten_num_arr = ($user_ids_str == '')?[]:$user_attention -> getAttenNumByUserids($user_ids_str);
		$atten_arr = [];
		foreach( $atten_num_arr as $a){
			$atten_arr[$a['follow_user_id']] = $a['atten_num'];
		}
		$project_num_arr = ($user_ids_str == '')?[]:$user_project_tag -> getProjectNumByUserids($user_ids_str);
		$arr = [];
		foreach($project_num_arr as $v){
			$arr[$v['project_id']] = $v['user_id'];
		}
		$arr = array_count_values( $arr );
//		dump($arr);
		foreach( $res as $k => &$v){
			foreach( $projects as $val){
				if($v['user_id'] == $val['user_id'] ){
					$v['project_id'] = $val['project_id'];
					$v['tag_id'] = $val['tag_id'];
					$v['user_type'] = $val['user_type'];
					$v['project_name'] = $val['project_name'];
					$v['tag_name'] = $val['tag_name'];
//					$v['src_name'] = $val['src_name'];
//					$v['path'] = $val['path'];
//					$v['resource_path'] = $val['resource_path'];
					$v['access_url'] = $val['access_url'];
//					$v['source_url'] = $val['source_url'];
//					$v['url'] = $val['url'];
					$v['atten_num'] = empty($atten_arr[$v['user_id']])?'':$atten_arr[$v['user_id']];
					$v['project_num'] = empty($arr[$v['user_id']])?'':$arr[$v['user_id']];
					break;
				}
			}
		}
		$ret['project_list'] = $res;
		
		return json_encode($ret);
	}
	
	//缓存关系链
	public function get_appid_group_list(){
		$ret = [
			'r' => '-1',
			'msg' => ''
		];
		$user_tim  = new UserTim;
		$group_list = $user_tim->group_get_appid_group_list();
		if($group_list && $group_list['ActionStatus'] == 'OK' && $group_list['ErrorCode'] == 0){
			$group_id_list = $group_list['GroupIdList'];
			foreach($group_id_list as $v){
				$group_info = $user_tim->group_get_group_member_info($v['GroupId'], 10000, 0);
				if($group_info['ActionStatus'] == 'OK' && $group_info['ErrorCode'] == 0){
					unset($group_info['ActionStatus']);
					unset($group_info['ErrorCode']);
					cache( 'group_'.$v['GroupId'], json_encode($group_info), 0);
					$member_list = $group_info['MemberList'];
					foreach($member_list as $m){
						if( !cache('member_'.$m['Member_Account']) ){
							$friends_arr = [];
							$member_info = $user_tim->sns_friend_get_all( $m['Member_Account'] );
							if($member_info['ActionStatus'] == 'OK' && $member_info['ErrorCode'] == 0){
								$friends_arr['FriendNum'] = $member_info['FriendNum'];
								$friends_arr['InfoItem'] = $member_info['InfoItem'];
								cache( 'member_'.$m['Member_Account'], json_encode($friends_arr), 0);
							}
						}
					}
					$ret['r'] = 0;
				}
			}
		}else{
			$ret['msg'] = '获取群组为空';
		}
		return json($ret);
	}
	
}



?>