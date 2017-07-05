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
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //获取用户openid
        $openid = session('wechat.oauth_user')->id;
        $user=User::where('openid',$openid)->first();
        if (empty($user)){
            return '你尚未绑定!请在订阅号中完成绑定!';
        }else{
            \Auth::login($user, true);
        }
        return $next($request);
    }
}
