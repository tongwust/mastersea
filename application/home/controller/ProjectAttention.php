<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class ProjectAttention extends Controller{
	
	public function my_atten_project_task_list(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',	
			'tasks' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user_id = input('user_id');
		$from = empty(input('from'))?0:intval(input('from'));
		$page_size = empty(input('page_size'))?10:intval(input('page_size'));
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( $user_id <= 0 || $from < 0 || $page_size <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$project_attention = model('ProjectAttention');
		$comment = model('Comment');
		$praise = model('Praise');
		$collect = model('Collect');
		
		$res = $project_attention -> myAttenProjectTasklist( $from, $page_size);
//		dump($res);
		if( count($res) > 0){
			$taskids_arr = array_column($res, 'task_id');//dump($taskids_arr);
			$task_ids_str = implode(',', $taskids_arr);
			$comment_arr = ($task_ids_str == '')?[]:$comment->get_task_comment_by_task_ids($task_ids_str, 2);
			if( $user_id > 0){//login
				$task_praise_res = ($task_ids_str == '')?[]:$praise -> get_user_praise( $user_id, $task_ids_str, 2);//dump($task_praise_res);
				$task_collect_res = ($task_ids_str == '')?[]:$collect -> get_user_collect( $user_id, $task_ids_str, 2);//dump($task_collect_res);
				$task_praise = [];
				foreach($task_praise_res as $r){
					$task_praise[$r['cid']] = $r['praise_id'];
				}
				$task_collect = [];
				foreach($task_collect_res as $r){
					$task_collect[$r['cid']] = $r['collect_id'];
				}
			}
//			dump($comment_arr);
			foreach($res as &$t){
				$t['comment'] = [];
				foreach($comment_arr as $c){
					if($t['task_id'] == $c['cid']){
						array_push($t['comment'], $c);
					}
				}
				if( $t['src_type'] == 1 ){
					$pos = strrpos($t['origin_access_url'], '.');
					if( $pos > 0){
						$t['access_url'] = substr( $t['origin_access_url'], 0, $pos).'_865x579'.substr( $t['origin_access_url'], $pos);
					}
				}else{
					$t['access_url'] = $t['origin_access_url'];
				}
				$t['login']['is_praise'] = isset($task_praise[$t['task_id']])?1:0;
				$t['login']['is_collect'] = isset($task_collect[$t['task_id']])?1:0;
			}
			$ret['tasks'] = $res;
		}
//		dump($ret);
		return json_encode( $ret );
	}
	
	public function add_pro_atten(){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',	
			'project_attention_id' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');
		$user_id = input('user_id');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( !($project_id > 0 && $user_id > 0) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$project_attention = model('ProjectAttention');
		
		$project_attention -> data(['project_id' => input('project_id'), 
								 'user_id' => input('user_id'),
								 'relation_type' => (empty(input('relation_type'))?1:input('relation_type'))
								 ]) -> save();
		$ret['project_attention_id'] = $project_attention -> project_attention_id;
		return json_encode( $ret );
	}
	
	public function del_pro_atten(){
		$ret = [
			'r' => 0,
			'msg' => '取消成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');	
		$user_id = input('user_id');
		$relation_type = input('relation_type');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( $project_id <= 0 || $user_id <= 0 ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$project_attention = model('ProjectAttention');
		$res = $project_attention -> destroy(['project_id' => $project_id,'user_id'=>$user_id,'relation_type'=>$relation_type]);
		if( $res <= 0){
			$ret['r'] = -2;
			$ret['msg']	= '取消失败';
		}
		return json_encode( $ret);
	}

}

?>