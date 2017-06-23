<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Comment extends Model{
	
	protected $table = 'comment';
	
	public function get_task_comment_by_task_ids( $task_ids, $type){
		
		$sql = 'SELECT c.*,u.name as username,s.src_name,s.path,s.access_url
				FROM comment AS c LEFT JOIN user AS u ON c.user_id = u.user_id
								  LEFT JOIN src_relation AS sr ON u.user_id = sr.relation_id && sr.type = 3
								  LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
				WHERE c.type = '.$type.' && c.cid IN ('.$task_ids.')
				ORDER BY c.cid ASC,c.create_time ASC';
		$res = Db::query( $sql );
		return $res;
	}
	
}
?>