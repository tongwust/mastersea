<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Loader;

class Tag extends Controller{
	
	//search tag
	public function search_tags(){
		
		$tag_tcs = new TcsQcloudApi(59630002);
		
		$res = $tag_tcs -> yunsouDataSearch();
		return $res;
	}
	//add tag
	public function add_search_tag(){
		$ret = [
			'r' => 0,
			'msg' => 'add success',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if( $tag_id <= 0 || $themeid <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符要求';
			return json_encode($ret);
			exit;
		}
		$tcs_qcloud_api = new TcsQcloudApi( 59630002);
		
		$res = $tcs_qcloud_api -> yunsouDataManipulation();
		$res = json_decode( $res, true);
		if($res['r'] != 0){
			return json_encode($res);
			exit;
		}
		return json_encode( $ret);
	}
	
	public function get_part_by_tagid_themeid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if($tag_id > 0 && $themeid > 0){
			
			$tag = model('Tag');
			$ret['data'] = $tag->get_tag_by_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';	
		}
		return json($ret);
	}
	
	public function get_dim_name_by_tagid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		$part = trim(input('part'));
		if($tag_id > 0 && $themeid > 0 && strlen($part) > 0){
			$tag = model('Tag');
			$ret['data'] = $tag->get_dim_tag_by_pid_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';
		}
		return json($ret);
	}
	
	public function getAll(){
		$result = [
			'r' => 0,
			'msg' => '',
			'data' => '',
		];
		$tag = model('Tag');
		$res = $tag->selectAll();
		$result['data'] = $res;
		return json($result);
	}
	
	public function add_province(){
		exit;
		$p = model('Province');
		
		$res = $p->getAllProvince();
		//dump( $res );
		foreach($res as $v){
			
			$this->add( 41, $v['province'], 14);
		}
	}
	public function add_city(){
		exit;
		$c = model('City');
		$res = $c->getAllCity( 140000 );//山西
//		foreach($res as $v){
//			//$v['short_name'] = $v['city'];
//			dump(mb_substr( $v['city'], 0, mb_strlen($v['city'])-1, 'utf-8'));echo $v['city'];exit;
//		}
		dump($res);
		foreach( $res as $v){
			
			$this->add( 45, $v['city'],mb_substr( $v['city'], 0, mb_strlen($v['city'])-1, 'utf-8'), 14);
		}
		
	}
	public function tag_add( $pid, $name, $short_name, $themeid){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',
		];
		$pid = input('pid');
		$name = input('name');
		$short_name = input('short_name');
		$themeid = empty(input('themeid'))?1:input('themeid');
		if( $pid > 0 ){
			$res = Db::query('call addTag(:pid,:name,:short_name,:themeid)',['pid'=>$pid,'name'=>$name,'short_name'=>$short_name,'themeid'=>$themeid]);
			if(!(count($res) > 0 && $res[0][0]['result'] == 1000)){
				$ret['r'] = -2;
				$ret['msg'] = '添加失败';
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode($ret);
	}
	
	public function tag_del(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		$pid = input('pid');
		$themeid = input('themeid');
		if($pid > 0){
			$res = Db::query('call delTag(:pid, :themeid)',['pid'=>$pid, 'themeid' => $themeid]);
			if(!(count($res) > 0 && $res[0][0]['result'] == 1000)){
				$ret['r'] = -2;
				$ret['msg'] = '删除失败';
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = 'pid 参数不符合要求';
		}
		return json_encode( $ret );
	}
	
	public function move(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		$tid = input('tid');
		if( $pid > 0 && $tid > 0 ){
			$res = Db::query('call moveTag(:pid,:tid)',['pid'=>$pid,'tid'=>$tid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg'] = '移动成功';
			}else{
				$result['msg'] = '移动失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	
}






?>