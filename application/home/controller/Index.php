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
		$user = model('User');
		if($user->check_name() > 0){
			$ret['r'] = 0;
			$ret['msg'] = '用户名已存在';
		}else{
			$ret['msg'] = '用户名可用';
		}
		return json_encode($ret);
		exit;
    }
    public function send_msg(){
    	header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
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
				return json_encode($ret);
				exit;
			}else{
				$ret['msg'] = '发送短信失败';
				return json_encode($ret);
				exit;
			}
		}catch(\Exception $e){
			$ret['msg'] = '发送短信出错'.$e;
			return json_encode($ret);
			exit;
		}
    }
    public function check_code(){
    	header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
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
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
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
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$mobile = trim(input('mobile'));
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
		if($user->check_name() > 0){
			$ret['r'] = -2;
			$ret['msg'] = '用户名已存在';
			return json_encode($ret);
			exit;
		}
		$pattern_email="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $ret['r'] = -4;
			$ret['msg'] = '格式错误';
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
		
		$user_contact->contact = $mobile;
		$user_contact->type = 1;
		Db::startTrans();
		try{
			$user->save();
			$user_contact->user_id = $user->user_id;
			$user_info->user_id = $user->user_id;
			$user_contact->save();
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
						$tag_res = $tag -> tag_add2( 30, $skill[$i]['name'], '', 11, 2);//30 other skill
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
//			$interest_arr = explode(',', $interest_ids);
//			if($interest_ids != '' && count($interest_arr) > 0){
//				$interest_list = [];
//				for($i = 0; $i < count($interest_arr); $i++){
//					array_push($interest_list, ['user_id'=>$user->user_id,"tag_id"=>$interest_arr[$i]]);
//				}
//				$user_tag->save($interest_list);
//			}
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '添加成功！';
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -6;
			$ret['msg'] = '数据库错误!'.$e;
			exit;
		}
		return json_encode($ret);
		//$this->email($user->name,$contract->email,md5($user->name.$user->pwd.$user->user_id),$user->user_id);
	}
	public function user_login(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
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
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$is_rember = input('is_rember');
		if($name == '' || $pwd == ''){
			$ret['msg'] = '用户名或密码不能为空';
			return json_encode($ret);
			exit;
		}
		$res = $user->get_user_info_by_name_pwd();
		if(count($res) > 0){
			$ret['r'] = 0;
			$ret['msg'] = '登陆成功';
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
			
			$ret['PHPSESSID'] = session_id();
			session('userinfo.user_id',$res[0]['user_id']);
			session('userinfo.name',$res[0]['name']);
			session('userinfo.sex', $res[0]['sex']);
			session('userinfo.path', $res[0]['access_url']);
			session('userinfo.resource_path', $res[0]['resource_path']);
			session('userinfo.access_url', $res[0]['path']);
			
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
		header("Access-Control-Allow-Origin:*");
    	header("Access-Control-Allow-Method:POST,GET");
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
		cache(session('user.user_id'), NULL);
		session('user', NULL);
		$ret['r'] = 0;
		return json_encode($ret);
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
		$tags = $user_tag -> get_tag_by_userid($user_id, 10, 3);
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
			.wrap{
				width: 100%;
				margin: 0 auto;
			}
			.content{
				width: 1000px;
				margin: 115px auto 0;
				text-align: center;
			}
			.hr{
				width: 800px;
				margin-left: 100px;
				margin-top: 75px;
				margin-bottom: 55px;
				border-bottom: 1px solid #e8eaec;
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
				width: 800px;
				margin-bottom: 65px;
				margin-left: 100px;
				border-bottom: 1px solid #e8eaec;
			}
			.proMar{
				font-size: 22px;
				margin-bottom: 55px;
			}
			.btn{
				width: 322px;
				height: 56px;
				border-radius: 28px;
				margin-left: 339px;
				margin-bottom: 82px;
				color: white;
				font-size: 26px;
				line-height: 56px;
				background-image: -webkit-linear-gradient(to top, #e61a62, #e93b38);
				background-image: linear-gradient(to top, #e61a62, #e93b38);
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
							<div class="btn"><a id="pro_link" href="www.mastersea.com:8090/project/show?project_id='.$project_id.'" target="_blank">马上加入</a></div>
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