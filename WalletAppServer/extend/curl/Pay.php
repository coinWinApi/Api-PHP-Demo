<?php
namespace curl;



class Pay
{
    private  $url='';           //api请求url
    private  $accessKey='';     //apiAccessKey
    private  $secretKey='';     //apiSecretKey
    public $api_method = '';
    public $req_method = '';


    function __construct($url,$accessKey,$secretKey) {
        date_default_timezone_set('UTC');                           //同步时区
        $this->url=$url;
        $this->accessKey=$accessKey;
        $this->secretKey=$secretKey;

    }

    //请求收币地址
    public function getPay($openid,$symbol='BTC',$remark=''){

        $this->api_method='/api/Pay';
        $this->req_method = 'GET';
        $param = [
            'symbol'=>$symbol,
            'openid'=>$openid,
            'remark'=>$remark
        ];

        $url = $this->create_sign_url($param);
        $return = $this->curl($url);
        return json_decode($return,true);
    }

    //获取服务器上当前的BTC的人民币价格
    public function getTicker($symbol='BTC'){
        $this->api_method='/api/ticker';
        $this->req_method = 'GET';
        $param = [
            'symbol'=>$symbol
        ];

        $url = $this->create_sign_url($param);
        $return = $this->curl($url);
        return json_decode($return,true);
    }


    //请求用户余额
    public function getBalance($openid)
    {
        $this->api_method='/api/Pay/getbalance';
        $this->req_method = 'GET';
        $param = [
            'openid'=>$openid
        ];

        $url = $this->create_sign_url($param);
       // dump($url);
        $return = $this->curl($url);

        return json_decode($return,true);
    }

    //请求用户收币历史记录
    public function getOrderHistory($openid,$startTime,$endTime)
    {
        $this->api_method='/api/Pay/getOrderHistory';
        $this->req_method = 'GET';
        $param = [
            'openid'=>$openid
        ];

        $url = $this->create_sign_url($param);
        $return = $this->curl($url.'&startTime='.$startTime."&endTime=".$endTime);
        return json_decode($return,true);
    }
    /**
     * 类库方法
     */

    /**
     * 获取时间戳到毫秒
     * @return bool|string
     */
    private  function getMillisecond() {
        date_default_timezone_set('UTC');
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectimes = substr($msectime,0,13);
    }

    /**
     * 获取时间戳到毫秒
     * @return bool|string
     */
    private function create_sign_url($append_param = []) {
        // 验签参数
        $param = [
            'accessKey' => $this->accessKey,
            'timestamp' => $this->getMillisecond(),
        ];
        if ($append_param) {
            foreach($append_param as $k=>$ap) {
                $param[$k] = $ap;
            }
        }
        return $this->url.$this->api_method.'?'.$this->bind_param($param);
    }
    // 组合参数
    private function bind_param($param) {
        $u = [];
        $sort_rank = [];
        foreach($param as $k=>$v) {
            $u[] = $k."=".urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "sign=".urlencode($this->create_sig($u));
        return implode('&', $u);
    }

    // 生成验签URL
    private function create_sig($param) {


        $sign_param_1 = $this->url.$this->api_method."?".implode('&', $param);
        //echo $sign_param_1;
        $signature = hash_hmac('sha256', $sign_param_1, $this->secretKey, true);
        return base64_encode($signature);
    }

    //数据请求
    private function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $output;
    }



}