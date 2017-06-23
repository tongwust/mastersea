<?php
	
namespace app\home\model;
use think\Model;
use think\Db;

class Province extends Model{
	
	protected $table = 'province';
	
	public function getAllProvince(){
		
		$sql = 'SELECT * FROM province';
		$res = Db::query($sql );
		return $res;
	}
	
	
}
?>