<?php
namespace app\index\controller;
use think\Controller;
use think\View;
use think\Request;
use app\index\model\Admin_user;


class Admin extends Controller
{
    public function index(){
    	$admin = new Admin_user;
    	$view = new View();
    	$arr = $admin->admin_list();
    	return $view->fetch('./admin/admin-list',['arr'=>$arr]);
    }
    public function get_admin_list(){
    	$result = [
			'r' => 0,
			'msg' => '',
			"result"=>'',
		];
    	$admin = new Admin_user;
    	$arr = $admin->admin_list();
    	$aoData = input('aoData');
    	$aoData = json_decode($aoData);
    	$iDisplayLength = 10; // 每页显示的数量
	    $iDisplayStart = 0; // 从哪一个开始显示
//	    $iSortCol_0 = 0;// order by 哪一列
//	    $sSortDir_0 = "asc"; 
	    $sEcho = 1;
	    foreach($arr as $k => $v){
	    	$arr[$k]['birthday'] = date('Y-m-d', $v['birthday']);
	    	$arr[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
    	}
//	    $sSearch = ''; // 搜索的内容，可结合MySQL中的like关键字实现搜索功能
	    foreach($aoData as $item) { 
	        if ($item -> name  == "iDisplayLength") {
	            $iDisplayLength = $item -> value;
	        }
	        if ($item -> name  == "iDisplayStart") {
	            $iDisplayStart = $item -> value;
	        }
//	        if ($item -> name  == "sSearch") {
//	            $sSearch = $item -> value;
//	        }
	        if($item -> name  == "sEcho") {
				$sEcho = $item -> value;
			}
    	}
    	
    	$count = count($arr);
    	$arrays['aaData'] = $arr;
	    $arrays['iTotalRecords'] = $count;
	    $arrays['iTotalDisplayRecords'] = $count;
	    $arrays['sEcho'] = $sEcho + 1;
	    $result['result'] = $arrays;
	    return json($result);
    }
    public function admin_add(){
    	$view = new View();
    	return $view->fetch('./admin/admin-add',['title'=>'后台管理系统']);
    }
    public function admin_edit(){
    	$view = new View();
    	return $view->fetch('./admin/admin-edit',['title'=>'编辑后台用户']);
    }
    public function get_admin_by_id(){
    	$id = input('id');
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$view = new View();
		if($id > 0){
			$admin = new Admin_user;
			$res = $admin->get_info_by_id();
			if(count($res) > 0){
				$result['r'] = 0;
				$result['msg'] = '查询成功';
				$result['username'] = $res[0]['username'];
				$result['sex'] = $res[0]['sex'];
				$result['fullname'] = $res[0]['fullname'];
				$result['birthday'] = $res[0]['birthday'];
				$result['status'] = $res[0]['status'];
				return $view->fetch('./admin/admin-edit',$res[0]);
				return json($result);exit;
			}else{
				$result['r'] = -2;
				$result['msg'] = '查询结果为空';
				return json($result);exit;
			}
		}else{
			$result['msg'] = '用户id不符合规则';
			return json($result);
			exit;
		}
    }
	public function add(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$admin = new Admin_user;
		if($admin->check_username() > 0){
			$result['r'] = -2;
			$result['msg'] = '用户名已存在';
			return json($result);
			exit;
		}
		$admin->username = input('username');
		$admin->pwd = md5(input('password'));
		$admin->sex = input('sex');
		$admin->fullname = input('fullname');
		$admin->status = input('status');
		$admin->level = input('level');
		$admin->birthday = input('birthday');
		$admin->create_time = time();
		if($admin->save()){
			$result['r'] = 0;
			$result['msg'] = '添加成功';
			return json($result);
		}else{
			$result['msg'] = '添加失败';
			return json($result);
		}
		
	}
}
?>