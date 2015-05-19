<?php
/**
 * 支付系统
 * wangbaoqing@imooly.com
 * 2015-05-03
 */
namespace Front\Controller;

use Org\Net\Http;
use Org\Util\String;
use Org\Util\Log;

class MpayController extends BaseController
{
    /**
     * 支付配置 支付宝
     */
    private $_alipay_config = array(
        //支付宝合作者id
        'partner_id'        => '2088311843874216',
        //请求有效性验证URL
        'notify_verify_url' => 'https://mapi.alipay.com/gateway.do?service=notify_verify',
        //交易状态
        'trade_status'      => array(
            'WAIT_BUYER_PAY' => 1,
            'TRADE_SUCCESS'  => 2,
            'TRADE_FINISHED' => 4,
            'TRADE_CLOSED'   => 3,
        ),
        'alipay_public_key'  => 'Common/key/alipay_public_key.pem',
    );

    /**
     * 支付配置 微信
     */
    private $_wxpay_config = array(
        'appid'     => 'wx501bd7cea77cc83a',
        'appsecret' => '89f629c822b71cabfe761f96265b4f71',
        'appkey'    => 'Jx51TawYD9iEBL0i4QC0RMc1fci5C1V435ZtyRVCmERHpJyRUnbWY8QGuhEExFhxaD3joCAHbJnbXoeozNEqixlrw6Tgol9uJ9uM4HRcwOnWFtI1uOE3h5l4nlH1d9HJ',
        'partnerid' => '1230268401',
        'partnerkey'=> 'a219ee862f39adc9146268a7d20a436b',
        'access_token_api' => 'https://api.weixin.qq.com/cgi-bin/token',
        'gen_prepay_api'   => 'https://api.weixin.qq.com/pay/genprepay',
        'pay_expire_time'  => 86400, //支付过期时间 24小时
        //交易状态
        'trade_status'      => array(
            0 => 2, //支付成功
        ),
    );

    //初始化
    public function __construct()
    {
        parent::__construct();

        //记录请求日志
        Log::requestLog('mpay');
    }

    public function index() {}

    /**
     * 错误返回
     */
    private function _E($code=0)
    {
        $error = array(
            0   => '未知错误！',
            201 => '请求失败 通信出错！',
            202 => '未获取到access_token！',
            203 => '支付数据package生成错误！',
            204 => '微信app_signature签名生成错误！',
            205 => '预支付单生成失败！',
            206 => '预支付单签名生成失败！',
            207 => '',
        );

        return isset($error[$code]) ? $error[$code] : '未知错误！';
    }

    /**
     * API返回数据
     */
    private function _apiReturn($code=100,$message=null,$data=array())
    {
        if (!$code || (!$message&&empty($data)) || !is_array($data)) return false;

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        return array(
            'error'  => false,
            'result' => json_encode($result),
        );
    }

    /**
     * API返回数据
     */
    private function _apiextReturn($code=100,$message=null,$data=array())
    {
        if (!$code || (!$message&&empty($data)) || !is_array($data)) return false;

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );
        echo json_encode($result);
        exit;
    }

    /**
     * 获取一个数组键排序后的键值字符串
     * @return key=value&key=value...
     */
    private function _array_join($array=array())
    {
        if (!is_array($array)||empty($array)) return false;

        $return = null;
        ksort($array);
        foreach ($array as $k=>$v) {
            $return .= $return ? '&'.$k.'='.$v : $k.'='.$v;
        }
        return $return;
    }

    /**
     * 生成支付单号
     */
    private function _gPayno()
    {
        return 'MP'.date('YmdHis',TIMESTAMP).rand(10000,99999);
    }

    /**
     * 生成token
     */
    private function _gToken()
    {
        return md5(chr(rand(65,90)).TIMESTAMP);
    }

    /**
     * 获取access_token 存储在共享内存中
     * 共享内存id:855
     * accesstoken过期时间7200-600秒
     * {"access_token":"...","expire_time":1523236558}
     */
    private function _wxpay_gsAccesstoken()
    {
        //从共享内存中获取数据
        $key  = 855;
        $flag = 'c';
        $mode = 0755;
        $size = 151;
        $shmid = shmop_open($key,$flag,$mode,$size);
        // shmop_delete($shmid);
        $accesstoken = shmop_read($shmid, 0, $size);
        if ($accesstoken) {
            $accesstoken = json_decode($accesstoken,true);
            // 如果获取到accesstoken并且数据未过期 直接返回
            if ($accesstoken['expire_time']>TIMESTAMP) return $accesstoken['access_token'];
        }

        //如果没有accesstoken或者accesstoken已过期
        //api地址
        $api = $this->_wxpay_config['access_token_api'].'?grant_type=client_credential&appid='.$this->_wxpay_config['appid'].'&secret='.$this->_wxpay_config['appsecret'];
        $HttpClient = Http::Init($api,1);
        $result = $HttpClient->get(null,array(),array(),'',0,array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
        if ($result['error']) return false;

        $result = json_decode($result['result'],true);
        if (isset($result['errcode'])) return false;

        //存取数据
        $accesstoken = array(
            'access_token' => $result['access_token'],
            'expire_time'  => TIMESTAMP+$result['expires_in']-600
        );
        $accesstoken = json_encode($accesstoken);
        shmop_write($shmid,$accesstoken,0);
        shmop_close($shmid);

        return $result['access_token'];
    }

    /**
     * 微信生成订单详情package
     */
    private function _wxpay_gsPackage($out_trade_no=null,$spbill_create_ip=null)
    {
        if (!$out_trade_no||!$spbill_create_ip) return false;

        //异步通知url
        $notifyurls = C('PAY.PAY_NOTIFY');
        $notifyurl = $notifyurls['wxpay'];

        //支付详情信息
        $package = array(
            'bank_type' => 'WX',
            'body'      => 'body',
            'attach'    => 'attach',
            'partner'   => $this->_wxpay_config['partnerid'],
            'out_trade_no' => $out_trade_no,
            'total_fee' => (string)(100.00),
            'fee_type'  => 1,
            'notify_url'=> $notifyurl,
            'spbill_create_ip' => $spbill_create_ip,
            'time_start'=> date('YmdHis',TIMESTAMP),
            'time_expire'  => date('YmdHis',TIMESTAMP+$this->_wxpay_config['pay_expire_time']),
            'transport_fee'=> 0,
            'product_fee'=> (string)(100.00),
            'goods_tag' => '魔力商品',
            'input_charset' => 'UTF-8',
        );
        ksort($package);
        $packagestring = $this->_array_join($package);
        $signvalue = md5($packagestring.'&key='.$this->_wxpay_config['partnerkey']);
        $signvalue = strtoupper($signvalue);

        //urlencode转码
        $packageencodestring = null;
        foreach ($package as $k=>$v) {
            $encodev = urlencode($v);
            $packageencodestring .= $packageencodestring ? '&'.$k.'='.$encodev : $k.'='.$encodev;
        }
        $packageencodestring .= '&sign='.$signvalue;

        return $packageencodestring;
    }

    /**
     * 微信生成app_signature签名
     * @param string
     */
    private function _wxpay_gsAppsignature($noncestr=null,$package=null,$traceid=null)
    {
        if (!$noncestr||!$package||!$traceid) return false;

        //待签名数据
        $data = array(
            'appid'  => $this->_wxpay_config['appid'],
            'appkey' => $this->_wxpay_config['appkey'],
            'noncestr' => $noncestr,
            'package'  => $package,
            'timestamp'=> TIMESTAMP,
            'traceid'  => $traceid,
        );
        //待签名字符串
        $signstring = $this->_array_join($data);
        //sha1签名
        $app_signature = sha1($signstring);

        return $app_signature;
    }

    /**
     * 微信生成预支付单签名
     */
    private function _wxpay_gsPrepaysign($noncestr=null,$package=null,$prepayid=null)
    {
        if (!$noncestr||!$package||!$prepayid) return false;

        //待签名数据
        $data = array(
            'appid'  => $this->_wxpay_config['appid'],
            'appkey' => $this->_wxpay_config['appkey'],
            'noncestr' => $noncestr,
            'package'  => $package,
            'partnerid'=> $this->_wxpay_config['partnerid'],
            'prepayid' => $prepayid,
            'timestamp'=> (string)TIMESTAMP,
        );
        //待签名字符串
        $signstring = $this->_array_join($data);
        //sha1签名
        $sign = sha1($sign);

        return $sign;
    }

    /**
     * 微信支付预处理 - 生成预支付单 返回微信APP的预支付单信息
     * @param string $out_trade_no 商户订单号-对应支付单号
     * @param string $spbill_create_ip 客户端ip
     * @param string $traceid 支付用户跟踪id
     * @return json 支付相关信息
     */
    public function wxgenprepay($out_trade_no=null,$spbill_create_ip=null,$traceid=null)
    {
        if (!$out_trade_no||!$spbill_create_ip||!$traceid) return $this->_apiReturn(101,'支付数据不完整');

        //唯一签名
        $noncestr = md5($out_trade_no.date('YmdHi'));

        //获取access_token
        $access_token = $this->_wxpay_gsAccesstoken();
        if (!$access_token) return $this->_apiReturn(202,$this->_E(202));
        //获取订单详情package
        $package = $this->_wxpay_gsPackage($out_trade_no,$spbill_create_ip);
        if (!$package) return $this->_apiReturn(203,$this->_E(203));
        //获取app_signature
        $app_signature = $this->_wxpay_gsAppsignature($noncestr,$package,$traceid);
        if (!$app_signature) return $this->_apiReturn(204,$this->_E(204));

        //微信预支付单api
        $api = $this->_wxpay_config['gen_prepay_api'].'?access_token='.$access_token;
        //预支付订单信息
        $postData = array(
            'appid'    => $this->_wxpay_config['appid'],
            'traceid'  => $traceid,
            'noncestr' => $noncestr,
            'timestamp'=> (string)TIMESTAMP,
            'package'  => $package,
            'sign_method'   => 'sha1',
            'app_signature' => $app_signature,
        );
        $postData = json_encode($postData);
        //发送请求 生成预支付单
        $HttpClient = Http::Init($api,1);
        $result = $HttpClient->post(null,$postData,array(),'',5,array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));
        if ($result['error']) return $this->_apiReturn(201,$this->_E(201));

        $result = json_decode($result['result'],true);
        //预支付单生成失败 返回错误
        if (!$result['prepayid']) return $this->_apiReturn(205,$this->_E(205));

        //预支付单生成成功 返回数据
        $package = 'Sign=WXpay';
        $data = array(
            'appid'    => $this->_wxpay_config['appid'],
            'noncestr' => $noncestr,
            'package'  => $package,
            'partnerid'=> $this->_wxpay_config['partnerid'],
            'prepayid' => $result['prepayid'],
            'timestamp'=> (string)TIMESTAMP,
        );
        //生成预支付单签名
        $sign = $this->_wxpay_gsPrepaysign($noncestr,$package,$result['prepayid']);
        if (!$sign) return $this->_apiReturn(206,$this->_E(206));

        //将预支付单签名加入data一起返回
        $data['sign'] = $sign;

        return $this->_apiReturn(100,'预支付单生成成功！',$data);
    }

    /**
     * 判断是否是支付宝请求
     */
    private function _alipay_isRequest($notify_id=null)
    {
        if (!$notify_id) return false;

        //验证api
        $api = $this->_alipay_config['notify_verify_url'].'&partner='.$this->_alipay_config['partner_id'].'&notify_id='.$notify_id;

        //初始化httpClient
        $HttpClient = Http::Init($api,1);
        $result = $HttpClient->get(null,array(),array(),'',0,array(
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        if ($result['result']=='true') {
            $return = true;
        } else {
            //记录错误日志
            
            $return = false;
        }

        return $return;
    }

    /**
     * 验证签名 - 支付宝
     */
    private function _alipay_rsaVerify($data=null,$signature=null,$alipay_public_key_file=null)
    {
        if (!file_exists($alipay_public_key_file)) return false;

        $alipay_public_key = file_get_contents($alipay_public_key_file);
        $public_key_id = openssl_get_publickey($alipay_public_key);

        $result = (bool)openssl_verify($data, base64_decode($signature), $public_key_id);

        openssl_free_key($public_key_id);
        return $result;
    }

    /**
     * 支付宝 - 验证请求有效性和签名是否真实有效
     */
    private function _alipay_verifyNotify($data=array())
    {
        if (!is_array($data)||empty($data)) return false;
        //验证请求
        //通知id
        $notify_id = $data['notify_id'];
        // if (!$notify_id || !$this->_alipay_isRequest($notify_id)) return false;

        //验证签名
        //签名数据
        $sign_type = $data['sign_type'];
        if ($sign_type == 'RSA') {
            //RSA验签
            $sign = $data['sign'];
            //待验签数据
            $datastr = null;
            ksort($data);
            foreach ($data as $k=>$v) {
                if ($k=='sign'||$k=='sign_type'||$v=='') continue;
                $datastr .= $datastr ? '&'.$k.'='.$v : $k.'='.$v;
            }
            //签名公钥
            $alipay_public_key_file = MODULE_PATH.$this->_alipay_config['alipay_public_key'];
            if (!$this->_alipay_rsaVerify($datastr,$sign,$alipay_public_key_file)) return false;
        } else {
            //记录日志
            
            return false;
        }

        return true;
    }

    /**
     * 支付宝回调接口
     */
    public function alipaynotify()
    {
        if (!IS_POST) {
            echo 'fail';
            exit;
        }

        //POST请求数据
        $data = $_REQUEST;
        //验签
        $verifyResult = $this->_alipay_verifyNotify($data);
        //验签成功
        if ($verifyResult) {
            //记录通知日志
            $this->_notify_log($data);
            
            //触发业务逻辑 修改支付单状态

            //支付单号
            $payno = $data['out_trade_no'];
            //支付金额
            $amount = $data['total_fee'];
            //通知状态
            $tradestatus = $data['trade_status'];
            //修改支付单状态 支付成功
            $return = $this->_paySuccess();
            if ($return) {
                echo 'success';

                //记录成功日志
                
                exit;
            }
        }

        //记录失败日志
        
        //验签失败或业务逻辑执行失败
        echo 'fail';
    }

    /**
     * 微信 - 验证请求有效性和签名是否真实有效
     */
    private function _wxpay_verifyNotify($data=array())
    {
        if (!is_array($data)||empty($data)) return false;

        //签名数据
        $sign_type = $data['sign_type'];
        $input_charset = $data['input_charset'];
        $sign = $data['sign'];

        //待签名数据
        ksort($data);
        $datastring = null;
        // $datastring = $this->_array_join($data);
        foreach ($data as $k=>$v) {
            if ($k=='sign'||$v=='') continue;
            $datastring .= $datastring ? '&'.strtolower($k).'='.$v : strtolower($k).'='.$v;
        }
        $datastring .= '&key='.$this->_wxpay_config['partnerkey'];
        $datastring = iconv($input_charset, 'GBK', $datastring);
        //验证签名
        $result = false;
        switch ($sign_type) {
            case 'MD5':
                $mysign = md5($datastring);
                $mysign = strtoupper($mysign);
                $result = $mysign===$sign ? true : false;
                break;
            case 'RSA':
                break;
            default:
                break;
        }

        return $result ? true : false;
    }

    /**
     * 微信回调接口
     */
    public function wxpaynotify()
    {
        if (!IS_POST) {
            echo 'fail';
            exit;
        }

        //REQUEST请求数据
        $data = $_REQUEST;
        //验签
        $verifyResult = $this->_wxpay_verifyNotify($data);
        //验签成功
        if ($verifyResult) {
            //记录通知日志
            $this->_notify_log($data);
            
            //触发业务逻辑 修改支付单状态

            //支付单号
            $payno = $data['out_trade_no'];
            //支付金额
            $amount = (float)($data['total_fee']/100);
            //通知状态
            $tradestatus = $data['trade_state'];
            //修改支付单状态 支付成功
            $return = $this->_paySuccess();
            if ($return) {
                echo 'success';

                //记录成功日志
                
                exit;
            }
        }

        //记录失败日志
        
        //验签失败或业务逻辑执行失败
        echo 'fail';
    }

    /**
     * 记录回调日志
     */
    private function _notify_log()
    {

    }

    /**
     * 根据支付通知结果状态 修改系统的支付单状态
     */
    private function _paySuccess()
    {

    }

    /**
     * 模拟支付宝请求
     */
    public function salipay()
    {
        return true;

        // $api = 'http://222.92.197.77/mpay/alipaynotify';
        $api = 'http://222.92.197.76/MoolyApp/mpay/alipaynotify';
        $paramvars = array(
            'discount' => '0.00',
            'payment_type' => '1',
            'subject' => '魔力会员充值',
            'trade_no' => '2015033000001000160047580576',
            'buyer_email' => 'omg_qq@163.com',
            'gmt_create' => '2015-03-30 17:31:03',
            'notify_type' => 'trade_status_sync',
            'quantity' => '1',
            'out_trade_no' => 'MP2015033017292921786',
            'seller_id' => '2088311843874216',
            'notify_time' => '2015-03-30 17:35:30',
            'body' => '魔力会员充值',
            'trade_status' => 'TRADE_SUCCESS',
            'is_total_fee_adjust' => 'N',
            'total_fee' => '0.02',
            'gmt_payment' => '2015-03-30 17:31:04',
            'seller_email' => 'zhifu@imooly.com',
            'price' => '0.02',
            'buyer_id' => '2088202448687164',
            'notify_id' => '0837dc65cd4c4938064103065e6548b12w',
            'use_coupon' => 'N',
            'sign_type' => 'RSA',
            'sign' => 'ducb7oFV7/yNeJEkUZrKKSj1QxT+Cyr1Qz6sG2R9SO2MXaztJqQx1srjYxZaxztLQhqLvd1FeIGTQtTDnE9NmRLr8DwZcsV0G7PDZZVolhre59GbKG1mNgU8F4vacDlBRNWuXOhN5WgPGNp+vJlpJuN3aOjN2i6OzL+j6SdpJow=',
        );

        //发送请求
        $HttpClient = Http::Init($api,1);
        $result = $HttpClient->post(null,$paramvars,array(),'',15);

        dump($result);exit;
    }

    /**
     * 模拟微信请求
     */
    public function swxpay()
    {
        return true;

        // $api = 'http://222.92.197.77/mpay/wxpaynotify';
        $api = 'http://222.92.197.77/mpay/wxpaynotify';
        $paramvars = array(
            'attach' => '商城订单',
            'bank_billno' => '201503196160365106',
            'bank_type' => '2024',
            'discount' => '0',
            'fee_type' => '1',
            'input_charset' => 'UTF-8',
            'notify_id' => 'l18MqLVnvVCjPrHZGj1cjpcikTgDLw7eKQgVsNpbLUkseuw_pr6TkSPL_ukq0yW4ewULSw1MP8JA6qC1YTB1Z9LO6WpyV6dd',
            'out_trade_no' => 'MP2015031915591168188',
            'partner' => '1230268401',
            'product_fee' => '1',
            'sign' => '36800F6EDC64F5F6A346C454FFA2B9E8',
            'sign_type' => 'MD5',
            'time_end' => '20150319160327',
            'total_fee' => '1',
            'trade_mode' => '1',
            'trade_state' => '0',
            'transaction_id' => '1230268401201503196194568564',
            'transport_fee' => '0',
        );

        //发送请求
        $HttpClient = Http::Init($api,1);
        $result = $HttpClient->post(null,$paramvars,array(),'',15);

        dump($result);exit;
    }
}