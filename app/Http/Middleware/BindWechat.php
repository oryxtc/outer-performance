<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use EasyWeChat\Foundation\Application;
use Illuminate\Routing\Route;

class BindWechat
{
    /**
     * Use Service Container would be much artisan.
     */
    private $wechat;

    private $user;

    /**
     * Inject the wechat service.
     */
    public function __construct(Application $wechat)
    {
        $this->wechat = $wechat;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(session('wechat.oauth_user')){
            $openid=session('wechat.oauth_user')->id;
            //绑定用户
            if(\Auth::attempt(['openid'=>$openid],true)===false){
                \EasyWeChat::server()->setMessageHandler(function ($message) use($openid){
                    $content=$message->Content;
                    if(preg_match('/^\x{7ed1}\x{5b9a}(.+)\x{5bc6}\x{7801}(.+)/u',$content,$matches)){
                        if(\Auth::attempt(['email'=>trim($matches[1]),'password'=>trim($matches[2])],true)){
                            $update_res=User::where('id',\Auth::user()->id)
                                ->update(['openid'=>$openid]);
                            if($update_res===false){
                                return '绑定失败';
                            }
                            return "请点击链接,查看更多功能! ".route('wechat.home');
                        }
                        return '绑定失败! 密码错误!';
                    }
                    $content="请输入:  绑定 your@email.com 密码 yourpassword   即可完成绑定!";
                    return $content;
                });
                return \EasyWeChat::server()->serve();
            }else{
                return "请点击链接,查看更多功能! ".route('wechat.home');
            }
        }
        return $next($request);
    }
}
