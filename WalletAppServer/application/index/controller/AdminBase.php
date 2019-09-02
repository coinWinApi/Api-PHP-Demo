<?php
namespace app\index\controller;

use app\index\model\SysUsers;
use jwt\Jwt;
use think\Controller;
use think\Session;
use think\Db;
class AdminBase extends Controller
{



    public function __construct()
    {
        parent::__construct();

        $this->checkLogin();
    }

    public function checkLogin(){

        //用户是否已登陆验证
        $user_id=(null!==session('user_id'))?session('user_id'):'';



        //检测用户及工作角色无效强制退出要求重新登陆
        $result=self::isValidAdmin($user_id);

        if($result==false)
        {
            //清除session
            session(null);
            header('HTTP/1.0 401 Unauthorized');

            exit();

        }

        /*
        $token=(null!==session('token'))?session('token'):'';
        $jwt=new Jwt();
        $result=$jwt->verifyToken($token);
        if($result==false)
        {
            //清除session
            session(null);
            header('HTTP/1.0 401 Unauthorized');
            exit();
        }*/

    }

    private static function isValidAdmin($user_id)
    {
        $result=true;
        $user=new SysUsers();
        //1检测用户名
        if($result==true)
        {
            $data=$user->findByAttributes(['id'=>$user_id]);
            if(empty($data))
            {
                $result=false;
            }
            unset($data);
        }

        return $result;
    }



}
