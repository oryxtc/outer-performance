<?php

namespace App\Http\Middleware;

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

            //认证用户
            if(\Auth::attempt(['openid'=>$openid],true)===false){
                \EasyWeChat::server()->setMessageHandler(function($message){
                    return "<a href='route('wechat.bind')'>请先完成用户绑定!</a>";
                });
                return \EasyWeChat::server()->serve();
            }
        }
        return $next($request);
    }
}
