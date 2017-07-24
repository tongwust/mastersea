<?php
namespace app\home\model;
use think\Model;
use think\Db;


class Collect extends Model{
	
	protected $table = 'collect';
	
	public function getTasksCollectNum( $task_ids){
		
		$sql = 'SELECT cid as task_id,count(user_id) AS collect_num
				FROM collect
				WHERE type = 2 && cid in('.$task_ids.')
				GROUP BY cid';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getProjectAttenNum( $cid, $type){
		
		$sql = 'SELECT DISTINCT(user_id)
				FROM collect
				WHERE cid = :cid && type = :type';
		$res = Db::query( $sql, ['cid' => $cid,'type' => $type]);
		
		return $res;
	}
	
	public function getCollectId( $cid, $user_id, $type){
		$sql = 'SELECT collect_id
				FROM collect
				WHERE cid = :cid && user_id = :user_id && type = :type
					LIMIT 1';
		$res = Db::query( $sql, ['cid'=>$cid,'user_id'=>$user_id,'type'=>$type]);
		
		return $res;
	}
	
	public function get_user_collect( $user_id, $cids, $type){
		$sql = 'SELECT cid,collect_id
				FROM collect
				WHERE user_id = :user_id && type = :type && cid in('.$cids.')';
		$res = Db::query( $sql, ['user_id'=>$user_id,'type'=>$type]);
		
		return $res;
	}
	public function getProjectCollectUsers($project_ids_str){
		$sql = 'SELECT c.cid as project_id,c.user_id,u.name username,ui.curr_company,s.src_id user_src_id,s.access_url user_access_url
				FROM collect AS c LEFT JOIN user_info AS ui ON c.user_id = ui.user_id
											 LEFT JOIN user AS u ON ui.user_id = u.user_id
											 LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
											 LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE c.type = 1 && c.cid in ('.$project_ids_str.')
					ORDER BY c.cid DESC';
				
		$res = Db::query( $sql);
		
		return $res;
	}
	public function getMyCollectProjectList($user_id){
		$from = (input('from'))?intval(input('from')):0;
		$page_size = (input('page_size'))?intval(input('page_size')):10;
		
		$sql = 'SELECT c.cid as project_id,p.name project_name,p.intro,p.project_start_time,p.project_end_time,p.praise_num,p.collect_num,
					   s.src_id project_src_id,s.access_url project_access_url
				FROM collect AS c INNER JOIN project AS p ON c.cid = p.project_id && p.status != -1
								  LEFT JOIN src_relation AS sr ON sr.relation_id = p.project_id && sr.type = 1
								  LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
				WHERE c.user_id = :user_id && c.type = 1
					ORDER BY c.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql,['user_id'=>$user_id] );
		
		return $res;
	}
	public function myCollectProjectTaskList( $user_id, $from, $page_size){
		$sql = 'SELECT c.cid as project_id,t.task_id,t.title,t.description,t.praise_num,t.collect_num,t.create_time,
					   s.src_id,s.src_name,s.src_order,s.type src_type,s.path,s.access_url,s.resource_path,CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_865x579.",SUBSTRING_INDEX(s.access_url,".",-1)) AS origin_access_url
				FROM collect AS c LEFT JOIN project AS p ON c.cid = p.project_id && p.status != -1
					LEFT JOIN project_task_user AS ptu ON ptu.project_id = p.project_id
					LEFT JOIN task AS t ON ptu.task_id = t.task_id && t.status != -1
					LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
					LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE c.user_id = :user_id && c.type = 1
						LIMIT :from,:page_size';
		$res = Db::query( $sql, [ 'user_id' => $user_id, 'from'=> $from, 'page_size' => $page_size]);
		
		return $res;
	}
//	public function add_collect(){
//		
//		$sql = 'INSERT INTO collect( cid, type, user_id) VALUES( :cid, :type, :user_id)';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]);
//		
//		return $res;
//	}
//
//	public function del_collect(){
//		
//		$sql = 'DELETE FROM collect WHERE cid = :cid && user_id = :user_id && type = :type';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'user_id' => input('user_id'), 'type' => input('type')]);
//		
//		return $res;
//	}
}

?>