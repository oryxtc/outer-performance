<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WechatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        \Log::info(\EasyWeChat::user()); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        \EasyWeChat::server()->setMessageHandler(function($message){
            return "欢迎关注 overtrue!！";
        });

        \Log::info('return response.');

        return \EasyWeChat::server()->serve();
    }
}
