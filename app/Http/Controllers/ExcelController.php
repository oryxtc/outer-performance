<?php

namespace App\Http\Controllers;

use App\Http\Method\UsersTemplate;
use Illuminate\Http\Request;

class ExcelController extends Controller
{

    const CHECK_DATA = [
        'belong_company' => '所属公司',
        'job_number' => '工号',
        'username' => '姓名',
        'status' => '状态',
        'property' => '性质',
        'contract_at' => '合同到期时间',
        'entry_at' => '入职时间',
        'formal_at' => '转正时间',
        'leave_at' => '离职时间',
        'email' => '企业邮箱',
    ];

    const HEAD_lIST = [
        'belong_company' => '所属公司',
        'job_number' => '工号',
        'username' => '姓名',
        'status' => '状态',
        'property' => '性质',
        'contract_at' => '合同到期时间',
        'entry_at' => '入职时间',
        'formal_at' => '转正时间',
        'leave_at' => '离职时间',
        'email' => '企业邮箱',
        'password' => '密码',
        'trial_pay' => '试用薪酬',
        'formal_pay' => '转正薪酬',
        'bank_number' => '银行卡号',
        'bank' => '银行',
        'post_name' => '岗位名称',
        'part_name' => '部门名称',
        'part_first_name' => '部门一级',
        'part_second_name' => '部门二级',
        'part_third_name' => '部门三级',
        'report_relation' => '汇报关系',
        'tutor_name' => '导师名称',
        'hrbp' => 'HRBP',
        'area' => '区域',
        'professional_rank' => '专业职级',
        'professional_title' => '专业称谓',
        'professional_so' => '专业子等',
        'management_rank' => '管理级别',
        'management_title' => '管理称谓',
        'graduate_school' => '毕业学校',
        'graduate_at' => '毕业时间',
        'education' => '学历',
        'sex' => '性别',
        'nation' => '名族',
        'born_at' => '出生日期',
        'address' => '住址',
        'id_card' => '身份证号',
        'issuing_authority' => '签发机关',
        'effective_start_at' => '有效起始时间',
        'effective_end_at' => '有效结束时间',
        'phone' => '手机号',
        'wechat' => '微信号',
        'personal_qq' => '个人QQ',
        'company_qq' => '企业QQ',
        'father' => '父亲',
        'father_phone' => '父亲手机号',
        'father_at' => '父亲生日',
        'mother' => '母亲',
        'mother_phone' => '母亲手机号',
        'mother_at' => '母亲生日',
        'parents_address' => '父母住址',
    ];

    /**
     * 导出员工表模板
     * @param UsersTemplate $export
     * @return mixed
     */
    public function exportUsersTemplate(UsersTemplate $export)
    {
        $head_list = static::HEAD_lIST;
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


    /**
     * 导出用户
     * @param Request $request
     * @param UsersTemplate $export
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportUsers(Request $request, UsersTemplate $export)
    {
        $check_data=$request->get('checkData',null);
        $search_data=$request->get('searchData',null);
        $head_list = static::HEAD_lIST;
        if(empty($check_data)){
            return ;
        }
        //如果是查询全部
        $check_data=in_array('*',$check_data)?'*':$check_data;
        //查询数据
        $users_data = \DB::table('users')
            ->select($check_data);
        //如果有查询条件
        if(!empty($search_data)){
            $users_data=$users_data
                ->where(key($search_data),'like','%'.$search_data[key($search_data)].'%');
        }
        //获取最终数据
        $users_data=$users_data->get();
        $users_data = $this->stdClassToArray($users_data);

        foreach ($check_data as $key=>$value){
            $head_list_value[]=$head_list[$value];
        }
        //导出数据
        $export->sheet('员工信息表', function ($sheet) use ($head_list_value, $users_data) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
            //填充主体
            $sheet->fromArray($users_data,null,'A2',true,false);
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
        $errors_mes = [];
        //处理数据
        foreach ($users_data as $user_key => $user) {
            //如果不存在email 则跳过
            if (array_key_exists($head_list['email'], $user) === false || empty($user[$head_list['email']])) {
                continue;
            }
            //判断email不能重复
            if (\DB::table('users')->where('email', $user[$head_list['email']])->count() > 0) {
                $errors_mes[] = "邮箱: " . $user[$head_list['email']] . " 已存在! <br/>";
                continue;
            }
            foreach ($user as $key => $value) {
                //特殊处理某些数据
                if ($head_list_flip[$key] === 'password') {
                    $save_data[$user_key][$head_list_flip[$key]] = empty($value) ? bcrypt($user[$head_list['email']]) : bcrypt($value);
                    //试用薪酬
                } elseif ($head_list_flip[$key] === 'trial_pay') {
                    $save_data[$user_key][$head_list_flip[$key]] = empty($save_value) ? round($user[$head_list['formal_pay']], 2) : round($value, 2);
                } else {
                    $save_data[$user_key][$head_list_flip[$key]] = $value;
                }
            }
        }
        if (empty($save_data)) {
            return $this->apiJson(false, '没有新增员工!');
        }
        //新增员工
        $save_res = \DB::table('users')
            ->insert($save_data);
        if ($save_res === false) {
            return $this->apiJson(false, '新增失败!');
        }
        if (!empty($errors_mes)) {
            return $this->apiJson(false, $errors_mes);
        }
        return $this->apiJson(true, $errors_mes);
    }
}
