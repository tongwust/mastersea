<?php
namespace app\home\model;
use think\Model;
use think\Db;

class City extends Model{
	
	protected $table = 'city';
	
	public function getAllCity( $pid ){
		
		$sql = 'SELECT *
				FROM city
				WHERE fatherID = :fatherID';
		$res = Db::query( $sql, ['fatherID' => $pid] );
		
		return $res;
	}
	
	
}
?>