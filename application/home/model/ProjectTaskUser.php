<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectTaskUser extends Model{
	
	protected $table = 'project_task_user';
	public function getPartTaskList( $project_ids_str){
		
		$sql = 'SELECT ptu.project_id,ptu.task_id,t.title,s.src_id,s.type src_type,s.access_url origin_access_url
				FROM project_task_user AS ptu JOIN task AS t ON ptu.task_id = t.task_id
											  JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
											  JOIN src AS s ON sr.src_id = s.src_id && s.type = 1
				WHERE ptu.project_id in ('.$project_ids_str.')';
				
		$res = Db::query($sql);
		return $res;
	}
	public function getPartTaskImgList( $project_ids_str){
		
		$sql = 'SELECT ptu.project_id,ptu.task_id,s.access_url taccess_url
				FROM project_task_user ptu JOIN task t ON ptu.task_id = t.task_id
										   JOIN src_relation sr ON t.task_id = sr.relation_id && sr.type = 2
										   JOIN src s ON sr.src_id = s.src_id && s.type = 1
				WHERE ptu.project_id in ('.$project_ids_str.')';
		$res = Db::query($sql);
		return $res;
	}
	public function get_task_src_comment_by_project_id(){
		$from  = (input('from'))?intval(input('from')):0;
		$page_size = ( input('page_size'))?intval(input('page_size')):10;
		$project_id = input('project_id');
		
		$sql = 'SELECT ptu.user_id,ptu.task_id,t.title,t.description,t.status,t.praise_num,t.create_time,t.task_order
				FROM project_task_user ptu LEFT JOIN task t ON ptu.task_id = t.task_id
				WHERE ptu.project_id = :project_id
					ORDER BY t.create_time DESC,t.task_order ASC LIMIT '.$from.','.$page_size;
		
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	
	public function deleteByTaskid( $task_ids){
		
		$sql = 'DELETE ptu,t
				FROM project_task_user AS ptu LEFT JOIN task AS t ON ptu.task_id = t.task_id
				WHERE ptu.task_id in ('. $task_ids. ')';
		$res = Db::query( $sql);
		
		return $res;
	}
	public function getDeleteMemberSrc(){
		$sql = 'SELECT s.src_id,s.resource_path,s.type
				FROM project_task_user AS ptu LEFT JOIN task AS t ON ptu.task_id = t.task_id
					LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
					LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE ptu.project_id = :project_id && ptu.user_id = :user_id';
				
		$res = Db::query( $sql, ['project_id' => input('project_id'), 'user_id'=> input('user_id')] );
		return $res;
	}
	public function deleteMemberFromTask(){
		
		$sql = 'DELETE ptu,t,sr,s
				FROM project_task_user AS ptu LEFT JOIN task AS t ON ptu.task_id = t.task_id
					LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
					LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE ptu.project_id = :project_id && ptu.user_id = :user_id';
				
		$res = Db::query( $sql, ['project_id' => input('project_id'), 'user_id'=> input('user_id')] );
		return $res;
	}
	public function getMyUploadSrcs( $user_id, $type, $from, $page_size){
		
		$sql = 'SELECT ptu.project_id,p.name project_name,ptu.task_id,s.access_url taccess_url,s.src_name tsrc_name,ptu.create_time
				FROM project_task_user AS ptu INNER JOIN project AS p ON ptu.project_id = p.project_id && p.status = 0
											  LEFT JOIN src_relation AS sr ON sr.relation_id = ptu.task_id && sr.type = 2
											  LEFT JOIN src AS s ON sr.src_id = s.src_id 
				WHERE ptu.user_id = :user_id && s.type = :type
					ORDER BY task_id DESC LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql, ['user_id' => $user_id,'type' => $type]);
		
		return $res;
	}
	public function searchMyUploadByFilename($user_id, $filename, $sortord, $from, $page_size){
		switch($sortord){
			case 1:
				$order = 'ptu.create_time DESC ';
				break;
			case 2:
				$order = 'ptu.create_time ASC ';
				break;
			case 3:
				$order = 's.type ASC ';
				break;
			default:
				$order = 'ptu.create_time DESC';
				break;
		}
		$sql = 'SELECT ptu.project_id,p.name project_name,ptu.task_id,s.access_url taccess_url,s.src_name tsrc_name,ptu.create_time,s.type
				FROM project_task_user AS ptu INNER JOIN project AS p ON ptu.project_id = p.project_id && p.status = 0
											  LEFT JOIN src_relation AS sr ON sr.relation_id = ptu.task_id && sr.type = 2
											  LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE ptu.user_id = :user_id && s.src_name like "%'. $filename .'%"
						ORDER BY '.$order.' limit '.$from.','.$page_size;
		$res = Db::query( $sql,['user_id' => $user_id]);
		return $res;
	}
	
	
}


?>