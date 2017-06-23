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
}

?>