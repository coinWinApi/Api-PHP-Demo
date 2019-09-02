<?php
namespace app\index\controller;

use curl\httplib;
use jwt\Jwt;
use think\Controller;
use app\index\model\SysUsers;
use think\Db;
use think\Paginator;
use think\Session;



class Apicallback extends Controller {
    private $SysUser;
    private $TUserLog;
    private  $accessKey='3BDAF00B6F374A01A4DFBE32257F7E05';
    private  $secretKey='1C1D365EEEAB40FD8E56901E27243DA0';
    private $url='http://yysyservice.com:8999/index.php/index';
    public $api_method = '';


    public function __construct() {


        $this->SysUser = new SysUsers();

    }


    public function _empty($method) {

        $request = request();
        $module = $request->module();
        $ctl = $request->controller();
        $act = $request->action();
        exit_jsons(['msg' => '方法不存在']);
    }



    public function  quotaAPI()
    {


        date_default_timezone_set('UTC');
        $this->api_method='/Apicallback/quotaAPI';
        $bodyData = @file_get_contents('php://input');
        //将获取到的值转化为数组格式
        $bodyData = json_decode($bodyData,true);
        if(empty($bodyData)){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('参数错误!')]));
        }
        //验证参数
        $sign=isset($bodyData['sign']) ? $bodyData['sign']:'';
        $orderId=isset($bodyData['orderId']) ? $bodyData['orderId']:'';
        $quota=isset($bodyData['quota']) ? $bodyData['quota']:'';
        $id=isset($bodyData['openid']) ? $bodyData['openid']:'';
        $timestamp=isset($bodyData['timestamp'])?$bodyData['timestamp']:'';
        $remark=isset($bodyData['remark'])?$bodyData['remark']:'';
        $symbol=isset($bodyData['symbol'])?$bodyData['symbol']:'';
        $confirm=isset($bodyData['confirm'])?$bodyData['confirm']:'';
        if($sign=='' or $quota=='' or $id=='' or $timestamp=='' or $confirm==''){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('参数错误')]));
        }

        if(!(date('Y-m-d H:i:s',strtotime('+5 minute'))>=$this->getMsecToMescdate($timestamp) and $this->getMsecToMescdate($timestamp) >=date('Y-m-d H:i:s',strtotime('-5 minute')))){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('时间戳错误')]));
        }
        $param=[
            'accessKey'=>$this->accessKey,
            'openid'=>$id,
            'quota'=>$quota,
            'remark'=>$remark,
            'symbol'=>$symbol,
            'timestamp'=>$timestamp,
            'orderId'=>$orderId,

        ];
        //验证签名
        if($this->vaildate($param,$sign)==false){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('签名错误!')]));
        }


        $sql="select id from sys_users where id=".$id;
        $only=Db::execute( $sql );
        if(empty($only)){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('用户不存在!')]));
        }

        if($confirm==1)
        {
            $sql="update sys_users set quota=quota+".$quota." where id=".$id;
            Db::execute( $sql );
        }

        $arr=array(
            'quota'=>$quota,
            'userid'=>$id,
            'symbol'=>$symbol,
            'remark'=>$remark,
            'orderId'=>$orderId,
            'confirm'=>$confirm
        );
        Db::table('t_user_log')
            ->insertGetId($arr);
        Db::table('t_user_log2')
            ->insertGetId($arr);


        exit(json_encode(['result'=>'suc','msg'=>urlencode('额度回调成功!')]));
    }

    public function  UpquotaAPI()
    {


        date_default_timezone_set('UTC');
        $this->api_method='/Apicallback/UpquotaAPI';
        $bodyData = @file_get_contents('php://input');
        //将获取到的值转化为数组格式
        $bodyData = json_decode($bodyData,true);
        if(empty($bodyData)){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('参数错误!')]));
        }
        //验证参数
        $sign=isset($bodyData['sign']) ? $bodyData['sign']:'';
        $orderId=isset($bodyData['orderId']) ? $bodyData['orderId']:'';
        $id=isset($bodyData['openid']) ? $bodyData['openid']:'';
        $timestamp=isset($bodyData['timestamp'])?$bodyData['timestamp']:'';
        $remark=isset($bodyData['remark'])?$bodyData['remark']:'';
        $confirm=isset($bodyData['confirm'])?$bodyData['confirm']:'';
        if($sign==''or $id=='' or $timestamp=='' or $confirm==''){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('参数错误')]));
        }

        if(!(date('Y-m-d H:i:s',strtotime('+5 minute'))>=$this->getMsecToMescdate($timestamp) and $this->getMsecToMescdate($timestamp) >=date('Y-m-d H:i:s',strtotime('-5 minute')))){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('时间戳错误')]));
        }
        $param=[
            'accessKey'=>$this->accessKey,
            'openid'=>$id,
            'remark'=>$remark,
            'timestamp'=>$timestamp,
            'orderId'=>$orderId,

        ];
        //验证签名
        if($this->vaildate($param,$sign)==false){
          //  exit(json_encode(['result'=>'erro','msg'=>urlencode('签名错误!')]));
        }


        $sql="select id from sys_users where id=".$id;
        $only=Db::execute( $sql );
        if(empty($only)){
            exit(json_encode(['result'=>'erro','msg'=>urlencode('用户不存在!')]));
        }

        $sql="update t_user_log2 set confirm=".$confirm ." where orderId=".$orderId;
        Db::execute( $sql );

        if($confirm==1)
        {
            $sql="update sys_users as a,t_user_log2 as b  set a.quota=a.quota+b.quota  where a.id=b.userid and  b.orderId=".$orderId;
            Db::execute( $sql );
        }



        exit(json_encode(['result'=>'suc','msg'=>urlencode('额度更新回调成功!')]));
    }















    // 生成验签URL
    private function create_sign($param) {
        $sign_param_1 = $this->url.$this->api_method."?".implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, $this->secretKey, true);
        //exit(json_encode(['result'=>'erro','msg'=>urlencode($sign_param_1),'asd'=> base64_encode($signature)]));


        return base64_encode($signature);
    }
    //验证签名是否有效
    private function vaildate($param,$questsign){
        $sign=$this->bind_param($param);
        $sign=str_replace("+"," ",$sign);
        $questsign=str_replace("+"," ",$questsign);
        if($questsign==$sign)
        {
            return true;
        }else{
            return false;
        }
    }
    private function bind_param($param) {
        $u = [];
        $sort_rank = [];
        foreach($param as $k=>$v) {
            $u[] = $k."=".urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        return $this->create_sign($u);
    }
    /**
     *时间戳 转   日期
     */
    public function getMsecToMescdate($time) {
        $tag='Y-m-d H:i:s';
        $a = substr($time,0,10);
        $b = substr($time,10);
        $date = date($tag,$a).'.'.$b;
        return $date;
    }


    
}
