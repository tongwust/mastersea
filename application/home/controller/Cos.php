<?php
namespace app\home\controller;
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'Auth.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'Helper.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'HttpClient.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'Api.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'HttpRequest.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'HttpResponse.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'LibcurlWrapper.php');
require_once(ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'QCloud' . DIRECTORY_SEPARATOR . 'Cos' . DIRECTORY_SEPARATOR . 'SliceUploading.php');

use think\Controller;
use QCloud\Cos\Api;

class Cos extends Controller{
	
	public $appid = '1253556758';
	public $bucket = COS_BUCKET;
	public $region = 'tj';
	
	protected $secret_id;
	protected $secret_key;
	
	protected $cosApi;
	
	const APPID = '1253556758';
	const BUCKET = COS_BUCKET;
	const REGION = 'tj';
	const SECRET_ID = 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N';
	const SECRET_KEY = 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
	
	public function __construct($appid = self::APPID, $bucket = self::BUCKET, $region = self::REGION, $secret_id = self::SECRET_ID, $secret_key = self::SECRET_KEY){
		date_default_timezone_set('PRC');
		$this->appid = $appid;
		$this->bucket = $bucket;
		$this->region = $region;
		$this->secret_id = $secret_id;
		$this->secret_key = $secret_key;
		$config = array(
		    'app_id' => $this->appid,
		    'secret_id' => $this->secret_id,
		    'secret_key' => $this->secret_key,
		    'region' => $this->region,   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
		    'timeout' => 60
		);
		$this->cosApi = new Api($config);
	}
	
	public function cos_upload($src, $dst){
		
		// Upload file into bucket.
		$ret = $this -> cosApi -> upload($this->bucket, $src, $dst);
		return $ret;
	}
	
	public function cos_delfile($dst){
		//del file
		$ret = $this -> cosApi -> delFile($this->bucket, $dst);
		return $ret;
	}
}




?>