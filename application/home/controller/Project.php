<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class Project extends Controller{
	
	public function index(){
		$view = new View();
		return $view->fetch('./test/upload');
	}
	public function my_show_project_task_list(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'tasks' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user_id = input('user_id');
		$from = empty(input('from'))?0:input('from');
		$page_size = empty(input('page_size'))?10:input('page_size');
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( $user_id <= 0 ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$user_project_tag = model('UserProjectTag');
		$comment = model('Comment');
		$praise = model('Praise');
		$collect = model('Collect');
		
		$res = $user_project_tag -> myShowProjectTasklist( $from, $page_size);
//		dump( $res );
		if( count($res) > 0){
			$taskids_arr = array_column($res, 'task_id');//dump($taskids_arr);
			$comment_arr = $comment->get_task_comment_by_task_ids(implode(',', $taskids_arr), 2);
			if( $user_id > 0){//login
				$task_praise_res = $praise -> get_user_praise( $user_id, implode(',', $taskids_arr), 2);//dump($task_praise_res);
				$task_collect_res = $collect -> get_user_collect( $user_id, implode(',', $taskids_arr), 2);//dump($task_collect_res);
				$task_praise = [];
				foreach($task_praise_res as $r){
					$task_praise[$r['cid']] = $r['praise_id'];
				}
				$task_collect = [];
				foreach($task_collect_res as $r){
					$task_collect[$r['cid']] = $r['collect_id'];
				}
			}
			//dump($comment_arr);
			foreach($res as &$t){
				$t['comment'] = [];
				foreach($comment_arr as $c){
					if($t['task_id'] == $c['cid']){
						array_push($t['comment'], $c);
					}
				}
				$t['login']['is_praise'] = isset($task_praise[$t['task_id']])?1:0;
				$t['login']['is_collect'] = isset($task_collect[$t['task_id']])?1:0;
			}
			$ret['tasks'] = $res;
		}
//		dump($ret);
		return json_encode( $ret );
	}
	public function get_project_creator_info(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'uinfo' => '',
			'pinfo' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');
		if( $project_id <= 0 ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$user_project_tag = model('UserProjectTag');
		$project = model('Project');
		
		$uinfo = $user_project_tag -> getProjectCreatorInfo();
		$pinfo = $project -> getProjectPartInfo();
//		dump($uinfo);dump($pinfo);
		
		$ret['uinfo'] = (count($uinfo) > 0)?$uinfo[0]:'';
		$ret['pinfo'] = (count($pinfo) > 0)?$pinfo[0]:'';
		
		return json_encode($ret);
	}
	
	public function add_project_cover_img(){
		$ret = [
			'r' => 0,
			'msg' => '创建成功',
			'src_id' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		$project_id = input('project_id');
		$access_url = input('access_url');
		if( $project_id <= 0 || $access_url == ''){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$src = model('Src');
		$src_relation = model('SrcRelation');
		Db::startTrans();
		try{
			$arr = explode('/',input('resource_path'));
			$src_arr = [
				'src_name'=>$arr[count($arr)-1],
				'type'=>3,
				'path'=>'/'.$arr[count($arr)-2],
				'url'=>input('url'),
				'source_url'=>input('source_url'),
				'resource_path'=>input('resource_path'),
				'access_url'=>input('access_url')
			];
			$src -> save( $src_arr );
			$src_relation -> save(['src_id'=>$src->src_id,'relation_id'=>input('project_id'),'type'=>1]);
			Db::commit();
			$ret['src_id'] = $src->src_id;
		}catch(\Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
			Db::rollback();
		}
		return json_encode( $ret);
	}
	
	public function delete_project_cover_img(){
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
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		$src_id = input('src_id');
		$project_id = input('project_id');
		if( $src_id <= 0 || $project_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = 'src_id参数不符';
			return json_encode($ret);
			exit;
		}
		$src_relation = model('SrcRelation');
		$res = $src_relation -> deleteProjectCoverImg();
		
		return json_encode( $ret );
	}
	public function get_members_info_by_project_id(){
		$ret = [
			"r" => 0,
			"msg" => '查询成功',
			'member_list' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');
		if( $project_id > 0){
			$user_project_tag = model('UserProjectTag');
			$res = $user_project_tag->getMemberInfoByProjectId();//dump($res);
			$arr = [];
			foreach($res as $k => $v){
				$arr[intval($v['user_id'])] = $v;
			}
			$member_arr = [];
			foreach($arr as $val){
				array_push( $member_arr, $val);
			}
			$ret['member_list'] = $member_arr;
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
//		dump($ret);
		return json_encode($ret);
	}
	public function change_project_status(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		$project_id = input('project_id');
		$status = input('status');
		if( $project_id <= 0 || $status === ''){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret);
			exit;
		}
		$project = model('Project');
		$project -> changeProjectStatus();
		
		return json_encode( $ret );
	}
	public function delete_member_from_project(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
			'path_list'=>[],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}
		$opt_id = input('opt_id');
		$user_id = input('user_id');
		$project_id = input('project_id');
		if( $opt_id > 0 && $user_id > 0 && $project_id > 0){
			
			$user_project_tag = model('UserProjectTag');
			$project_task_user = model('ProjectTaskUser');
			
			$user_type_arr = $user_project_tag->getMemberType();
			$flag = false;
			foreach( $user_type_arr as $v){
				if( $v['user_type'] == 1){
					$flag = true;	break;
				}
			}
			if( $flag ){//1.负责人
				Db::startTrans();
				try{
					$user_project_tag -> deleteMemberFromProject();
					$ret['path_list'] = $project_task_user -> getDeleteMemberSrc();
					$project_task_user -> deleteMemberFromTask();
					Db::commit();
				}catch(\Exception $e){
					Db::rollback();
					$ret['r'] = -3;
					$ret['msg'] = '添加数据异常'.$e;
				}
			}else{
				$ret['r'] = -2;
				$ret['msg'] = '操作人不是负责人';
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode( $ret );
	}
	public function add_member_to_project(){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];//$user_id=5;
		$sig_arr = convertUrlQuery(base64_decode( input('sig') ));
		if( !(isset($sig_arr['project_id']) && $sig_arr['project_id'] > 0 && isset($sig_arr['charge_user_id']) && $sig_arr['charge_user_id'] > 0 && isset($sig_arr['user_id']) && $sig_arr['user_id'] > 0 && $user_id == $sig_arr['user_id'] ) ){
			$ret['r'] = -1;
			$ret['msg'] = 'sig参数格式不符';
			return json_encode($ret);
			exit;
		}
		$user_project_tag = model('UserProjectTag');dump($sig_arr);
		$res = $user_project_tag -> getMemberTag( $sig_arr['project_id'], $sig_arr['user_id']);
		if(count($res) > 0){
			$ret['r'] = -2;
			$ret['msg'] = '用户已是项目中的成员';
		}else{
			$user_project_tag -> user_id = $sig_arr['user_id'];
			$user_project_tag -> project_id = $sig_arr['project_id'];
			$user_project_tag -> user_type	= $sig_arr['user_type'];
			$user_project_tag -> tag_id = $sig_arr['tag_id'];
			$user_project_tag -> save();
		}
		return json_encode($ret);
	}
	//yunsou添加项目内容 all
	public function add_project_search_keys(){
		header("Access-Control-Allow-Origin:*");
    	header("Access-Control-Allow-Method:POST,GET");
		
		$project_tcs = new TcsQcloudApi(58740002);
		
		$res = $project_tcs -> projectDataManipulation();
		
		return json_encode($res);
	}
	
	//向腾讯云添加 搜索记录
	public function add_search_key_by_project_id($project_id){
//		$project_id = input('project_id');
		
		$project_tcs = new TcsQcloudApi(58740002);
		
		$project_tcs->DataManipulationByProjectId( $project_id );
		
	}
	
	public function get_user_project_list(){
		$ret = [ 
			"r" => 0,
			"msg" => '查询成功',
			'project_list' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user_id = input('user_id');
		if( $user_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$user_project_tag = model('UserProjectTag');
		$project_atten = model('ProjectAttention');
		
		$res = $user_project_tag -> getProjectListByUserid();
		
		$project_id_arr = array_column( $res, 'project_id');
		$project_ids_str = implode( ',', $project_id_arr);
		
		$atten_arr = $project_atten -> getProjectAttenNum($project_ids_str);
		$arr = [];
		foreach($atten_arr as $val){
			$arr[$val['project_id']] = $val['atten_num'];
		}
		
		$members = $user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = [];
		foreach( $members as $v){
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
		foreach( $res as &$v){
			$v['atten_num'] = empty($arr[$v['project_id']])?0:$arr[$v['project_id']];
			$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']];
		}
		$ret['project_list'] = $res;
		
		return json_encode($ret);
	}
	public function get_search_project_ids(){
		$ret = [
			"r" => 0,
			"msg" => '获取成功',
			'data' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode( input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode( $ret);
			exit;
		}
		$search_query = input('search_query');
		if( empty( $search_query)){
			$ret['r'] = -1;
			$ret['msg'] = '参数为空';
			return json_encode($ret);
			exit;
		}
		$project_tcs = new TcsQcloudApi( 58740002 );
		$res_json = $project_tcs -> yunsouDataSearch();
		$data = json_decode( $res_json, true);
		$ret['r'] = $data['r'];
		$ret['data'] = $data['data'];
//		dump($ret);
		return json_encode($ret);
	}
	public function get_search_project_list(){
		$ret = [
			"r" => 0,
			"msg" => '查询成功',
			'project_list' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$user_id = input('user_id');
		$search_query = input('search_query');
		$project_ids_str = input('project_ids');
		
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		$project_atten = model('ProjectAttention');
//		$project_tcs = new TcsQcloudApi( 58740002 );
		if( empty($search_query) ){
			$res = $project->get_latest_hot_project();
			$project_id_arr = array_column( $res, 'project_id');
			$project_ids_str = implode( ',', $project_id_arr);
		}else{
			//search
			$res = $project -> getSearchProjects( $project_ids_str );
		}
		if( empty($project_ids_str) || $project_ids_str == ''){
			$ret['msg'] = '数据为空';
			return json_encode($ret);
			exit;
		}
		$users = $user_project_tag -> get_user_info_by_project_ids( $project_ids_str );
		$members = $user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = array();
		foreach( $members as $v){
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
		$atten_arr = $project_atten -> getProjectAttenNum($project_ids_str);
		$arr = [];
		foreach($atten_arr as $val){
			$arr[$val['project_id']] = $val['atten_num'];
		}
		//dump($users);
		foreach($res as $k => &$v){
			foreach($users as $u){
				if( $v['project_id'] == $u['project_id'] ){
					$v['user_id'] = $u['user_id'];
					$v['username'] = $u['username'];
					$v['src_name'] = $u['src_name'];
					$v['path'] = $u['path'];
					$v['access_url'] = $u['access_url'];
					break;
				}
			}
			$v['project_atten_num'] = empty($arr[$v['project_id']])?0:$arr[$v['project_id']];
			$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']];
		}
		$ret['project_list'] = $res;
		//dump($res);
		return json_encode($ret);
	}
	//项目基本信息
	public function get_project_baseinfo(){
		$ret = [ 
			"r" => 0,
			"msg" => '查询成功',
			'project' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');
		$user_id = input('user_id');
		if( session('userinfo') != null ){
			$user_id = session('userinfo')['user_id'];
		}
		if( $project_id <= 0 ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
			return json_decode($ret);
			exit;
		}
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		$src_relation = model('SrcRelation');
		$project_tag = model('ProjectTag');
//		$project_atten = model('ProjectAttention');
		$collect = model('Collect');
		$praise = model('Praise');
		
		$projectInfo = $project->get_project_by_id();
		$tags = $user_project_tag->get_tag_by_userid_projectid();
		$srcs = $src_relation->getSrcinfo( $project_id, 1, 3);
		$address = $project_tag->get_tag_by_project_id();
//		$atten_num = $project_atten -> getProjectAttenNumByProjectId();
		$collect_num = $collect -> getProjectAttenNum( $project_id, $user_id, 1);
		
		$ret['project'] = (count($projectInfo) > 0)?array_merge( $ret['project'], $projectInfo[0]):[];
		$ret['project']['tags'] = (count($tags)> 0)?$tags:[];
		$ret['project']['srcs'] = (count($srcs)> 0)?$srcs[0]:[];
		$ret['project']['address'] = (count($address) > 0)?$address[0]:[];
//		$ret['project']['atten_num'] = (count($atten_num) > 0)?count($atten_num):0;
		if( $user_id > 0){
//			$is_atten_res = $project_atten -> check_project_atten_me($project_id, $user_id, 1);
//			$user_ids = array_column($atten_num,'user_id');
			$is_collect_res = $collect -> getCollectId( $project_id, $user_id, 1);
			$ret['project']['login']['is_collect'] = (count($is_collect_res) > 0 && $is_collect_res[0]['collect_id'] > 0)?1:0;
			$is_praise_res = $praise -> getPraiseId($project_id, $user_id, 1);
			$ret['project']['login']['is_praise'] = (count($is_praise_res) > 0 && $is_praise_res[0]['praise_id'] > 0)?1:0;
		}
		return json_encode( $ret );
	}
	
	public function update_project_baseinfo(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$opt_id = session('userinfo')['user_id'];
		}
		$project_id = input('project_id');
		$opt_id = input('opt_id');
		$reg_date = '/^d{4}-d{2}-d{2} d{2}:d{2}:d{2}$/s';
		if( $project_id <= 0 || $opt_id <= 0 || preg_match( $reg_date, input('project_start_time')) == 0 || preg_match( $reg_date, input('project_end_time')) == 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$project = model('Project');
		$res = $project -> updateProjectById();
		return json_encode( $ret);
	}
	
	//项目中任务详情
	public function get_task_detail_by_projectid(){
		$ret = [
			"r" => -1,
			"msg" => '',
			'data' => [],
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$project_id = input('project_id');
		$user_id = input('user_id');
		if(session('userinfo') != null){
			$user_id = session('userinfo')['user_id'];
		}
		if($project_id > 0){
			$project = model('Project');
			$user_project_tag = model('UserProjectTag');
			$project_task_user = model('ProjectTaskUser');
			$src_relation = model('SrcRelation');
			$comment = model('Comment');
			$collect = model('Collect');
			$praise = model('Praise');
			$collect = model('Collect');
			
			$ret['data']['skill'] = $user_project_tag->get_tag_by_userid_projectid();
			
			$tasks = $project_task_user->get_task_src_comment_by_project_id();
			$ret['data']['tasks'] = [];
			if( count($tasks) > 0){
				$task_arr = array_column($tasks, 'task_id');
				$src_arr = $src_relation->get_task_src_by_task_ids(implode(',', $task_arr), 2);//task
				$res = $collect -> getTasksCollectNum( implode(',', $task_arr));//dump($res);
				if( $user_id > 0){//login
					$task_praise_res = $praise -> get_user_praise( $user_id, implode(',', $task_arr), 2);//dump($task_praise_res);
					$task_collect_res = $collect -> get_user_collect( $user_id, implode(',', $task_arr), 2);//dump($task_collect_res);
					$task_praise = [];
					foreach($task_praise_res as $r){
						$task_praise[$r['cid']] = $r['praise_id'];
					}
					$task_collect = [];
					foreach($task_collect_res as $r){
						$task_collect[$r['cid']] = $r['collect_id'];
					}
				}
				$collect_arr = [];
				foreach( $res as $c){
					$collect_arr[$c['task_id']] = $c['collect_num'];
				}
				foreach($tasks as &$v){
					$v['collect_num'] = isset($collect_arr[$v['task_id']])?$collect_arr[$v['task_id']]:0;
					foreach($src_arr as $value){
						if($value['task_id'] == $v['task_id']){
							$v = array_merge( $v, $value);
							break;
						}
					}
					$v['login']['is_praise'] = isset($task_praise[$v['task_id']])?1:0;
					$v['login']['is_collect'] = isset($task_collect[$v['task_id']])?1:0;
				}
				$comment_arr = $comment->get_task_comment_by_task_ids(implode(',', $task_arr), 2);//dump($comment_arr);
				foreach($tasks as &$t){
					$t['comment'] = [];
					foreach($comment_arr as $c){
						if($t['task_id'] == $c['cid']){
							array_push($t['comment'], $c);
						}
					}
				}
				
				$ret['data']['tasks'] = $tasks;
			}
			$ret['r'] = 0;
			$ret['msg'] = '查询成功';
		}else{
			$ret['msg'] = '参数不符合要求';
		}
//		dump( $ret );
		return json_encode($ret);
	}
	
	public function add_project_task(){
		$ret = [
			'r' => 0,
			'msg' => '创建成功',
			'task_id' => '',
			'src_id' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		$project_id = input('project_id');
		if( $project_id <= 0 || $user_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
			return json_encode($ret);
			exit;
		}
		$task = model('Task');
		$project_task_user = model('ProjectTaskUser');
		$src = model('Src');
		$src_relation = model('SrcRelation');
		Db::startTrans();
		try{
			$task->data(['title'=>input('title'),'task_order' => input('task_order')]) -> isUpdate(false) -> save();
			$task_id = $task->task_id;
			
			$project_task_user -> data(['project_id' => $project_id,'task_id'=>$task_id,'user_id'=>$user_id]) -> isUpdate(false) -> save();
			
			$info = pathinfo( input('resource_path') );
			$path_arr = isset($info['dirname'])?explode('/', $info['dirname']):[];
			$src_arr = [
						'src_name' => isset($info['basename'])?$info['basename']:'',
						'type' => input('type'),
						'path' => '/' . ( (count($path_arr) > 0)?$path_arr[count($path_arr) - 1]:'' ),
						'access_url' => input('access_url'),
						'resource_path' => input('resource_path'),
						'url' => input('url'),
						'source_url' => input('source_url')
						];
			$src->data( $src_arr )->isUpdate(false)->save();
			$src_id = $src->src_id;
			$src_relation->data(['src_id'=>$src_id,'relation_id'=>$task_id,'type'=>2])->isUpdate(false)->save();
			Db::commit();
			$ret['task_id'] = $task_id;
			$ret['src_id'] = $src_id;
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
		}
		return json_encode($ret);
	}
	public function add(){
		$ret = [ 
			"r" => -1,
			"msg" => ''
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode( $ret);
			exit;
		}else{
			$user_id = session('userinfo')['user_id'];
		}
		if( $user_id <= 0){
			$ret['msg'] = '用户id 不能为空';
			return json_encode($ret);
			exit;
		}
		$name = trim(input('name'));
		$type = input('type');
		$en_name = trim(input('en_name'));
		$cat_name = trim(input('cat_name'));
		$address = trim(input('address'));
		$project_start_time = input('project_start_time');
		$project_end_time = input('project_end_time');
		$intro = trim(input('intro'));
		$skill_ids = trim(input('skill_ids'));
		$tasks = json_decode(input('task'),true);
		$cover = json_decode(input('cover'),true);
		
		$status = 0;	//待审
		$project = model('Project');
		$task = model('Task');
		
//		$project_task = model('ProjectTask');
//		$user_task = model('UserTask');
		
		$user_project_tag = model('UserProjectTag');
		$project_task_user = model('ProjectTaskUser');
		
		
		$src = model('Src');
		$src_relation = model('SrcRelation');
		$user_tim = new UserTim;
		
		Db::startTrans();
		try{
			$project->name = $name;
			$project->type = $type;
//			$project->en_name = $en_name;
//			$project->cat_name = $cat_name;
//			$project->address = $address;
//			$project->project_start_time = $project_start_time;
//			$project->project_end_time = $project_end_time;
			$project->intro = $intro;
			$project->save();
			
			$project_id = $project->project_id;
//			$skill_arr = explode(',' , $skill_ids);
//			if(count($skill_arr) > 0){
//				$list = [];
//				for( $i = 0; $i < count($skill_arr); $i++){
//					array_push($list, ['user_id' => $user_id,'project_id' => $project_id,'tag_id' => $skill_arr[$i]]);
//				}
//				$user_project_tag->saveAll( $list );
//			}
			$user_project_tag -> save(['project_id'=>$project_id,'user_id'=>$user_id,'tag_id'=>0,'user_type'=>1 ]);
			foreach( $cover as $v ){
				$info = pathinfo($v['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$cover_arr = [
							'src_name'=> $info['basename'],
							'type'=> 3,
							'src_order'=> 0,
							'path'=> '/' . $path_arr[count($path_arr) - 1],
							'access_url'=>$v['access_url'],
							'resource_path'=>$v['resource_path'],
							'url'=>$v['url'],
							'source_url'=>$v['source_url'],
							'status'=> 0
							];
				$src->data( $cover_arr )->isUpdate(false)->save();
				$src_relation->data([ 'src_id' => $src->src_id, 'relation_id' => $project_id, 'type' => 1])->isUpdate(false)->save();//项目3
			}
			for($j = 0; $j < count($tasks); $j++){

				$task->data(['title'=>$tasks[$j]['title'],'description' => $tasks[$j]['description'],'task_order' => $tasks[$j]['task_order'] ])->isUpdate(false)->save();
				
				$task_id = $task->task_id;
				
//				$project_task->data(['project_id'=>$project_id,'task_id'=>$task_id])->isUpdate(false)->save();
//				$user_task->data(['user_id'=>$user_id,'task_id'=>$task_id])->isUpdate(false)->save();
				$project_task_user -> data(['project_id' => $project_id,'task_id'=>$task_id,'user_id'=>$user_id]) -> isUpdate(false) -> save();

				$info = pathinfo($tasks[$j]['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$src_arr = [
							'src_name' => $info['basename'],
							'type' => $tasks[$j]['type'],
							'src_order' => $tasks[$j]['src_order'],
							'path' => '/' . $path_arr[count($path_arr) - 1],
							'access_url' => $tasks[$j]['access_url'],
							'resource_path' => $tasks[$j]['resource_path'],
							'url' => $tasks[$j]['url'],
							'source_url' => $tasks[$j]['source_url'],
							'status' => $tasks[$j]['status']
							];
				$src->data( $src_arr )->isUpdate(false)->save();
				$src_id = $src->src_id;

				$src_relation->data(['src_id'=>$src_id,'relation_id'=>$task_id,'type'=>2])->isUpdate(false)->save();
			}
//			$user_tim->group_create_group($project_id,'Public', $name, $user_id, 1);// create group - 1:work 2:life
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '创建项目成功';
			$ret['project_id'] = $project_id;
		}catch( \Exception $e){
			Db::rollback();
			$ret['msg'] = '添加数据异常'.$e;
		}
		$this->add_search_key_by_project_id($project_id);
		return json_encode($ret);
	}
	
	
	
	public function upload(Request $request){
		$ret = [
			"r" => -1,
			"msg" => ''
		];
		$user_id = input('user_id');
		$files = request()->file('image');
		if( is_array($files) ){
			foreach($files as $file){
				$info = $file->validate(['size'=>10*1024*1024,'ext'=>'jpg,png,gif,jpeg,bmp'])->move(ROOT_PATH.'public/upload/img/'.$user_id.'/');
				
			}
		}else{
			
		}
	}
	
	
	
}



?>