<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Msg extends Model{
	
	protected $table = 'msg';
	
	public function getMySendMsgs( $user_id ){
		
		$sql = 'SELECT m.receive_user_id,u.name receive_user_name,m.msg_id,m.status,m.view_time,
					   mt.msg_title,mt.msg_content,mt.create_time,s.src_id,s.access_url
				FROM msg AS m LEFT JOIN msg_text AS mt ON m.msg_id = mt.msg_id
							  LEFT JOIN src_relation AS sr ON m.receive_user_id = sr.relation_id && sr.type = 3
							  LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
							  LEFT JOIN user AS u ON u.user_id = m.receive_user_id
				WHERE m.send_user_id = :send_user_id
						ORDER BY mt.create_time DESC,m.status DESC';
		$res = Db::query( $sql, ['send_user_id' => $user_id]);
		
		return $res;
	}
	
	public function getMyReceiveMsgs( $user_id ){
		
		$sql = 'SELECT m.send_user_id,u.name send_user_name,m.msg_id,m.status,m.view_time,
					   mt.msg_title,mt.msg_content,mt.create_time,s.src_id,s.access_url
				FROM msg AS m LEFT JOIN msg_text AS mt ON m.msg_id = mt.msg_id
							  LEFT JOIN src_relation AS sr ON m.send_user_id = sr.relation_id && sr.type = 3
							  LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
							  LEFT JOIN user AS u ON u.user_id = m.send_user_id
				WHERE m.receive_user_id = :receive_user_id
						ORDER BY mt.create_time DESC,m.status DESC';
		$res = Db::query( $sql, ['receive_user_id' => $user_id]);
		
		return $res;
	}
	
	public function delSingleMsg(){
		$sql = 'DELETE m,mt
				FROM msg AS m LEFT JOIN msg_text AS mt ON m.msg_id = mt.msg_id
				WHERE m.send_user_id = :send_user_id && m.receive_user_id = :receive_user_id && m.msg_id = :msg_id';
				
		$res = Db::query($sql,['send_user_id'=>input('send_user_id'),'receive_user_id'=>input('receive_user_id'),'msg_id'=>input('msg_id')]);
		return $res;
	}
	public function changeSingleMsgStatus(){
		$sql = 'UPDATE msg
				SET status = :status,view_time = :view_time
				WHERE send_user_id = :send_user_id && receive_user_id = :receive_user_id && msg_id = :msg_id';
		$res = Db::query( $sql, ['send_user_id'=>input('send_user_id'),'receive_user_id'=>input('receive_user_id'),'msg_id'=>input('msg_id'),'status' => 1,'view_time' => date('Y-m-d H:i:s',time())]);	
		
		return $res;
	}
	
	public function getUnreadMsgNum($user_id){
		$sql = 'SELECT count(msg_id) msg_num
				FROM msg
				WHERE receive_user_id = :receive_user_id && status = 0';
		$res = Db::query( $sql, ['receive_user_id'=>$user_id]);
		
		return $res;
	}
}

?>