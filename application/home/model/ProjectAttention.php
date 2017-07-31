<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectAttention extends Model{
	
	protected $table = 'project_attention';
	
	public function check_project_atten_me( $project_id, $user_id, $relation_type){
		$sql = 'SELECT COUNT(project_attention_id) num
				FROM project_attention
				WHERE project_id = :project_id && user_id =: user_id && relation_type =: relation_type
					LIMIT 1';
		$res = Db::query( $sql, ['project_id'=>$project_id,'user_id'=>$user_id,'relation_type'=>$relation_type]);
		
		return $res;
	}
	public function add(){
		
		$sql = 'INSERT INTO project_attention(project_id, user_id, relation_type) VALUES(:project_id, :user_id, :relation_type)';
		
		$res = Db::query( $sql, ['project_id' => input('project_id'), 
								 'user_id' => input('user_id'),
								 'relation_type' => (empty(input('relation_type'))?1:input('relation_type'))
								 ]);
		return $res;
	}
	public function getMyAttenProjectNum(){
		$user_id = input('user_id');
		$sql = 'SELECT user_id,count(project_id) AS my_atten_pnum
				FROM project_attention
				WHERE user_id = :user_id && relation_type = 1
					GROUP BY user_id';
		$res = Db::query( $sql, ['user_id' => $user_id] );
		
		return $res;
	}
	public function getProjectAttenNum($project_ids_str){
		
		$sql = 'SELECT project_id,count(user_id) as atten_num
				FROM project_attention
				WHERE project_id in ('.$project_ids_str.')
				GROUP BY project_id';
		$res = Db::query( $sql );
		return $res;
	}
	public function getProjectAttenUsers($project_ids_str){
		$sql = 'SELECT pa.project_id,pa.user_id,u.name username,ui.curr_company,s.src_id user_src_id,s.access_url user_access_url
				FROM project_attention AS pa LEFT JOIN user_info AS ui ON pa.user_id = ui.user_id
											 LEFT JOIN user AS u ON ui.user_id = u.user_id
											 LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
											 LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE pa.project_id in ('.$project_ids_str.')
					ORDER BY pa.project_id DESC';
				
		$res = Db::query( $sql);
		return $res;
	}
	public function getProjectAttenNumByProjectId(){
		$project_id = input('project_id');
		$sql = 'SELECT DISTINCT(user_id)
				FROM project_attention
				WHERE project_id = :project_id && relation_type = 1';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	public function myAttenProjectTasklist($user_id, $from, $page_size){
		$sql = 'SELECT pa.project_id,t.task_id,t.title,t.description,t.praise_num,t.collect_num,t.create_time,
					   s.src_id,s.src_name,s.src_order,s.type src_type,s.path,s.access_url,s.resource_path,CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_865x579.",SUBSTRING_INDEX(s.access_url,".",-1)) AS origin_access_url
				FROM project_attention AS pa LEFT JOIN project AS p ON pa.project_id = p.project_id && p.status = 0
					LEFT JOIN project_task_user AS ptu ON ptu.project_id = p.project_id
					LEFT JOIN task AS t ON ptu.task_id = t.task_id && t.status != -1
					LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
					LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE pa.user_id = :user_id
						LIMIT :from,:page_size';
		$res = Db::query( $sql, ['user_id' => $user_id,'from'=>$from,'page_size'=>$page_size]);
		
		return $res;
	}
	public function getMyAttenProjectList($user_id){
		$from = (input('from'))?intval(input('from')):0;
		$page_size = (input('page_size'))?intval(input('page_size')):10;
		
		$sql = 'SELECT pa.project_id,p.name project_name,p.intro,p.project_start_time,p.project_end_time,p.praise_num,
					   s.src_id project_src_id,s.access_url project_access_url
				FROM project_attention AS pa LEFT JOIN project AS p ON pa.project_id = p.project_id && p.status = 0
											 LEFT JOIN src_relation AS sr ON sr.relation_id = p.project_id && sr.type = 1
											 LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
				WHERE pa.user_id = :user_id && pa.relation_type = 1
					ORDER BY pa.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql, ['user_id' => $user_id ]);
		
		return $res;
	}
}
?>