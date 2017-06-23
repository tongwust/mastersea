<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Task extends Model{
	
	protected $table = 'task';
	
	public function updateTaskByTaskid(){
		
		$task_id = input('task_id');
		$sql = 'UPDATE task
				SET title = :title,description = :description
				WHERE task_id = :task_id';
		$res = Db::query( $sql, [ 'task_id' => $task_id, 'title' => input('title'), 'description' => input('description') ]);
		
		return $res;
	}
	
	public function deleteByTaskid(){
		$sql = 'DELETE FROM task WHERE task_id = :task_id';
		
		$res = Db::query( $sql, ['task_id' => input('task_id')] );
		return $res;
	}
	
//	public function updatePraiseNum( $opt ){
//		
////		$sql = 'UPDATE task 
////				SET praise_num = praise_num + 1
////				WHERE task_id = :task_id';
////		$res = Db::query( $sql, ['task_id' => input('cid')]);
//		$res = 0;
//		if( $opt == 1){
//			$res = Db::table('task')->where('task_id',input('cid'))->setInc('praise_num');	
//		}else if( $opt == 2){
//			$res = Db::table('task')->where('task_id',input('cid'))->where('praise_num', '>', 0)->setDec('praise_num');
//		}
//		return $res;
//	}
//	
//	public function updateCollectNum( $opt ){
//		$res = 0;
//		if( $opt == 1){
//			$res = Db::table('task')->where('task_id', input('cid'))->setInc('collect_num');
//		}else if( $opt == 2){
//			$res = Db::table('task')->where('task_id', input('cid'))->where('collect_num', '>', 0)->setDec('collect_num');
//		}
//		return $res;
//	}
}


?>