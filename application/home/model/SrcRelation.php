<?php
namespace app\home\model;
use think\Model;
use think\Db;

class SrcRelation extends Model{
	
	protected $table = 'src_relation';
	
	public function get_task_src_by_task_ids( $task_ids, $type){
		
		$sql = 'SELECT sr.relation_id as task_id,s.src_id,s.src_name,s.src_order,s.type as src_type,s.status as src_status,s.path,s.access_url origin_access_url,s.resource_path,
					CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_865x579.",SUBSTRING_INDEX(s.access_url,".",-1)) access_url
				FROM src_relation sr LEFT JOIN src s ON sr.src_id = s.src_id && sr.type = '.$type.'
				WHERE (s.type = 1 || (s.type > 4 && s.type <= 8) ) && sr.relation_id in ('.$task_ids.')';
		$res = Db::query( $sql);
		
		return $res;
	}
	public function get_srcs_by_relation_ids( $relation_ids, $type){
		
		$sql = 'SELECT sr.relation_id,s.access_url
				FROM src_relation sr LEFT JOIN src s ON sr.src_id = s.src_id 
				WHERE sr.type = '.$type.' && sr.relation_id in ('.$relation_ids.')';
		$res = Db::query( $sql);
		
		return $res;
	}
	public function getSrcinfo( $relation_id, $r_type, $type){
		
		$sql = 'SELECT s.src_id,s.src_name,s.type,s.status,s.path,s.access_url origin_access_url,
				CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_339x387.",SUBSTRING_INDEX(s.access_url,".",-1)) AS access_url,s.source_url,s.url,s.resource_path
				FROM src_relation AS sr LEFT JOIN src AS s ON sr.src_id = s.src_id && sr.type = :r_type
				WHERE sr.relation_id = :relation_id && s.type = :type';
		$res = Db::query( $sql, ['relation_id' => $relation_id,'r_type' => $r_type, 'type' => $type]);
		return $res;
	}
	public function deleteProjectCoverImg(){
		
		$sql = 'DELETE sr,s
				FROM src_relation AS sr LEFT JOIN src AS s ON sr.src_id = s.src_id && sr.type = 1
				WHERE sr.src_id = :src_id && sr.relation_id = :relation_id';
		$res = Db::query( $sql, ['src_id' => input('src_id'), 'relation_id'=> input('project_id')]);
		
		return $res;
	}
	public function src_relation_delete_by_srcid(){
		
		$src_id = input('src_id');
		$sql = 'DELETE 
				FROM src_relation
				WHERE src_id = :src_id && type = 3';
		$res = Db::query( $sql, ['src_id'=> $src_id]);
		
		return $res;
	}
	
	public function deleteHeadImgBySrcid($src_id){
		$sql = 'DELETE
				FROM src_relation
				WHERE src_id = :src_id';
		$res = Db::query($sql, ['src_id'=>$src_id]);
		
		return $res;
	}
	
	public function deleteByTaskid(){
		
		$sql = 'DELETE sr,s
				FROM src_relation AS sr LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE sr.relation_id = :relation_id && sr.type = 2';
				
		$res = Db::query( $sql, ['relation_id' => input('task_id')] );
		return $res;
	}
	
	public function deleteSrc( $relation_id, $type){
		$sql = 'DELETE sr,s
				FROM src_relation AS sr LEFT JOIN sr.src_id = s.src_id
				WHERE sr.relation_id = :relation_id && type = :type';
		$res = Db::query( $sql, ['relation_id' => $relation_id, 'type' => $type]);
		
		return $res;
	}
}
?>