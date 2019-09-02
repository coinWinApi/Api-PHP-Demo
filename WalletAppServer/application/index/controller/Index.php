<?php
namespace app\index\controller;
use AES\AESMcrypt;
use app\index\model\MapMoneySymbolId;
use app\index\model\SysUsers;
use app\index\model\TUserLog;
use app\index\model\TUserLog2;
use app\index\model\WalletAddress;
use curl\httplib;
use curl\Pay;
use jwt\Jwt;
use think\Controller;



class Index extends Controller
{
    private $wallet_address;
    private $mapmoneysymbolId;
    private $user;
    private $tuserlog;
    private $tuserlog2;
    private $r;

    public function __construct() {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Methods:*');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        //$this->wallet_address=new WalletAddress();
        //$this->mapmoneysymbolId=new MapMoneySymbolId();

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
            exit;
        }



        $this->r=new Pay('https://yysyservice.com:20002','3BDAF00B6F374A01A4DFBE32257F7E05','1C1D365EEEAB40FD8E56901E27243DA0');

        $this->user=new SysUsers();
        $this->tuserlog=new TUserLog();
        $this->tuserlog2=new TUserLog2();

    }

    public function _empty($method)
    {
        $request = request();
        $module=$request->module();
        $ctl=$request->controller();
        $act=$request->action();
        exit_jsons(['msg'=>'方法不存在']);
    }



    public function getQuota(){
        $id=input('id');

        if($id=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>'']);
        }

        $o = $this->user->findByAttributes(['id' => $id]);
        if($o)
        {
            exit_jsons(['code'=>1000,'msg'=>'操作成功!','data'=>$o['quota']]);
        }else{
            exit_jsons(['code'=>1001,'msg'=>'操作失败!','data'=>'']);
        }

    }


    public function getCoinrate(){
        $type=input('type');

        if($type=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>[]]);
        }

        $tt=$this->r->getTicker($type);
        if($tt)
        {
            if($tt['success']==true)
            {
                exit_jsons(['code'=>1000,'msg'=>'操作成功!','data'=>$tt['data']]);
            }else{
                exit_jsons(['code'=>1001,'msg'=>$tt['data'],'data'=>'']);
            }
        }else{
            exit_jsons(['code'=>1001,'msg'=>'网络异常请稍后再试','data'=>'']);
        }


    }

    public function getCoinaddress(){



        $type=input('type');
        $id=input('id');
        if($type=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>[]]);
        }

        $this->tuserlog->deleteByWhere(['userid'=>$id]);    //删除临时表中的所有记录


        $tt=$this->r->getPay($id,$type,time());
        if($tt)
        {
            if($tt['success']==true)
            {
                exit_jsons(['code'=>1000,'msg'=>'操作成功!','data'=>$tt['data']]);
            }else{
                exit_jsons(['code'=>1001,'msg'=>$tt['data'],'data'=>'']);
            }
        }else{
            exit_jsons(['code'=>1001,'msg'=>'网络异常请稍后再试','data'=>'']);
        }
    }

    public function getQuotallog(){
        $id=input('id');
        $symbol=input('symbol');
        if($id=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>'']);
        }

        $have=$this->tuserlog->findByAttributes(['userid'=>$id,'symbol'=>$symbol]);
        if($have)
        {
            $this->tuserlog->deleteByWhere(['id'=>$have['id']]);
            exit_jsons(['code'=>1000,'msg'=>'操作成功','data'=>$have]);

        }else{
            exit_jsons(['code'=>1002,'msg'=>'正在充值请稍后','data'=>'']);
        }

    }




    public function getOrderStatus() {
        $orderid=input('orderid');
        if($orderid=='') {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>'']);
        }

        $have=$this->tuserlog2->findByAttributes(['orderid'=>$orderid]);
        if($have)
        {
            //$this->tuserlog->deleteByWhere(['orderid'=>$have['orderid']]);
            exit_jsons(['code'=>1000,'msg'=>'操作成功','data'=>$have]);

        }else{
            exit_jsons(['code'=>1002,'msg'=>'正在充值请稍后','data'=>'']);
        }
    }
    public function getPayBalance(){

        $id=input('id');
        if($id=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>[]]);
        }
        $tt=$this->r->getBalance($id);
        if($tt)
        {
            if($tt['success']==true)
            {
                exit_jsons(['code'=>1000,'msg'=>'操作成功!','data'=>$tt['data']]);
            }else{
                exit_jsons(['code'=>1001,'msg'=>$tt['data'],'data'=>'']);
            }
        }else{
            exit_jsons(['code'=>1001,'msg'=>'网络异常请稍后再试','data'=>'']);
        }
    }

    public function getPayOrderHistory() {
        $id=input('id');
        $startTime=input('startTime');
        $endTime=input('endTime');
        if($id=='')
        {
            exit_jsons(['code'=>1001,'msg'=>'参数错误!','data'=>[]]);
        }
        $tt=$this->r->getOrderHistory($id,$startTime,$endTime);
        if($tt)
        {
           // dump($tt);
            if($tt['success']==true)
            {
                exit_jsons(['code'=>1000,'msg'=>'操作成功!','data'=>$tt['data']]);
            }else{
                exit_jsons(['code'=>1001,'msg'=>$tt['data'],'data'=>'']);
            }
        }else{
            exit_jsons(['code'=>1001,'msg'=>'网络异常请稍后再试','data'=>'']);
        }

    }




    }
