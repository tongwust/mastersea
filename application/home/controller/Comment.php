<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Comment extends Controller{
	
	//评论添加
	public function comment_add(){
		$ret = [
			'r' => 0,
			'msg' => '评论成功',
			'pc_id' => '',
		];
		$pid = input('pid');
		$cid = input('cid');
		$type = input('type');
		$user_id = input('user_id');
		$content = trim(input('content'));
		
		$comment = model('Comment');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if($user_id > 0 && strlen($content) > 0){
			$comment->pid = $pid;
			$comment->cid = $cid;
			$comment->type = $type;
			$comment->user_id = $user_id;
			$comment->content = $content;
			
			$comment->save();
			$ret['pc_id'] = $comment->pc_id;
		}else{
			$ret['r'] = -1;
			$ret['msg'] = 'user_id非法或评论内容不能空';
		}
		return json_encode($ret);
	}
	
	//删除评论
	public function comment_del(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		$pc_id = input('pc_id');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( $pc_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$comment = model( 'Comment' );
		$comment -> destroy(['pc_id' => $pc_id]);
		
		return json_encode( $ret );
	}
	
}


?>