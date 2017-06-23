<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Praise extends Controller{
	
	public function add_praise(){
		$ret = [
			'r' => 0,
			'msg' => '点赞成功',
			'praise_id' => '',
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
		if( !($cid > 0 && $user_id > 0 && ($type == 1 || $type == 2)) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		Db::startTrans();
		try{
			$praise = model('Praise');
			$project = model('Project');
			$task = model('Task');
			//后续记录到cache
			if( $type == 1){
				$res = $project -> where('project_id', input('cid'))->setInc('praise_num');
			}else{
				$res = $task -> where('task_id',input('cid'))->setInc('praise_num');
			}
			if( $res <= 0 ){
				exception('数据修改失败', -3);
			}
			$praise -> data(['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]) -> save();
			$ret['praise_id'] = $praise->praise_id;
			Db::commit();
		}catch( \Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
			Db::rollback();
		}
		return json_encode( $ret );
	}
	
	public function del_praise(){
		$ret = [
			'r' => 0,
			'msg' => '取消成功',
		];
		$user_id = input('user_id');
		$cid = input('cid');
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
		$praise = model('Praise');
		$project = model('Project');
		$task = model('Task');
		Db::startTrans();
		try{
			
			$res = ($type == 1)?$project -> where('project_id', input('cid'))->where('praise_num', '>', 0)->setDec('praise_num'):$task -> where('task_id',input('cid'))->where('praise_num', '>', 0)->setDec('praise_num');
			if( $res <= 0 ){
				exception('数据修改失败', -3);
			}
			$praise -> destroy(['cid' => $cid,'user_id' => $user_id, 'type' => $type]);
			
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
		}
		
		return json_encode( $ret );
	}
	
}


?>