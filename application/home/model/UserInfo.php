<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserInfo extends Model{
	
	protected $table = 'user_info';
	
	public function get_user_detail_by_id( $user_id){
//		$user_id = input('user_id');
		
		$sql = 'SELECT u.user_id,u.name,u.status,
					   ui.sex,ui.birthday,ui.fullname,ui.en_name,ui.curr_company,ui.en_company,ui.short_name,ui.work_age,ui.education_school,
					   ui.history,ui.intro,ui.latest_update_time,ui.create_time,
					   s.src_id,s.resource_path,s.access_url
				FROM user AS u LEFT JOIN user_info AS ui ON u.user_id = ui.user_id
							   LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
							   LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE u.user_id=:user_id';
		$res = Db::query($sql,['user_id' => $user_id]);
		return $res;
	}
	
	
}
?>