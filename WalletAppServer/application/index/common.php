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

//统一返回json格式数据方法
if(!function_exists('exit_jsons')) {
    function exit_jsons($reData){
        exit(json_encode($reData));
    }
}
//发送短信验证码方法
function sendSms($mobile,$code)
{

    $params = array ();

    // *** 需用户填写部分 ***
    $config = \think\Config::get('aliyun');

    // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    $accessKeyId = $config['accessKeyId'];
    $accessKeySecret = $config['accessKeySecret'];

    // fixme 必填: 短信接收号码
    $params["PhoneNumbers"] = $mobile;

    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $params["SignName"] = $config['SignName'];

    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $params["TemplateCode"] = $config['TemplateCode'];

    // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
    $params['TemplateParam'] = Array (
        "code" => $code,
        // "product" => "阿里通信"
    );

    // fixme 可选: 设置发送短信流水号
    $params['OutId'] = "000001";

    // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
    $params['SmsUpExtendCode'] = "1234567";


    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new \aliyun\sms\SignatureHelper();

    // 此处可能会抛出异常，注意catch
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
        // fixme 选填: 启用https
        // ,true
    );

    return $content;

    // ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
    // error_reporting(E_ALL); // 显示所有错误提示，仅用于测试时排查问题
    // set_time_limit(0); // 防止脚本超时，仅用于测试使用，生产环境请按实际情况设置
    // header("Content-Type: text/plain; charset=utf-8"); // 输出为utf-8的文本格式，仅用于测试
}

 function ip($type=0) {

     $type    =  $type ? 1 : 0;
     static $ip  =   NULL;
     if ($ip !== NULL) return $ip[$type];
     if(isset($_SERVER['HTTP_X_REAL_IP'])){//nginx 代理模式下，获取客户端真实IP
         $ip=$_SERVER['HTTP_X_REAL_IP'];
     }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {//客户端的ip
         $ip     =   $_SERVER['HTTP_CLIENT_IP'];
     }elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {//浏览当前页面的用户计算机的网关
         $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
         $pos    =   array_search('unknown',$arr);
         if(false !== $pos) unset($arr[$pos]);
         $ip     =   trim($arr[0]);
     }elseif (isset($_SERVER['REMOTE_ADDR'])) {
         $ip     =   $_SERVER['REMOTE_ADDR'];//浏览当前页面的用户计算机的ip地址
     }else{
         $ip=$_SERVER['REMOTE_ADDR'];
     }
     // IP地址合法验证
     $long = sprintf("%u",ip2long($ip));
     $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);

     return $ip[$type];

}

function sendLoginMessage($phone){
    return '';
}

//生成不同位数的随机字符串
function getRandom($param){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for($i=0;$i<$param;$i++)
    {
        $key .= $str{mt_rand(0,32)};    //生成php随机数
    }
    return $key;
}


////获得访客浏览器类型
function GetBrowser() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return  $br;
    } else {
        return "获取浏览器信息失败！";
    }
}
//获取设备信息
function equipmentSystem(){


    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if(stristr($agent,'iPad')) {
        $fb_fs = "1";
        $version='iPad'.GetBrowser();
    }else if(preg_match('/Android (([0-9_.]{1,3})+)/i',$agent,$version)) {
        $fb_fs = "2";
        $version='Android'.$version[1];
    }else if(stristr($agent,'Linux')){
        $fb_fs = "1";
        $version='Linux'.GetBrowser();
    }else if(preg_match('/iPhone OS (([0-9_.]{1,3})+)/i',$agent,$version)){
        $fb_fs = "3";
        $version='iPhone OS'.$version[1];
    }else if(preg_match('/Mac OS X (([0-9_.]{1,5})+)/i',$agent,$version)){
        $fb_fs = "1";
        $version='Mac OS'.GetBrowser();
    }else if(preg_match('/unix/i',$agent)){
        $fb_fs = "1";
        $version='unix'.GetBrowser();
    }else if(preg_match('/windows/i',$agent,$version)){
        $fb_fs = "1";
        $version='windows'.GetBrowser();
    }else{
        $fb_fs = "未知(Unknown)";
        $version='';
    }

    return array(
        'device_type'=>$fb_fs,
        'device_name'=>$version
    );
}

//处理json转为array格式
function ext_json_decode($str, $mode=false){
    if(preg_match('/\w:/', $str)){
        $str = preg_replace('/(\w+):/is', '"$1":', $str);
    }
    return json_decode($str, $mode);
}


function RsaDecryptPri($encrypted){                 //RSA私钥解密
    $pi_key= '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCpCeRhdvkpRht3Gr1GPy393+CDn5t7A1pFntQut8oa+hd/b+CU
opP53Yw+QoX88X/F7IlOyWLKRswPLyWbLqlFrsm6/dBtJlMTQuDMnySpkvwYsWT3
q2DPeGNdPErV8dj/YzEh27oOKh/d1rbzeyn9K5GmS27MfIYQjQFi/sNQqQIDAQAB
AoGAcedhhi+GK8K7BSH2gpxWIGk4P1lQiq6yqJEjByv/Ovhk1xGLMnzu24LnWpi2
8r/Eypjd2UIomIbmQCET5SKnGl4TvY8/X3eSBV9usERkJcHSJ3ebK8IIeKWDtbLo
/fdl5tefhXS9m0yCZ4PhS9uiTm0fxn/J3u/3bsROJzGYFYECQQDSaZjZd4KSYtuX
2F8CnlKGg6eToKKen3DcgKntTjTElQuG0e9rSdBNeTK8PmPjHiXyS/M3MDRV/kUt
vyVVVOyRAkEAzamE8hOOpl005P8YxzGgx9LByc5CpKzbH0QZkb2g+7a1QCE7ldcS
6VejXOBW/KbW+k3td/wrHz3Tne4lqfIOmQJBAKXKrLRVlZ2wpWSNCbfvdgklfYo2
HUytRumHu90PLUbkRbPSgTrha29QGyj2ZBBV9gJn09ldcy967ZlxLoKBKvECQCwI
r75lEZXtPvYI6HU92v7t0TA0SXCY0hHPsunRgDZk2Eny058xfYsYiJHKNtXBoyXU
qZOXGmSGCFk4NDg+64ECQQDJUeWwL0PdUxJ80g3m+Sy5pEfglVlgh5EWZjOREUww
og5CPJKbAv3VFlCuuDdT6cbzAM5mBoivDrRxx3RA0mkl
-----END RSA PRIVATE KEY-----';
    $decrypted='';
    $isOkay=openssl_private_decrypt(base64_decode($encrypted), $decrypted, $pi_key);//私钥解密

    return $decrypted;
}

function RSAEncryptPub($data){              //RSA公钥加密
    $pub_key='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCpCeRhdvkpRht3Gr1GPy393+CDn5t7A1pFntQut8oa+hd/b+CUopP53Yw+QoX88X/F7IlOyWLKRswPLyWbLqlFrsm6/dBtJlMTQuDMnySpkvwYsWT3q2DPeGNdPErV8dj/YzEh27oOKh/d1rbzeyn9K5GmS27MfIYQjQFi/sNQqQIDAQAB
-----END PUBLIC KEY-----';
    $encrypted='';
    openssl_public_encrypt($data,$encrypted,$pub_key);

    return base64_encode($encrypted);
}