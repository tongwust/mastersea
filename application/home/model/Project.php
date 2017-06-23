<?php
namespace app\home\model;
use think\Model;
use think\Db;


class Project extends Model{
	protected $table = 'project';
	
	public function getProjectPartInfo(){
		$sql = 'SELECT p.project_id,p.name,s.src_id,s.access_url
				FROM project AS p LEFT JOIN src_relation AS sr ON  p.project_id = sr.relation_id && sr.type = 1
					LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
				WHERE p.project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => input('project_id')]);
		
		return $res;
	}
	public function updateProjectById(){
 		$sql = 'UPDATE project 
 				SET name = :name,en_name = :en_name,cat_name = :cat_name,intro = :intro,
 					project_start_time = :project_start_time,project_end_time = :project_end_time
 				WHERE project_id = :project_id';
 		$res = Db::query($sql, ['project_id'=>input('project_id'),'name'=>input('name'),'en_name'=>input('en_name'),'cat_name'=>input('cat_name'),'intro'=>input('intro'),'project_start_time'=>input('project_start_time'),'project_end_time'=>input('project_end_time')]);
		
		return $res;
	}
	public function get_project_by_id(){
		
		$project_id = input('project_id');
		$sql = 'SELECT * FROM project WHERE project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		
		return $res;
	}
	public function getSearchKeyByProjectId($project_id){
//		$project_id = input('project_id');
		$sql = 'SELECT project_id,name,en_name,cat_name,address,intro,praise_num,collect_num,create_time
				FROM project
				WHERE project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => $project_id ]);
		
		return $res;
	}
	public function get_latest_hot_project(){
		$from = empty(input('from'))?0:input('from');
		$page_size = empty(input('page_size'))?10:input('page_size');
		
		$sql = 'SELECT p.project_id,p.name,p.type,p.status,p.praise_num,p.collect_num,p.intro,s.src_name as project_img,s.path as project_path,s.access_url as project_access_url
				FROM project AS p LEFT JOIN src_relation sr ON p.project_id = sr.relation_id && sr.type = 1
					 LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 3
				WHERE p.status != -1
				ORDER BY p.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getAllProjectList(){
		
		$sql = 'SELECT project_id,name,en_name,cat_name,address,intro,praise_num,collect_num,create_time
				FROM project';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getSearchProjects( $project_ids_str ){
		$from = empty(input('from'))?0:input('from');
		$page_size = empty(input('page_size'))?10:input('page_size');
		
		$sql = 'SELECT p.project_id,p.name,p.type,p.status,p.praise_num,p.collect_num,p.intro,s.src_name as project_img,s.path as project_path,s.access_url project_access_url
				FROM project AS p LEFT JOIN src_relation sr ON p.project_id = sr.relation_id && sr.type = 1
					 LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 3
				WHERE p.status != -1 && p.project_id in ('.$project_ids_str.') LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql );
		return $res;
	}
	public function changeProjectStatus(){
		$sql = 'UPDATE project
				SET status = :status
				WHERE project_id = :project_id';
				
		$res = Db::query($sql, ['project_id' => input('project_id'), 'status' => input('status')]);
		return $res;		
	}
//	public function updatePraiseNum( $opt ){
//		
////		$sql = 'UPDATE project 
////				SET praise_num = praise_num + 1
////				WHERE project_id = :project_id';
////		$res = Db::query( $sql, ['project_id' => input('cid')]);
//		$res = 0;
//		if( $opt == 1 ){
//			$res = Db::table('project')->where('project_id', input('cid'))->setInc('praise_num');
//		}else if( $opt == 2){
//			$res = Db::table('project')->where('project_id', input('cid'))-where('praise_num', '>', 0)->setDec('praise_num');
//		}
//		return $res;
//	}
//	public function updateIncCollectNum(){
//		$res = 0;
//		if( $opt == 1){
//			$res = Db::table('project')->where('project_id', input('cid'))->setInc('collect_num');
//		}else if( $opt == 2){
//			$res = Db::table('project')->where('project_id', input('cid'))->where('collect_num', '>', 0)->setDec('collect_num');
//		}
//		return $res;
//	}
}

?>