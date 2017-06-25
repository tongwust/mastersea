<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Praise extends Model{
	
	protected $table = 'praise';
	
	
	public function getPraiseId( $cid, $user_id, $type){
		$sql = 'SELECT praise_id
				FROM praise
				WHERE cid = :cid && user_id = :user_id && type = :type
					LIMIT 1';
		$res = Db::query( $sql, ['cid'=>$cid,'user_id'=>$user_id,'type'=>$type]);
		
		return $res;
	}
	public function get_user_praise( $user_id, $cids, $type){
		$sql = 'SELECT cid,praise_id
				FROM praise
				WHERE user_id = :user_id && type = :type && cid in('.$cids.')';
		$res = Db::query( $sql, ['user_id'=>$user_id,'type'=>$type]);
		
		return $res;
	}
//	public function add_praise(){
//		
//		$sql = 'INSERT INTO praise( cid, type, user_id) VALUES( :cid, :type, :user_id)';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]);
//		
//		return $res;
//	}
//	
//	public function del_praise(){
//		
//		$sql = 'DELETE FROM praise WHERE cid = :cid && user_id = :user_id && type = :type';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'user_id' => input('user_id'), 'type' => input('type')]);
//		
//		return $res;
//	}
	
}


?>