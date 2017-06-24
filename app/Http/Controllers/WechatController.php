<?php

namespace App\Http\Controllers;

use App\WechatUser;
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
            return '消息回复测试成功';
        });

        return \EasyWeChat::server()->serve();
    }

    public function demoServe(){
        $message=new WechatUser();
        //获取用户openid
        $openid = session('wechat.oauth_user')->id;
        switch ($message->MsgType){
            case 'text':
                //如果匹配 个人信息
                if(preg_match('/^(\x{4E2A}\x{4EBA}\x{4FE1}\x{606F})$/u',$message->Content)){
                    //查询该用户个人信息
                    $user_info=\DB::table('users')
                        ->select('')
                        ->where('openid',$openid)
                        ->first();
                    return '这是个人信息';
                }else{
                    return '未识别信息';
                }
        }
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
