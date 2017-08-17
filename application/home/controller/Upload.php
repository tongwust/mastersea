<?php
namespace app\home\controller;
use think\Controller;
use think\Request;

class Upload extends Controller{
	
	public function upload_doc(Request $request){
		$ret = [
			"r" => 0,
			"msg" => 'upload succ',
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
//		$user_id = 1;
		// 获取表单上传文件
	    $files = request()->file("files");
	    $fnames = explode(',',input('fnames'));
	    if( count($fnames) == 0 || count($fnames) != count($files) ){
	    	$ret['r'] = -1;
	    	$ret['msg'] = 'fnames 参数不符';
	    	return json_encode( $ret);
	    	exit;
	    }
	    set_time_limit(0);
	    $i = 0;
	    trace( $files, 'files');
	    foreach($files as $file){
	        // 移动到框架应用根目录/public/uploads/ 目录下
	        $info = $file -> validate(['size'=>50*1024*1024,'ext'=>'doc,docx,xls,xlsx,ppt,pptx,txt,rtf']) -> move(ROOT_PATH . 'public' . DS . 'upload');
	        if( $info){
	            // 成功上传后 获取上传信息
	            try{
	            	$doc_file = $info->getPathname();
					$output_file = explode('.', $doc_file)[0].'.pdf';
					$command = 'java -jar /opt/jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar '.$doc_file.' '.$output_file;
					$res = exec($command);
					$cos = new Cos;
					$cos -> cos_upload($output_file, '/'.$user_id.'/'.$fnames[$i].'.pdf' );
					unlink( $doc_file);
					unlink( $output_file);
				}catch( \Exception $e){
					$ret['r'] = -2;
					$ret['msg'] = $e->getMessage();
					return json_encode( $ret);
	            	exit;
				}
	        }else{
	            // 上传失败获取错误信息
	            $ret['r'] = -3;
	            $ret['msg'] = $file->getError();
	            return json_encode( $ret);
	            exit;
	        }
	        $i++;
	    }
	    return json_encode($ret);
	}
	
}



?>