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
            //绑定用户
            if(\Auth::attempt(['openid'=>$openid],true)===false){
                \EasyWeChat::server()->setMessageHandler(function($message){
                    $content="请点击链接: \n".route('wechat.bind')."\n 完成用户绑定!";
                    return $content;
                });
                return \EasyWeChat::server()->serve();
            }
        }
        return $next($request);
    }
}
