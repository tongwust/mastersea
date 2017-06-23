<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Loader;

class SrcRelation extends Controller{
	//upload head_img
	public function user_src_upload(){
		$ret = [
			'r' => 0,
			'msg' => '',
			'src_id' => '',
			'src_name' => '',
		];
		$user_id = input('user_id');
		$resource_path = input('resource_path');
		if( $user_id > 0 && $resource_path != ''){
			$src = model('Src');
			$src_relation = model('SrcRelation');
			
			$info = pathinfo($resource_path);
			$src->src_name = $info['basename'];
			$path_arr = explode('/', $info['dirname']);
			$src->path = '/' . $path_arr[count($path_arr) - 1];
			$src->type = 2;//头像
			$src->access_url = input('access_url');
			$src->resource_path = $resource_path;
			$src->source_url = input('source_url');
			$src->url = input('url');
			
			Db::startTrans();		
			try{
				//delete old head_img
//				$src_relation -> deleteSrc( $user_id, 3);
				$src->save();
				$src_relation->src_id = $src->src_id;
				$src_relation->relation_id = $user_id;
				$src_relation->type = 3;//user
				$src_relation->save();
				Db::commit();
				$ret['msg'] = '添加成功';
				$ret['src_id'] = $src->src_id;
				$ret['src_name'] = $src->src_name;
			}catch( \Exception $e){
				Db::rollback();
				$ret['r'] = -2;
				$ret['msg'] = '添加数据失败'. $e;
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode( $ret );
	}
	//delete head_img
	public function user_src_delete(){
		$ret = [
			'r' => 0,
			'msg' => '删除数据成功',
		];
		$user_id = input('user_id');
		$src_id = input('src_id');
		if( $src_id > 0 && $user_id > 0 ){
			$src = model('Src');
			$src_relation = model('SrcRelation');
			
			Db::startTrans();
			try{
				$src->src_delete_by_srcid();
				$src_relation->src_relation_delete_by_srcid();
				Db::commit();
			}catch( \Exception $e){
				Db::rollback();
				$ret['r'] = -2;
				$ret['msg'] = '删除数据失败';
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode($ret);
	}
}


?>