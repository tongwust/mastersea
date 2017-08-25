<?php
namespace app\home\model;
use think\Model;
use think\Db;

class User extends Model{
	
	protected $table = 'user';
	
	public function check_name( $name){
		
		$res = Db::query('select user_id 
    							from user
    							where name=:name',
    							['name'=>$name]);
    	return $res;
	}
	
	public function get_user_info_by_name_pwd($name, $pwd){
		
		$sql = 'SELECT u.user_id,u.name,ui.sex,s.path,s.resource_path,s.access_url
				FROM user AS u INNER JOIN user_info AS ui ON u.user_id = ui.user_id
						LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
				  		LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
				WHERE u.name=:name && u.pwd=:pwd && u.status=1
						LIMIT 1';
		$res = Db::query( $sql, ['name'=>$name,'pwd'=>$pwd] );
		return $res;
	}
	public function get_user_info_by_moblie_pwd($mobile, $pwd){
		
		$sql = 'SELECT ut.user_id,u.name,ui.sex,s.path,s.resource_path,s.access_url
				FROM user_contact ut
						JOIN user u ON ut.user_id = u.user_id
						JOIN user_info ui ON u.user_id = ui.user_id
						LEFT JOIN src_relation sr ON ui.user_id = sr.relation_id && sr.type = 3
						LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 2
				WHERE ut.contact = :contact && ut.type = 1 && u.pwd = :pwd && u.status = 1
						LIMIT 1';
		$res = Db::query( $sql, ['contact' => $mobile,'pwd' => $pwd]);
		return $res;
	}
	public function check_name_pwd(){
		$name = trim(input('name'));
		$pwd = md5(trim(input('pwd')));
		$res = Db::query('select user_id,name
								from user
								where name=:name && pwd=:pwd && status=1',
								['name'=>$name,'pwd'=>$pwd]);
		
		return $res;
	}
	public function searchUserByName(){
		$sql = 'SELECT u.user_id,u.name,s.src_id,s.access_url
				FROM user u LEFT JOIN user_contact uc ON u.user_id = uc.user_id
							LEFT JOIN src_relation sr ON u.user_id = sr.relation_id && sr.type = 3
							LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 2
				WHERE u.status = 1 && (LOCATE(:name,u.name) > 0 || uc.contact = :contact)
					  GROUP BY u.user_id';
		$res = Db::query($sql, ['name' => input('name'),'contact' => input('name')]);
		return $res;
	}
	
	public function checkPwdByUserid( $user_id, $oldpwd){
		$sql = 'SELECT user_id
				FROM user
				WHERE user_id = :user_id && pwd = :pwd';
		$res = Db::query( $sql, ['user_id' => $user_id,'pwd' => $oldpwd]);
		return $res;
	}
	public function  changeUserPass( $user_id, $pwd){
		$sql = 'UPDATE user
				SET pwd = :pwd
				WHERE user_id = :user_id';
		$res = Db::execute( $sql,['user_id' => $user_id,'pwd' => $pwd]);
		return $res;
	}
	public function checkUsernameMobile( $unm){
		$sql = 'SELECT u.user_id,u.name username,uc.contact mobile
				FROM user u 
						JOIN user_contact uc ON u.user_id = uc.user_id
				WHERE u.name = :name || (uc.contact = :contact && uc.type = 1)';
		$res = Db::query( $sql, ['name'=> $unm,'contact' => $unm]);
		return $res;
	}
}


?>