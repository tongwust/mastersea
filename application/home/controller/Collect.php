<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class Collect extends Controller{
	
	public function my_collect_project_task_list(){
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
//		$user_id = input('user_id');
		$from = empty(input('from'))?0:input('from');
		$page_size = empty(input('page_size'))?10:input('page_size');
		
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		
//		$project_attention = model('ProjectAttention');
		$comment = model('Comment');
		$praise = model('Praise');
		$collect = model('Collect');
		
//		$res = $project_attention -> myAttenProjectTasklist( $user_id, $from, $page_size);
		$res = $collect -> myCollectProjectTaskList( $user_id, $from, $page_size);
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
				$t['login']['is_praise'] = isset($task_praise[$t['task_id']])?1:0;
				$t['login']['is_collect'] = isset($task_collect[$t['task_id']])?1:0;
			}
			$ret['tasks'] = $res;
		}
//		dump($ret);
		return json_encode( $ret );
	}
	
	public function add_collect(){
		$ret = [
			'r' => 0,
			'msg' => '收藏成功',
			'collect_id' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$cid = input('cid');//1 项目id,2 任务
		$user_id = input('user_id');
		$type = input('type');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( !($cid > 0 && $user_id > 0 && ($type == 1 || $type == 2) ) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		Db::startTrans();
		try{
			$collect = model('Collect');
			$project = model('Project');
			$task = model('Task');
			if( $type == 1){
				$res = $project -> where('project_id', input('cid'))->setInc('collect_num');
			}else{
				$res = $task -> where('task_id', input('cid'))->setInc('collect_num');
			}
			if( $res <= 0){
				exception('数据修改失败', -3);
			}
			$collect -> data(['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]) -> save();
			$ret['collect_id'] = $collect->collect_id;
			Db::commit();
		}catch( \Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
			Db::rollback();
		}
		return json_encode( $ret );
	}
	public function del_collect(){
		$ret = [
			'r' => 0,
			'msg' => '收藏成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$cid = input('cid');
		$user_id = input('user_id');
		$type = input('type');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( !($cid > 0 && ($type == 1 || $type == 2) ) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		Db::startTrans();
		try{
			$collect = model('Collect');
			$project = model('Project');
			$task = model('Task');
			
			if( $type == 1){
				$res = $project -> where('project_id', input('cid'))->where('collect_num', '>', 0)->setDec('collect_num');
			}else{
				$res = $task -> where('task_id', input('cid'))->where('collect_num', '>', 0)->setDec('collect_num');
			}
			if( $res <= 0){
				exception('数据修改失败', -3);
			}
			$collect -> destroy(['user_id' => $user_id,'cid' => $cid,'type' => $type]);
			Db::commit();
		}catch(\Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
			Db::rollback();
		}
		return json_encode( $ret );
	}
	
}

?>