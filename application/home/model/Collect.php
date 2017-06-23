<?php
namespace app\home\model;
use think\Model;
use think\Db;


class Collect extends Model{
	
	protected $table = 'collect';
	
	public function getTasksCollectNum( $task_ids){
		
		$sql = 'SELECT cid as task_id,count(user_id) AS collect_num
				FROM collect
				WHERE type = 2 && cid in('.$task_ids.')
				GROUP BY cid';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function get_user_collect( $user_id, $cids, $type){
		$sql = 'SELECT cid,collect_id
				FROM collect
				WHERE user_id = :user_id && type = :type && cid in('.$cids.')';
		$res = Db::query( $sql, ['user_id'=>$user_id,'type'=>$type]);
		
		return $res;
	}
//	public function add_collect(){
//		
//		$sql = 'INSERT INTO collect( cid, type, user_id) VALUES( :cid, :type, :user_id)';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]);
//		
//		return $res;
//	}
//
//	public function del_collect(){
//		
//		$sql = 'DELETE FROM collect WHERE cid = :cid && user_id = :user_id && type = :type';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'user_id' => input('user_id'), 'type' => input('type')]);
//		
//		return $res;
//	}
}

?>