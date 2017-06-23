<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Tag extends Model{
	
	protected $table = 'user';
	
	public function get_tag_by_themeid(){
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		$sql = 'SELECT ti.name,ti.tag_id as tagid,t.themeid
				FROM tag AS t LEFT JOIN tag_info AS ti ON ti.tag_id = t.tag_id
				WHERE t.pid = :tag_id && t.themeid = :themeid';
		$res = Db::query( $sql, ["tag_id"=>$tag_id,"themeid"=>$themeid]);
		return $res;
	}
	
	public function get_dim_tag_by_pid_themeid(){
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		$part = trim(input('part'));
		$sql = 'SELECT ti.tag_id,ti.name
				FROM tag_info AS ti LEFT JOIN tag AS t ON ti.tag_id = t.tag_id
				WHERE t.pid = :tag_id && t.themeid = :themeid && ti.name LIKE %:part%';
		$res = Db::query( $sql, ['tag_id' => $tag_id, 'themeid' => $themeid, 'part' => $part]);
		return $res;
	}
	
	public function selectAll(){
		$sql = 'SELECT t.tag_id,concat(repeat("-",t.level),ti.name),t.themeid,t.lft,t.rgt
				FROM tag AS t LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE themeid = 1 ORDER BY t.lft';
		$res = Db::query($sql);
    	return count($res);
	}
	
}
?>