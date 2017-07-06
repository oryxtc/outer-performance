<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Http\Controllers\Voyager\Traits\BreadRelationshipParser;
use App\User;
use App\WechatUser;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;

class WechatController extends Controller
{
    use BreadRelationshipParser;
    public $user;

    public function __construct()
    {
        $this->middleware('web');
//        $this->middleware('wechat.oauth');
//        $this->middleware('wechat.bind')->except('serve');
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
                if ($user=User::where('openid',$openid)->first()) {
                    return "请点击链接,查看更多功能! " . route('wechat.home');
                } else {
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
                }
            }
            return  '请关于订阅号,并完成绑定!';
        });
        //返回服务
        return \EasyWeChat::server()->serve();
    }

    /**
     * 首頁
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home()
    {
        $id =auth()->id();
        $slug ='users';

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        return view('wechat.home',compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
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
     * 获取考勤详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceInfo(Request $request){
        $status=[
            '1'=>'待审核',
            '11'=>'退审',
            '21'=>'通过',
        ];
        $request_data=$request->all();
        $id=$request_data['id'];
        $info=Attendance::where('id',$id)
            ->first()
            ->toArray();
        //查询审核人
        $approver_arr=explode(',',$info['approver']);
        $relevant_arr=explode(',',$info['relevant']);
        $username_list=User::select(['job_number','username'])->whereIn('job_number',array_merge($approver_arr,$relevant_arr))->pluck('username','job_number')->toArray();
        foreach ($approver_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $approver_str[]=$username_list[$value];
        }
        foreach ($relevant_arr as $key=>$value){
            if(!$value){
                continue;
            }
            $relevant_str[]=$username_list[$value];
        }

        $retrial=json_decode($info['retrial']);
        $retrial_str=[];
        foreach ($retrial as $key=>$value){
            $retrial_str[$username_list[$key]]=$status[$value];
        }
        $data['info']=$info;
        $data['approver']=$approver_str;
        $data['relevant']=$relevant_str;
        return $this->apiJson(true,'',$data);
    }


    /**
     * 获取考勤列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function  getAttendanceList(){
        $status=[
            '1'=>'待审核',
            '11'=>'退审',
            '21'=>'通过',
        ];

        $attendance_list=Attendance::orderBy('id','DESC')->get()->toArray();
        $job_number=auth()->user()->job_number;
        $response_data=[];
        foreach ($attendance_list as $key=>$item){
            if($job_number==$item['job_number']){
                $item['can_review']=false;
                $item['status']=$status[$item['status']];
                $response_data[]=$item;
            }elseif (in_array($job_number,explode(',',$item['approver']))){
                $item['can_review']=true;
                $item['status']=$status[$item['status']];
                $response_data[]=$item;
            }elseif (in_array($job_number,explode(',',$item['relevant']))){
                $item['can_review']=false;
                $item['status']=$status[$item['status']];
                $response_data[]=$item;
            }
        }

        $response_data=array_slice($response_data,0,20);
        return view('wechat.attendanceList',['data'=>$response_data]);
    }


    /**
     * 考勤详情
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function attendanceInfo(Request $request,$id){
        $response_data=[];
        return view('wechat.attendanceInfo',['data'=>$response_data]);
    }


    /**
     * 提交考勤
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
        $save['created_at'] = date('Y-m-d H:i:s', time());

        //重写转审
        $status=1;
        $approver=$user_info['approver'];
        $approver_arr=explode(',',$approver);
        if(empty($approver)){
            $save['retrial']='{}';
        }else{
            foreach ($approver_arr as $value){
                $retrial_arr[$value]=$status;
            }
            $save['retrial']=json_encode($retrial_arr);
        }

        $save_res = Attendance::insert($save);
        if ($save_res === false) {
            return $this->apiJson(false, '提交失败!');
        }
        return $this->apiJson(true, '提交成功!');
    }

}
