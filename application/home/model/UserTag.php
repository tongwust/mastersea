<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserTag extends Model{
	
	protected $table = 'user_tag';
	
	public function getTagsByUserIds($user_ids_str){
		$sql = 'SELECT ut.user_id,ut.tag_id,ti.name tag_name
				FROM user_tag AS ut LEFT JOIN tag_info AS ti ON ut.tag_id = ti.tag_id
				WHERE ut.user_id in ('.$user_ids_str.')
					ORDER BY ut.user_id ASC';
		$res = Db::query( $sql );
		
		return $res;
	}
	
	public function get_address_position_skill_interest_by_userid(){
		$user_id = input('user_id');
		$sql = 'SELECT ut.tag_id,t.themeid,t.pid,ti.name
				FROM user_tag AS ut LEFT JOIN tag AS t ON ut.tag_id = t.tag_id
					LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE ut.user_id = :user_id';
		$res = Db::query($sql, ['user_id' => $user_id]);
		return $res;
	}
	
	public function delete_user_tag($user_id, $themeid){
		
		$sql = 'DELETE ut 
				FROM user_tag AS ut 
						INNER JOIN tag AS t ON ut.tag_id = t.tag_id
				WHERE ut.user_id = :user_id && t.themeid = :themeid';
		$res = Db::query($sql , ['user_id'=> $user_id, 'themeid' => $themeid]);
		
		return $res;
	}
	
	public function get_tag_by_userid($user_id, $themeid){
		$sql = 'SELECT ti.tag_id,ti.name as tag_name
				FROM user_tag AS ut INNER JOIN tag AS t ON ut.tag_id = t.tag_id && t.themeid = '.$themeid.' 
									LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE ut.user_id = '.$user_id;
		
		$res = Db::query( $sql);
		return $res;
	}
	public function hotTags( $themeid, $page_size){
		
		$sql = 'SELECT count(ut.tag_id) as num,ti.tag_id,ti.name
				FROM user_tag AS ut INNER JOIN tag AS t ON ut.tag_id = t.tag_id && t.themeid = :themeid
									LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				GROUP BY ut.tag_id ORDER BY num DESC LIMIT '.$page_size;
		$res = Db::query( $sql, ['themeid' => $themeid]);
		
		return $res;
	}
//	public function get_position_tag_by_userid($user_id, $pid, $themeid){
//		
//		$sql = 'SELECT ti.tag_id,ti.name
//				FROM user_tag ut INNER JOIN tag t ON ut.tag_id = t.tag_id 
//								 LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
//				WHERE ut.user_id = :user_id && t.themeid = :themeid && t.pid IN (SELECT tag_id as pid FROM tag WHERE pid = :pid && themeid = :themeid)';
//		
//		$res = Db::query( $sql, ['user_id' => $user_id, 'pid' => $pid, 'themeid' => $themeid]);
//		return $res;
//	}
	
}



?>