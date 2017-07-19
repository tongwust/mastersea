<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectTaskUser extends Model{
	
	protected $table = 'project_task_user';
	public function getPartTaskList( $project_ids_str){
		$from  = (input('from'))?intval(input('from')):0;
		$page_size = (input('page_size'))?intval(input('page_size')):10;
		
		$sql = 'SELECT ptu.project_id,ptu.task_id,t.title,s.src_id,s.access_url
				FROM project_task_user AS ptu LEFT JOIN task AS t ON ptu.task_id = t.task_id
											  LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
											  LEFT JOIN src AS s ON sr.src_id = s.src_id
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
	
	public function deleteByTaskid(){
		
		$sql = 'DELETE 
				FROM project_task_user 
				WHERE task_id = :task_id && project_id = :project_id';
		$res = Db::query( $sql, ['task_id' => input('task_id'),'project_id' => input('project_id') ]);
		return $res;
	}
	public function getDeleteMemberSrc(){
		$sql = 'SELECT s.src_id,s.resource_path
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
}


?>