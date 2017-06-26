<?php

namespace App\Http\Controllers;

use App\Http\Method\UsersTemplate;
use Illuminate\Http\Request;

class ExcelController extends Controller
{

    /**
     * 导出员工表模板
     * @param UsersTemplate $export
     * @return mixed
     */
    public function exportUsersTemplate(UsersTemplate $export)
    {
        $head_list = UsersTemplate::HEAD_lIST;
        $head_list_value = array_values($head_list);
        //导出数据
        $export->sheet('员工信息表', function ($sheet) use ($head_list_value) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
        });
        return $export->export('xlsx');
    }


    public function importUsers(Request $request)
    {
        //从导入的excel中获取数据
        $filename = $request->file('file')->getPathname();
        $users_data = \Excel::load($filename, function ($reader) {
            // reader methods
        })->get()->toArray();
        //准备数据
        $head_list = UsersTemplate::HEAD_lIST;
        $head_list_flip = array_flip(UsersTemplate::HEAD_lIST);
        $save_data = [];
        $errors_mes=[];
        //处理数据
        foreach ($users_data as $user_key=>$user) {
            //如果不存在email 则跳过
            if (array_key_exists($head_list['email'], $user) === false || empty($user[$head_list['email']])){
                continue;
            }
            //判断email不能重复
            if(\DB::table('users')->where('email',$user[$head_list['email']])->count() > 0){
                $errors_mes[]="邮箱: ".$user[$head_list['email']]." 已存在! <br/>";
                continue;
            }
            foreach ($user as $key => $value) {
                //特殊处理某些数据
                if ($head_list_flip[$key] === 'password') {
                    $save_data[$user_key][$head_list_flip[$key]] = empty($value) ? bcrypt($user[$head_list['email']]) : bcrypt($value);
                    //试用薪酬
                } elseif ($head_list_flip[$key] === 'trial_pay') {
                    $save_data[$user_key][$head_list_flip[$key]] = empty($save_value) ? round($user[$head_list['formal_pay']], 2) : round($value, 2);
                }else{
                    $save_data[$user_key][$head_list_flip[$key]]=$value;
                }
            }
        }
        if(empty($save_data)){
            return $this->apiJson(false,'没有新增员工!');
        }
        //新增员工
        $save_res=\DB::table('users')
            ->insert($save_data);
        if($save_res===false){
            return $this->apiJson(false,'新增失败!');
        }
        if(!empty($errors_mes)){
            return $this->apiJson(false,$errors_mes);
        }
        return $this->apiJson(true,$errors_mes);
    }
}
