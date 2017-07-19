<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Loader;

class Tag extends Controller{
	
	public function hot_tags(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$page_size = empty(input('page_size'))?5:input('page_size');
		if( input('tstr') == '' && $page_size > 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$list = explode( ',', input('tstr'));
		$user_tag = model('UserTag');
		foreach($list as $v){
			switch($v){
				case 'p':
					$themeid = 10;
					$ret['plist'] = $user_tag -> hotTags( $themeid, $page_size);
					break;
				case 's':
					$themeid = 11;
					$ret['slist'] = $user_tag -> hotTags( $themeid, $page_size);
					break;
				case 'l':
					$themeid = 13;
					$ret['llist'] = $user_tag -> hotTags( $themeid, $page_size);
					break;
				case 'a':
					$themeid = 14;
					$ret['alist'] = $user_tag -> hotTags( $themeid, $page_size);
					break;
			}
		}
//		dump($ret);
		return json_encode($ret);
	}
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
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
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
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
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
//		exit;
		$p = model('Province');
		
		$res = $p->getAllProvince();
		//dump( $res );
		return json_encode(array_column($res,'province'));
//		foreach($res as $v){
//			
//			$this->add( 41, $v['province'], 14);
//		}
	}
	public function add_city(){
		
		$c = model('City');
		$res = $c->getAllCity( 420000 );
//		foreach($res as $v){
//			//$v['short_name'] = $v['city'];
//			dump(mb_substr( $v['city'], 0, mb_strlen($v['city'])-1, 'utf-8'));echo $v['city'];exit;
//		}
		return  json_encode((array_column($res,'city')) );
//		foreach( $res as $v){
//			
//			$this->add( 45, $v['city'],mb_substr( $v['city'], 0, mb_strlen($v['city'])-1, 'utf-8'), 14);
//		}
		
	}
	public function tag_add( $pid, $name, $short_name, $themeid, $type){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',
			'tag_id' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$pid = input('pid');
		$name = input('name');
		$short_name = input('short_name');
		$themeid = (empty(input('themeid'))?0:input('themeid'));
		$type = empty(input('type'))?1:input('type');
		
		$tag = model('Tag');
		if( $pid > 0 && $themeid > 0){
			//$res = Db::query('call addTag(:pid,:name,:short_name,:themeid,:type,@id)',['pid'=>$pid,'name'=>$name,'short_name'=>$short_name,'themeid'=>$themeid,'type'=>$type]);
//			$sql = "call addTag('{$pid}','{$name}','{$short_name}','{$themeid}','{$type}',@id)";
//			$res = Db::query($sql,array());
			$res = $tag -> addTag( $pid, $name, $short_name, $themeid, $type);
			if(!(count($res) > 0 && $res[0][0]['result'] == 1000)){
				$ret['r'] = -2;
				$ret['msg'] = '添加失败';
				return json_encode($ret);
				exit;
			}
			$ret['tag_id'] = $res[0]['tag_id'];
//			dump($res);
//			$tag_id = Db::query('select @id');
//			$ret['tag_id'] = (count($tag_id) > 0)?$tag_id[0]['@id']:'';
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode($ret);
	}
	
	public function tag_add2( $pid, $name, $short_name, $themeid, $type){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',
			'tag_id' => '',
		];
		if( $pid > 0 && $themeid > 0){
			$res = $tag -> addTag( $pid, $name, $short_name, $themeid, $type);
			if(!(count($res) > 0 && $res[0][0]['result'] == 1000)){
				$ret['r'] = -2;
				$ret['msg'] = '添加失败';
				return json_encode($ret);
				exit;
			}
			$ret['tag_id'] = $res[0]['tag_id'];
//			dump( $tag_id);
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return $ret;
	}
	public function test_add_tag(){
		$arr = ['市场文案策划','活动策划','活动执行','公关总监','公关经理','公关专员','广告创意','广告文案策划',
				'美术指导','会展活动策划','项目总监','项目经理','项目主管','舞美设计','同声传译','化妆师','造型师','主持人','司仪','设计总监','平面设计师','3D设计师','舞台视觉设计师','创意总监','灯光设计师','礼仪','模特','音响师' ];
		foreach($arr as $v){
			dump($v);
		}
//		foreach($arr as $v){
//			$res = tag_add2( 108, $v,'',10,1);
//			dump($res);
//		}
		
	}
	public function tag_del(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
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
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
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