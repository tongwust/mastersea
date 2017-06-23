<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectTask extends Model{
	
	protected $table = 'project_task';
	
	public function get_task_src_comment_by_project_id(){
		$from  = empty(input('from'))?0:input('from');
		$page_size = empty( input('page_size'))?10:input('page_size');
		$project_id = input('project_id');
		$sql = 'SELECT pt.task_id,t.title,t.description,t.status,t.praise_num,t.create_time
				FROM project_task pt LEFT JOIN task t ON pt.task_id = t.task_id
				WHERE pt.project_id = :project_id
					ORDER BY t.create_time DESC LIMIT '.$from.','.$page_size;
		
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	
	public function deleteByTaskid(){
		
		$sql = 'DELETE FROM project_task WHERE task_id = :task_id';
		$res = Db::query( $sql, ['task_id' => input('task_id') ]);
		return $res;
	}
}


?>