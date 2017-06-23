<?php
namespace app\index\model;
use think\Model;
use think\Db;

class Admin_user extends Model
{
	protected $table = 'admin_user';
	
	public function check_username(){
		$username = input('username');
		$res = Db::query('select id 
    							from admin_user
    							where username=:username',
    							['username'=>$username]);
    	return count($res);
	}
	public function admin_list(){
		return Db::query('select id,username,sex,fullname,level,birthday,create_time,status from admin_user');
	}
	public function get_info_by_id(){
		$id = input('id');
		return Db::query('select username,sex,fullname,birthday,status from admin_user where id=:id',['id'=>$id]);
	}
}


?>