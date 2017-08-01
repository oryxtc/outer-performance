<?php

namespace App\Http\Controllers\Voyager;

use App\Attendance;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\WagesController;
use App\Memo;
use App\Provident;
use App\User;
use App\Wage;
use App\Welfare;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\Role;

class VoyagerWageController extends VoyagerBreadController
{

    public function confirmStatus(){
        $update_res=Wage::where('status',0)->update(['status'=>1]);
        if($update_res===false){
            return $this->apiJson(false,'操作失败!');
        }
        return $this->apiJson(true);
    }

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('browse_' . $dataType->name);

        $getter = $dataType->server_side ? 'paginate' : 'get';

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $relationships = $this->getRelationships($dataType);

            if ($model->timestamps) {
                $dataTypeContent = call_user_func([$model->with($relationships)->latest(), $getter]);
            } else {
                $dataTypeContent = call_user_func([
                    $model->with($relationships)->orderBy($model->getKeyName(), 'DESC'),
                    $getter,
                ]);
            }

            //Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }
        //多选框字段
        $checkData = ExcelController::WAGE_HEAD;
        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'checkData'));
    }


    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('edit_' . $dataType->name);

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_' . $dataType->name);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }


    // POST BRE(A)D
    public function store(Request $request)
    {
        //验证数据
        $this->validator($request->all())->validate();

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        Voyager::canOrFail('add_' . $dataType->name);

        //Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

            return redirect()
                ->route("voyager.{$dataType->slug}.edit", ['id' => $data->id])
                ->with([
                    'message' => "Successfully Added New {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    /**
     * 获取工资列表
     * @param Request $request
     * @return mixed
     */
    public function getWagesList(Request $request)
    {
        $head_list = ExcelController::WAGE_HEAD;
        if (empty($request->get('checkData', []))) {
            $check_data = ExcelController::WAGE_HEAD;
        } else {
            $request_check_data = $request->get('checkData', []);
            foreach ($request_check_data as $key => $value) {
                if ($value === 'true') {
                    $check_data[] = $key;
                }
            }
        }
        $wage = Wage::select(array_merge($check_data, ['id']))->orderBy('id', 'DESC');
        $response_data = \Datatables::eloquent($wage);
        //过滤字段
        $response_data = $response_data->editColumn('status', function (Wage $wage) {
            if ($wage->status == '1') {
                $status = '已确认';
            } else {
                $status = '待确认';
            }
            return $status;
        });
        //过滤字段
        $response_data = $response_data->editColumn('period_at', function (Wage $wage) {
            return date('Y-m',strtotime($wage->period_at));
        });
        //添加编辑
        $response_data = $response_data->addColumn('action', function (Wage $wage) {
            return view('voyager::wages.operate', ['wage' => $wage]);
        });
        //指定搜索栏模糊匹配
        $response_data = $response_data->filter(function ($query) use ($request, $head_list) {
            foreach ($head_list as $key => $value) {
                if ($request->has($key)) {
                    $query->where($key, 'like', "%{$request->get($key)}%");
                }
            }
            //如果有开始日期
            if ($request->has('period_at_start')) {
                $firstday = date('Y-m-01', strtotime($request->get('period_at_start')));
                $query->where('period_at', '>=', "{$firstday}");
            }
            //如果有结束日期
            if ($request->has('period_at_end')) {
                $firstday = date('Y-m-01', strtotime($request->get('period_at_end')));
                $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
                $query->where('period_at', '<=', "{$lastday}");
            }
        });
        //删除id
        $response_data = $response_data->removeColumn('id');
        //生成实例
        $response_data = $response_data->make();
        return $response_data;
    }


    /**
     * 计算工资
     */
    public function calculateWages()
    {
        $save_data = [];
        $limit_date = $this->getLimitDate();
        //获取员工
        $users_list = User::WhereDate('leave_at', '>', $limit_date['min_limit_date'])
            ->orWhereNull ('leave_at')
            ->get()
            ->toArray();
        //获取未计算员工
        $calculate_users_list=Wage::WhereDate('period_at', '>=', $limit_date['min_limit_date'])
            ->WhereDate('period_at', '<', $limit_date['max_limit_date'])
            ->pluck('job_number')
            ->toArray();
        //过滤掉已经计算过的
        foreach ($users_list as $key=>$item){
            if(in_array($item['job_number'],$calculate_users_list)){
                unset($users_list[$key]);
            }
        }
        //开始计算
        foreach ($users_list as $key => $user) {
            $wages_class=new WagesController($user);
            $save_data[]=$wages_class->calculateWage();
        }
        $add_save=Wage::insert($save_data);
        if($add_save===false){
            return $this->apiJson(false,'计算失败!');
        }
        return $this->apiJson();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return \Validator::make($data, [
            'job_number' => 'required|string',
        ]);
    }

    /**
     * 获取姓名
     * @param $user
     * @return mixed
     */
    public function getUsername($user){
        $job_number = $user['job_number'];
        $username=User::where('job_number',$job_number)
            ->value('username');
        return $username;
    }

    /**
     * 获取现金发放
     * @param $user
     * @return float
     */
    public function getCash($user){
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $cash = Memo::where('job_number', $job_number)
            ->whereDate('period_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('period_at', '>', $limit_date['min_limit_date'])
            ->sum('cash');
        return round($cash, 2);
    }


    /**
     * 获取代扣个税
     * @param $user
     * @param $pre_tax_subtotal
     * @return float|int
     */
    public function getTaxPersonal($user,$pre_tax_subtotal){
        $base=3500;
        //工资小于3500不扣税
        if ($pre_tax_subtotal <= 3500)
        {
            return 0;
        }
        //应纳税所得
        $value    = $pre_tax_subtotal - $base;
        //税率
        $tax_rate = 0.00;
        //扣除数
        $de_num   = 0;
        if ( $value <= 1500 )
        {
            $tax_rate = 0.03;
        }else if ( $value > 1500 && $value <= 4500 )
        {
            $tax_rate = 0.1;
            $de_num   = 105;
        }else if ( $value > 4500 && $value <= 9000 )
        {
            $tax_rate = 0.2;
            $de_num   = 555;
        }else if ( $value > 9000 && $value <= 35000 )
        {
            $tax_rate = 0.25;
            $de_num   = 1005;
        }else if ( $value > 35000 && $value <= 55000 )
        {
            $tax_rate = 0.3;
            $de_num   = 2755;
        }else if ( $value > 55000 && $value <= 80000 )
        {
            $tax_rate = 0.35;
            $de_num   = 5505;
        }else if ( $value > 80000 )
        {
            $tax_rate = 0.45;
            $de_num   = 13505;
        }
        return round($value * $tax_rate - $de_num,2);
    }

    /**
     * 获取社保和公积金
     * @param $user
     * @return mixed
     */
    public function getProvidentInfo($user)
    {
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $info = Provident::where('job_number', $job_number)
            ->whereDate('period_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('period_at', '>', $limit_date['min_limit_date'])
            ->first();
        if(empty($info)){
            $info['social_security_personal']=0;
            $info['social_security_company']=0;
            $info['provident_fund_personal']=0;
            $info['provident_fund_company']=0;
            return $info;
        };
        $info=$info->toArray();
        return $info;
    }

    /**
     * 事故扣款
     * @param $user
     * @return float
     */
    public function getCharge($user)
    {
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $charges = Memo::where('job_number', $job_number)
            ->whereDate('period_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('period_at', '>', $limit_date['min_limit_date'])
            ->sum('charge');
        return round($charges, 2);
    }

    /**
     * 获取交通通讯补贴
     * @param $user
     * @return float
     */
    public function getTrafficCommunication($user)
    {
        $driver = $user['driver'];
        $probation = $this->getProbation($user);
        $formal = $this->getFormal($user);

        $professional_so = $user['professional_so'];
        $info = Welfare::where('professional_so', $professional_so)
            ->first();
        if (empty($info)) {
            return 0;
        }
        $info = $info->toArray();
        if ($driver == '是') {
            $TrafficCommunication = $info['traffic_driver'] - 0 + $info['communication'];
            if ($probation - 0 + $formal < 25) {
                $TrafficCommunication = $TrafficCommunication / 30 * ($probation + $formal);
            }
        } else {
            $TrafficCommunication = $info['traffic_notdriver'] - 0 + $info['communication'];
            if ($probation - 0 + $formal < 25) {
                $TrafficCommunication = $TrafficCommunication / 30 * ($probation + $formal);
            }
        }
        return round($TrafficCommunication, 2);
    }

    /**
     * 获取加班工资
     * @param $user
     * @return float
     */
    public function getPaySick($user)
    {
        $overtime_probation = $this->getOvertimeProbation($user)['overtime_probation'];
        $overtime_formal = $this->getOvertimeProbation($user)['overtime_formal'];

        $pay_sick = $user['trial_pay'] / 30 * $overtime_probation + $user['formal_pay'] / 30 * $overtime_formal;
        return $pay_sick;
    }

    /**
     * 获取固定福利
     * @param $user
     * @return float
     */
    public function getFixed($user)
    {
        $probation = $this->getProbation($user);
        $formal = $this->getFormal($user);

        $professional_so = $user['professional_so'];
        $fixed = Welfare::where('professional_so', $professional_so)
            ->value('fixed');
        if ($probation - 0 + $formal < 25) {
            $fixed = $fixed / 30 * ($probation + $formal);
        }
        return round($fixed, 2);
    }

    /**
     * 奖金津贴
     * @param $user
     * @return float
     */
    public function getBonus($user)
    {
        $job_number = $user['job_number'];
        $professional_so = $user['professional_so'];
        $limit_date = $this->getLimitDate();
        $bonus = Memo::where('job_number', $job_number)
            ->whereDate('period_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('period_at', '>', $limit_date['min_limit_date'])
            ->sum('bonus');
        $extend=Memo::where('job_number', $job_number)
            ->whereDate('period_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('period_at', '>', $limit_date['min_limit_date'])
            ->sum('extend');
        //获取对应专业子等的扩展福利
        $welfare_info = Welfare::where('professional_so', $professional_so)
            ->first();
        return round($bonus-0+$extend+$welfare_info['extended_first']+$welfare_info['extended_second'], 2);
    }

    /**
     * 获取在岗工资
     * @param $user
     * @return mixed
     */
    public function getPayWages($user)
    {
        //试用期天数
        $probation = $this->getProbation($user);
        //正式期天数
        $formal = $this->getFormal($user);
        //病假天数
        $sick = $this->getSick($user);
        //产假天数
        $maternity=$this->getMaternity($user);

        $pay_wages = $user['trial_pay'] / 30 * $probation + $user['formal_pay'] / 30 * $formal + 1200 / 30 * $sick + 1800/30*$maternity;

        return round($pay_wages, 2);
    }

    /**
     * 获取事假天数
     * @param $user
     * @return float
     */
    public function getLeaveDay($user)
    {
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $leave_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', 11)
            ->where('type', '<>', '加班')
            ->whereDate('start_at', '>', $limit_date['min_limit_date'])
            ->whereDate('start_at', '<=', $limit_date['max_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            $hours_total = $this->dateToHours($item['continued_at']);
            $leave_day = $leave_day + $hours_total;

        }
        return ceil($leave_day / 9);
    }


    /**
     * 获取病假天数
     * @param $user
     * @return float
     */
    public function getSick($user)
    {
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $sick = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', 11)
            ->where('type', '病假')
            ->whereDate('start_at', '>', $limit_date['min_limit_date'])
            ->whereDate('start_at', '<=', $limit_date['max_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            $hours_total = $this->dateToHours($item['continued_at']);
            $sick = $sick + $hours_total;

        }
        return floor($sick / 9);
    }

    /**
     * 获取产假天数
     * @param $user
     * @return float
     */
    public function getMaternity($user){
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        $sick = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', 11)
            ->where('type', '产假')
            ->whereDate('start_at', '>', $limit_date['min_limit_date'])
            ->whereDate('start_at', '<=', $limit_date['max_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            $hours_total = $this->dateToHours($item['continued_at']);
            $sick = $sick + $hours_total;

        }
        return floor($sick / 9);
    }

    /**
     * 获取限定日期
     */
    public function getLimitDate()
    {
        $today_date = date('Y-m', time());
        $min_limit_date = date('Y-m-d 00:00:00', strtotime(date('Y-m-01', strtotime($today_date)) . ' -1 month'));
        $max_limit_date = date('Y-m-d 24:00:00', strtotime(date('Y-m-d', strtotime($min_limit_date)) . ' +1 month -1 day'));
        return ['min_limit_date' => $min_limit_date, 'max_limit_date' => $max_limit_date];
    }


    /**
     * 获取试用期天数
     * @param $user
     * @return float|int
     */
    public function getProbation($user)
    {
        $job_number = $user['job_number'];
        $limit_date = $this->getLimitDate();
        //转正时间
        $formal_at = empty($user['formal_at']) ? '1970-01-01' : $user['formal_at'];
        //入职时间
        $entry_at = $user['entry_at'];
        //事假天数
        $leave_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '<>', '加班')
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();

        foreach ($attendances_list as $item) {
            //确认开始时间
            if(strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])){
                $start_at=$limit_date['min_limit_date'];
            }else{
                $start_at=$item['start_at'];
            }
            //确认截止时间
            if(strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])){
                $end_at=$limit_date['max_limit_date'];
            }else{
                $end_at=$item['end_at'];
            }
            dd($limit_date,date('Y-m-d H:i:s',strtotime($limit_date['max_limit_date']. '-1 day')));
            $hours_total = $this->dateToHours($item['continued_at']);
            $leave_day = $leave_day + $hours_total;
        }
        $leave_day = ceil($leave_day / 9);

        $limit_date = $this->getLimitDate();
        if (strtotime($entry_at) <= strtotime($limit_date['min_limit_date'])) {
            if (strtotime($formal_at) <= strtotime($limit_date['min_limit_date'])) {
                return 0 - $leave_day;
            } elseif (strtotime($formal_at) >= strtotime($limit_date['max_limit_date'])) {
                return 30 - $leave_day;
            } else {
                $probation_day = (strtotime($formal_at) - strtotime($limit_date['min_limit_date']));
                $probation_day = ceil(abs($probation_day) / 86400);
                return $probation_day - $leave_day;
            }
        } else {
            if (strtotime($formal_at) <= strtotime($limit_date['max_limit_date'])) {
                $probation_day = (strtotime($formal_at) - strtotime($entry_at));
                $probation_day = ceil(abs($probation_day) / 86400);
                return $probation_day - $leave_day;
            } else {
                $probation_day = (strtotime($limit_date['max_limit_date']) - strtotime($entry_at));
                $probation_day = ceil(abs($probation_day) / 86400);
                return $probation_day - $leave_day;
            }
        }
    }

    /**
     * 获取装正天数
     * @param $user
     * @return float|int
     */
    public function getFormal($user)
    {
        $job_number = $user['job_number'];
        $leave_at = $user['leave_at'];
        $entry_at = $user['entry_at'];
        $limit_date = $this->getLimitDate();
        $probation_day = $this->getProbation($user);
        //转正时间
        $formal_at = empty($user['formal_at']) ? '2000-01-01' : $user['formal_at'];

        //事假天数
        $leave_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', 11)
            ->where('type', '<>', '加班')
            ->whereDate('start_at', '>', $limit_date['min_limit_date'])
            ->whereDate('start_at', '<=', $limit_date['max_limit_date'])
            ->whereDate('start_at', '>=', $formal_at)
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            $hours_total = $this->dateToHours($item['continued_at']);
            $leave_day = $leave_day + $hours_total;
        }
        $leave_day = ceil($leave_day / 9);


        if (empty($leave_at)) {
            return 30 - $probation_day - $leave_day;
        } elseif (strtotime($leave_at) <= strtotime($limit_date['max_limit_date']) && strtotime($entry_at) <= strtotime($limit_date['min_limit_date'])) {
            $formal_day = (strtotime($leave_at) - strtotime($limit_date['min_limit_date']));
            $formal_day = ceil(abs($formal_day) / 86400) - 1;
            return $formal_day - $probation_day - $leave_day;
        } elseif (strtotime($leave_at) <= strtotime($limit_date['max_limit_date']) && strtotime($entry_at) > strtotime($limit_date['min_limit_date'])) {
            $formal_day = (strtotime($leave_at) - strtotime($entry_at));
            $formal_day = ceil(abs($formal_day) / 86400);
            return $formal_day - $probation_day - $leave_day;
        } else {
            return 30 - $probation_day - $leave_day;
        }
    }


    /**
     * 获取加班天数
     * @param $user
     * @return array
     */
    public function getOvertimeProbation($user)
    {
        $job_number = $user['job_number'];
        $formal_at = $user['formal_at'];
        $limit_date = $this->getLimitDate();
        $overtime_probation = 0;
        $overtime_formal = 0;

        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', 11)
            ->where('type', '加班')
            ->whereDate('start_at', '>', $limit_date['min_limit_date'])
            ->whereDate('start_at', '<=', $limit_date['max_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            if (strtotime($item['start_at']) <= strtotime($formal_at)) {
                $hours_total = $this->dateToHours($item['continued_at']);
                $overtime_probation = $overtime_probation + $hours_total;
            } elseif (strtotime($item['start_at']) > strtotime($formal_at)) {
                $hours_total = $this->dateToHours($item['continued_at']);
                $overtime_formal = $overtime_formal + $hours_total;
            }
        }
        return ['overtime_probation' => floor($overtime_probation / 9), 'overtime_formal' => floor($overtime_formal / 9)];
    }

    /**
     * 日期转小时
     * @param $date
     * @return int
     */
    public function dateToHours($date)
    {
        $hours_total = 0;
        if (preg_match('/(\d+)\x{5929}(\d)\x{5c0f}\x{65f6}/u', $date, $matches)) {
            $hours_total = $matches[1] * 9 + $matches[2];
        }
        return $hours_total;
    }

}
