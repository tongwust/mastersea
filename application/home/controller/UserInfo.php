<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class UserInfo extends Controller
{
	public function index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function get_sess_info(){
    	$ret = [
    		'r' => 0,
    		'msg' => '已登录',
    		'userinfo' =>'',
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
      	if( !session('userinfo') ){
    		$ret['r'] = 0;
    		$ret['msg'] = '未登录，请登录';
    		return json_encode( $ret);
    		exit;
    	}
    	$user_info = model('UserInfo');
    	$res = $user_info -> getUserPartInfo(session('userinfo')['user_id']);
//  	$ret['userinfo'] = session('userinfo');
    	$ret['userinfo'] = (count($res) > 0)?$res[0]:[];
    	return json_encode($ret);
    }
    public function get_my_atten_project_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'pinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
    		$ret['r'] = -100;
    		$ret['msg'] = '请登录';
    		return json_encode($ret);
    		exit;
    	}
    	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
    	$project_attention = model('ProjectAttention');
    	$user_project_tag = model('UserProjectTag');
    	
    	$res = $project_attention -> getMyAttenProjectList($user_id);
    	
    	$project_ids_str = implode(',',array_unique(array_column($res,'project_id')));
    	
    	$atten_arr = ($project_ids_str == '')?[]:$project_attention -> getProjectAttenNum($project_ids_str);
    	$users_arr = ($project_ids_str == '')?[]:$user_project_tag -> get_user_info_by_project_ids($project_ids_str);

    	$atten = [];
    	foreach($atten_arr as $a){
    		$atten[$a['project_id']] = $a['atten_num'];
    	}
    	$users = [];
    	foreach($users_arr as $u){
    		$users[$u['project_id']] = $u;
    	}
//  	dump($users);
    	foreach($res as $k => &$v){
    		$v['atten_num'] = isset($atten[$v['project_id']])?$atten[$v['project_id']]:0;
    		if( isset($users[$v['project_id']])){
    			$v['user_id'] = $users[ $v['project_id'] ]['user_id'];
    			$v['user_name'] = $users[ $v['project_id'] ]['username'];
    			$v['user_src_id'] = $users[ $v['project_id'] ]['src_id'];
    			$v['user_access_url'] = $users[ $v['project_id'] ]['access_url'];
    			$pos = strrpos($v['project_origin_access_url'], '.');
    			$v['project_access_url'] = ( $pos > 0)?substr( $v['project_origin_access_url'], 0, $pos).'_339x387'.substr( $v['project_origin_access_url'], $pos):'';
    			$v['project_start_time'] = (strtotime($v['project_start_time']) > 0)?$v['project_start_time']:'';
				$v['project_end_time'] = (strtotime($v['project_end_time']) > 0)?$v['project_end_time']:'';
    		}else{
    			unset( $res[$k] );
    		}
    	}
    	$ret['pinfo'] = $res;
//  	dump($ret);
    	return json_encode($ret);
    }
    public function get_my_collect_project_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'pinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
    		$ret['r'] = -100;
    		$ret['msg'] = '请登录';
    		return json_encode($ret);
    		exit;
    	}
    	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
    	$collect = model('Collect');
    	$user_project_tag = model('UserProjectTag');
    	
    	$res = $collect -> getMyCollectProjectList($user_id);
    	
    	$project_ids_str = implode(',',array_unique(array_column($res,'project_id')));
//  	dump($project_ids_str);
//  	$atten_arr = $project_attention -> getProjectAttenNum($project_ids_str);
    	$users_arr = ($project_ids_str == '')?[]:$user_project_tag -> get_user_info_by_project_ids($project_ids_str);
    	//dump($users_arr);
    	$members = ($project_ids_str == '')?[]:$user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = array();
		foreach( $members as $v){
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
//  	$atten = [];
//  	foreach($atten_arr as $a){
//  		$atten[$a['project_id']] = $a['atten_num'];
//  	}
    	$users = [];
    	foreach($users_arr as $u){
    		$users[$u['project_id']] = $u;
    	}
    	foreach($res as $k => &$v){
//  		$v['atten_num'] = isset($atten[$v['project_id']])?$atten[$v['project_id']]:0;
			$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']]-1;
    		if( isset($users[$v['project_id']])){
    			$v['user_id'] = $users[ $v['project_id'] ]['user_id'];
    			$v['user_name'] = $users[ $v['project_id'] ]['username'];
    			$v['user_src_id'] = $users[ $v['project_id'] ]['src_id'];
    			$v['user_access_url'] = $users[ $v['project_id'] ]['access_url'];
    			$pos = strrpos($v['project_origin_access_url'], '.');
    			$v['project_access_url'] = ( $pos > 0)?substr( $v['project_origin_access_url'], 0, $pos).'_339x387'.substr( $v['project_origin_access_url'], $pos):'';
    			$v['project_start_time'] = (strtotime($v['project_start_time']) > 0)?$v['project_start_time']:'';
				$v['project_end_time'] = (strtotime($v['project_end_time']) > 0)?$v['project_end_time']:'';
    		}else{
    			unset( $res[$k] );
    		}
    	}
    	$ret['pinfo'] = $res;
//  	dump($ret);
    	return json_encode($ret);
    }
    public function get_my_atten_user_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'uinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
    		$ret['r'] = -100;
    		$ret['msg'] = '请登录';
    		return json_encode($ret);
    		exit;
    	}
    	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
    	$user_attention = model('UserAttention');
    	$user_project_tag = model('UserProjectTag');
    	$user_tag = model('UserTag');
    	
    	$res = $user_attention -> getMyAttenUserList($user_id);
    	$flist = $user_attention -> getMyFriends( $user_id );//dump($flist);
    	$friend_arr = [];
    	foreach($flist as $val){
    		$friend_arr[$val['user_id']] = $val['user_id'];
    	}
//  	dump($friend_arr);
    	$user_ids_str = implode(',',array_column( $res, 'user_id'));
    	$project_num_arr = ($user_ids_str == '')?[]:$user_project_tag -> getProjectNumByUserids($user_ids_str);

		$arr = [];
		foreach($project_num_arr as $v){
			
			$arr[$v['user_id']] = isset($arr[$v['user_id']])?$arr[$v['user_id']] + 1:1;
		}
//		$arr = array_count_values( $arr );
		$tags = ($user_ids_str == '')?[]:$user_tag -> getTagsByUserIds($user_ids_str);
		
    	$atten_num_arr = ($user_ids_str == '')?[]:$user_attention -> getAttenNumByUserids($user_ids_str);
		$atten_arr = [];
		foreach( $atten_num_arr as $a){
			$atten_arr[$a['follow_user_id']] = $a['atten_num'];
		}
		$project_src = ($user_ids_str == '')?[]:$user_project_tag -> getProjectCoverByUserids($user_ids_str);

    	foreach($res as &$v){
    		$v['tags'] = [];
    		$v['is_friend'] = isset($friend_arr[$v['user_id']])?1:0;
    		foreach($tags as $p){
    			if($v['user_id'] == $p['user_id']){
    				if($p['tag_id'] > 0){
    					array_push($v['tags'],['tag_id' => $p['tag_id'],'tag_name'=>$p['tag_name']]);
    				}
    			}
    		}
    		$v['project_num'] = isset($arr[$v['user_id']])?$arr[$v['user_id']]:0;
    		$v['atten_num'] = isset($atten_arr[$v['user_id']])?$atten_arr[$v['user_id']]:0;
    		foreach($project_src as $ps){
    			if( $v['user_id'] == $ps['user_id']){
    				$v['project_id'] = $ps['project_id'];
    				$v['project_src_id'] = $ps['project_src_id'];
    				$v['project_origin_access_url'] = $ps['project_origin_access_url'];
    				$pos = strrpos($ps['project_origin_access_url'], '.');
					$v['project_access_url'] = ( $pos > 0)?substr( $ps['project_origin_access_url'], 0, $pos).'_339x387'.substr( $ps['project_origin_access_url'], $pos):'';

    				break;
    			}
    		}
    	}
//  	dump($res);
    	$ret['uinfo'] = $res;
    	return json_encode( $ret);
    }
        public function get_my_join_project_member_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'uinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
//  	if( !session('userinfo') ){
//  		$ret['r'] = -100;
//  		$ret['msg'] = '请登录';
//  		return json_encode( $ret );
//  		exit;
//  	}
//  	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
		$user_id = input('user_id');
    	if( $user_id <= 0){
    		$ret['r'] = -1;
    		$ret['msg'] = 'user_id参数不符合要求';
    		return json_encode($ret);
    		exit;
    	}
    	$user_attention = model('UserAttention');
    	$user_project_tag = model('UserProjectTag');
    	$user_tag = model('UserTag');
    	$from = (input('from') >= 0)?intval(input('from')):0;
    	$page_size = (input('page_size') > 0)?intval(input('page_size')):35;
//  	$res = $user_attention -> getMyAttenUserList($user_id);
		$res = $user_project_tag -> getMyJoinProjectMembers($user_id, $from, $page_size);
    	$my_atten_list = $user_attention -> getMyAttenUsersByUserId( $user_id );
    	
    	$my_atten_arr = array_column($my_atten_list,'follow_user_id');
    	$user_ids_str = implode(',',array_column( $res, 'user_id'));
    	$project_num_arr = ($user_ids_str == '')?[]:$user_project_tag -> getProjectNumByUserids($user_ids_str);
		$arr = [];
		foreach($project_num_arr as $v){
			
			$arr[$v['user_id']] = isset($arr[$v['user_id']])?$arr[$v['user_id']] + 1:1;
		}
//		$arr = array_count_values( $arr );
		$tags = ($user_ids_str == '')?[]:$user_tag -> getTagsByUserIds($user_ids_str);
    	$atten_num_arr = ($user_ids_str == '')?[]:$user_attention -> getAttenNumByUserids($user_ids_str);
		$atten_arr = [];
		foreach( $atten_num_arr as $a){
			$atten_arr[$a['follow_user_id']] = $a['atten_num'];
		}
		$project_src = ($user_ids_str == '')?[]:$user_project_tag -> getProjectCoverByUserids($user_ids_str);
		$we_atten_list = ($user_ids_str == '')?[]:$user_attention -> getWeAttenUsers($user_ids_str);
//		dump($we_atten_list);
    	foreach($res as &$v){
    		$v['tags'] = [];
    		$v['is_atten'] = in_array($v['user_id'], $my_atten_arr)?1:0;
    		$v['is_by_atten'] = 0;
    		
			foreach($we_atten_list as $w){
				if( $v['user_id'] == $w['user_id'] && $user_id == $w['follow_user_id']){
					$v['is_by_atten'] = 1;
				}
			}
    		
    		foreach($tags as $p){
    			if($v['user_id'] == $p['user_id']){
    				if($p['tag_id'] > 0){
    					array_push($v['tags'],['tag_id' => $p['tag_id'],'tag_name'=>$p['tag_name']]);
    				}
    			}
    		}
    		$v['project_num'] = isset($arr[$v['user_id']])?$arr[$v['user_id']]:0;
    		$v['atten_num'] = isset($atten_arr[$v['user_id']])?$atten_arr[$v['user_id']]:0;
    		foreach($project_src as $ps){
    			if( $v['user_id'] == $ps['user_id']){
    				$v['project_id'] = $ps['project_id'];
    				$v['project_src_id'] = $ps['project_src_id'];
    				$v['project_origin_access_url'] = $ps['project_origin_access_url'];
    				$pos = strrpos($ps['project_origin_access_url'], '.');
					$v['project_access_url'] = ( $pos > 0)?substr( $ps['project_origin_access_url'], 0, $pos).'_339x387'.substr( $ps['project_origin_access_url'], $pos):'';
    				break;
    			}
    		}
    	}
//  	dump($res);
    	$ret['uinfo'] = $res;
    	return json_encode( $ret);
    }
    
    public function get_my_project_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'pinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
//  	if( !session('userinfo') ){
//  		$ret['r'] = -100;
//  		$ret['msg'] = '请登录';
//  		return json_encode($ret);
//  		exit;
//  	}
    	$me_id = session('userinfo')['user_id'];
    	$user_id = input('user_id');
    	if( $user_id <= 0){
    		$ret['r'] = -1;
    		$ret['msg'] = 'user_id参数不符合要求';
    		return json_encode($ret);
    		exit;
    	}
    	$user_project_tag = model('UserProjectTag');
    	$project_task_user = model('ProjectTaskUser');
    	$project_tag = model('ProjectTag');
    	$user_info = model('UserInfo');
    	$praise = model('Praise');
    	$collect = model('Collect');
    	
    	$res = $user_project_tag -> GetMyProjectFullList( $user_id );
    	$project_ids_str = implode( ',', array_column( $res, 'project_id') );
    	$user_ids_str = implode(',', array_column( $res, 'user_id'));
    	$users_info = ($user_ids_str == '')?[]:$user_info -> getUsersPartInfo( $user_ids_str);
//  	dump($res);
    	$tasks = ($project_ids_str == '')?[]:$project_task_user -> getPartTaskList( $project_ids_str);
    	$addr = ($project_ids_str == '')?[]:$project_tag -> getProjectTags( $project_ids_str);
//  	dump($tasks);
		$members = ($project_ids_str == '')?[]:$user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = array();
		foreach( $members as $v){
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
		$project_praise_res = ($project_ids_str == '' || $me_id <= 0)?[]:$praise -> get_user_praise( $me_id, $project_ids_str, 1);//dump($task_praise_res);
		$project_collect_res = ($project_ids_str == '' || $me_id <= 0)?[]:$collect -> get_user_collect( $me_id, $project_ids_str, 1);//dump($task_collect_res);
		$project_praise = [];
		foreach($project_praise_res as $r){
			$project_praise[$r['cid']] = $r['praise_id'];
		}
		$project_collect = [];
		foreach($project_collect_res as $r){
			$project_collect[$r['cid']] = $r['collect_id'];
		}
    	foreach( $res as &$v){
    		$v['task'] = [];
    		foreach( $tasks as $t){
    			if( $v['project_id'] == $t['project_id']){
    				unset( $t['project_id']);
    				if( $t['src_type'] == 1 ){
						$pos = strrpos($t['origin_access_url'], '.');
						if( $pos > 0){
							$t['access_url'] = substr( $t['origin_access_url'], 0, $pos).'_865x579'.substr( $t['origin_access_url'], $pos);
						}
					}else{
						$t['access_url'] = $t['origin_access_url'];
					}
    				array_push( $v['task'], $t);
    			}
    		}
    		$v['address'] = [];
    		foreach( $addr as $a){
    			if( $v['project_id'] == $a['project_id']){
    				unset($a['project_id']);
    				array_push( $v['address'], $a);
    			}
    		}
    		$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']]-1;
    		$v['login']['is_praise'] = isset($project_praise[$v['project_id']])?1:0;
			$v['login']['is_collect'] = isset($project_collect[$v['project_id']])?1:0;
			$v['username'] = '';
			$v['access_url'] = '';
			foreach( $users_info as $u){
				if( $v['user_id'] == $u['user_id']){
					$v['username'] = $u['username'];
					$v['access_url'] = $u['access_url'];
				}
			}
			$v['project_start_time'] = (strtotime($v['project_start_time']) > 0)?$v['project_start_time']:'';
			$v['project_end_time'] = (strtotime($v['project_end_time']) > 0)?$v['project_end_time']:'';
    	}
    	$ret['pinfo'] = $res;
//  	dump($ret );
		//trace($ret,'get_my_project_list');
    	return json_encode( $ret);
    }
    
    public function get_atten_me_user_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'uinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
//  	if( !session('userinfo') ){
//  		$ret['r'] = -100;
//  		$ret['msg'] = '请登录';
//  		return json_encode($ret);
//  		exit;
//  	}
//  	$user_id = session('userinfo')['user_id'];
//  	$user_id = 40;//test
		$user_id = input('user_id');
    	if( $user_id <= 0){
    		$ret['r'] = -1;
    		$ret['msg'] = 'user_id参数不符合要求';
    		return json_encode($ret);
    		exit;
    	}
    	$user_attention = model('UserAttention');
    	$user_project_tag = model('UserProjectTag');
    	$user_tag = model('UserTag');
    	
    	$res = $user_attention -> getAttenMeUserList($user_id);
    	$flist = $user_attention -> getMyFriends( $user_id );//dump($flist);
    	$friend_arr = [];
    	foreach($flist as $val){
    		$friend_arr[$val['user_id']] = $val['user_id'];
    	}
//  	dump($res);
    	$user_ids_str = implode(',',array_column( $res, 'user_id'));
    	$project_num_arr = ($user_ids_str == '')?[]:$user_project_tag -> getProjectNumByUserids($user_ids_str);
//  	dump($project_num_arr);
		$arr = [];
		foreach($project_num_arr as $v){
			
			$arr[$v['user_id']] = isset($arr[$v['user_id']])?$arr[$v['user_id']] + 1:1;
		}
//		$arr = array_count_values( $arr );
//		$project_tags = [];
		$tags = ($user_ids_str == '')?[]:$user_tag -> getTagsByUserIds($user_ids_str);
		
    	$atten_num_arr = ($user_ids_str == '')?[]:$user_attention -> getAttenNumByUserids($user_ids_str);
		$atten_arr = [];
		foreach( $atten_num_arr as $a){
			$atten_arr[$a['follow_user_id']] = $a['atten_num'];
		}
//  	dump($atten_arr);
    	$project_src = ($user_ids_str == '')?[]:$user_project_tag -> getProjectCoverByUserids($user_ids_str);
//  	dump($project_src);
    	foreach($res as &$v){
    		$v['tags'] = [];
    		$v['is_friend'] = isset($friend_arr[$v['user_id']])?1:0;
    		foreach($tags as $p){
    			if($v['user_id'] == $p['user_id']){
    				if($p['tag_id'] > 0){
    					array_push($v['tags'],['tag_id' => $p['tag_id'],'tag_name'=>$p['tag_name']]);
    				}
    			}
    		}
    		$v['project_num'] = isset($arr[$v['user_id']])?$arr[$v['user_id']]:0;
    		$v['atten_num'] = isset($atten_arr[$v['user_id']])?$atten_arr[$v['user_id']]:0;
    		foreach($project_src as $ps){
    			if( $v['user_id'] == $ps['user_id']){
    				$v['project_id'] = $ps['project_id'];
    				$v['project_src_id'] = $ps['project_src_id'];
    				$v['project_origin_access_url'] = $ps['project_origin_access_url'];
    				$pos = strrpos($ps['project_origin_access_url'], '.');
					$v['project_access_url'] = ( $pos > 0)?substr( $ps['project_origin_access_url'], 0, $pos).'_339x387'.substr( $ps['project_origin_access_url'], $pos):'';
    				break;
    			}
    		}
    	}
//  	dump($res);
    	$ret['uinfo'] = $res;
    	return json_encode( $ret);
    }
    public function get_my_project_atten_user_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'pinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
    		$ret['r'] = -100;
    		$ret['msg'] = '请登录';
    		return json_encode($ret);
    		exit;
    	}
    	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
    	$user_project_tag = model('UserProjectTag');
    	$project_attention = model('ProjectAttention');
    	$user_tag = model('UserTag');
    	
    	$res = $user_project_tag -> GetMyProjectList( $user_id );
//  	dump($res);
    	$project_ids_str = implode(',', array_column( $res, 'project_id'));
    	
    	$users = ($project_ids_str == '')?[]:$project_attention -> getProjectAttenUsers($project_ids_str);
//  	dump($users);
    	$user_ids_str = implode(',', array_unique(array_column($users,'user_id')) );
    	$tags = ($user_ids_str == '')?[]:$user_tag -> getTagsByUserIds($user_ids_str);
//  	dump($tags);
		foreach($users as &$us){
			$us['tags'] = [];
			foreach($tags as $t){
				if($us['user_id'] == $t['user_id']){
					if($t['tag_id'] > 0){
						array_push($us['tags'],['tag_id' => $t['tag_id'],'tag_name'=>$t['tag_name']]);
					}
    			}
			}
		}
    	foreach($res as &$v){
    		$v['patten_num'] = 0;
    		$v['users'] = [];
    		foreach($users as $u){
    			if($v['project_id'] == $u['project_id']){
    				array_push($v['users'],['user_id'=>$u['user_id'],'username'=>$u['username'],'curr_company'=>$u['curr_company'],'user_src_id'=>$u['user_src_id'],'user_access_url'=>$u['user_access_url'],'tags'=>$u['tags']]);
    				$v['patten_num'] = $v['patten_num'] + 1;
    			}
    		}
    		$v['project_start_time'] = (strtotime($v['project_start_time']) > 0)?$v['project_start_time']:'';
    		$v['project_end_time'] = (strtotime($v['project_end_time']) > 0)?$v['project_end_time']:'';
    	}
//  	dump($res);
    	$ret['pinfo'] = $res;
    	return json_encode($ret);
    }
    public function get_my_project_collect_user_list(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    		'pinfo' => [],
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
    		$ret['r'] = -100;
    		$ret['msg'] = '请登录';
    		return json_encode($ret);
    		exit;
    	}
    	$user_id = session('userinfo')['user_id'];
//  	$user_id = 3;
    	$user_project_tag = model('UserProjectTag');
    	$collect = model('Collect');
    	$user_tag = model('UserTag');
    	
    	$res = $user_project_tag -> GetMyProjectList( $user_id );
//  	dump($res);
    	$project_ids_str = implode(',', array_column( $res, 'project_id'));
    	
    	$users = ($project_ids_str == '')?[]:$collect -> getProjectCollectUsers($project_ids_str);
//  	dump($users);
    	$user_ids_str = implode(',', array_unique(array_column($users,'user_id')) );
    	$tags = ($user_ids_str == '')?[]:$user_tag -> getTagsByUserIds($user_ids_str);
//  	dump($tags);
		foreach($users as &$us){
			$us['tags'] = [];
			foreach($tags as $t){
				if($us['user_id'] == $t['user_id']){
					if($t['tag_id'] > 0){
						array_push($us['tags'],['tag_id' => $t['tag_id'],'tag_name'=>$t['tag_name']]);
					}
    			}
			}
			if( count($us['tags']) == 0 ){
				array_push( $us['tags'], ['tag_id' => '','tag_name' => '']);
			}
		}
    	foreach($res as &$v){
//  		$v['pcoll_num'] = 0;
    		$v['users'] = [];
    		foreach($users as $u){
    			if($v['project_id'] == $u['project_id']){
    				array_push($v['users'],['user_id'=>$u['user_id'],'username'=>$u['username'],'curr_company'=>$u['curr_company'],'user_src_id'=>$u['user_src_id'],'user_access_url'=>$u['user_access_url'],'tags'=>$u['tags']]);
//  				$v['pcoll_num'] = $v['pcoll_num'] + 1;
    			}
    		}
    		$pos = strrpos($v['project_origin_access_url'], '.');
			$v['project_access_url'] = ( $pos > 0)?substr( $v['project_origin_access_url'], 0, $pos).'_339x387'.substr( $v['project_origin_access_url'], $pos):'';
			$v['project_start_time'] = (strtotime($v['project_start_time']) > 0)?$v['project_start_time']:'';
			$v['project_end_time'] = (strtotime($v['project_end_time']) > 0)?$v['project_end_time']:'';
    	}
//  	dump($res);
    	$ret['pinfo'] = $res;
    	return json_encode($ret);
    }
    
    public function get_user_part_info(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	$user_id = input('user_id');
    	if( $user_id > 0 ){
    		$user_info = model('UserInfo');
    		$user_attention = model('UserAttention');
    		$user_project_tag = model('UserProjectTag');
    		$user_tag = model('UserTag');
    		
    		$user_res = $user_info->get_user_detail_by_id( $user_id);
    		if(count($user_res) > 0){
    			$ret = array_merge( $ret, $user_res[0]);
    			$atten_res = $user_attention->get_follow_users_by_id( $user_id);
    			$project_res = $user_project_tag->get_project_by_userid();
    			
    			$tag_res = $user_tag->get_address_position_skill_interest_by_userid();
    			$address = [];
    			$position = [];
    			$skill = [];
    			foreach($tag_res as $v){
    				if($v['themeid'] == 10){
    					array_push( $position, ['tag_id'=>$v['tag_id'],'name'=>$v['name']]);
    				}else if($v['themeid'] == 11){
    					array_push( $skill, ['tag_id'=>$v['tag_id'],'name'=>$v['name']]);
    				}else if($v['themeid'] == 14){
    					array_push( $address, ['tag_id'=>$v['tag_id'],'name'=>$v['name']]);
    				}
    			}
    			$ret['address'] = $address;
    			$ret['position'] = $position;
    			$ret['skill'] = $skill;
    			$ret['follow_num'] = count($atten_res);
    			$ret['project_num'] = count($project_res);
    		}else{
    			$ret['r'] = -2;
    			$ret['msg'] = '没有查询到user_id信息';
    		}
    	}else{
    		$ret['r'] = -1;
    		$ret['msg'] = '参数user_id不符合要求';
    	}
    	return json_encode($ret);
    }
    //个人详细信息
    public function get_user_info(){
    	$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'data' => [],
			'position' => [],
			'skill' => [],
			'concern' => [],
			'contact' => [],
			'language' => [],
			'address' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user_id = input('user_id');
    	$user_info = model('UserInfo');
    	$user_tag = model('UserTag');
    	$user_contact = model('UserContact');
    	$user_project_tag = model('UserProjectTag');
    	$user_attention = model('UserAttention');
    	$project_attention = model('ProjectAttention');
    	$me_id = 0;
    	if(session('userinfo') != null){
			$me_id = session('userinfo')['user_id'];
		}
    	if( $user_id > 0 ){
    		$position = $user_tag->get_tag_by_userid($user_id, 10);//user_id themeid
    		$skill = $user_tag->get_tag_by_userid($user_id, 11);
    		$concern = $user_tag->get_tag_by_userid($user_id, 9);
    		$language = $user_tag->get_tag_by_userid($user_id, 13);
    		$address = $user_tag -> get_tag_by_userid($user_id, 14);
    		$result = $user_info->get_user_detail_by_id( $user_id);
    		$partners_num = $user_project_tag -> getPartnersNumByUserId();
    		$parr = [];
    		//dump($partners_num);
//  		foreach( $partners_num as $v){
//  			$parr[$v['project_id']] = $v['user_num'];
//  		}
//  		dump($partners_num);
    		$by_atten_unum = $user_attention -> get_follow_users_by_id( $user_id);
    		$my_atten_unum = $user_attention -> getMyAttenUsersByUserId( $user_id);
    		$by_atten_pnum = $user_project_tag -> getProjectAttenNum();
    		$project_res = $user_project_tag -> get_project_by_userid();
    		$byArr = [];
    		foreach( $by_atten_pnum as $val){
    			$byArr[$val['project_id']] = $val['user_num'];
    		}
    		$my_atten_pnum = $project_attention -> getMyAttenProjectNum();
    		
    		$contact = $user_contact->get_user_contact_by_userid();
    		if( count($result) > 0 ){
    			$ret['data'] = (count($result[0]) > 0)?$result[0]:[];
    			$ret['data']['partners_num'] = (count($partners_num) == 0)?0:count($partners_num) - 1;
    			$ret['data']['by_atten_unum'] = count($by_atten_unum);
    			$ret['data']['my_atten_unum'] = count($my_atten_unum);
    			$user_arr = array_column($by_atten_unum,'user_id');
    			$ret['data']['is_atten'] = in_array( $me_id, $user_arr)?1:0;
    			$ret['data']['by_atten_pnum'] = array_sum($byArr);
    			$ret['data']['my_atten_pnum'] = (count($my_atten_pnum) > 0)?$my_atten_pnum[0]['my_atten_pnum']:0;
    			$ret['data']['project_num'] = count($project_res);
    			$ret['position'] = $position;
    			$ret['skill'] = $skill;
    			$ret['concern'] = $concern;
    			$ret['contact'] = $contact;
    			$ret['language'] = $language;
    			$ret['address'] = $address;
    		}
    	}else{
    		$ret['r'] = -1;
    		$ret['msg'] = '传入的user_id不合法';
    	}
//  	dump($ret);
    	return json_encode($ret);
    }
    
    public function update_user_info(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
//  	$user_id = input('user_id');
    	$sex = input('sex');
    	$birthday = input('birthday');
    	$fullname = trim(input('fullname'));
    	$en_name = trim(input('en_name'));
    	
    	$curr_company = input('curr_company');
    	$short_name = input('short_name');
    	
    	$address = json_decode( input('address'), true);
    	$position = json_decode( input('position'), true);
    	$language = json_decode( input('language'), true);
    	$skill = json_decode( input('skill'), true);
    	$concern = json_decode( input('concern'), true);
    	
    	$education_school = input('education_school');
    	$history = input('history');
    	$intro = input('intro');
    	$contact = json_decode(input('contact'),true);
    	$latest_update_time = time();
    	
    	$user_info = model('UserInfo');
    	$user_contact = model('UserContact');
    	$user_tag = model('UserTag');
    	$tag = new Tag;
    	if($user_id > 0){
    		Db::startTrans();
    		try{
    			if( preg_match('/^\d{4}(\-|\/|.)\d{1,2}\1\d{1,2}$/', $birthday) ){
    				$data = ['sex','birthday','fullname','en_name','curr_company','en_company','short_name','education_school','intro'];
    			}else{
    				$data = ['sex','fullname','en_name','curr_company','en_company','short_name','education_school','intro'];
    			}
    			$res = $user_info->allowField( $data )->save(input(),['user_id'=>$user_id]);
//  			$user_contact -> del_user_contact_by_userid( $user_id);
    			if(count($contact) > 0){
    				$res = $user_contact->saveAll( $contact);
    			}
    			$user_tag -> delete_user_tag($user_id, 10);//del position(参数 user_id,themeid)
//  			$user_tag -> delete_user_tag($user_id, 534);//del other position
    			if( count( $position ) > 0 ){
    				$position_list = [];
					for($i = 0; $i < count($position); $i++){
						if( $position[$i]['tag_id'] ){
							array_push( $position_list, ['user_id' => $user_id, 'tag_id' => $position[$i]['tag_id'] ] );
						}else if( !$position[$i]['tag_id'] && $position[$i]['name'] ){
							$tag_res = $tag -> tag_add2( 534, $position[$i]['name'], '', 10, 2);//534 other position
							if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
								array_push( $position_list, ['user_id' => $user_id, 'tag_id' => $tag_res['tag_id'] ] );
							}
						}
					}
					if( count($position_list) > 0){
						$user_tag -> saveAll( $position_list );
					}
    			}
    			$user_tag -> delete_user_tag($user_id, 14);//del address province
    			$user_tag -> delete_user_tag($user_id, 14);//del address city
    			if( count( $address ) > 0){
    				$address_list = [];
    				for($i = 0; $i < count($address); $i++){
    					if( $address[$i]['tag_id']){
    						array_push( $address_list, ['user_id' => $user_id, 'tag_id' => $address[$i]['tag_id'] ] );
    					}
    				}
    				if( count($address_list) > 0){
						$user_tag -> saveAll( $address_list );
					}
    			}
    			$user_tag -> delete_user_tag($user_id, 11);//del skill
    			if( count( $skill ) > 0 ){
    				$skill_list = [];
					for($i = 0; $i < count($skill); $i++){
						if( $skill[$i]['tag_id'] ){
							array_push( $skill_list, ['user_id' => $user_id, 'tag_id' => $skill[$i]['tag_id'] ] );
						}else if( !$skill[$i]['tag_id'] && $skill[$i]['name'] ){
							$tag_res = $tag -> tag_add2( 534, $skill[$i]['name'], '', 11, 2);
							if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
								array_push( $skill_list, ['user_id' => $user_id, 'tag_id' => $tag_res['tag_id'] ] );
							}
						}
					}
					if( count($skill_list) > 0){
						$user_tag -> saveAll( $skill_list );
					}
    			}
    			$user_tag -> delete_user_tag($user_id, 13);//del language
    			if( count( $language ) > 0){
    				$language_list = [];
    				for($i = 0; $i < count($language); $i++){
    					if( $language[$i]['tag_id']){
    						array_push( $language_list, ['user_id' => $user_id, 'tag_id' => $language[$i]['tag_id'] ] );
    					}
    				}
    				if( count($language_list) > 0){
						$user_tag -> saveAll( $language_list );
					}
    			}
    			$user_tag -> delete_user_tag($user_id, 9);//del concern
    			if( count($concern) > 0){
    				$concern_list = [];
    				for($i = 0; $i < count($concern); $i++){
						if( $concern[$i]['tag_id'] ){
							array_push( $concern_list, ['user_id' => $user_id, 'tag_id' => $concern[$i]['tag_id'] ] );
						}else if( !$concern[$i]['tag_id'] && $concern[$i]['name'] ){
							$tag_res = $tag -> tag_add2( 22, $concern[$i]['name'], '', 9, 2);
							if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
								array_push( $concern_list, ['user_id' => $user_id, 'tag_id' => $tag_res['tag_id'] ] );
							}
						}
					}
					if( count($concern_list) > 0){
						$user_tag -> saveAll( $concern_list );
					}
    			}
    			$result['r'] = 0;
    			$result['msg'] = '修改成功';
    			Db::commit();
    		}catch(\Exception $e){
    			Db::rollback();
    			$result['r'] = -6;
				$result['msg'] = '插入数据出错!'.$e;
    		}
    	}else{
    		$result['msg'] = 'user_id不符合要求';
    	}
    	return json_encode($result);
    }
    
    public function search_user_by_name(){
    	$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'ulist' => [],
		];
		$name = input('name');
		$user_id = input('user_id');
		$encrypt = new Encrypt;
		if($name == ''){
			$ret['r'] = -1;
			$ret['msg'] = 'name参数不能为空';
			return json_encode($ret);
			exit;
		}
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
    	$user = model('User');
    	$ret['ulist'] = $user -> searchUserByName();
//  	dump($ret);
    	return json_encode( $ret);
    }
    public function get_my_upload_srcs(){
    	$ret = [
    		'r'	=> 0,
    		'msg' => '查询成功',
    		'tasks' => [],
    	];
    	$encrypt = new Encrypt;
    	if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
    	if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
//		$user_id = input('user_id');
    	$type = input('type');
    	$from = empty(input('from'))?0:intval(input('from'));
    	$page_size = empty(input('page_size'))?15:intval(input('page_size'));
    	if( $type != 1 && $type != 5 && $type != 6 && $type != 7){
    		$ret['r'] = -1;
    		$ret['msg'] = 'type 参数不符';
    		return json_encode($ret);
    		exit;
    	}
    	$project_task_user = model('ProjectTaskUser');
    	$src_relation = model('SrcRelation');
    	
    	$res = $project_task_user -> getMyUploadSrcs( $user_id, $type, $from, $page_size);
    	$project_ids_arr = array_column( $res, 'project_id');
    	$project_ids_arr = array_unique( $project_ids_arr);
    	$project_ids_str = implode(',', $project_ids_arr);
    	$plist = ($project_ids_str == '')?[]:$src_relation -> get_srcs_by_relation_ids( $project_ids_str, 1);
    	foreach($res as &$v){
    		foreach($plist as $p){
    			if( $v['project_id'] == $p['relation_id']){
    				$pos = strrpos($p['access_url'], '.');
					if( $pos > 0){
						$v['paccess_url'] = substr( $p['access_url'], 0, $pos).'_339x387'.substr( $p['access_url'], $pos);
					}
    			}
    		}
    		$v['origin_taccess_url'] = $v['taccess_url'];
    		if( $type == 1){
    			$pos = strrpos($v['taccess_url'], '.');
				if( $pos > 0){
					$v['taccess_url'] = substr( $v['taccess_url'], 0, $pos).'_865x579'.substr( $v['taccess_url'], $pos);
				}
    		}else if( $type == 6){
    			$pos = strrpos($v['taccess_url'], '.');
				if( $pos > 0){
					$v['taccess_url'] = substr( $v['taccess_url'], 0, $pos).'.pdf';
				}
    		}
    		$v['create_time'] = date('Y年m月d日',strtotime($v['create_time']));
    	}
    	$ret['tasks'] = $res;
    	return json_encode( $ret);
    }
    public function search_my_upload_by_filename(){
    	$ret = [
    		'r'	=> 0,
    		'msg' => '查询成功',
    		'tasks' => [],
    	];
    	$encrypt = new Encrypt;
    	if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
//		$user_id = input('user_id');
    	$filename = input('filename');
    	$sortord = input('sortord');
    	$from = empty(input('from'))?0:intval(input('from'));
    	$page_size = empty(input('page_size'))?15:intval(input('page_size'));
    	if( mb_strlen($filename) == 0){
    		$ret['r'] = -1;
    		$ret['msg'] = 'filename参数不符要求';
    		return json_encode( $filename);
    		exit;
    	}
    	$project_task_user = model('ProjectTaskUser');
    	$src_relation = model('SrcRelation');
    	$res = $project_task_user -> searchMyUploadByFilename($user_id, $filename, $sortord, $from, $page_size);
    	$project_ids_arr = array_column( $res, 'project_id');
    	$project_ids_arr = array_unique( $project_ids_arr);
    	$project_ids_str = implode(',', $project_ids_arr);
    	$plist = ($project_ids_str == '')?[]:$src_relation -> get_srcs_by_relation_ids( $project_ids_str, 1);
    	
    	foreach($res as &$v){
    		foreach($plist as $p){
    			if( $v['project_id'] == $p['relation_id']){
    				$pos = strrpos($p['access_url'], '.');
					if( $pos > 0){
						$v['paccess_url'] = substr( $p['access_url'], 0, $pos).'_339x387'.substr( $p['access_url'], $pos);
					}
    			}
    		}
    		$v['origin_taccess_url'] = $v['taccess_url'];
    		if( $v['type'] == 1){
    			$pos = strrpos($v['taccess_url'], '.');
				if( $pos > 0){
					$v['taccess_url'] = substr( $v['taccess_url'], 0, $pos).'_865x579'.substr( $v['taccess_url'], $pos);
				}
    		}else if($v['type'] == 6){
    			$pos = strrpos($v['taccess_url'], '.');
				if( $pos > 0){
					$v['taccess_url'] = substr( $v['taccess_url'], 0, $pos).'.pdf';
				}
    		}
    		//2017-07-04 16:12:35
    		$v['create_time'] = date('Y年m月d日',strtotime($v['create_time']));
    	}
    	$ret['tasks'] = $res;
    	return json_encode( $ret);
    }
    public function get_my_project_time_list(){
    	$ret = [
    		'r'	=> 0,
    		'msg' => '查询成功',
    		'plist' => [],
    	];
    	$encrypt = new Encrypt;
    	if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
//		$user_id = input('user_id');
		$from = empty(input('from'))?0:intval(input('from'));
		$page_size = empty(input('page_size'))?5:intval(input('page_size'));
    	$user_project_tag = model('UserProjectTag');
    	$project_task_user = model('ProjectTaskUser');
    	$user_tag = model('UserTag');
    	
    	$res = $user_project_tag -> GetMyProjectTimeList( $user_id, $from, $page_size);
    	$project_ids_str = implode(',', array_column($res,'project_id') );
    	$mem = ($project_ids_str == '')?[]:$user_project_tag -> get_project_members_info($project_ids_str);
    	$task = ($project_ids_str == '')?[]:$project_task_user -> getPartTaskImgList( $project_ids_str);
    	$position = $user_tag -> get_tag_by_userid($user_id, 10);
    	foreach( $res as &$v){
    		if( $v['project_start_time'] != '0000-00-00'){
    			$start_time = strtotime($v['project_start_time']);
    			$end_time = ($v['project_end_time'] != '0000-00-00')?strtotime($v['project_end_time']):0;
    			$v['year'] = date('Y', $start_time);
    			if( $end_time > 0 && $v['year'] != date('Y',$end_time ) ){
    				$v['monthday'] = date('n月j日', $start_time) .' - '. date('Y年n月j日',$end_time);
    			}else if( $end_time > 0 && $v['year'] == date('Y',$end_time )){
    				$v['monthday'] = date('n月j日', $start_time) .' - '. date('n月j日',$end_time);
    			}else{
    				$v['monthday'] = date('n月j日', $start_time);
    			}
    		}else{
    			$start_time = strtotime($v['create_time']);
    			$v['year'] = date('Y', $start_time);
    			$v['monthday'] = date('n月j日', $start_time);
    		}
    		unset($v['project_start_time']);
    		unset($v['project_end_time']);
    		unset($v['create_time']);
    		$v['position'] = (count($position) > 0)?$position[0]['tag_name']:'';
    		$v['mem'] = [];
    		foreach( $mem as $m){
    			if( $v['project_id'] == $m['project_id']){
    				unset($m['project_id']);
    				array_push($v['mem'],$m);
    			}
    		}
    		$v['task'] = [];
    		foreach( $task as $t){
    			if( $v['project_id'] == $t['project_id']){
    				$pos = strrpos( $t['taccess_url'],'.');
    				$taccess_url = ($pos > 0)?substr($t['taccess_url'],0,$pos).'_865x579'.substr($t['taccess_url'],$pos):$t['taccess_url'];
    				array_push( $v['task'], ['task_id'=>$t['task_id'],'taccess_url'=>$taccess_url]);
    			}
    		}
    	}
    	$ret['plist'] = $res;dump($ret);
    	return json_encode($ret);
    }
    
    
}
?>