<?php
namespace app\home\controller;
use think\Controller;

class Encrypt extends Controller{
	
	const ENCRYPT_STR = '*^&$#@`#';
	const ENCRYPT_KEY = 'mastersea';
	
	public function token_encrypt(){
		$ret = [
			'r' => 0,
			'msg' => '',
		];
		$str = self::ENCRYPT_STR;
		$key = self::ENCRYPT_KEY;
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
		$key = self::ENCRYPT_KEY;
	   	$len = strlen( $str);
	   	$key = md5( $key);
	  	$str = base64_decode( $str);
	   	$str = substr( $str, 8, $len-8);
	   	$tmp = "";
	   	for( $i = 0; $i < strlen( $str); $i++){
	    	$tmp .= substr( $str, $i, 1) ^ substr( $key, $i, 1);
	   	}
	   	return $tmp == self::ENCRYPT_STR;
	}
	
}



?>