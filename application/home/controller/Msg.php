<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Msg extends Controller{
	public function test(){
//		$msg_content = input('msg_content');dump($msg_content);
//		$param = convertUrlQuery($msg_content);
//		dump($param);
		
//		dump(Encrypt::ENCRYPT_STR);
		$arr = [
			0 => ['send_user_id'=>3,'receive_user_id'=>6,'msg_title'=>'','type'=>2,'msg_content'=>'cHJvamVjdF9pZD0yJmNoYXJnZV91c2VyX2lkPTMmdXNlcl9pZD01JnRhZ19pZD0wJnVzZXJfdHlwZT0z'],
			1 => ['send_user_id'=>3,'receive_user_id'=>8,'msg_title'=>'','type'=>2,'msg_content'=>'cHJvamVjdF9pZD0yJmNoYXJnZV91c2VyX2lkPTMmdXNlcl9pZD04JnRhZ19pZD0wJnVzZXJfdHlwZT0z'],
		];
		return json_encode($arr);
		$a = base64_encode('project_id=2&charge_user_id=3&user_id=8&tag_id=0&user_type=3');dump($a);
		$a = base64_decode($a);dump($a);
		$encrypt = new Encrypt($a);
		$b = $encrypt -> token_encrypt($a);
		$c = json_decode($b,true);
		dump( $c);
		$d = $encrypt -> token_decode( $c['token'] );
		dump($d);
		dump(convertUrlQuery($d));
		
	}
	public function send_single_msg(){
		$ret = [
			'r' => 0,
			'msg' => '发送成功',
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
			return json_encode($ret);
			exit;
		}
		$opt_id = session('userinfo')['user_id'];
		$send_user_id = input('send_user_id');
//		$send_user_id = 3;//test
		$receive_user_id = input('receive_user_id');
		$receive_user_name = input('receive_user_name');
		$msg_content = trim( input('msg_content'));
		$type = input('type')?input('type'):1;
		if($send_user_id <= 0 || ($receive_user_id <= 0 && $receive_user_name == '') || empty($msg_content)){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$manage_project_member = model('ManageProjectMember');
		if( $type == 2 || $type == 3 || $type == 4 || $type == 5 || $type == 6){
			$msg_arr = convertUrlQuery( base64_decode($msg_content) );
			if( !(isset($msg_arr['project_id']) && isset($msg_arr['charge_user_id']) && isset($msg_arr['user_id'])) ){
				$ret['r'] = -1;
				$ret['msg'] = 'msg_content参数格式不符';
				return json_encode($ret);
				exit;
			}
			if( $type == 2 || $type == 4){
				$mpm_res = $manage_project_member -> check_user_manage_record($msg_arr['user_id'], $opt_id, $msg_arr['project_id'], $type);
				if( count($mpm_res) > 0){
					$ret['r'] = -4;
					$ret['msg'] = 'opt_id='.$opt_id.' 在'.$mpm_res[0]['create_time'].'内已操作过 user_id='.$msg_arr['user_id'].'，请勿重复操作';
					$ret['user_id'] = $msg_arr['user_id'];
					return json_encode($ret);
					exit;
				}
			}
		}
		if($receive_user_id <= 0 && $receive_user_name != ''){
			$user = model('User');
			$res = $user -> check_name( $receive_user_name );
			if($res && count($res) > 0){
				$receive_user_id = $res[0]['user_id'];
			}else{
				$ret['r'] = -3;
				$ret['msg'] = '用户名不存在';
				return json_encode($ret);
				exit;
			}
		}
		$msg_text = model('MsgText');
		$msg = model('Msg');
		Db::startTrans();
		try{
			$msg_text -> msg_content = $msg_content;
			$msg_text -> msg_title = input('msg_title');
			$msg_text -> type = $type;
			$msg_text -> save();
			
			$msg -> msg_id = $msg_text->msg_id;
			$msg -> send_user_id = $send_user_id;
			$msg -> receive_user_id = $receive_user_id;
			$msg -> save();
			if( $type == 2 || $type == 4){
				$manage_project_member -> user_id = $msg_arr['user_id'];
				$manage_project_member -> opt_id = $opt_id;
				$manage_project_member -> project_id = $msg_arr['project_id'];
				$manage_project_member -> type = $type;
				$manage_project_member -> save();
			}
			$ret['msg_id'] = $msg_text->msg_id;
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -2;
			$ret['msg'] = '数据库操作出错'.$e;
		}
		return json_encode($ret);
	}
	
	public function send_multi_msgs(){
		$ret = [
			'r' => 0,
			'msg' => '发送成功',
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
			return json_encode($ret);
			exit;
		}
		$send_user_id = session('userinfo')['user_id'];
		$opt_id = $send_user_id;
//		$send_user_id = input('send_user_id');
//		$send_user_id = 3;//test
		$msgs = json_decode(input('msgs'),true);
		if($send_user_id <= 0 || count($msgs) <= 0 ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$text_arr = [];
		$mpm_arr = [];
		$manage_project_member = model('ManageProjectMember');
		foreach( $msgs as $k => &$v){
			$v['send_user_id'] = $send_user_id;
			if(  $v['type'] == 2 || $v['type'] == 3 || $v['type'] == 4 || $v['type'] == 5 || $v['type'] == 6){
				$msg_arr = convertUrlQuery( base64_decode($v['msg_content']) );
				if( !(isset($msg_arr['project_id']) && isset($msg_arr['charge_user_id']) && isset($msg_arr['user_id'])) ){
					$ret['r'] = -3;
					$ret['msg'] = 'msg_content参数格式不符';
					return json_encode($ret);
					exit;
				}
				if( $v['type'] == 2 || $v['type'] == 4){
					$mpm_res = $manage_project_member -> check_user_manage_record($msg_arr['user_id'], $opt_id, $msg_arr['project_id'], $v['type']);
					if( count($mpm_res) > 0){
						$ret['r'] = -4;
						$ret['msg'] = 'opt_id='.$opt_id.' 在'.$mpm_res[0]['create_time'].'内已操作过 user_id='.$msg_arr['user_id'].'，请勿重复操作';
						$ret['user_id'] = $msg_arr['user_id'];
						return json_encode($ret);
						exit;
					}
					array_push( $mpm_arr, ['user_id' => $msg_arr['user_id'],'opt_id'=>$opt_id,'project_id'=>$msg_arr['project_id'],'type'=>$v['type']] );
				}
			}
			array_push( $text_arr, ['msg_title'=>$v['msg_title'],'msg_content'=>$v['msg_content'],'type'=>$v['type']] );
		}
		$msg_text = model('MsgText');
		$msg = model('Msg');
		Db::startTrans();
		try{
			$msg_ids = $msg_text -> saveAll( $text_arr);
			$msg_list = [];
			$i = 0;
			foreach( $msgs as $val){
				array_push( $msg_list, ['msg_id'=>$msg_ids[$i++]->msg_id,'send_user_id'=>$val['send_user_id'],'receive_user_id'=>$val['receive_user_id']]);
			}
			$msg -> saveAll( $msg_list);
			$ret['msg_ids'] = array_column( $msg_list,'msg_id');
			$manage_project_member -> saveAll( $mpm_arr);
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -2;
			$ret['msg'] = '数据库操作出错'.$e;
		}
		return json_encode($ret);
	}
	
	public function get_my_send_msgs(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'mlist'	=> [],
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
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$msg = model('Msg');
		
		$res = $msg -> getMySendMsgs( $user_id );
		$ret['mlist'] = $res;
//		dump($ret);
		return json_encode($ret);
	}
	
	public function get_my_receive_msgs(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'mlist'	=> [],
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
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = input('user_id');
		$msg = model('Msg');
		
		$res = $msg -> getMyReceiveMsgs( $user_id );
		$ret['mlist'] = $res;
//		dump($ret);
		return json_encode($ret);
	}
	
	public function del_single_msg(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
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
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$send_user_id = input('send_user_id');
		$receive_user_id = input('receive_user_id');
		$msg_id = input('msg_id');
		if( $send_user_id <= 0 || $receive_user_id <= 0 || $msg_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$msg = model('Msg');
		$res = $msg -> delSingleMsg();
//		Db::startTrans();
//		try{
//			$msg -> destroy(['send_user_id' => $send_user_id,'receive_user_id'=> $receive_user_id,'msg_id' => $msg_id]);
//			$msg_text -> destroy(['msg_id' => $msg_id]);
//			
//			Db::commit();
//		}catch(\Exception $e){
//			Db::rollback();
//			$ret['r'] = -2;
//			$ret['msg'] = '数据库错误'.$e;
//		}
		
		return json_encode($ret);
	}
	
	public function change_single_msg_status(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
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
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$send_user_id = input('send_user_id');
		$receive_user_id = input('receive_user_id');
		$msg_id = input('msg_id');
		$status = input('status');
		if( $send_user_id <= 0 || $receive_user_id <= 0 || $msg_id <= 0 || ($status <= 0 || $status > 3)){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$msg = model('Msg');
		$res = $msg -> changeSingleMsgStatus($send_user_id,$receive_user_id,$msg_id,$status);
		
		return json_encode($ret);
	}
	
	public function get_unread_msg_num(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
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
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$msg = model('Msg');
		
		$res = $msg -> getUnreadMsgNum($user_id);
		$ret['msg_num'] = (count($res) > 0)?$res[0]['msg_num']:0;
		
		return json_encode($ret);
	}
	
}


?>