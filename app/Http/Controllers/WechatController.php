<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\User;
use App\WechatUser;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    public $user;

    public function __construct()
    {
//        $this->middleware('web');
        $this->middleware('wechat.oauth');
        $this->middleware('wechat.bind')->except('serve');
    }

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        //获取用户openid
        $openid = session('wechat.oauth_user')->id;
        \EasyWeChat::server()->setMessageHandler(function ($message) use ($openid) {
            if (session('wechat.oauth_user')) {
                if (\Auth::attempt(['openid' => $openid], true) === false) {
                    //如果匹配到 绑定XXX 密码XXX则完成绑定
                    if (preg_match('/^\x{7ed1}\x{5b9a}(.+)\x{5bc6}\x{7801}(.+)/u', $message->Content, $matches)) {
                        if (\Auth::attempt(['email' => trim($matches[1]), 'password' => trim($matches[2])], true)) {
                            $update_res = User::where('id', \Auth::user()->id)
                                ->update(['openid' => $openid]);
                            if ($update_res === false) {
                                return '绑定失败';
                            }
                            return "请点击链接,查看更多功能! " . route('wechat.home');
                        }
                        return '绑定失败! 密码错误!';
                    }
                    return "请输入:  绑定 your@email.com 密码 yourpassword   即可完成绑定!";;
                } else {
                    return "请点击链接,查看更多功能! " . route('wechat.home');
                }
            }
            return  '请关于订阅号,并完成绑定!';
        });
        //返回服务
        return \EasyWeChat::server()->serve();
    }


    public function home()
    {
        return view('wechat.home');
    }

    public function demoServe()
    {
        $message = new WechatUser();
        //获取用户openid
        $openid = session('wechat.oauth_user')->id;
        switch ($message->MsgType) {
            case 'text':
                //如果匹配 个人信息
                if (preg_match('/^(\x{4E2A}\x{4EBA}\x{4FE1}\x{606F})$/u', $message->Content)) {
                    //查询该用户个人信息
                    $user_info = \DB::table('users')
                        ->select('')
                        ->where('openid', $openid)
                        ->first();
                    return '这是个人信息';
                } else {
                    return '未识别信息';
                }
        }
    }

    public function bind()
    {
        //获取用户openid
        $openid = session('wechat.oauth_user')->id;
        return view('wechat.bind', ['openid' => $openid]);
    }


    /**
     * 获取用户名称列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersList()
    {
        $users_list = User::select(['job_number', 'username', 'part_name']);
        $users_list = $users_list->get()->toArray();
        $data = [];
        foreach ($users_list as $key => $item) {
            $data[$item['job_number']] = $item['username'] . '---' . $item['part_name'];
        }
        return $this->apiJson(true, '', $data);
    }

    /**
     * 获取用户基本信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserInfo()
    {
        $user_info = \Auth::user()->toArray();
        return $this->apiJson(true, '', $user_info);
    }

    public function applyAttendance(Request $request)
    {
        $user_info = \Auth::user()->toArray();
        $save['job_number'] = $user_info['job_number'];
        $save['username'] = $user_info['username'];
        $save['type'] = $request->get('type');
        $save['title'] = $request->get('title');
        $save['reson'] = $request->get('reson');
        $save['start_at'] = $user_info['start_at'];
        $save['end_at'] = $user_info['end_at'];
        $save['continued_at'] = $user_info['continued_at'];
        $save['approver'] = $user_info['approver'];
        $save['relevant'] = $user_info['relevant'];
        $save['status'] = 0;
        $save['retrial'] = '{}';
        $save['created_at'] = date('Y-m-d H:i:s', time());
        $save_res = Attendance::insert($save);
        if ($save_res === false) {
            return $this->apiJson(false, '提交失败!');
        }
        return $this->apiJson(true, '提交成功!');
    }

    /**
     * 创建菜单
     */
    public function createMenu()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key" => "V1001_TODAY_MUSIC"
            ],
            [
                "name" => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url" => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url" => "http://v.qq.com/"
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
