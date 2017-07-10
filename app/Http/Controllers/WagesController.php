<?php

namespace App\Http\Controllers;



use App\Attendance;
use App\User;

class WagesController extends Controller
{
    public $user=null;
    public $limit_date=[];
    public $sick_leave=0; //病假天数
    public $thing_leave=0; //事假天数
    public $maternity_leave=0; //产假天数

    public function __construct($user,$limit_date)
    {
        $this->user=$user;
        $this->limit_date=$limit_date;
    }

    public function calculateWage(){
        $user=$this->user;
        $job_number=$user['job_number'];

        //所属区间
        $save_data['period_at'] = date('Y-m-d', strtotime('-1 month '));
        //工号
        $save_data['job_number'] = $job_number;
        //姓名
        $save_data['username'] = $this->getUsername($job_number);
        //试用期天数
        $save_data['probation'] = $this->getProbation($job_number);

        return $save_data;
    }

    /**
     * 获取姓名
     * @param $job_number
     * @return mixed
     */
    public function getUsername($job_number){
        $username=User::where('job_number',$job_number)
            ->value('username');
        return $username;
    }

    /**
     * 获取试用期天数
     * @param $job_number
     * @return float|int
     */
    public function getProbation($job_number)
    {
        $user=$this->user;
        $limit_date = $this->limit_date;
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
            //总时长(天数)
            $total_at=ceil((strtotime($end_at)-strtotime($start_at))/3600/24);
            //增加对应假期天数
            if($item['type']==='病假'){
                $this->sick_leave+=$total_at;
            }elseif($item['type']==='产假'){
                $this->maternity_leave+=$total_at;
            }else{
                $this->thing_leave+=$total_at;
            }
            dd($start_at,$end_at,$total_at);
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

}
