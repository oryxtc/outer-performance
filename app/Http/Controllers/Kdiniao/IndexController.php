<?php
/**
 * Created by PhpStorm.
 * User: oryxt
 * Date: 2018/4/2
 * Time: 9:34
 */

namespace App\Http\Controllers\Kdiniao;

use App\Http\Controllers\Controller;

//批量打印接口地址
defined('API_URL') or define('API_URL', 'http://www.kdniao.com/External/PrintOrder.aspx');
//IP服务地址
defined('IP_SERVICE_URL') or define('IP_SERVICE_URL', 'http://www.kdniao.com/External/GetIp.ashx');
//电商ID
defined('EBusinessID') or define('EBusinessID', '1295921');
defined('TestEBusinessID') or define('TestEBusinessID', '1295921');
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('APIKey') or define('APIKey', '0adb7183-de36-49a4-9ce1-33724893dda5');
//请求url，正式环境地址：http://api.kdniao.cc/api/Eorderservice    测试环境地址：http://testapi.kdniao.cc:8081/api/EOrderService
defined('ReqURL') or define('ReqURL', 'http://testapi.kdniao.cc:8081/api/Eorderservice');

//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', '0adb7183-de36-49a4-9ce1-33724893dda5');

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
        //OrderCode:需要打印的订单号，和调用快递鸟电子面单的订单号一致，PortName：本地打印机名称，请参考使用手册设置打印机名称。支持多打印机同时打印。
        $request_data = '[{"OrderCode":"3922490598838","PortName":"SEC30CDA7B0CDB4"}]';
        $data_sign    = $this->encrypt($this->get_ip() . $request_data, APIKey);
        //是否预览，0-不预览 1-预览
        $is_priview   = '1';
        $request_data = urlencode($request_data);
        //组装表单
        $form = '<form id="form1" method="POST" action="' . API_URL . '"><input type="text" name="RequestData" value="' . $request_data . '"/><input type="text" name="EBusinessID" value="' . EBusinessID . '"/><input type="text" name="DataSign" value="' . $data_sign . '"/><input type="text" name="IsPreview" value="' . $is_priview . '"/></form><script>form1.submit();</script>';
        print_r($form);
    }

    public function getPrint(){
        $eorder = [];
        $eorder["ShipperCode"] = "YD";
        $eorder["OrderCode"] = "1";
        $eorder["PayType"] = 1;
        $eorder["ExpType"] = 1;
        $eorder["CustomerName"] = 'testyd';
        $eorder["CustomerPwd"] = 'testydpwd';
        $eorder["MonthCode"] = '1';
        $eorder["IsReturnTemp"] = '1';
        $eorder["IsReturnPrintTemplate"] = '1';

        $sender = [];
        $sender["Name"] = "李先生33333";
        $sender["Mobile"] = "18888888888";
        $sender["ProvinceName"] = "李先生33333";
        $sender["CityName"] = "深圳市";
        $sender["ExpAreaName"] = "福田区";
        $sender["Address"] = "赛格广场5401AB";

        $receiver = [];
        $receiver["Name"] = "李先生2222";
        $receiver["Mobile"] = "18888888888";
        $receiver["ProvinceName"] = "李先生2222";
        $receiver["CityName"] = "深圳市";
        $receiver["ExpAreaName"] = "福田区";
        $receiver["Address"] = "赛格广场5401AB";

        $commodityOne = [];
        $commodityOne["GoodsName"] = "其他";
        $commodity = [];
        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;


        //调用电子面单
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);

        //$jsonParam = JSON($eorder);//兼容php5.2（含）以下

        echo "电子面单接口提交内容：<br/>".$jsonParam;
        $jsonResult = $this->submitEOrder($jsonParam);


        //解析电子面单返回结果
        $result = json_decode($jsonResult, true);
        echo "<br/><br/>电子面单提交结果:<br/>";
        print_r($result);
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * Json方式 调用电子面单接口
     */
   public function submitEOrder($requestData){
        $datas = array(
            'EBusinessID' => TestEBusinessID,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, AppKey);
        $result=$this->sendPost(ReqURL, $datas);

        //根据公司业务处理返回的信息......

        return $result;
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

    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    public function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }

    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    public function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }



}