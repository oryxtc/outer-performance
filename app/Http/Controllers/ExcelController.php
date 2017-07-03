<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Http\Method\UsersTemplate;
use App\Provident;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class ExcelController extends Controller
{

    const CHECK_DATA = [
        'role_id' => '角色',
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
        'driver' => '是否开车',
        'driver_id' => '车牌号',
        'father' => '父亲',
        'father_phone' => '父亲手机号',
        'father_at' => '父亲生日',
        'mother' => '母亲',
        'mother_phone' => '母亲手机号',
        'mother_at' => '母亲生日',
        'parents_address' => '父母住址',
        'parents_address' => '父母住址',
    ];

    const HEAD_LIST = [
        'role_id' => '角色',
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
        'driver' => '是否开车',
        'driver_id' => '车牌号',
        'father' => '父亲',
        'father_phone' => '父亲手机号',
        'father_at' => '父亲生日',
        'mother' => '母亲',
        'mother_phone' => '母亲手机号',
        'mother_at' => '母亲生日',
        'parents_address' => '父母住址',
        'parents_address' => '父母住址',
    ];

    const PROVIDENT_HEAD=[
        'job_number'=>'工号',
        'period_at'=>'所属期间',
        'social_security_personal'=>'社保个人部分',
        'social_security_company'=>'社保公司部分',
        'provident_fund_personal'=>'公积金个人部分',
        'provident_fund_company'=>'企业邮箱地址',
        'provident_fund_company'=>'公积金公司部分',
        'status'=>'状态',
    ];

    const ATTENDANCE_HEAD=[
        'created_at'=>'申请时间',
        'username'=>'姓名',
        'type'=>'类型',
        'title'=>'标题',
        'reson'=>'事由',
        'start_at'=>'开始时间',
        'end_at'=>'结束时间',
        'continued_at'=>'申请时长',
        'status'=>'状态',
    ];

    const MEMO_HEAD=[
        'period_at'=>'所属期间',
        'job_number'=>'工号',
        'bonus'=>'奖金津贴',
        'cash'=>'现金发放',
        'charge'=>'事故扣款',
        'extend'=>'扩展奖励',
        'remark'=>'备注',
    ];

    /**
     * 导出员工表模板
     * @param UsersTemplate $export
     * @return mixed
     */
    public function exportUsersTemplate(UsersTemplate $export)
    {
        $head_list = static::HEAD_LIST;
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
        $check_data = $request->get('checkData', null);
        $search_data = $request->get('searchData', null);
        $head_list = static::HEAD_LIST;
        //导出时过滤掉密码
        unset($head_list['password']);
        if (empty($check_data)) {
            return;
        }
        //如果是查询全部
        $check_data = ($check_data === '*' || in_array('*', $check_data)) ? array_keys($head_list) : $check_data;
        //查询数据
        $users_data = User::select($check_data);
        //如果有查询条件
        if (!empty($search_data)) {
            $users_data = $users_data
                ->where(key($search_data), 'like', '%' . $search_data[key($search_data)] . '%');
        }
        //获取最终数据
        $users_data = $users_data->get()->toArray();
        //角色id转为角色名称
        foreach ($users_data as $key=>&$user){
            $user['role_id']=$this->getRoleName($user['role_id']);
        }
        foreach ($check_data as $key => $value) {
            $head_list_value[] = $head_list[$value];
        }
        //导出数据
        $export->sheet('员工信息表', function ($sheet) use ($head_list_value, $users_data) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
            //填充主体
            $sheet->fromArray($users_data, null, 'A2', true, false);
        });
        return $export->export('xlsx');
    }


    /**
     * 导入员工
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importUsers(Request $request)
    {
        //从导入的excel中获取数据
        $filename = $request->file('file')->getPathname();
        $users_data = \Excel::load($filename, function ($reader) {
            // reader methods
        })->get()->toArray();
        //准备数据
        $head_list = static::HEAD_LIST;
        $head_list_flip = array_flip($head_list);
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
            //判断工号不能重复
            if (\DB::table('users')->where('job_number', $user[$head_list['job_number']])->count() > 0) {
                $errors_mes[] = "工号: " . $user[$head_list['job_number']] . " 已存在! <br/>";
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


    /**
     * 社保和公积金表模板
     * @return mixed
     */
    public function exportProvidentsTemplate()
    {
        $head_list = static::PROVIDENT_HEAD;
        $head_list_value = array_values($head_list);
        $export=\Excel::create('社保和公积金表');
        //导出数据
        $export->sheet('员工信息表', function ($sheet) use ($head_list_value) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 15);
            $sheet->setWidth('B', 15);
            $sheet->setWidth('C', 15);
            $sheet->setWidth('D', 15);
            $sheet->setWidth('E', 20);
            $sheet->setWidth('F', 20);
            $sheet->setWidth('G', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
        });
        return $export->export('xlsx');
    }

    /**
     * 导出社保和公积金
     * @param Request $request
     * @return mixed
     */
    public function exportProvidents(Request $request)
    {
        $search_data = $request->get('searchData', null);
        $head_list=static::PROVIDENT_HEAD;
        $export=\Excel::create('社保和公积金表');
        //增加索引列
        \DB::statement(\DB::raw('set @rownum=0'));
        //查询数据
        $providents_data = Provident::select(array_merge([\DB::raw('@rownum  := @rownum  + 1 AS rownum')],array_keys($head_list)));
        //如果有查询条件
        if (!empty($search_data)) {
            $providents_data = $providents_data
                ->where(key($search_data), 'like', '%' . $search_data[key($search_data)] . '%');
        }
        //如果有开始日期
        if($request->has('period_at_start')){
            $firstday = date('Y-m-01',strtotime($request->get('period_at_start')));
            $providents_data = $providents_data->where('period_at', '>=', "{$firstday}");
        }
        //如果有结束日期
        if($request->has('period_at_end')){
            $firstday = date('Y-m-01',strtotime($request->get('period_at_end')));
            $lastday = date('Y-m-d',strtotime("$firstday +1 month -1 day"));
            $providents_data = $providents_data->where('period_at', '<=', "{$lastday}");
        }
        //获取最终数据
        $providents_data = $providents_data->get()->toArray();

        //新增用户名称字段
        foreach ($providents_data as $key=>&$provident){
            array_splice($provident,0,1,['rownum'=>$provident['rownum'],'username'=>$this->getUsername($provident['job_number'])]);
        }
        $head_list_value=array_values(array_merge(['rownum'=>'序号'],['username'=>'姓名'],$head_list));
        //导出数据
        $export->sheet('社保和公积金表', function ($sheet) use ($head_list_value, $providents_data) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
            //填充主体
            $sheet->fromArray($providents_data, null, 'A2', true, false);
        });
        return $export->export('xlsx');
    }

    /**
     * 导入员工
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importProvidents(Request $request)
    {
        //从导入的excel中获取数据
        $filename = $request->file('file')->getPathname();
        $providents_data = \Excel::load($filename, function ($reader) {
            // reader methods
        })->get()->toArray();
        //准备数据
        $head_list = static::PROVIDENT_HEAD;
        $head_list_flip = array_flip($head_list);
        $save_data = [];
        $errors_mes = [];
        //处理数据
        foreach ($providents_data as $provident_key => $provident) {
            //如果不存在工号 则跳过
            if (array_key_exists($head_list['job_number'], $provident) === false || empty($provident[$head_list['job_number']])) {
                continue;
            }
            //判断工号必须已存在
            if (\DB::table('users')->where('job_number', $provident[$head_list['job_number']])->count() < 1) {
                $errors_mes[] = "工号: " . $provident[$head_list['job_number']] . " 不存在! <br/>";
                continue;
            }
            foreach ($provident as $key => $value) {
                //特殊处理某些数据
                if ($head_list_flip[$key] === 'period_at') {
                    $validator=\Validator::make(['period_at'=>$value],[
                        'period_at'=>'date'
                    ]);
                    $save_data[$provident_key][$head_list_flip[$key]] = $validator->fails() ? null :$value;
                    //试用薪酬
                } else {
                    $save_data[$provident_key][$head_list_flip[$key]] = $value;
                }

            }
        }
        if (empty($save_data)) {
            return $this->apiJson(false, '没有新增社保和公积金!');
        }
        //新增社保和公积金
        $save_res = \DB::table('providents')
            ->insert($save_data);
        if ($save_res === false) {
            return $this->apiJson(false, '新增失败!');
        }
        if (!empty($errors_mes)) {
            return $this->apiJson(false, $errors_mes);
        }
        return $this->apiJson(true, $errors_mes);
    }


    /**
     * 考勤模板
     * @return mixed
     */
    public function exportAttendancesTemplate()
    {
        $head_list = [
            'created_at'=>'申请时间',
            'job_number'=>'工号',
            'username'=>'姓名',
            'type'=>'类型',
            'title'=>'标题',
            'reson'=>'事由',
            'start_at'=>'开始时间',
            'end_at'=>'结束时间',
            'continued_at'=>'申请时长',
            'status'=>'状态',
        ];
        $head_list_value = array_values($head_list);
        $export=\Excel::create('考勤表');
        //导出数据
        $export->sheet('考勤表', function ($sheet) use ($head_list_value) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 20);
            $sheet->setWidth('B', 15);
            $sheet->setWidth('C', 15);
            $sheet->setWidth('D', 15);
            $sheet->setWidth('E', 20);
            $sheet->setWidth('F', 20);
            $sheet->setWidth('G', 20);
            $sheet->setWidth('H', 20);
            //填充头部
            $sheet->prependRow($head_list_value);
        });
        return $export->export('xlsx');
    }


    /**
     * 导出考勤表
     * @param Request $request
     * @return mixed
     */
    public function exportAttendances(Request $request)
    {
        $search_data = $request->get('searchData', null);
        $head_list = [
            'created_at'=>'申请时间',
            'job_number'=>'工号',
            'username'=>'姓名',
            'type'=>'类型',
            'title'=>'标题',
            'reson'=>'事由',
            'start_at'=>'开始时间',
            'end_at'=>'结束时间',
            'continued_at'=>'申请时长',
            'status'=>'状态',
        ];

        $export=\Excel::create('考勤表');
        //增加索引列
        \DB::statement(\DB::raw('set @rownum=0'));
        //查询数据
        $attendances_data = Attendance::select(array_merge([\DB::raw('@rownum  := @rownum  + 1 AS rownum')],array_keys($head_list)))->where('status','<>',0);
        //如果有查询条件
        if (!empty($search_data)) {
            if(key($search_data)=='status'){
                if (preg_match("/(.*)[\x{9000}](.*)/u",$search_data[key($search_data)])){
                    $status=11;
                }elseif (preg_match("/(.*)[\x{901A}](.*)/u",$search_data[key($search_data)])){
                    $status=21;
                }else{
                    $status=1;
                }
                $attendances_data = $attendances_data
                    ->where('status',$status);
            }else{
                $attendances_data = $attendances_data
                    ->where(key($search_data), 'like', '%' . $search_data[key($search_data)] . '%');
            }
        }

        //如果有开始日期
        if($request->has('start_at')){
            $attendances_data = $attendances_data->where('start_at', '>=', "{$request->get('start_at')}");
        }
        //如果有结束日期
        if($request->has('end_at')){
            $attendances_data = $attendances_data->where('end_at', '<=', "{$request->get('end_at')}");
        }
        //获取最终数据
        $attendances_data = $attendances_data->get()->toArray();
        //新增用户名称字段
        foreach ($attendances_data as $key=>&$attendance){
            if($attendance['status']=='11'){
                $attendance['status']='退审';
            }elseif ($attendance['status']=='21'){
                $attendance['status']='通过';
            }elseif ($attendance['status']=='1'){
                $attendance['status']='待审核';
            }
        }
        $head_list_value=array_values(array_merge(['rownum'=>'序号'],$head_list));
        //导出数据
        $export->sheet('考勤表', function ($sheet) use ($head_list_value, $attendances_data) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A', 10);
            //填充头部
            $sheet->prependRow($head_list_value);
            //填充主体
            $sheet->fromArray($attendances_data, null, 'A2', true, false);
        });
        return $export->export('xlsx');
    }


    /**
     * 导入考勤
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importAttendances(Request $request)
    {
        //从导入的excel中获取数据
        $filename = $request->file('file')->getPathname();
        $attendances_data = \Excel::load($filename, function ($reader) {
            // reader methods
        })->get()->toArray();
        //准备数据
        $head_list = [
            'created_at'=>'申请时间',
            'job_number'=>'工号',
            'username'=>'姓名',
            'type'=>'类型',
            'title'=>'标题',
            'reson'=>'事由',
            'start_at'=>'开始时间',
            'end_at'=>'结束时间',
            'continued_at'=>'申请时长',
            'status'=>'状态',
        ];

        $head_list_flip = array_flip($head_list);
        $save_data = [];
        $errors_mes = [];
        //处理数据
        foreach ($attendances_data as $attendance_key => $attendance) {
            //如果不存在工号 则跳过
            if (array_key_exists($head_list['job_number'], $attendance) === false || empty($attendance[$head_list['job_number']])) {
                continue;
            }
            //判断工号必须已存在
            if (\DB::table('users')->where('job_number', $attendance[$head_list['job_number']])->count() < 1) {
                $errors_mes[] = "工号: " . $attendance[$head_list['job_number']] . " 不存在! <br/>";
                continue;
            }
            foreach ($attendance as $key => $value) {
                //特殊处理某些数据
                if ($head_list_flip[$key] === 'start_at') {
                    $validator=\Validator::make(['start_at'=>$value],[
                        'start_at'=>'date'
                    ]);
                    $save_data[$attendance_key][$head_list_flip[$key]] = $validator->fails() ? null :$value;
                    //试用薪酬
                }elseif ($head_list_flip[$key] === 'end_at'){
                    $validator=\Validator::make(['end_at'=>$value],[
                        'end_at'=>'date'
                    ]);
                    $save_data[$attendance_key][$head_list_flip[$key]] = $validator->fails() ? null :$value;
                }elseif ($head_list_flip[$key] === 'created_at'){
                    $validator=\Validator::make(['created_at'=>$value],[
                        'created_at'=>'date'
                    ]);
                    $save_data[$attendance_key][$head_list_flip[$key]] = $validator->fails() ? null :$value;
                }elseif ($head_list_flip[$key] === 'status'){
                    if (preg_match("/(.*)[\x{9000}](.*)/u",$value)){
                        $status=11;
                    }elseif (preg_match("/(.*)[\x{901A}](.*)/u",$value)){
                        $status=21;
                    }else{
                        $status=1;
                    }
                    $save_data[$attendance_key][$head_list_flip[$key]] = $status;
                }
                else {
                    $save_data[$attendance_key][$head_list_flip[$key]] = $value;
                }
            }
        }
        if (empty($save_data)) {
            return $this->apiJson(false, '没有新增考勤!');
        }
        //新增社保和公积金
        $save_res = \DB::table('attendances')
            ->insert($save_data);
        if ($save_res === false) {
            return $this->apiJson(false, '新增失败!');
        }
        if (!empty($errors_mes)) {
            return $this->apiJson(false, $errors_mes);
        }
        return $this->apiJson(true, $errors_mes);
    }







    /**
     * 获取角色名称
     * @param $role_id
     * @return string
     */
    protected function getRoleName($role_id){
        if(empty($role_id)){
            return '未设置角色';
        }else{
            return Role::where('id',$role_id)->value('display_name');
        }
    }

    /**
     * 获取姓名
     * @param $job_number
     * @return string
     */
    protected function getUsername($job_number){
        if(empty($job_number)){
            return '';
        }else{
            return User::where('job_number',$job_number)->value('username');
        }
    }


}
