<?php
namespace app\index\controller;

class Error
{  
    public function _empty($method)
    {
        $request = request();
        $module=$request->module();
        $ctl=$request->controller();
        $act=$request->action();
        exit_jsons(['msg'=>'控制器不存在']);        
    }
}
