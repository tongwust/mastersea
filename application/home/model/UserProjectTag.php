<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserProjectTag extends Model{
	
	protected $table = 'user_project_tag';
	
	public function get_project_by_userid(){
		
		$user_id = input('user_id');
		$sql = 'SELECT project_id
				FROM user_project_tag
				WHERE user_id = :user_id GROUP BY project_id';
		
		$res = Db::query( $sql, ['user_id' => $user_id]);
		return $res;
	}
	
	public function GetMyProjectList($user_id){
		$from = empty(input('from'))?0:intval(input('from'));
		$page_size = empty(input('page_size'))?5:intval(input('page_size'));
		
		$sql = 'SELECT DISTINCT(upt.project_id),upt.tag_id,upt.create_time,ti.name tag_name,
							p.name,p.collect_num,p.project_start_time,p.project_end_time,p.duty,
							s.src_id project_src_id,s.access_url project_access_url
				FROM user_project_tag AS upt LEFT JOIN project AS p ON upt.project_id = p.project_id && p.status != -1
											 LEFT JOIN src_relation AS sr ON sr.relation_id = p.project_id && sr.type = 1
											 LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
											 LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.user_id = :user_id && upt.user_type = 1 
					ORDER BY upt.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['user_id' => $user_id]);
		
		return $res;
	}
	
	public function GetMyProjectFullList($user_id){
		$from = empty(input('from'))?0:intval(input('from'));
		$page_size = empty(input('page_size'))?5:intval(input('page_size'));
		
		$sql = 'SELECT DISTINCT(b.project_id),b.create_time,b.user_id,
							p.name as project_name,p.cat_name,p.address,p.duty,p.intro,p.collect_num,p.praise_num,p.project_start_time,p.project_end_time,
							s.src_id project_src_id,s.access_url project_origin_access_url,CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_339x387.",SUBSTRING_INDEX(s.access_url,".",-1)) AS project_access_url
				FROM user_project_tag AS a 
						 INNER JOIN user_project_tag as b ON a.project_id = b.project_id && b.user_type = 1
						 INNER JOIN project AS p ON b.project_id = p.project_id && p.status != -1
						 LEFT JOIN src_relation AS sr ON sr.relation_id = p.project_id && sr.type = 1
						 LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
				WHERE a.user_id = :user_id 
					ORDER BY b.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['user_id' => $user_id]);
		
		return $res;
	}
	public function getProjectListByUserid(){
		$from = empty(input('from'))?0:intval(input('from'));
		$page_size = empty(input('page_size'))?5:intval(input('page_size'));
		$user_id = input('user_id');
		
		$sql = 'SELECT DISTINCT(b.project_id),b.user_id,u.name as username,p.name as project_name,p.type,p.status,p.intro,p.praise_num,
						s.access_url as project_origin_access_url,CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_339x387.",SUBSTRING_INDEX(s.access_url,".",-1)) AS project_access_url
				FROM user_project_tag as a
					INNER JOIN user_project_tag as b ON a.project_id = b.project_id && b.user_type = 1
					INNER JOIN project as p ON b.project_id = p.project_id && p.status != -1
					LEFT JOIN src_relation as sr ON p.project_id = sr.relation_id && sr.type = 1
					LEFT JOIN src as s ON sr.src_id = s.src_id && s.type = 3
					LEFT JOIN user as u ON b.user_id = u.user_id
				WHERE a.user_id = :user_id  
					ORDER BY b.create_time DESC LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql, ['user_id' => $user_id ]);
		
		return $res;
	}
	public function getProjectAttenNum(){
		$user_id = input('user_id');
		$sql = 'SELECT pa.project_id,count(user_id) as user_num
				FROM project_attention AS pa
				WHERE project_id IN (SELECT DISTINCT(project_id)
									 FROM user_project_tag
									 WHERE user_id = '.$user_id.' && user_type = 1)
					GROUP BY project_id';
		$res = Db::query( $sql );
		
		return $res;
	}
	
	
	public function getPartnersNumByUserId(){
		$user_id = input('user_id');
		
//		$sql = 'SELECT b.project_id,count(b.user_id) as user_num
//				FROM user_project_tag a LEFT JOIN user_project_tag b ON a.project_id = b.project_id
//				WHERE a.user_id = '.$user_id.' && a.user_type = 1
//					GROUP BY b.project_id';
		$sql = 'SELECT DISTINCT(user_id)
				FROM user_project_tag
				WHERE project_id IN (SELECT DISTINCT(project_id)
									 FROM user_project_tag
									 WHERE user_id = '.$user_id.')';
		$res = Db::query( $sql);
		
		return $res;
	}
	
	public function get_tag_by_userid_projectid(){
		$project_id = input('project_id');
		$user_id = input('user_id');
		
		$sql = 'SELECT upt.tag_id,ti.name AS tag_name,upt.user_type
				FROM user_project_tag AS upt LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.user_id = :user_id && upt.project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $user_id,'project_id' => $project_id]);
		
		return $res;
	}
	
	public function getProjectCreatorInfo(){
		$sql = 'SELECT DISTINCT(u.user_id),u.name,s.src_id,s.access_url
				FROM user_project_tag AS upt LEFT JOIN user AS u ON upt.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON u.user_id = sr.relation_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE upt.project_id = :project_id && upt.user_type = 1';
				
		$res = Db::query( $sql, ['project_id' => input('project_id')]);
		
		return $res;
	}
	
	public function getMemberInfoByProjectId(){
		$project_id = input('project_id');
		
		$sql = 'SELECT upt.user_id,upt.tag_id,upt.user_type,ti.name AS tag_name,u.name AS username,s.src_id,s.src_name,s.path,s.access_url
				FROM user_project_tag AS upt LEFT JOIN user AS u ON upt.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
					LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
					LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.project_id = :project_id
				ORDER BY upt.create_time DESC';
		$res = Db::query( $sql, ['project_id'=>$project_id] );
		return $res;
	}
	
	public function getMemberType(){
		$opt_id = input('opt_id');
		$project_id = input('project_id');
		
		$sql =  'SELECT user_type
				FROM user_project_tag
				WHERE user_id = :user_id && project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $opt_id, 'project_id' => $project_id]);
		
		return $res;
	}
	
	public function getMemberTag( $project_id, $user_id){
		$sql = 'SELECT tag_id,user_type
				FROM user_project_tag
				WHERE project_id = :project_id && user_id = :user_id';
		$res = Db::query( $sql, ['project_id' => $project_id, 'user_id' => $user_id]);
		
		return $res;
	}
	
	public function deleteMemberFromProject(){
		$project_id = input('project_id');
		$user_id = input('user_id');
		
		$sql = 'DELETE
				FROM user_project_tag
				WHERE project_id = :project_id && user_id = :user_id';
		$res = Db::query( $sql, ['project_id'=>$project_id, 'user_id' => $user_id]);
		return $res;
	}
	
	public function get_user_info_by_project_ids($project_ids_str){
		
		$sql = 'SELECT DISTINCT(upt.project_id),upt.user_id,u.name as username,s.src_id,s.src_name,s.path,s.access_url
				FROM user_project_tag upt LEFT JOIN user u ON upt.user_id = u.user_id
					LEFT JOIN src_relation sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 2
				WHERE upt.user_type = 1 && upt.project_id in('.$project_ids_str.')';
		$res = Db::query( $sql );
		return $res;
	}
	
	public function get_project_members($project_ids_str){
		
		$sql = 'SELECT user_id,project_id
				FROM user_project_tag
				WHERE project_id in('.$project_ids_str.') 
					GROUP BY user_id,project_id';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getPartInfoByProjectId( $project_id ){
		
		$sql = 'SELECT count(DISTINCT(user_id)) member_num
				FROM user_project_tag
				WHERE project_id = :project_id';
		$res = Db::query( $sql ,['project_id' => $project_id]);
		return $res;
	}
	public function getUserTags($project_ids_str){
	
		$sql = 'SELECT upt.project_id,upt.tag_id,ti.name
				FROM user_project_tag AS upt LEFT JOIN tag AS t ON upt.tag_id = t.tag_id
					LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE upt.project_id in('.$project_ids_str.')
					GROUP BY upt.project_id,upt.tag_id';
		$res = Db::query( $sql );
		return $res;
	}
	public function getUserTagsByProjectId($project_id){
		
		$sql = 'SELECT upt.project_id,upt.tag_id,ti.name
				FROM user_project_tag AS upt LEFT JOIN tag AS t ON upt.tag_id = t.tag_id
					LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE upt.project_id = :project_id
					GROUP BY upt.project_id,upt.tag_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	public function getUserProjectByUserids($user_ids_str){
		
		$sql = 'SELECT DISTINCT(upt.user_id),upt.project_id,upt.tag_id,upt.user_type,p.name project_name,ti.name as tag_name,s.access_url
				FROM user_project_tag as upt LEFT JOIN project as p ON upt.project_id = p.project_id
					LEFT JOIN src_relation as sr ON sr.relation_id = p.project_id && sr.type = 1
					LEFT JOIN src as s ON sr.src_id = s.src_id
					LEFT JOIN user_tag as ut ON upt.user_id = ut.user_id
					INNER JOIN tag as t ON ut.tag_id = t.tag_id && t.themeid = 10
					LEFT JOIN tag_info as ti ON t.tag_id = ti.tag_id
				WHERE upt.user_id in ('.$user_ids_str.') && upt.user_type = 1
					 GROUP BY upt.user_id';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getProjectNumByUserids($user_ids_str){
		
		$sql = 'SELECT upt.user_id,upt.project_id,upt.tag_id,ti.name AS tag_name
				FROM user_project_tag AS upt LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE user_type = 1 && user_id in ('.$user_ids_str.')
					ORDER BY upt.user_id ASC';
		$res = Db::query( $sql);
		
		return $res;
	}
	
	public function getProjectCoverByUserids($user_ids_str){
		
		$sql = 'SELECT DISTINCT(upt.project_id),upt.user_id,s.src_id project_src_id,s.access_url project_access_url,upt.create_time
				FROM user_project_tag AS upt LEFT JOIN src_relation AS sr ON upt.project_id = sr.relation_id && sr.type = 1
											 LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 3
				WHERE upt.user_type=1 && upt.user_id in('.$user_ids_str.')
					 ORDER BY upt.create_time DESC';
		$res = Db::query($sql);
		
		return $res;
	}
	
	public function myShowProjectTasklist( $from, $page_size){
		
		$sql = 'SELECT DISTINCT(t.task_id),upt.project_id,t.title,t.description,t.status,t.praise_num,t.collect_num,t.create_time,
						s.src_id,s.src_name,s.src_order,s.type src_type,s.status src_status,s.path,s.resource_path,s.access_url,
						CONCAT(SUBSTRING_INDEX(s.access_url,".",4),"_865x579.",SUBSTRING_INDEX(s.access_url,".",-1)) AS origin_access_url
				FROM user_project_tag AS upt INNER JOIN project_task_user AS ptu ON upt.project_id = ptu.project_id
					LEFT JOIN task AS t ON ptu.task_id = t.task_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = t.task_id && sr.type = 2
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 1
				WHERE upt.user_id = :user_id && upt.user_type = 1
					ORDER BY t.create_time DESC
					LIMIT :from,:page_size';
		$res = Db::query( $sql, ['user_id' => input('user_id'),'from' => $from, 'page_size'=> $page_size]);		
		
		return $res;
	}
	public function getMyJoinProjectMembers( $user_id, $from, $page_size){
		
		$sql = 'SELECT DISTINCT(b.user_id),u.name user_name,s.src_id user_src_id,s.access_url user_access_url
				FROM user_project_tag a INNER JOIN user_project_tag b ON a.project_id = b.project_id
					LEFT JOIN user AS u ON b.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE a.user_id = :user_id && b.user_id != :self_id
					LIMIT '.$from.','.$page_size;
					
		$res = Db::query($sql, ['user_id'=>$user_id,'self_id'=>$user_id]);
		
		return $res;
	}
	
//	public function getProjectMemberNum($project_ids_str){
//		
//		$sql = 'SELECT count(project_id) as member_num
//				FROM user_project_tag
//				WHERE project_id in ('.$project_ids_str.')
//				GROUP BY project_id,user_id';
//		$res = Db::query( $sql );
//		return $res;
//	}
}
?>