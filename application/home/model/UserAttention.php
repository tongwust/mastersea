<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserAttention extends Model{
	
	protected $table = 'user_attention';

	public function getAttenUserinfo(){
		$follow_user_id = input('user_id');
		$sql = 'SELECT ua.user_id,u.name as username,ui.en_name,ui.fullname,s.src_id head_src_id,s.src_name head_src_name,s.path head_path,
						s.resource_path head_resource_path,s.access_url head_access_url,s.source_url head_source_url,s.url as head_url
				FROM user_attention as ua LEFT JOIN user as u ON ua.user_id = u.user_id
					LEFT JOIN user_info as ui ON u.user_id = ui.user_id
					LEFT JOIN src_relation as sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src as s ON sr.src_id = s.src_id
				WHERE ua.follow_user_id = :follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id ]);
		
		return $res;
	}
	
	public function getAttenMeUserList( $user_id){
		$from = input('from')?intval(input('from')):0;
		$page_size = input('page_size')?intval(input('page_size')):35;
		
		$sql = 'SELECT DISTINCT(ua.user_id),u.name user_name,s.src_id user_src_id,s.access_url user_access_url
				FROM user_attention AS ua LEFT JOIN user AS u ON ua.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE ua.follow_user_id = :follow_user_id
					LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['follow_user_id' => $user_id]);
		
		return $res;
	}
	public function getMyAttenUserList( $user_id){
		$from = input('from')?intval(input('from')):0;
		$page_size = input('page_size')?intval(input('page_size')):35;
		
		$sql = 'SELECT ua.follow_user_id user_id,u.name user_name,s.src_id user_src_id,s.access_url user_access_url
				FROM user_attention AS ua LEFT JOIN user AS u ON ua.follow_user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE ua.user_id = :user_id
					LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['user_id' => $user_id]);
		
		return $res;
	}
	public function get_follow_users_by_id($follow_user_id){
		
		$sql = 'SELECT user_id
				FROM user_attention
				WHERE follow_user_id=:follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id]);
		
		return $res;
	}
	public function getUserAttenId($user_id, $follow_user_id, $relation_type){
		$sql = 'SELECT user_attention_id
				FROM user_attention
				WHERE user_id = :user_id && follow_user_id = :follow_user_id && relation_type = :relation_type';
		$res = Db::query($sql, ['user_id'=>$user_id, 'follow_user_id'=> $follow_user_id,'relation_type' => $relation_type]);
		
		return $res;
	}
	public function getMyAttenUsersByUserId($user_id){
		$sql = 'SELECT follow_user_id
				FROM user_attention
				WHERE user_id = :user_id';
		$res = Db::query( $sql, ['user_id' => $user_id]);
		
		return $res;
	}
	public function getAttenNumByUserids( $user_ids_str ){
		
		$sql = 'SELECT follow_user_id,count(user_id) as atten_num
				FROM user_attention
				WHERE follow_user_id in ('.$user_ids_str.') GROUP BY follow_user_id';
		
		$res = Db::query( $sql);
		return $res;
	}
	public function getWeAttenUsers($user_ids_str){
		$sql = 'SELECT user_id,follow_user_id
				FROM user_attention
				WHERE user_id in ('.$user_ids_str.') ORDER BY user_id ASC';
		$res = Db::query($sql);
		
		return $res;		
	}
	public function getMyFriends( $follow_user_id){
		$sql = 'SELECT a.user_id,u.name username
				FROM user_attention a INNER JOIN user_attention b ON a.user_id = b.follow_user_id && b.user_id = a.follow_user_id
									  LEFT JOIN user u ON a.user_id = u.user_id && u.status = 1
				WHERE a.follow_user_id = :follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id]);
		
		return $res;
	}
	public function getMyFriendList( $follow_user_id ){
		$sql = 'SELECT a.user_id,u.name,s.src_id,s.access_url
				FROM user_attention a INNER JOIN user_attention b ON a.user_id = b.follow_user_id && b.user_id = a.follow_user_id
									  LEFT JOIN user u ON a.user_id = u.user_id && u.status = 1
									  LEFT JOIN src_relation sr ON u.user_id = sr.relation_id && sr.type = 3
									  LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 2
				WHERE a.follow_user_id = :follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id]);
		
		return $res;
	}
	
}

?>