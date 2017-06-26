<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Config;
use think\Cookie;


class CosCloud extends Controller{
	
	public $appid = '1253556758';
	public $bucket = 'shining';
	public $region = 'tj';
	
	protected $secret_id;
	protected $secret_key;
	
	const APPID = '1253556758';
	const BUCKET = 'shining';
	const REGION = 'tj';
	const SECRET_ID = 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N';
	const SECRET_KEY = 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
	
	public function __construct($appid = self::APPID, $bucket = self::BUCKET, $region = self::REGION, $secret_id = self::SECRET_ID, $secret_key = self::SECRET_KEY){
		
		$this->appid = $appid;
		$this->bucket = $bucket;
		$this->region = $region;
		$this->secret_id = $secret_id;
		$this->secret_key = $secret_key;
		
	}

	public function gen_multi_sig(){
		$ret = [
			'r' => 0,
			'msg' => '',
			'sig' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$random = intval( (mt_rand()/mt_getrandmax())*pow(2, 32) );
		$now = time();
		$e = $now + 60;
		$path = '';
		$multi_str = 'a='.$this->appid.'&k='.$this->secret_id.'&e='.$e.'&t='.$now.'&r='.$random.'&f='.$path.'&b='.$this->bucket;
		
		$multi_sig = base64_encode(hash_hmac('SHA1',$multi_str,$this->secret_key,true).$multi_str);
		$ret['sig'] = $multi_sig;
		
		return json_encode( $ret );
	}
	
	public function gen_once_sig(){
		
		$ret = [
			'r' => 0,
			'msg' => '',
			'sig' => '',
		];
		$encrypt = new Encrypt;
		if( $encrypt -> token_decode(input('token')) != Encrypt::ENCRYPT_STR ){
			$ret['r'] = -10;
			$ret['msg'] = '接口验证失败';
			return json_encode($ret);
			exit;
		}
		$random = intval( (mt_rand()/mt_getrandmax())*pow(2, 32) );
		$now = time();
		$e = 0;
		$path = input('resource_path');
		$once_str = 'a='.$this->appid.'&k='.$this->secret_id.'&e='.$e.'&t='.$now.'&r='.$random.'&f='.$path.'&b='.$this->bucket;
		
		$once_sig = base64_encode(hash_hmac('SHA1', $once_str, $this->secret_key, true).$once_str);
		$ret['sig'] = $once_sig;
		
		return json_encode( $ret );
	}
}




?>