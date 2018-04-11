<?php
/**
 * Created by PhpStorm.
 * User: oryxt
 * Date: 2018/4/2
 * Time: 9:34
 */

namespace App\Http\Controllers\Kdiniao;

use App\Http\Controllers\Controller;
class IndexController extends Controller
{


    public function printOrder()
    {
        /**
         *快递鸟批量打印DEMO
         *
         * @author    kdniao
         * @date            2017-11-22
         * @description    先通过快递鸟电子面单接口提交电子面单后，再组装POST表单调用快递鸟批量打印接口页面
         */

        //批量打印接口地址
        defined('API_URL') or define('API_URL', 'http://www.kdniao.com/External/PrintOrder.aspx');
        //IP服务地址
        defined('IP_SERVICE_URL') or define('IP_SERVICE_URL', 'http://www.kdniao.com/External/GetIp.ashx');
        //电商ID
        defined('EBusinessID') or define('EBusinessID', '1295921');
        //电商加密私钥，快递鸟提供，注意保管，不要泄漏
        defined('APIKey') or define('APIKey', '0adb7183-de36-49a4-9ce1-33724893dda5');

        //OrderCode:需要打印的订单号，和调用快递鸟电子面单的订单号一致，PortName：本地打印机名称，请参考使用手册设置打印机名称。支持多打印机同时打印。
        $request_data = '[{"OrderCode":"3922490598838","PortName":"YD"}]';
        $data_sign    = $this->encrypt($this->get_ip() . $request_data, APIKey);
        //是否预览，0-不预览 1-预览
        $is_priview   = '1';
        $request_data = urlencode($request_data);
        //组装表单
        $form = '<form id="form1" method="POST" action="' . API_URL . '"><input type="text" name="RequestData" value="' . $request_data . '"/><input type="text" name="EBusinessID" value="' . EBusinessID . '"/><input type="text" name="DataSign" value="' . $data_sign . '"/><input type="text" name="IsPriview" value="' . $is_priview . '"/></form><script>form1.submit();</script>';
        print_r($form);


    }


    public function is_private_ip($ip)
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }


    public function get_ip()
    {
        //获取客户端IP
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (!$ip || $this->is_private_ip($ip)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, IP_SERVICE_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            return $output;
        } else {
            return $ip;
        }
    }


    public function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }
}