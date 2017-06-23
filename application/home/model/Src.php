<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Src extends Model{
	
	protected $table = 'src';
	
	public function src_delete_by_srcid(){
		
		$src_id = input('src_id');
		$sql = 'DELETE 
				FROM src
				WHERE src_id = :src_id';
		$res = Db::query($sql, ['src_id'=>$src_id]);
		return $res;
	}
}
?>