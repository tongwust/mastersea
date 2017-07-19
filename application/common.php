<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use phpmailer\PHPMailer;
use phpmailer\SMTP;
use imgEncode\GIFEncoder;

function interface_log($content){
	$datefile = date('Y-m-d',time());
	$file = ROOT_PATH.'/public/log/log'.$datefile.'.log';
	file_put_contents ( $file ,  $content ,  FILE_APPEND  |  LOCK_EX );
}

//function check_sess(){
//	$user_info = '';
//	if( cookie('PHPSESSID') != ''){
////		session_id(cookie('PHPSESSID'));
//		$user_info = session('userinfo');
//	}
//	return $user_info;
//}

// 应用公共文件
/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'utf8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    // 设定使用SMTP服务
    $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          // 使用安全协议
    $mail->Host = "smtp.exmail.qq.com"; // SMTP 服务器
    $mail->Port = 465;                  // SMTP服务器的端口号
    $mail->Username = "haiyang.tong@shining.one";    // SMTP服务器用户名
    $mail->Password = "19850829Tong";     // SMTP服务器密码
    $mail->SetFrom('haiyang.tong@shining.one', 'haiyang.tong');
    $replyEmail = 'haiyang.tong@shining.one';                   //留空则为发件人EMAIL
    $replyName = 'haiyang.tong';                    //回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
//  $mail->MsgHTML($body);
	$mail->Body = $body;
//	$mail->IsBodyHtml = true;
	$mail->ContentType = 'text/html';
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
//  return $mail->Send() ? true : $mail->ErrorInfo;
	if(!$mail->send()){// 发送邮件  
        echo "Message could not be sent.";  
        return "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息  
    }else{  
        return true;  
    }
}

/**
* [std_class_object_to_array 将对象转成数组]
* @param [stdclass] $stdclassobject [对象]
* @return [array] [数组]
*/
//function std_class_object_to_array($stdclassobject)
//{
//　　$_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
//　　foreach ($_array as $key => $value) {
//　　　　$value = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
//　　　　$array[$key] = $value;
//　　}
//　　return $array;
//}

function convertUrlQuery($query)
{
 	$queryParts = explode('&', $query);
  	$params = array();
  	foreach ($queryParts as $param) 
  	{
    	$item = explode('=', $param);
    	$params[$item[0]] = $item[1];
  	}
 	return $params;
}

function getUrlQuery($array_query)
{
  	$tmp = array();
  	foreach($array_query as $k=>$param)
  	{
   	 	$tmp[] = $k.'='.$param;
  	}
 	$params = implode('&',$tmp);
  	return $params;
}


