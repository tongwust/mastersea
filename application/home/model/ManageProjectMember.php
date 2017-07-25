<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ManageProjectMember extends Model{
	
	protected $table = 'manage_project_member';
		
	public function check_user_manage_record($user_id, $opt_id, $project_id, $type){
		
		$sql = 'SELECT mpm_id,create_time
				FROM manage_project_member
				WHERE user_id = :user_id && opt_id = :opt_id && project_id = :project_id && type = :type && create_time >= DATE_SUB(NOW(),INTERVAL 24 HOUR)';
		
		$res = Db::query( $sql, ['user_id'=>$user_id,'opt_id'=>$opt_id,'project_id'=>$project_id,'type'=>$type]);
		return $res;
	}



}







?>