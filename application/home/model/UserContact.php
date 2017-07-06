<?php

namespace app\home\model;
use think\Model;
use think\Db;

class UserContact extends Model{
	
	protected $table = 'user_contact';
	
	public function get_user_contact_by_userid(){
		$user_id = input('user_id');
		$sql = 'SELECT * FROM user_contact WHERE user_id = :user_id ORDER BY type ASC';
		$res = Db::query($sql , ['user_id' => $user_id]);
		return $res;
		
	}
	public function del_user_contact_by_userid( $user_id){
		$sql = 'DELETE 
				FROM user_contact
				WHERE user_id = :user_id';
		$res = Db::query( $sql,['user_id' => $user_id]);
		
		return $res;
	}
}

?>