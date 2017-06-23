<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Cookie;

class UserTim extends Controller
{
	#app基本信息
	protected $sdkappid ;
	protected $usersig ;
	protected $identifier ;
	protected $sig = '';
    
    const SDKAPPID = 1400028629;
    const USERSIG = 'eJxljsFOg0AURfd8BWFtzDAMFExctNjEpqilUFu6IcgMdNowTIZXsBj-XcUmkni357x374em67oRB9Ftluf1WUAKF8kM-U43kHHzB6XkNM0gtRT9B9m75IqlWQFMDdC0bRsjNHY4ZQJ4wa8G1KLszg2MjIae0qHm9wX5vseug72xwssBPs03-iL0**VaoulqNiNLFbd2vovwdtKenPBVymn2EjWo64JiH-hluChpYs3dI6mg2xRBHLUP-a7frw7ho6igPbI4WR*SLXHU23N3P6oEXrHrIM-0XDJx3RFtmWp4LQYBI9M2sYV*Ymif2hcpKF-D';
    const IDENTIFIER = 'tongwust';
    #开放IM https接口参数, 一般不需要修改
	protected $http_type = 'https://';
	protected $method = 'post';
	protected $im_yun_url = 'console.tim.qq.com';
	protected $version = 'v4';
	protected $contenttype = 'json';
	protected $apn = '0';
	
	public function __construct($sdkappid = self::SDKAPPID, $usersig = self::USERSIG, $identifier = self::IDENTIFIER)
    {
    	$this->sdkappid = $sdkappid;
    	$this->usersig = $usersig;
    	$this->identifier = $identifier;
        parent::__construct();
    }
	public function index(){
    	$view = new View();
    	$user_id = 3;
    	$username = 'tong';
    	$nick = $username;
    	$face_url = '';
    	$this->gen_sig( $user_id );
    	cookie( 'sig', $this->sig, ['prefix' => 'think_', 'expire' => 179*24*3600]);
    	$ret = $this->account_import( $user_id, $nick, $face_url);
    	return $view->fetch( './chat/index', ['user_id' => $user_id, "user_sig" => $this->sig]);
    }
    
    public function open_msg_svc_get_history( $chat_type, $msg_time){
    	$result = [
	    	"r"  => -1,
	    	'msg'=> '',
	    ];
    	$chat_type = input('chat_type');
    	$msg_time = input('msg_time');
    	#构造消息
    	$msg = array(
    		'ChatType' => $chat_type,
    		'MsgTime'  => $msg_time
    	);
    	$req_data = json_encode($msg);
    	$ret = $this->api( 'open_msg_svc', 'get_history', $this->identifier, $this->usersig, $req_data);
    	$ret = json_decode($ret, true);
		if(count($ret) > 0 && $ret['ActionStatus'] == 'OK' && $ret['ErrorCode'] == 0){
			$res = $this->getFileContent($ret['File'][0]['URL'],'','',1);
			$msg_arr = json_decode($res['data'],true);
			$msg_list = $msg_arr['MsgList'];
			$history_msg = model('HistoryMsg');
			for($i = 0; $i < count($msg_list); $i++){
				$history_msg->chat_type = $msg_arr['ChatType'];
				$history_msg->msg_time = $msg_arr['MsgTime'];
				$history_msg->from_account = $msg_list[$i]['From_Account'];
				if($msg_arr['ChatType'] == 'Group'){
					$history_msg->group_id = $msg_list[$i]['GroupId'];
				}else{
					$history_msg->to_account = $msg_list[$i]['To_Account'];
					$history_msg->msg_random = $msg_list[$i]['MsgRandom'];
				}
				$history_msg->msg_timestamp = $msg_list[$i]['MsgTimestamp'];
				$history_msg->msg_seq = $msg_list[$i]['MsgSeq'];
				$history_msg->msg_body = json_encode($msg_list[$i]['MsgBody']);
				$history_msg->save();
			}
			if(count($msg_list) > 0){
				$result['r'] = 0;
				$result['msg'] = '获取文件并插入数据库成功';
			}else{
				$result['msg'] = '获取文件成功，数据为空';
			}
		}else{
			$result['msg'] = 'api获取历史记录文件出错';
		}
		return json($result);
    }
    
  	public function getFileContent( $url, $path='', $filename='', $type = 1){
  		$ret = [
	    	"r"  => -1,
	    	'msg'=> '',
	    ];
	    if( $url == '')	{	$ret['msg'] = 'url不能为空';	return $ret;	};
	    switch($type){
	    	case 0:
	    		$ch=curl_init();
		        $timeout=5;
		        curl_setopt($ch,CURLOPT_URL,$url);
		        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);//最长执行时间
		        curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);//最长等待时间
		        $file_content = curl_exec($ch);
		        curl_close($ch);
		        break;
		    case 1:
		    	ob_start();
		        readgzfile($url);
		        $file_content = ob_get_contents();
		        ob_end_clean();
		        break;
		    case 2:
		    	$file_content = file_get_contents($url);
		    	break;
	    }
	    if(empty($file_content)){
			$ret['下载错误,无法获取下载文件！'];
	    }else{
	    	$ret['r'] = 0;
	    }
	    $ret['data'] = $file_content;
	    return $ret;
	}
    //friend add
    public function sns_friend_add($account_id, $receiver)
	{
		$account_id = input('account_id');
		$receiver = input('receiver');
		#构造新消息
		$msg = array(
				'From_Account' => $account_id,
				'AddFriendItem' => array(),
				'AddType' => "Add_Type_Single",
				'ForceAddFlags' => 1
				);
		$receiver_arr = array(
			'To_Account' => $receiver,
			'Remark' => "",
			'GroupName' => "",
			'AddSource' => "AddSource_Type_Unknow",
			'AddWording' => "I'm Test1"
		);
		array_push($msg['AddFriendItem'], $receiver_arr);
		#将消息序列化为json串
		$req_data = json_encode($msg);

		$ret = $this->api("sns", "friend_add", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
    //delete friend single
    public function sns_friend_delete($account_id, $frd_id)
	{
		$account_id = input('account_id');
		$frd_id = input('frd_id');
		#构造新消息
		$frd_list = array();
		//要添加的好友用户
		array_push($frd_list, $frd_id);

		$msg = array(
				'From_Account' => $account_id,
				'To_Account' => $frd_list,
				'DeleteType' => "Delete_Type_Single"
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("sns", "friend_delete", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
	//get friend all
	function sns_friend_get_all($account_id)
	{
		#构造高级接口所需参数
//		$account_id = input('account_id');
		$tag_list = array(
				"Tag_Profile_IM_Nick",
				"Tag_SNS_IM_Remark"
				);
		$ret = $this->sns_friend_get_all2($account_id, $tag_list);
		return $ret;
	}

	function sns_friend_get_all2($account_id, $tag_list)
	{
		#构造新消息
		$msg = array(
				'From_Account' => $account_id,
				'TimeStamp' => 0,
				'TagList' => $tag_list,
				'LastStandardSequence' => 1,
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("sns", "friend_get_all", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
	
    //friend import
    public function sns_friend_import($account_id, $receiver)
	{
		$account_id = input('account_id');
		$receiver = input('receiver');
		#构造新消息
		$msg = array(
				'From_Account' => $account_id,
				'AddFriendItem' => array(),
				'AddType' => "Add_Type_Both",
				'ForceAddFlags' => 1
				);
		$receiver_arr = array(
			'To_Account' => $receiver,
			'Remark' => "",
			'GroupName' => "",
			'AddSource' => "AddSource_Type_Unknow",
			'AddWording' => "I'm Test1"
		);
		array_push($msg['AddFriendItem'], $receiver_arr);
		#将消息序列化为json串
		$req_data = json_encode($msg);echo $req_data;

		$ret = $this->api("sns", "friend_import", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
    //create group
    function group_create_group( $group_id, $group_type = 'Public', $group_name = 'work', $owner_id, $type = 1)
	{
//		$group_id = input('group_id');
//		$group_type = input('group_type');
//		$group_name = input('group_name');
//		$owner_id = input('owner_id');
//		$type = input('type');//1:work,2:life
		$AppDefinedData = array(
			"Key" => "g_type", // APP自定义的字段Key
        	"Value" => $type // 自定义字段的值
		);
		#构造高级接口所需参数
		$info_set = array(
				'group_id' => $group_id,
				'introduction' => null,
				'notification' => null,
				'face_url' => null, 
				'max_member_num' => 2000,
				'AppDefinedData' => array(), 
				);
		$mem_list = array();
		array_push($info_set['AppDefinedData'], $AppDefinedData);
		$ret = $this->group_create_group2($group_type, $group_name, $owner_id, $info_set, $mem_list);
		return json($ret);
	}
	
	function group_get_appid_group_list()
	{
		#构造高级接口所需参数
		$ret = $this->group_get_appid_group_list2(50, null, null);
		return $ret;
	}
	
	function group_get_appid_group_list2($limit, $offset, $group_type)
	{

		#构造新消息
		$msg = array(
				'Limit' => $limit,
				'Offset' => $offset,
				'GroupType' => $group_type
				);  
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "get_appid_group_list", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
	
	function group_get_group_member_info($group_id, $limit, $offset)
	{
		#构造新消息
		$msg = array(
				"GroupId" => $group_id,
				"Limit" => $limit,
				"Offset" => $offset
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "get_group_member_info", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
	
	function group_create_group2($group_type, $group_name, $owner_id, $info_set, $mem_list)
	{

		#构造新消息
		$msg = array(
				'Type' => $group_type,
				'Name' => $group_name,
				'Owner_Account' => $owner_id,
				'AppDefinedData' => $info_set['AppDefinedData'],
				'GroupId' => $info_set['group_id'], 
				'Introduction' => $info_set['introduction'],
				'Notification' => $info_set['notification'],
				'FaceUrl' => $info_set['face_url'],
				'MaxMemberCount' => $info_set['max_member_num'],
			//	'ApplyJoinOption' => $info_set['apply_join'],
				'MemberList' => $mem_list
				);  
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "create_group", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
    
    function group_add_group_member($group_id, $member_id, $silence)
	{
		$group_id = input('group_id');
		$member_id = input('member_id');
		$silence = input('silence');
		#构造新消息
		$mem_list = array();
		$mem_elem = array(
				"Member_Account" => $member_id
				);
		array_push($mem_list, $mem_elem);
		$msg = array(
				"GroupId" => $group_id,  
				"MemberList" => $mem_list,
				"Silence" => $silence
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "add_group_member", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
	
    function group_delete_group_member($group_id, $member_id, $silence)
	{
		$group_id = input('group_id');
		$member_id = input('member_id');
		$silence = input('silence');
		#构造新消息
		$mem_list = array();
		array_push($mem_list, $member_id);
		$msg = array(
				"GroupId" => $group_id,  
				"MemberToDel_Account" => $mem_list,
				"Silence" => $silence
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "delete_group_member", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
	
	function group_destroy_group($group_id)
	{
		$group_id = input('group_id');
		#构造新消息
		$msg = array(
				"GroupId" => $group_id,  
				)
			;  
		#将消息序列化为json串
		$req_data = json_encode($msg);
	
		$ret = $this->api("group_open_http_svc", "destroy_group", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
	//get user joined group
	function group_get_joined_group_list($account_id)
	{
		$account_id = input('account_id');
		#构造高级接口所需参数
		$base_info_filter = array(
				"Type",               //群类型(包括Public(公开群), Private(私密群), ChatRoom(聊天室))
				"Name",               //群名称
				"Introduction",       //群简介
				"Notification",       //群公告
				"FaceUrl",            //群头像url地址
				"CreateTime",         //群组创建时间
				"Owner_Account",      //群主id
				"LastInfoTime",       //最后一次系统通知时间
				"LastMsgTime",        //最后一次消息发送时间
				"MemberNum",          //群组当前成员数目
				"MaxMemberNum",       //群组内最大成员数目
				"ApplyJoinOption"     //申请加群处理方式(比如FreeAccess 自由加入, NeedPermission 需要同意)
				);
		$self_info_filter = array(
				"Role",            //群内身份(Amin/Member)
				"JoinTime",        //入群时间
				"MsgFlag",         //消息屏蔽类型
				"UnreadMsgNum"     //未读消息数量
				);

		$ret = $this->group_get_joined_group_list2($account_id, null, $base_info_filter, $self_info_filter);
		return json($ret);
	}
	
	function group_get_joined_group_list2($account_id, $group_type, $base_info_filter, $self_info_filter)
	{
		#构造新消息
		$filter = new Filter();
		$filter->GroupBaseInfoFilter = $base_info_filter;
		$filter->SelfInfoFilter = $self_info_filter;
		$msg = array(
				"Member_Account" => $account_id, 
				"ResponseFilter" => $filter
				);  
		#将消息序列化为json串
		$req_data = json_encode($msg);

		$ret = $this->api("group_open_http_svc", "get_joined_group_list", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
	// get role in group
	function group_get_role_in_group($group_id, $member_id)
	{
		$group_id = input('group_id');
		$member_id = input('member_id');
		#构造新消息
		$mem_list = array();
		array_push($mem_list, $member_id);
		$msg = array(
				"GroupId" => $group_id,
				"User_Account" => $mem_list,
				);
		#将消息序列化为json串
		$req_data = json_encode($msg);

		$ret = $this->api("group_open_http_svc", "get_role_in_group", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return json($ret);
	}
	public function openim_send_msg($account_id, $receiver, $text_content)
	{
		$account_id = input('account_id');
		$receiver = input('receiver');
		$text_content = input('text_content');
		#构造高级接口所需参数
		$msg_content = array();
		//创建array 所需元素
		$msg_content_elem = array(
				'MsgType' => 'TIMTextElem',  //文本类型
				'MsgContent' => array(
					'Text' => $text_content, //hello 为文本信息
					)
				);
		//将创建的元素$msg_content_elem, 加入array $msg_content
		array_push($msg_content, $msg_content_elem);
		$ret = $this->openim_send_msg2($account_id, $receiver, $msg_content);
		return json($ret);
	}
	public function openim_send_msg2($account_id, $receiver, $msg_content)
	{
		#构造新消息 
		$msg = array(
				'To_Account' => $receiver,
				'MsgSeq' => rand(1, 65535),
				'MsgRandom' => rand(1, 65535),
				'MsgTimeStamp' => time(),
				'MsgBody' => $msg_content,
				'From_Account' => $account_id
				); 
		#将消息序列化为json串
		$req_data = json_encode($msg);

		$ret = $this->api("openim", "sendmsg", $this->identifier, $this->usersig, $req_data);
		$ret = json_decode($ret, true);
		return $ret;
	}
    public function gen_sig($user_id){
    	$result = [
			'r' => -1,
			'msg' => '',
			'sig' => '',
		];
    	try{
		    $api = new TLSSigAPI();
		    $api->SetAppid(1400028629);
		    $private = file_get_contents(APP_KEYS.DIRECTORY_SEPARATOR.'private_key');
		    $api->SetPrivateKey($private);
		    $public = file_get_contents(APP_KEYS.DIRECTORY_SEPARATOR.'public_key');
		    $api->SetPublicKey($public);
		    $sig = $api->genSig($user_id);
		    $this->sig = $sig;
		    $result['sig'] = $sig;
			$result['r'] = 0;
		}catch(Exception $e){
		    $result['msg'] = $e;
		}
		return $result;
    }
    
    public function account_import($identifier, $nick, $face_url)
    {
        #构造新消息 
        $msg = array(
            'Identifier' => $identifier,
            'Nick' => $nick,
            'FaceUrl' => $face_url,
        );
        #将消息序列化为json串
        $req_data = json_encode($msg);
        $ret = $this->api("im_open_login_svc", "account_import", $this->identifier, $this->usersig, $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }
    
	/** 
	 * 构造访问REST服务器的参数,并访问REST接口
	 * @param string $server_name 服务名
	 * @param string $cmd_name 命令名
	 * @param string $identifier 用户名
	 * @param string $usersig 用来鉴权的usersig
	 * @param string $req_data 传递的json结构
	 * $param bool $print_flag 是否打印请求，默认为打印
	 * @return string $out 返回的签名字符串
	 */
	public function api($service_name, $cmd_name, $identifier, $usersig, $req_data, $print_flag = false)
	{   
		//$req_tmp用来做格式化输出
		$req_tmp = json_decode($req_data, true);
		# 构建HTTP请求参数，具体格式请参考 REST API接口文档 (http://avc.qcloud.com/wiki/im/)(即时通信云-数据管理REST接口)
        $parameter =  "usersig=" . $this->usersig
            . "&identifier=" . $this->identifier
            . "&sdkappid=" . $this->sdkappid
            . "&contenttype=" . $this->contenttype;
		$url = $this->http_type . $this->im_yun_url . '/' . $this->version . '/' . $service_name . '/' .$cmd_name . '?' . $parameter;
		
		if($print_flag)
		{
			echo "Request Url:\n";
			echo $url;
			echo "\n";
			echo "Request Body:\n";
			echo json_format($req_tmp);
			echo "\n";
		}
		$ret = $this->http_req('https', 'post', $url, $req_data);
		return $ret;
	}
	
	/**
	 * 向Rest服务器发送请求
	 * @param string $http_type http类型,比如https
	 * @param string $method 请求方式，比如POST
	 * @param string $url 请求的url
	 * @return string $data 请求的数据
	 */
	public static function http_req($http_type, $method, $url, $data)
	{
		$ch = curl_init();
		if (strstr($http_type, 'https'))
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		}   

		if ($method == 'post')
		{
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else
		{
			$url = $url . '?' . $data;
		}		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT,100000);//超时时间

		try
		{
			$ret=curl_exec($ch);
		}catch(Exception $e)
		{
			curl_close($ch);
			return json_encode(array('ret'=>0,'msg'=>'failure'));
		}
		curl_close($ch);
		return $ret;
	}
}
//辅助过滤器类
class Filter{};


/** Json数据格式化方法
 * @param array $data 数组数据
 * @param string $indent 缩进字符，默认4个空格
 * @return sting json格式字符串
 */
function json_format($data, $indent=null)
{

	// 对数组中每个元素递归进行urlencode操作，保护中文字符
//	array_walk_recursive($data, 'json_format_protect');

	// json encode
	$data = json_encode($data);

	// 将urlencode的内容进行urldecode
	$data = urldecode($data);

	// 缩进处理
	$ret = '';
	$pos = 0;
	$length = strlen($data);
	$indent = isset($indent)? $indent : '    ';
	$newline = "\n";
	$prevchar = '';
	$outofquotes = true;
	for($i=0; $i<=$length; $i++){
		$char = substr($data, $i, 1);
		if($char=='"' && $prevchar!='\\')
		{
			$outofquotes = !$outofquotes;
		}elseif(($char=='}' || $char==']') && $outofquotes)
		{
			$ret .= $newline;
			$pos --;
			for($j=0; $j<$pos; $j++){
				$ret .= $indent;
			}
		}
		$ret .= $char;
		if(($char==',' || $char=='{' || $char=='[') && $outofquotes)
		{
			$ret .= $newline;
			if($char=='{' || $char=='['){
				$pos ++;
			}

			for($j=0; $j<$pos; $j++){
				$ret .= $indent;
			}
		}
		$prevchar = $char;
	}
	return $ret;
}


/**
 * json_formart辅助函数
 * @param String $val 数组元素
 */
function json_format_protect(&$val)
{
	if($val!==true && $val!==false && $val!==null)
	{
		$val = urlencode($val);
	}
}


?>