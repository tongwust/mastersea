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
	
	public function get_user_info_by_name_pwd(){
		$name = trim(input('name'));
		$pwd = md5(trim(input('pwd')));
		$sql = 'SELECT u.user_id,u.name,ui.sex,s.src_name,s.path,s.resource_path,s.access_url
				FROM user AS u LEFT JOIN user_info AS ui ON u.user_id = ui.user_id
						LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
				  		LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
				WHERE u.name=:name && u.pwd=:pwd && u.status=1';
		$res = Db::query( $sql, ['name'=>$name,'pwd'=>$pwd] );
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
	
	
}


?>