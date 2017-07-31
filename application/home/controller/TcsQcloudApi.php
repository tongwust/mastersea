<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;

class TcsQcloudApi extends Controller{
	
	protected $HttpUrl = "yunsou.api.qcloud.com";
	protected $HttpMethod = "POST";
	protected $isHttps = true;
	
	protected $secretKey;
	protected $secretId;
	protected $appId;
	//shining
	const SECRETKEY = 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
	const SECRETID	= 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N';
//	const APPID = '58260002';
	const REGION = 'bj';
	//project
//	const APPID = '58740002';
	
	public function __construct($appId = self::APPID, $secretId = self::SECRETID, $secretKey = self::SECRETKEY){
		
		$this->appId = $appId;
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
	}
	//云搜中添加项目数据
	public function projectDataManipulation(){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
		];
		$this->HttpMethod = 'POST';
		$op_type = (input('op_type'))?input('op_type'):'add';//default add
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		
		$content = $project -> getAllProjectList();
		$project_id_arr = array_column( $content, 'project_id');
		$project_ids_str = implode( ',', $project_id_arr);
		$tags = ($project_ids_str == '')?[]:$user_project_tag -> getUserTags($project_ids_str);
		$tag_names = [];
		foreach( $tags as $val){
			$tag_names[$val['project_id']] = isset($tag_names[$val['project_id']])?($tag_names[$val['project_id']].' '.$val['name']):$val['name'];
		}
		$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=> 'DataManipulation',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'op_type' => $op_type,
	        'appId' => $this->appId,
		);
		$PRIVATE_PARAMS = [];//dump($tag_names);dump($content);
		foreach($content as $k => $v){
			$PRIVATE_PARAMS['contents.'.$k.'.projectid'] = $v['project_id'];
			$PRIVATE_PARAMS['contents.'.$k.'.praisenum'] = $v['praise_num'];
			$PRIVATE_PARAMS['contents.'.$k.'.collectnum'] = $v['collect_num'];
			$PRIVATE_PARAMS['contents.'.$k.'.name'] = $v['name'];
			$PRIVATE_PARAMS['contents.'.$k.'.englishname'] = $v['en_name'];
			$PRIVATE_PARAMS['contents.'.$k.'.catname'] = $v['cat_name'];
			$PRIVATE_PARAMS['contents.'.$k.'.address'] = $v['address'];
			$PRIVATE_PARAMS['contents.'.$k.'.intro'] = $v['intro'];
			$PRIVATE_PARAMS['contents.'.$k.'.tagname'] = empty($tag_names[$v['project_id']])?'':$tag_names[$v['project_id']];
			$PRIVATE_PARAMS['contents.'.$k.'.createtime'] = $v['create_time'];
		}
		$res = $this->CreateRequest($COMMON_PARAMS, $PRIVATE_PARAMS);
		$ret['r'] = $res['retcode'];
		$ret['msg'] = $res['errmsg'];
		return json_encode($ret);
	}
	public function DataManipulationByProjectId($project_id){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
		];
		$this->HttpMethod = 'POST';
		$op_type = (input('op_type'))?input('op_type'):'add';//default add
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		
		$content = $project -> getSearchKeyByProjectId($project_id);
		$tags = $user_project_tag -> getUserTagsByProjectId($project_id);
		$tag_names = [];
		foreach( $tags as $val){
			$tag_names[$val['project_id']] = isset($tag_names[$val['project_id']])?($tag_names[$val['project_id']].' '.$val['name']):$val['name'];
		}
		$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=> 'DataManipulation',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'op_type' => $op_type,
	        'appId' => $this->appId,
		);
		$PRIVATE_PARAMS = [];//dump($tag_names);dump($content);
		foreach($content as $k => $v){
			$PRIVATE_PARAMS['contents.'.$k.'.projectid'] = $v['project_id'];
			$PRIVATE_PARAMS['contents.'.$k.'.praisenum'] = $v['praise_num'];
			$PRIVATE_PARAMS['contents.'.$k.'.collectnum'] = $v['collect_num'];
			$PRIVATE_PARAMS['contents.'.$k.'.name'] = $v['name'];
			$PRIVATE_PARAMS['contents.'.$k.'.englishname'] = $v['en_name'];
			$PRIVATE_PARAMS['contents.'.$k.'.catname'] = $v['cat_name'];
			$PRIVATE_PARAMS['contents.'.$k.'.address'] = $v['address'];
			$PRIVATE_PARAMS['contents.'.$k.'.intro'] = $v['intro'];
			$PRIVATE_PARAMS['contents.'.$k.'.tagname'] = empty($tag_names[$v['project_id']])?'':$tag_names[$v['project_id']];
			$PRIVATE_PARAMS['contents.'.$k.'.createtime'] = $v['create_time'];
		}
		$res = $this->CreateRequest($COMMON_PARAMS, $PRIVATE_PARAMS);
		trace('tcs_res',$res);
		$ret['r'] = $res['retcode'];
		$ret['msg'] = $res['errmsg'];
		return json_encode($ret);
	}
	
	public function yunsouDataManipulation(){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
//		if( !session('userinfo') ){
//			$ret['r'] = -100;
//			$ret['msg'] = '未登录';
//			return json_encode( $ret);
//			exit;
//		}else{
//			$user_id = session('userinfo')['user_id'];
//		}
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if( !($tag_id > 0 && $themeid > 0) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
			return json_encode( $ret );
			exit;
		}
		$op_type = (input('op_type'))?input('op_type'):'add';//default add
		
		$tag = model('Tag');
		$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=> 'DataManipulation',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'op_type' => $op_type,
	        'appId' => $this->appId,
		);
		$content = $tag->get_tag_by_themeid();//dump($content);
		$PRIVATE_PARAMS = [];
		foreach($content as $k => $v){
			$PRIVATE_PARAMS['contents.'.$k.'.name'] = $v['name'];
			$PRIVATE_PARAMS['contents.'.$k.'.tagid'] = $v['tagid'];
			$PRIVATE_PARAMS['contents.'.$k.'.themeid'] = $v['themeid'];
		}
		$res = $this->CreateRequest( $COMMON_PARAMS, $PRIVATE_PARAMS);
		//dump($res);
		$ret['r'] = isset( $res['retcode'])?$res['retcode']:$res['code'];
		$ret['msg'] = isset( $res['errmsg'])?$res['errmsg']:$res['message'];
		return json_encode( $ret);
	}
	
	public function yunsouDataSearch(){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
			'data' => [],
		];
		$search_query = input('search_query');
		if( $search_query == ''){
			$ret['r'] = -1;
			$ret['msg'] = '查询内容不能为空';
			return json_encode( $ret );
			exit;
		}
		$from = empty(input('from'))?0:intval(input('from'));
		$num_per_page = empty(input('page_size'))?10:intval(input('page_size'));
		$page_id = floor($from / $num_per_page);
		$COMMON_PARAMS = array(
	        'Nonce' => rand(),
	        'Timestamp' => time(NULL),
	        'Action' => 'DataSearch',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'search_query' => $search_query,
	        'page_id' => $page_id,
	        'num_per_page' => $num_per_page,
	        'appId' =>  $this->appId,
		);
		$PRIVATE_PARAMS = [];
		
		$res = $this->CreateRequest( $COMMON_PARAMS, $PRIVATE_PARAMS);
		$ret['r'] = $res['code'];
		$ret['msg'] = $res['message'];
		$ret['data'] = isset($res['data'])?$res['data']:[];
		return json_encode( $ret );
	}
	
	
	public function CreateRequest( $COMMON_PARAMS, $PRIVATE_PARAMS)
	{
	    $FullHttpUrl = $this->HttpUrl."/v2/index.php";
	
	    /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
	    $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
	    ksort($ReqParaArray);
	
	    $SigTxt = $this->HttpMethod.$FullHttpUrl."?";
	
	    $isFirst = true;
	    foreach ($ReqParaArray as $key => $value)
	    {
	        if (!$isFirst) 
	        { 
	            $SigTxt = $SigTxt."&";
	        }
	        $isFirst= false;
	        /*拼接签名原文时，如果参数名称中携带_，需要替换成.*/
	        if(strpos($key, '_'))
	        {
	            $key = str_replace('_', '.', $key);
	        }
	        $SigTxt = $SigTxt.$key."=".$value;
	    }
	    /*********************根据签名原文字符串 $SigTxt，生成签名 Signature******************/
	    $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $this->secretKey, true));
	    /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
	    $Req = "Signature=".urlencode($Signature);
	    foreach ($ReqParaArray as $key => $value)
	    {
	        $Req=$Req."&".$key."=".urlencode($value);
	    }
	    /*********************************发送请求********************************/
	    if($this->HttpMethod === 'GET')
	    {
	        if($this->isHttps === true)
	        {
	            $Req="https://".$FullHttpUrl."?".$Req;
	        }
	        else
	        {
	            $Req="http://".$FullHttpUrl."?".$Req;
	        }
	        //echo ($Req);
	        $Rsp = file_get_contents($Req);
	    }
	    else
	    {
	        if($this->isHttps === true)
	        {
	            $Rsp= $this->SendPost("https://".$FullHttpUrl,$Req);
	        }
	        else
	        {
	            $Rsp= $this->SendPost("http://".$FullHttpUrl,$Req);
	        }
	    }
		return json_decode( $Rsp, true);
	}

	public function SendPost($FullHttpUrl,$Req){
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Req);
        curl_setopt($ch, CURLOPT_URL, $FullHttpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->isHttps === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }
        $result = curl_exec($ch);
        
        return $result;
	}
	
}






?>