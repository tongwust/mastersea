<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Task extends Controller{
	
	//任务信息更新
	public function update_task_by_taskid(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
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
		$task_id = input('task_id');
		if( $task_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$task = model('Task');
		$res = $task->updateTaskByTaskid();
//		dump($res);
		return json_encode( $ret );
	}
	
	//项目下的任务的删除
	public function delete_project_task(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		$opt_id = input('opt_id');
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
			$opt_id = session('userinfo')['user_id'];
		}
		$task_ids = input('task_id');
		if( $task_ids == '' || $opt_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$project_task_user = model('ProjectTaskUser');
//		$task = model('Task');
		$src_relation = model('SrcRelation');
		Db::startTrans();
		try{
			$project_task_user -> deleteByTaskid( $task_ids);
//			$task->deleteByTaskid();
			$src_relation -> deleteByTaskid( $task_ids);
			Db::commit();
		}catch(\Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e;
			Db::rollback();
		}
		return json_encode( $ret );
	}
	
	
	
}


?>