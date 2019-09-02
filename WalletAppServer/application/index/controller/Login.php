<?php
namespace app\index\controller;
use app\index\model\SysOnlineDeviceTemp;
use app\index\model\SysOnlineUserTem;
use app\index\model\SysOnlineUserTemp;
use curl\httplib;
use jwt\Jwt;
use think\Controller;
use app\index\model\SysUsers;
use think\Paginator;
use think\Session;



class Login extends Controller {
    private $SysUser;
    private $SysOnlineUserTemp;
    private $SysOnlineDeviceTemp;


    public function __construct() {


        $this->SysUser = new SysUsers();
        $this->SysOnlineUserTemp = new SysOnlineUserTemp();
        $this->SysOnlineDeviceTemp = new SysOnlineDeviceTemp();
    }


    public function _empty($method) {

        $request = request();
        $module = $request->module();
        $ctl = $request->controller();
        $act = $request->action();
        exit_jsons(['msg' => '方法不存在']);
    }


    //发送短信验证码
    public function SemCheck() {
        header("Access-Control-Allow-Origin: http://localhost/login/index.html");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $phone = input('phone');
        $type = input('type');


        $exist = $this->SysUser->findByAttributes(['phone' => $phone]);
        if (!$exist and $type == 'login') {
            exit_jsons(['status' => 0, 'message' => '当前手机号未注册!']);
        }

        if ($exist and $type == 'regin') {
            exit_jsons(['status' => 0, 'message' => '当前手机号已注册!']);
        }

        $code = mt_rand(100000, 999999);
        $isOk = sendSms($phone, $code);
        if ($isOk->Message == 'OK') {
            session($type . $phone, $code);
            session($type . 'session_expire', time() + 60 * 10);              //设置短信验证码的过期时间
            $result = array(
                'message' => '验证码发送成功',
                'status'  => 1,
            );
        } else {
            $result = array(
                'message' => '验证码发送失败！请稍后重试',
                'status'  => 0,
            );
        }

        exit_jsons($result);
    }

    //验证token是否有效
    public function checktoken() {

        $token = input('token');
        $key = input('key');
        $jwt = new Jwt();

        $version = input('version');
        $server_version = config('version');

        if ($version < $server_version) {
            exit_jsons(['result' => 0, 'msg' => '当前版本过低,限制登录!']);
        }
        $token_data = $jwt->verifyToken($token);
        if ($token_data) {

            $user_data = $this->SysUser->findByAttributes(['id' => $token_data['uid']]);
            if ($user_data) {
                session('key', $key);
                session('user_id', $user_data['id']);
                session('token', $token);
                $this->SysUser->updateByWhere(['id' => $user_data['id']], array('last_online_ip' => ip()));
                exit_jsons(['result' => 1, 'message' => '身份验证成功', 'id' => session('user_id')]);
            } else {
                exit_jsons(['result' => 0, 'message' => '用户不存在']);
            }
        } else {
            exit_jsons(['result' => 0, 'message' => '身份已经失效请重新登录']);
        }
    }

    //登录
    public function loginSem() {

        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $arr = array('code' => 0, 'msg' => '登录失败!请稍后再试','data'=>[]);

        $username = input('username');
        $password = input('password');

        if($username=='' || $password=='')
        {
            exit_jsons(['code' => 1001, 'msg' => '参数错误!','data'=>'']);
        }


        $exist = $this->SysUser->findByAttributes(['username' => $username]);
        if ($exist) {
            $o = $this->SysUser->findByAttributes(['username' => $username, 'password' => md5($password)]);
            //dump($o);
            //exit;
            if ($o) {
                session('user_id',$o['id']);
                exit_jsons(['code' => 1000, 'msg' => '操作成功!','data'=>$o['id']]);
            } else {
                exit_jsons(['code' => 1001, 'msg' => '密码错误!','data'=>'']);
            }
        } else {
            exit_jsons(['code' => 1001, 'msg' => '当前用户不存在!','data'=>'']);
        }
        exit_jsons($arr);
    }


    //登录
    public function login()
    {

            header("Access-Control-Allow-Origin: http://localhost/login/index.html");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

           $arr=array('result'=>0,'msg'=>'登录失败!请稍后再试');
           $encrypted=input('data');
           $phone=input('phone');
           $version=input('version');
           $login_device=input('login_device');
           $decrypted='';
           $is_success=false;


            $server_version=config('version');

            if($version<$server_version)
            {
                exit_jsons(['result'=>0,'msg'=>'当前版本过低,限制登录!']);
            }

           //$isOkay=openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->pi_key);//私钥解密
            $decrypted=RsaDecryptPri($encrypted);




           if (!empty($decrypted))                                              //1.判断rsa解密是否正确
           {

               $data=json_decode($decrypted, true);                                               //1.1 将json转为数组并取出对应的数据


               //$phone=$data['phone'];
              
               $vertify=$data['vertify'];
               //$iv=$data['iv'];
               $key=$data['key'];
               $code_time=session('loginsession_expire');

               $type=$data['type'];                                                         //判断是手机验证码登录还是密码登录



               if($type==1)
               {

                   if (!empty($code_time) and $code_time>time())
                   {
                       $code=session('login'.$phone);

                       if ($vertify!=$code)                                             //3.判断验证码是否正确
                       {
                           $arr=array('result'=>0,'msg'=>'手机验证码错误!');
                           exit_jsons($arr);
                       }else{
                           $is_success=true;
                       }
                   }
                   else{

                       $arr=array('result'=>0,'msg'=>'验证码已失效请重新获取!');
                       exit_jsons($arr);
                   }
               }
               elseif($type==2)
               {
                       $exist=$this->SysUser->findByAttributes(['phone'=>$phone]);
                        if($exist)
                        {
                            $o=$this->SysUser->findByAttributes(['phone'=>$phone,'password'=>md5($vertify)]);
                            if($o)
                            {
                                $is_success=true;
                            }else{
                                exit_jsons(['result'=>0,'msg'=>'密码错误!']);
                            }
                        }else{
                            exit_jsons(['result'=>0,'msg'=>'当前手机号未注册!']);
                        }

               }

               if($is_success==true)
               {
                   //session('iv',$iv);                                           //4.登录成功,将用于AES解密的iv和key存到session当中以便于后面用到加密与解密数据
                   session('key',$key);
                   //Session::delete($phone);                                   //删除短信验证码的session




                   $only=$this->SysUser->findByAttributes(['phone'=>$phone]);
                   if ($only)
                   {


                       //登录成功发送http请求给C#
                       $payload_test=array('iss'=>'yysy','iat'=>time()-3000,'exp'=>time()+40200,'nbf'=>time()-3000,'uid'=>$only['id'],'jti'=>md5(uniqid('JWT').time()),'aud'=>'wallet','http://schemas.microsoft.com/ws/2008/06/identity/claims/role'=>'PHPClient');
                       $jwt=new Jwt();
                       $token=$jwt->getToken($payload_test);
                       session('token',$token);

                       $curl = new  httplib();
                       $json_data=array('uid'=>$only['id'],'key'=>session('key'));
                       $curl->set_header('Authorization','Bearer '.$token);
                       $curl->set_header('Content-Type','text/x-plain-rsa-json');
                       $rsa=RSAEncryptPub(json_encode($json_data));
                       $curl->request('http://192.168.1.105:5000/api/AesKeys',$rsa);

                       if($curl->get_statcode()==202){
                      //if(1==1){
                           $this->SysUser->updateByWhere(['id'=>$only['id']],array('last_online_ip'=>ip()));


                           //$this->SysOnlineUserTemp->insert($only['id']);                          //新增在线用户表（临时数据表）数据
                           //$this->SysOnlineDeviceTemp->insert($only['id']);
                           session('user_id',$only['id']);

                           $arr=array('result'=>1,'msg'=>'登录成功!','token'=>session('token'),'id'=>session('user_id'));

                           exit_jsons($arr);

                       }else{
                           $arr=array('result'=>0,'msg'=>'网络错误请刷新后重试2！');
                           exit_jsons($arr);
                       }

                   }else{
                       $arr=array('result'=>0,'msg'=>'网络错误请刷新后重试1！');
                       exit_jsons($arr);
                   }


               }


           }
           else{
               $arr=array('result'=>0,'msg'=>'数据错误请刷新后重试！');
               exit_jsons($arr);
           }
                 
    }

    //退出登录
    public function loginOut(){

        //清除session
        session(null);
        //清除cookie
        cookie(null);


        $arr=array('result'=>1,'msg'=>'退出成功！');
        exit_jsons($arr);


    }

    //注册
    public function Regin(){
        header("Access-Control-Allow-Origin: http://localhost/login/index.html");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $arr=array('result'=>0,'msg'=>'注册失败!请稍后再试');
        $encrypted=input('data');
        $phone=input('phone');
        $version=input('version');
        $login_device=input('login_device');

        $server_version=config('version');

        if($version<$server_version)
        {
            exit_jsons(['result'=>0,'msg'=>'当前版本过低,限制注册!']);
        }
        //$decrypted='';

        //$isOkay=openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->pi_key);//私钥解密

        $decrypted=RsaDecryptPri($encrypted);

        if (!empty($decrypted))                                              //1.判断rsa解密是否正确
        {
            $data=json_decode($decrypted, true);                                               //1.1 将json转为数组并取出对应的数据
            if(is_array($data))
            {
                //$phone=$data['phone'];
                $password=$data['password'];
                $confirmpassword=$data['confirmpassword'];
                $vertify=$data['vertify'];
                $key=$data['key'];
                if(empty($phone)){ exit_jsons(['result'=>0,'msg'=>'手机号不能为空']);};
                if(empty($password)){ exit_jsons(['result'=>0,'msg'=>'密码不能为空']);};
                if(empty($confirmpassword)){ exit_jsons(['result'=>0,'msg'=>'确认密码不能为空']);};
                if(empty($vertify)){ exit_jsons(['result'=>0,'msg'=>'手机验证码不能为空']);};

                $exist=$this->SysUser->findByAttributes(['phone'=>$phone]);
                if($exist)
                {
                    exit_jsons(['result'=>0,'msg'=>'当前手机号已注册!']);
                }

                $code_time=session('reginsession_expire');
                if (!empty($code_time) and $code_time>time()) {
                    $code = session('regin' . $phone);

                    if ($vertify!=$code)                                             //3.判断验证码是否正确
                    {

                        exit_jsons(['result'=>0,'msg'=>'手机验证码错误!']);
                    }else{
                       if($password==$confirmpassword)
                       {

                            $id=$this->SysUser->insert(['phone'=>$phone,'password'=>md5($password),'nickname'=>getRandom(10)]);
                           //登录成功发送http请求给C#
                           $payload_test=array('iss'=>'yysy','iat'=>time()-3000,'exp'=>time()+40200,'nbf'=>time()-3000,'uid'=>$id,'jti'=>md5(uniqid('JWT').time()),'aud'=>'wallet','http://schemas.microsoft.com/ws/2008/06/identity/claims/role'=>'PHPClient');
                           $jwt=new Jwt();
                           $token=$jwt->getToken($payload_test);


                           $curl = new  httplib();
                           $json_data=array('uid'=>$id,'key'=>$key);
                           $curl->set_header('Authorization','Bearer '.$token);
                           $curl->set_header('Content-Type','text/x-plain-rsa-json');
                           $rsa=RSAEncryptPub(json_encode($json_data));
                           $curl->request('http://52.77.226.46:41856/api/AesKeys',$rsa);
                           if($curl->get_statcode()==202){

                               //if(1==1){
                               $this->SysUser->updateByWhere(['id'=>$id],array('last_online_ip'=>ip()));
                               //$this->SysOnlineUserTemp->insert($only['id']);                          //新增在线用户表（临时数据表）数据
                               //$this->SysOnlineDeviceTemp->insert($only['id']);
                               session('user_id',id);
                               session('token',$token);
                               session('key',$key);
                               $arr=array('result'=>2,'msg'=>'登录成功!','token'=>session('token'),'id'=>session('user_id'));
                               exit_jsons($arr);
                           }else{
                               exit_jsons(['result'=>1,'msg'=>'注册成功!']);
                               exit_jsons($arr);
                           }

                       }else{
                           exit_jsons(['result'=>0,'msg'=>'两次密码输入不一致!']);
                       }
                    }

                }
                else{
                    exit_jsons(['result'=>0,'msg'=>'验证码已经失效!']);
                }
            }
            exit_jsons($arr);
        }
        else{
            $arr=array('result'=>0,'msg'=>'数据错误请刷新后重试！');
            exit_jsons($arr);
        }

    }


    
}
