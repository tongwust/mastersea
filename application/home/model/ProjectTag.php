<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectTag extends Model{
	protected $table = 'project_tag';
	
	public function get_tag_by_project_id(){
		$project_id = input('project_id');
		
		$sql = 'SELECT ti.tag_id,ti.name as tag_name
				FROM project_tag AS pt LEFT JOIN tag_info AS ti ON pt.tag_id = ti.tag_id
				WHERE pt.project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	public function getProjectTags( $project_ids_str){
		$sql = 'SELECT pt.project_id,ti.tag_id,ti.name
				FROM project_tag AS pt LEFT JOIN tag_info AS ti ON pt.tag_id = ti.tag_id
				WHERE pt.project_id in ('.$project_ids_str.')';
		$res = Db::query( $sql);
		return $res;
	}
	
	public function delete_project_tag($project_id, $themeid, $level){
		$sql = 'DELETE pt 
				FROM project_tag AS pt 
					INNER JOIN tag AS t ON pt.tag_id = t.tag_id
				WHERE pt.project_id = :project_id && t.themeid = :themeid && t.level = :level';
		$res = Db::query($sql , ['project_id'=> $project_id, 'themeid' => $themeid, 'level' => $level]);
		
		return $res;
	}
}

?>