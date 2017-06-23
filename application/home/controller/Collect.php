<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class Collect extends Controller{
	
	public function add_collect(){
		$ret = [
			'r' => 0,
			'msg' => '收藏成功',
			'collect_id' => '',
		];
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