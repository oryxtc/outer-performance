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

        \EasyWeChat::server()->setMessageHandler(function($message){
            $user = session('wechat.oauth_user')->id;
            return $user;
        });

        \Log::info('return response.');

        return \EasyWeChat::server()->serve();
    }
}
