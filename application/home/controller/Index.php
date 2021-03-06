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
use other\Captcha;
use sms\SmsSingleSender;

class Index extends Controller
{
	public function index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function test($id = ''){
    	
		$captcha = new Captcha((array)Config::get('captcha'));
        
        return $captcha->entry( $id);
    }
    public function check_img_code(){
    	$ret = [
			'r' => 0,
			'msg' => '验证成功',
		];
		$encrypt = new Encrypt;
		$token = input('token');
		if( $encrypt -> token_decode($token) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$captcha = new Captcha((array)Config::get('captcha'));
		if( !$captcha->check(input('code'), $token) ){
			$ret['r'] = -1;
			$ret['msg'] = '验证码不正确';
		}
//  	if( strtolower(input('code')) != strtolower(cache($token)) ){
//  		$ret['r'] = -1;
//  		$ret['msg'] = '验证码不正确';
//  	}
    	return json_encode( $ret);
    }
    public function generate_check_code_img(){
    	$ret = [
			'r' => 0,
			'msg' => '',
			'data' => '',
			'sessid' => '',
		];
    	$token = input('token');
    	$captcha = new Captcha((array)Config::get('captcha'));
        
        $ret['data'] = $captcha->entry( $token);
    	
    	$ret['sessid'] = session_id();
    	return json_encode( $ret);
//  	session_start();
//		$checkCode = '';
//		$chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ23456789'; 
//		for($i = 0; $i < 4; $i++){
//			
//			$checkCode .= substr( $chars, mt_rand(0,strlen($chars)-1), 1); 
//		}
//		$_SESSION['code']=strtoupper( $checkCode);// 记录session
//		cache( $token, $checkCode, 10*60);
//		$this -> ImageCode( $checkCode, 60);// 显示GIF动画
//		return json_encode( $ret);
    }
	/** 
	*ImageCode 生成包含验证码的GIF图片的函数 
	*@param $string 字符串 
	*@param $width 宽度 
	*@param $height 高度 
	**/
	public function ImageCode($string='',$width=75,$height=25){ 
	 	$authstr=$string?$string:((time()%2==0)?mt_rand(1000,9999):mt_rand(10000,99999)); 
	  	$board_width=$width; 
	  	$board_height=$height; 
	  	// 生成一个32帧的GIF动画 
	  	for($i=0;$i<32;$i++){
	    	ob_start(); 
	    	$image=imagecreate($board_width,$board_height); 
	    	imagecolorallocate($image,0,0,0); 
		    // 设定文字颜色数组 
		    $colorList[]=ImageColorAllocate($image,15,73,210); 
		    $colorList[]=ImageColorAllocate($image,0,64,0); 
		    $colorList[]=ImageColorAllocate($image,0,0,64); 
		    $colorList[]=ImageColorAllocate($image,0,128,128); 
		    $colorList[]=ImageColorAllocate($image,27,52,47); 
		    $colorList[]=ImageColorAllocate($image,51,0,102); 
		    $colorList[]=ImageColorAllocate($image,0,0,145); 
		    $colorList[]=ImageColorAllocate($image,0,0,113); 
		    $colorList[]=ImageColorAllocate($image,0,51,51); 
		    $colorList[]=ImageColorAllocate($image,158,180,35); 
		    $colorList[]=ImageColorAllocate($image,59,59,59); 
		    $colorList[]=ImageColorAllocate($image,0,0,0); 
		    $colorList[]=ImageColorAllocate($image,1,128,180); 
		    $colorList[]=ImageColorAllocate($image,0,153,51); 
		    $colorList[]=ImageColorAllocate($image,60,131,1); 
		    $colorList[]=ImageColorAllocate($image,0,0,0); 
		    $fontcolor=ImageColorAllocate($image,0,0,0); 
		    $gray=ImageColorAllocate($image,245,245,245); 
		    $color=imagecolorallocate($image,255,255,255); 
		    $color2=imagecolorallocate($image,255,0,0); 
		    imagefill($image,0,0,$gray); 
		    $space=15;// 字符间距 
		    if($i>0){// 屏蔽第一帧 
		      $top=0; 
		      for($k=0;$k<strlen($authstr);$k++){ 
		        $colorRandom=mt_rand(0,sizeof($colorList)-1); 
		        $float_top=rand(0,4); 
		        $float_left=rand(0,3); 
		        imagestring($image,6,$space*$k,$top+$float_top,substr($authstr,$k,1),$colorList[$colorRandom]); 
		      } 
		    } 
		    for($k=0;$k<20;$k++){ 
		      	$colorRandom=mt_rand(0,sizeof($colorList)-1); 
		     	imagesetpixel($image,rand()%70,rand()%15,$colorList[$colorRandom]); 
		    
		    } 
		    // 添加干扰线 
		    for($k=0;$k<3;$k++){ 
		      $colorRandom=mt_rand(0,sizeof($colorList)-1); 
		      $todrawline=1; 
		      if($todrawline){ 
		        imageline($image,mt_rand(0,$board_width),mt_rand(0,$board_height),mt_rand(0,$board_width),mt_rand(0,$board_height),$colorList[$colorRandom]); 
		      }else{ 
		        $w=mt_rand(0,$board_width); 
		        $h=mt_rand(0,$board_width); 
		        imagearc($image,$board_width-floor($w / 2),floor($h / 2),$w,$h, rand(90,180),rand(180,270),$colorList[$colorRandom]); 
		      } 
		    } 
		    imagegif($image); 
		    imagedestroy($image); 
		    $imagedata[]=ob_get_contents(); 
		    ob_clean(); 
		    ++$i; 
		}
		$gif = new GIFEncoder($imagedata);
		Header('Content-type:image/gif');
		echo base64_encode( $gif->GetAnimation());
	}
    public function check_username(){
    	$ret = [
			'r' => 0,
			'msg' => '可用',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user = model('User');
		if( input('name') == ''){
			$ret['msg'] = '用户名不能为空';
			return json_encode($ret);
			exit;
		}
		if(count($user->check_name(input('name'))) > 0){
			$ret['r'] = -1;
			$ret['msg'] = '用户名已存在';
		}
		return json_encode($ret);
    }
    public function check_mobile(){
    	$ret = [
    		'r' => 0,
    		'msg' => '可用',
    	];
    	$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$mobile = trim(input('mobile'));
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if( !preg_match($pattern_mobile, $mobile) ){
			$ret['r'] = -1;
			$ret['msg'] = 'mobile 参数格式不符';
			return json_encode($ret);
			exit;
		}
    	$user_contact = model('UserContact');
    	if( count($user_contact -> contact_is_exists( $mobile)) ){
    		$ret['r'] = -2;
    		$ret['msg'] = '此手机号已注册过';
    	}
    	return json_encode( $ret);
    }
    
    public function send_msg(){
    	$ret = [
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
		$mobile = trim( input('mobile'));
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $ret['r'] = -4;
			$ret['msg'] = '格式错误';
			return json_encode($ret);
			exit;
		}
		if( cache($mobile) ){
			$ret['r'] = -5;
			$ret['msg'] = '手机号'.$mobile.'间隔60s才能再发送，请勿频繁操作';
			return json_encode($ret);
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
				$ret['r'] = 0;
				$ret['msg'] = '发送成功';
			}else{
				$ret['msg'] = '发送短信失败';
			}
		}catch(\Exception $e){
			$ret['msg'] = '发送短信出错'.$e;
		}
		return json_encode($ret);
    }
    
    public function check_code(){

    	$ret = [
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
    	$code = trim(input('code'));
    	$mobile = trim(input('mobile'));
    	if(cache($mobile) && cache($mobile) == $code && $code ){
    		$ret['r'] = 0;
    		$ret['msg'] = '验证通过';
    	}else{
    		$ret['msg'] = '未通过';
    	}
    	return json_encode($ret);
    }
	public function register(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'PHPSESSID' => '',
			'user_id' => '',
			'name' => '',
			'sex' => '',
			'path' => '',
			'resource_path' => '',
			'access_url' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$mobile = trim(input('mobile'));
		$email = trim(input('email'));
		$position = json_decode(trim(input('position')), true);
		$skill = json_decode(trim(input('skill')), true);
		$concern = json_decode(trim(input('concern')), true);
		if($name == '' || $pwd == '' || $repwd == '' || $mobile == ''){
			$ret['r'] = -5;
			$ret['msg'] = '用户名 密码或邮箱不能为空！';
			return json_encode($ret);
			exit;
		}
		if(input('pwd') != input('repwd')){
			$ret['r'] = -3;
			$ret['msg'] = '两次输入的密码不一致';
			return json_encode($ret);
			exit;
		}
		if(count($user->check_name( $name)) > 0){
			$ret['r'] = -2;
			$ret['msg'] = '用户名已存在';
			return json_encode($ret);
			exit;
		}
		$pattern_email="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $ret['r'] = -4;
			$ret['msg'] = '手机号格式错误';
			return json_encode($ret);
			exit;
		}
		if(!preg_match( $pattern_email, $email)){
		    $ret['r'] = -4;
			$ret['msg'] = '邮件格式错误';
			return json_encode($ret);
			exit;
		}
		//创建用户
		$user->name = $name;
		$user->pwd = md5($pwd);
		$user->status = 1;
		$user_contact = model('UserContact');
		$user_info = model('UserInfo');
		$user_tag = model('UserTag');
		$tag = new Tag;
//		$user_contact->contact = $mobile;
//		$user_contact->type = 1;
		Db::startTrans();
		try{
			$user->save();
//			$user_contact->user_id = $user->user_id;
			$contact_arr = [['contact'=>$mobile,'type'=>1,'user_id'=>$user->user_id],['contact'=>$email,'type'=>2,'user_id'=>$user->user_id]];
			$user_info->user_id = $user->user_id;
//			$user_contact->save();
			$user_contact->saveAll($contact_arr);
			
			$user_info->save();
			if( count( $position) > 0){
				$position_list = [];
				for($i = 0; $i < count($position); $i++){
					if( $position[$i]['tag_id'] ){
						array_push( $position_list, ['user_id' => $user->user_id, 'tag_id' => $position[$i]['tag_id'] ] );
					}else if( !$position[$i]['tag_id'] && $position[$i]['name'] ){
						$tag_res = $tag -> tag_add2( 534, $position[$i]['name'], '', 10, 2);//534 other position
						if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
							array_push( $position_list, ['user_id' => $user->user_id, 'tag_id' => $tag_res['tag_id'] ] );
						}
					}
				}
				if( count($position_list) > 0){
					$user_tag -> saveAll( $position_list );
				}
			}
			if( count( $skill ) > 0){
				$skill_list = [];
				for($i = 0; $i < count($skill); $i++){
					if( $skill[$i]['tag_id']){
						array_push( $skill_list, ['user_id' => $user->user_id, 'tag_id' => $skill[$i]['tag_id'] ]);
					}else if( !$skill[$i]['tag_id'] && $skill[$i]['name']){
						$tag_res = $tag -> tag_add2( 534, $skill[$i]['name'], '', 11, 2);//30 other skill
						if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
							array_push( $skill_list, ['user_id' => $user->user_id, 'tag_id' => $tag_res['tag_id'] ] );
						}
					}
				}
				if( count($skill_list) > 0){
					$user_tag -> saveAll( $skill_list );
				}
			}
			if( count( $concern) > 0){
				$concern_list = [];
				for($i = 0; $i < count($concern); $i++){
					if( $concern[$i]['tag_id']){
						array_push( $concern_list, ['user_id' => $user->user_id, 'tag_id' => $concern[$i]['tag_id'] ]);
					}else if( !$concern[$i]['tag_id'] && $concern[$i]['name'] ){
						$tag_res = $tag -> tag_add2( 22, $concern[$i]['name'], '', 9, 2);//30 concern
						if( $tag_res['r'] == 0 && $tag_res['tag_id'] > 0){
							array_push( $concern_list, ['user_id' => $user->user_id, 'tag_id' => $tag_res['tag_id'] ] );
						}
					}
				}
				if( count( $concern_list) > 0){
					$user_tag -> saveAll( $concern_list);
				}
			}
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '添加成功！';
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -6;
			$ret['msg'] = '数据库错误!'.$e;
			return json_encode($ret);
			exit;
		}
		$session_config = [
		    'prefix'     => 'think',
		    'type'       => '',
		    'auto_start' => true,
		    'expire'	 => 3*3600,
		    'use_cookies'=> true,
		];
		session($session_config);
		$ret['PHPSESSID'] = session_id();
		session('userinfo.user_id',$user->user_id);
		session('userinfo.name',$name);
		session('userinfo.sex', 1);//default
		$ret['user_id'] = $user -> user_id;
		$ret['name'] = $name;
		
		$subject= $name.' 注册成功';
        $content = '欢迎加入shining.me';
		send_mail( $email, $name, $subject, $content);
		return json_encode($ret);
		//$this->email($user->name,$contract->email,md5($user->name.$user->pwd.$user->user_id),$user->user_id);
	}
	public function user_login(){
		$ret = [
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
		
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$is_rember = input('is_rember');
		
		if($name == '' || $pwd == ''){
			$ret['msg'] = '用户名或密码不能为空';
			return json_encode($ret);
			exit;
		}
		$user = model('User');
//		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
//		if( preg_match($pattern_mobile, $name) ){
		$res = $user -> get_user_info_by_name_pwd( $name, md5($pwd));
		$res = (count( $res) > 0)?$res:$user -> get_user_info_by_moblie_pwd($name, md5($pwd));
//		}else{
//		$res = $user -> get_user_info_by_name_pwd( $name, md5($pwd));
//		}
		if(count($res) > 0){
			$ret['r'] = 0;
			$ret['msg'] = '登陆成功';
			$session_config = [
			    'prefix'     => 'think',
			    'type'       => '',
			    'auto_start' => true,
			    'expire'	 => 3*3600,
			    'use_cookies'=> true,
			];
			if($is_rember == 1)	$session_config['expire'] = 7*24*3600;
			session($session_config);
			$res[0]['access_url'] = ($res[0]['access_url'] != null)?$res[0]['access_url']:'';
			$res[0]['resource_path'] = ($res[0]['resource_path'] != null)?$res[0]['resource_path']:'';
			$res[0]['path'] = ($res[0]['path'] != null)?$res[0]['path']:'';
			
			$ret['PHPSESSID'] = session_id();
			session('userinfo.user_id',$res[0]['user_id']);
			session('userinfo.name',$res[0]['name']);
			session('userinfo.sex', $res[0]['sex']);
//			session('userinfo.access_url', $res[0]['access_url']);
//			session('userinfo.resource_path', $res[0]['resource_path']);
//			session('userinfo.path', $res[0]['path']);
			
			$ret = array_merge( $ret, $res[0] );
//			$expire = ($is_rember == 1)?7*24*3600:2*3600;
//			cache( $result['PHPSESSID'], $res[0], $expire );
//			dump($res[0]);
//			dump(cache($res[0]['user_id']));
//			$ret = $user_tim->gen_sig($res[0]['user_id']);
//			cookie( 'sig', $ret['sig'], ['prefix' => 'think_', 'expire' => 179*24*3600]);
		}else{
			$ret['msg'] = '用户名或密码错误';
		}
		return json_encode($ret);
	}
	
	public function user_logout(){

		$ret = [
			'r' => 0,
			'msg' => 'out success',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
//		cache(session('userinfo.user_id'), NULL);
		session('userinfo', NULL);
		return json_encode($ret);
	}
	public function change_user_pass(){
		$ret = [
			'r' => 0,
			'msg' => '密码修改成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$oldpwd = trim(input('oldpwd'));
		if( $pwd == '' || $pwd != $repwd){
			$ret['r'] = -1;
			$ret['msg'] = '密码不能为空且两次输入一致';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
//		$user_id = input('user_id');
		$user = model('User');
		$res = $user -> checkPwdByUserid( $user_id, md5($oldpwd));
		if( count( $res) == 0 ){
			$ret['r'] = -2;
			$ret['msg'] = '输入原密码不正确';
			return json_encode( $ret);
			exit;
		}
		$user -> changeUserPass( $user_id, md5($pwd));
		return json_encode( $ret);
	}
	public function change_user_pass_bycode(){
		$ret = [
			'r' => 0,
			'msg' => '密码修改成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$pwd = input('pwd');
		$repwd = input('repwd');
		if( $pwd == '' || $pwd != $repwd){
			$ret['r'] = -2;
			$ret['msg'] = '密码不能为空或两次输入不一致';
			return json_encode($ret);
			exit;
		}
		$mobile = input('mobile');
		$user_id = input('user_id');
		$username = input('username');
		$enc_token = md5( substr(md5(input('user_id')),-8).substr(md5(input('username')),0,8).substr(md5(input('mobile')),-8) );
		if( $user_id > 0 && strlen( $username) > 0 && strlen( $mobile) > 0 && $enc_token == input('enc_token')){
			$user = model('User');
			$user -> changeUserPass( $user_id, md5($pwd));
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '校验失败';
		}
		return json_encode($ret);
	}
	public function check_username_mobile(){
		$ret = [
			'r' => 0,
			'msg' => '验证信息存在',
			'mobile' => '',
			'user_id' => '',
			'username' => '',
			'enc_token' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$unm = input('unm');
		if( $unm == ''){
			$ret['r'] = -1;
			$ret['msg'] = 'unm不能为空';
			return json_encode($ret);
			exit;
		}
		$user = model('User');
		$res = $user -> checkUsernameMobile( $unm);
		if( count( $res) > 0){
			$ret['mobile'] = $res[0]['mobile'];
			$ret['user_id'] = $res[0]['user_id'];
			$ret['username'] = $res[0]['username'];
			$ret['enc_token'] = md5( substr(md5($ret['user_id']),-8).substr(md5($ret['username']),0,8).substr(md5($ret['mobile']),-8) );
		}else{
			$ret['r'] = -2;
			$ret['msg'] = '用户名或手机号不存在';
		}
//		dump($ret);
		return json_encode($ret);
	}
	
	public function change_user_status(){

		$str = input('str');
		$user_id = input('id');
		if($str == '' || $user_id){
			echo '<script>alert("错误的链接地址！");</script>';
			exit;
		}
		$user = model('User');
		$user->save( ['status' => 1],['user_id' => $user_id]);
	}
	
	public function invite_member_send_email(){
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
		}else{
			$user_id = session('userinfo')['user_id'];
			$username = session('userinfo')['name'];
		}
//		$user_id = 3;//text
		$project_id = input('project_id');
		$to_email = input('to_email');
		$pattern_email="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
		if( !preg_match( $pattern_email, $to_email) || $project_id <= 0){
		    $ret['r'] = -1;
			$ret['msg'] = '参数错误';
			return json_encode($ret);
			exit;
		}
		$user_project_tag = model('UserProjectTag');
		$user_info = model('UserInfo');
		$project = model('Project');
		$user_tag = model('UserTag');
		
		$member_num = $user_project_tag -> getPartInfoByProjectId( $project_id );
		
		$pinfo = $project -> getProjectPartInfo();
//		dump($pinfo);
		$uinfo = $user_info -> get_user_detail_by_id( $user_id);
		$username = isset($uinfo[0]['name'])?$uinfo[0]['name']:'';
//		dump($uinfo);
		$tags = $user_tag -> get_tag_by_userid($user_id, 10);
//		dump($tags);
		
		$str = '<style>
			*{
				margin: 0;padding: 0;
			}
			.clear::after{
				clear: both;
				display: block;
				content: "";
			}
			a{
				text-decoration: none;
			}
			body{
				background-color: #e5e5e5;
			}
			.wrap{
				width: 100%;
				margin: 0 auto;
				background-color: #e5e5e5;
			}
			.content{
				width: 1000px;
				margin: 5px auto;
				padding-top: 90px;
				text-align: center;
				background-color: white;
			}
			.hr{
				width: 1000px;
				margin-top: 75px;
				margin-bottom: 55px;
				border-bottom: 5px solid #e5e5e5;
			}
			.friends{
				font-size: 24px;
			}
			.userName{
				color: #00a0ff;
			}
			.userMsg{
				margin-top: 45px;
				margin-left: 323px;
			}
			.userImg{
				float: left;
			}
			.userImg img{
				width: 100px;height: 100px;
				border-radius: 50%;
				border: 7px solid #eeeeee;
			}
			.userIntro{
				float: left;
				margin-left: 25px;
			}
			.userIntroName{
				font-size: 30px;
				font-weight:700;
				text-align: left;
			}
			.divHr{
				width: 420px;
				border-bottom:1px solid #e8eaec;
				margin-top:10px;margin-bottom: 10px;
			}
			.userIntroPost{
				color: #a8a8a8;
				font-size: 16px;
				text-align: left;
			}
			.projectImg{
				width: 340px;height: 390px;
				text-align: center;
				margin: 55px auto;
			}
			.projectImg img{
				width: 340px;height: 390px;
			}
			.projectMsg{
				font-size: 22px;
				margin-bottom: 25px;
			}
			.prohr{
				width: 1000px;
				margin-bottom: 65px;
				border-bottom: 5px solid #e5e5e5;
			}
			.proMar{
				font-size: 22px;
				margin-bottom: 55px;
			}
			button{
				width: 322px;
				height: 56px;
				border: 0;
				outline: none;
				border-radius: 28px;
				margin-bottom: 82px;
				color: white;
				cursor: pointer;
				font-size: 26px;
				line-height: 56px;
				background-image: -webkit-linear-gradient(to top, #e61a62, #e93b38);
				background-image: linear-gradient(to top, #e61a62, #e93b38);
			}
			button a{
				color: white;
				width: 322px;
				height: 56px;
				border-radius: 28px;
				text-decoration: none;
			}
		</style>';
		$str = $str . '<div class="wrap">
						<div class="content">
							<img src="http://shining-1253556758.costj.myqcloud.com/common/logo.png" />
							<div class="hr"></div>
							<p class="friends">你的伙伴<span class="userName">'.$username.'</span>，邀请你加入"<span class="projectName">'.(isset($pinfo[0]['name'])?$pinfo[0]['name']:'').'</span>"项目组。</p>
							<div class="userMsg clear">
								<div class="userImg">
									<img src="'.(isset($uinfo[0]['access_url'])?$uinfo[0]['access_url']:'').'" />
								</div>
								<div class="userIntro">
									<p class="userIntroName">'.$username.'</p>
									<div class="divHr"></div>
									<p class="userIntroPost">'.(isset($tags[0]['name'])?$tags[0]['name']:'').'</p>
									<p class="userIntroPost">'.(isset($uinfo[0]['curr_company'])?$uinfo[0]['curr_company']:'').'</p>
								</div>
							</div>
							<div class="projectImg">
								<img src="'.(isset($pinfo[0]['access_url'])?$pinfo[0]['access_url']:'').'" />
							</div>
							<p class="projectMsg">项目组目前已经有<span class="userName">'.(isset($member_num[0]['member_num'])?$member_num[0]['member_num']:0).'</span>位成员。</p>
							<p class="proMar">在这里，你可以分享你的一切精彩内容！马上加入吧？</p>
							<div class="prohr"></div>
							<button><a id="pro_link" href="http://123.206.33.53/project/show?project_id='.$project_id.'" style="text-decoration:none" target="_blank">马上加入</a></button>
						</div>
					</div>';
        $subject='邀请';
        $content = $str;
//      $content='恭喜你，邮件发送成功。 <a href="'.url('home/index/change_user_status',['id'=>$user_id]).'">点此链接激活账号</a>';
//      $content=$content.' <img src="http://shining-1253556758.file.myqcloud.com/3/d710ecaede4674351c2d3702a61f5f60.png">';
        send_mail( $to_email, $username, $subject, $content);
        
        return json_encode( $ret);
    }
	
	
}

?>