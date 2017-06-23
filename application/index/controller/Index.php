<?php
namespace app\index\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Index extends Controller
{
    public function index(){
    	$view = new View();
    	return $view->fetch('./login',['title'=>'后台管理系统']);
    }
    public function index_index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function login(){
    	$username = input('username');
    	$pwd = input('pwd');
    	if($username != '' && $pwd != ''){
    		$res = Db::query('select id,status 
    							from admin_user
    							where username=:username and pwd=:pwd',
    							['username'=>$username,'pwd'=>md5($pwd)]);
    		if( count($res) > 0){
    			if($res[0]['status'] == 1){
    				Session::set('id',$res[0]['id']);
	    			Session::set('username',$username);
	    			$this->redirect('index/index_index');
    			}else{
    				$this->error('此账号已经被禁用！');
    			}
    		}
 			else{
    			$this->error('用户名或密码不正确！');
    		}
    	}
    	else{
    		$this->error('用户名或密码不正确！');
    	}
    }
    
}
