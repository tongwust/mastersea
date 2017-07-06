<?php
namespace app\home\Controller;
use think\Controller;

class Encrypt extends Controller{
	protected $encrypt_str;
	protected $encrypt_key;
	const ENCRYPT_STR = '*^&$#@`#';
	const ENCRYPT_KEY = 'mastersea';
	
	public function __construct($encrypt_str = self::ENCRYPT_STR, $encrypt_key = self::ENCRYPT_KEY){
		
		$this -> encrypt_str = $encrypt_str;
		$this -> encrypt_key = $encrypt_key;
	}
	public function token_encrypt(){
		$ret = [
			'r' => 0,
			'msg' => '',
		];
		$str = $this -> encrypt_str;
		$key = $this -> encrypt_key;
		$key = md5( $key);
	   	$k = md5( rand( 0, 100));//相当于动态密钥
	   	$k = substr( $k, 0, 8);
	   	$tmp = "";
	   	for( $i = 0; $i < strlen( $str); $i++){
	    	$tmp .= substr( $str, $i, 1) ^ substr( $key, $i, 1);
	   	}
	   	$ret['token'] = base64_encode( $k.$tmp);
	   	
	   	return json_encode($ret);
	}
	
	public function token_decode( $str){
		$key = $this -> encrypt_key;
	   	$len = strlen( $str);
	   	$key = md5( $key);
	  	$str = base64_decode( $str);
	   	$str = substr( $str, 8, $len-8);
	   	$tmp = "";
	   	for( $i = 0; $i < strlen( $str); $i++){
	    	$tmp .= substr( $str, $i, 1) ^ substr( $key, $i, 1);
	   	}
	   	return $tmp;
	}
	
}



?>