<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Cache;
use think\Config;
use think\Loader;
use think\Log;

use sms\SmsSingleSender;

class Index extends Controller
{
	public function index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function check_username(){
    	header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		if($user->check_name() > 0){
			$result['r'] = 0;
			$result['msg'] = '用户名已存在';
		}else{
			$result['msg'] = '用户名可用';
		}
		return json_encode($result);
		exit;
    }
    public function send_msg(){
    	header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$mobile = trim(input('mobile'));
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $result['r'] = -4;
			$result['msg'] = '格式错误';
			return json_encode($result);
			exit;
		}
		try{
			$appid = 1400028629;
			$appkey = 'ac63e8e5a3ee3982de81c35bc6fcf1d6';
			$tmpid = 15906;
			Loader::import('sms\SmsSingleSender', EXTEND_PATH);
			$singleSender = new SmsSingleSender($appid,$appkey);
			$code = mt_rand(1000,9999);
			$params = array($code,"60");
			$res = $singleSender->send(0,"86",$mobile,"注册的验证码为".$code."，有效期为60秒。","","");
//			$res = $singleSender->sendWithParam('86',$mobile,$tmpid,$params,"shining","","");
			$res = json_decode( $res, true);
			if($res['result'] == 0){
				cache( $mobile, $code, 60);
				$result['r'] = 0;
				$result['msg'] = '发送成功';
				return json_encode($result);
				exit;
			}else{
				$result['msg'] = '发送短信失败';
				return json_encode($result);
				exit;
			}
		}catch(\Exception $e){
			$result['msg'] = '发送短信出错'.$e;
			return json_encode($result);
			exit;
		}
    }
    public function check_code(){
    	header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
    	$result = [
			'r' => -1,
			'msg' => '',
		];
    	$code = trim(input('code'));
    	$mobile = trim(input('mobile'));
    	if(cache($mobile) && cache($mobile) == $code && $code ){
    		$result['r'] = 0;
    		$result['msg'] = '验证通过';
    	}else{
    		$result['msg'] = '未通过';
    	}
    	return json_encode($result);
    }
	public function register(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$mobile = trim(input('mobile'));
		$position_id = input('position_id');
		$skill_ids = trim(input('skill_ids'));
		$interest_ids = trim(input('interest_ids'));
		
		if($name == '' || $pwd == '' || $repwd == '' || $mobile == ''){
			$result['r'] = -5;
			$result['msg'] = '用户名 密码或邮箱不能为空！';
			return json_encode($result);
			exit;
		}
		if(input('pwd') != input('repwd')){
			$result['r'] = -3;
			$result['msg'] = '两次输入的密码不一致';
			return json_encode($result);
			exit;
		}
		if($user->check_name() > 0){
			$result['r'] = -2;
			$result['msg'] = '用户名已存在';
			return json_encode($result);
			exit;
		}
		$pattern_email="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $result['r'] = -4;
			$result['msg'] = '格式错误';
			return json_encode($result);
			exit;
		}
		//创建用户
		$user->name = $name;
		$user->pwd = md5($pwd);
		$user->status = 1;
		$user_contact = model('UserContact');
		$user_info = model('UserInfo');
		$user_tag = model('UserTag');
		$user_contact->contact = $mobile;
		$user_contact->type = 1;
		Db::startTrans();
		try{
			$user->save();
			$user_contact->user_id = $user->user_id;
			$user_info->user_id = $user->user_id;
			$user_contact->save();
			$user_info->save();
			if($position_id != ''){
				
				$user_tag->user_id = $user->user_id;
				$user_tag->tag_id = $position_id;
				$user_tag->save();
			}
			$skill_arr = explode(',', $skill_ids);
			if($skill_ids != '' && count($skill_arr) > 0){
				$skill_list = [];
				for($i = 0; $i < count($skill_arr); $i++){
					array_push($skill_list, ['user_id'=>$user->user_id,"tag_id"=>$skill_arr[$i]]);
				}
				$user_tag->save($skill_list);
			}
			$interest_arr = explode(',', $interest_ids);
			if($interest_ids != '' && count($interest_arr) > 0){
				$interest_list = [];
				for($i = 0; $i < count($interest_arr); $i++){
					array_push($interest_list, ['user_id'=>$user->user_id,"tag_id"=>$interest_arr[$i]]);
				}
				$user_tag->save($interest_list);
			}
			Db::commit();
			$result['r'] = 0;
			$result['msg'] = '添加成功！';
		}catch(\Exception $e){
			Db::rollback();
			$result['r'] = -6;
			$result['msg'] = '数据库错误!'.$e;
			exit;
		}
		return json_encode($result);
		//$this->email($user->name,$contract->email,md5($user->name.$user->pwd.$user->user_id),$user->user_id);
	}
	public function user_login(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$is_rember = input('is_rember');
		if($name == '' || $pwd == ''){
			$result['msg'] = '用户名或密码不能为空';
			return json_encode($result);
			exit;
		}
		$res = $user->get_user_info_by_name_pwd();
		if(count($res) > 0){
			$result['r'] = 0;
			$result['msg'] = '登陆成功';
			$session_config = [
			    'prefix'     => 'think',
			    'type'       => '',
			    'auto_start' => true,
			    'expire'	 => 3600,
			    'use_cookies'=> true,
			];
			if($is_rember == 1)	$session_config['expire'] = 7*24*3600;
			session($session_config);
			$res[0]['access_url'] = ($res[0]['access_url'] != null)?$res[0]['access_url']:'';
			$res[0]['resource_path'] = ($res[0]['resource_path'] != null)?$res[0]['resource_path']:'';
			$res[0]['path'] = ($res[0]['path'] != null)?$res[0]['path']:'';
			
			$result['PHPSESSID'] = session_id();
			session('userinfo.user_id',$res[0]['user_id']);
			session('userinfo.name',$res[0]['name']);
			session('userinfo.sex', $res[0]['sex']);
			session('userinfo.path', $res[0]['access_url']);
			session('userinfo.resource_path', $res[0]['resource_path']);
			session('userinfo.access_url', $res[0]['path']);
			
			$result = array_merge( $result, $res[0] );
//			$expire = ($is_rember == 1)?7*24*3600:2*3600;
//			cache( $result['PHPSESSID'], $res[0], $expire );
//			dump($res[0]);
//			dump(cache($res[0]['user_id']));
//			$ret = $user_tim->gen_sig($res[0]['user_id']);
//			cookie( 'sig', $ret['sig'], ['prefix' => 'think_', 'expire' => 179*24*3600]);
		}else{
			$result['msg'] = '用户名或密码错误';
		}
		return json_encode($result);
	}
	
	public function user_logout(){
		header("Access-Control-Allow-Origin:*");
    	header("Access-Control-Allow-Method:POST,GET");
		$result = [
			'r' => -1,
			'msg' => '',
		];
		cache(session('user.user_id'), NULL);
		session('user', NULL);
		$result['r'] = 0;
		return json($result);
	}
	
	public function change_user_status(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$str = input('str');
		$user_id = input('id');
		if($str == '' || $user_id){
			echo '<script>alert("错误的链接地址！");</script>';
			exit;
		}
		$user = model('User');
		$user->save([
			'status'=>1,
		],['user_id'=>$user_id]);
	}
	
	public function email($name,$toemail,$str,$user_id) {
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
        $subject='新注册账户激活邮件';
        $content='恭喜你，邮件发送成功。 <a href="'.url('home/index/change_user_status',['str'=>$str,'id'=>$user_id]).'">点此链接激活账号</a>';
        send_mail($toemail,$name,$subject,$content);
    }
	
	
}

?>