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
//        dd(session('wechat.oauth_user'));
        \EasyWeChat::server()->setMessageHandler(function($message){
            //获取用户的openid
//            $id = session('wechat.oauth_user')->id;
//            switch ($message->MsgType){
//                case 'text':
//                    if(preg_match("/^('个人信息')$/",$message->Content)){
//                       return '这是个人信息';
//                    }else{
//                        return '未识别信息';
//                    }
//            }
            return '123123';
        });

        return \EasyWeChat::server()->serve();
    }


    /**
     * 创建菜单
     */
    public function createMenu(){
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        \EasyWeChat::menu()->add($buttons);
    }
}
