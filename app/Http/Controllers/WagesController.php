<?php

namespace App\Http\Controllers;


use App\Attendance;
use App\Constant;
use App\Memo;
use App\Provident;
use App\User;
use App\Welfare;

class WagesController extends Controller
{
    public $user                = null;
    public $job_number          = null;
    public $limit_date          = [];
    public $overtime_probation  = 0; //试用期加班天数
    public $overtime_formal     = 0; //正式加班天数
    public $sick_probation      = 0; //试用病假天数
    public $sick_formal         = 0; //正式病假天数
    public $think_probation     = 0; //试用事假天数
    public $think_formal        = 0; //正式事假天数
    public $sick                = 0;//病假天数
    public $maternity_probation = 0; //试用产假天数
    public $maternity_formal    = 0; //正式产假天数
    public $maternity           = 0; //产假天数
    public $probation           = 0; //试用期天数
    public $formal              = 0; //正式期天数
    public $provident_info      = [];// 社保公积金信息
    public $salary              = 0; // 薪酬
    public $daily_hours         = 0; // 每日工作时长
    public $month_daily         = 0; // 每月工作时长


    public function __construct($user)
    {
        $this->user = $user;
        $this->user['leave_at'] = empty($user['leave_at']) ? date('Y-m-d H:i:s', strtotime('now +10 years')) : $user['leave_at'];

        $this->job_number = $user['job_number'];
        $this->limit_date = $this->getLimitDate();

        $this->sick_probation = $this->getSickProbation();
        $this->sick_formal = $this->getSickFormal();

        $this->maternity_probation = $this->getMaternityProbation();
        $this->maternity_formal = $this->getMaternityFormal();

        $this->think_probation = $this->getThinkProbation();
        $this->think_formal = $this->getThinkFormal();

        $this->provident_info = $this->getProvidentInfo();

        $this->daily_hours = $this->getDailyHours();

        $this->month_daily = $this->getMonthDaily();

        $this->salary = $this->getSalaryInfo();


    }


    public function calculateWage()
    {
        //所属区间
        $save_data['period_at'] = date('Y-m-d', strtotime('-1 month '));
        //工号
        $save_data['job_number'] = $this->job_number;
        //姓名
        $save_data['username'] = $this->getUsername();
        //病假天数
        $save_data['sick'] = $this->getSick();
        //产假天数
        $save_data['maternity'] = $this->getMaternity();
        //试用加班天数
        $save_data['overtime_probation'] = $this->getOvertimeProbation();
        //正式加班天数
        $save_data['overtime_formal'] = $this->getOvertimeFormal();
        //试用期天数
        $save_data['probation'] = $this->getProbation();
        //正式期天数
        $save_data['formal'] = $this->getFormal();
        //在岗工资
        $save_data['pay_wages'] = $this->getPayWages();
        //加班工资
        $save_data['pay_sick'] = $this->getPaySick();
        //工资小计
        $save_data['pay_subtotal'] = $save_data['pay_wages'] - 0 + $save_data['pay_sick'];
        //奖金津贴
        $save_data['bonus'] = $this->getBonus();
        //月固定津贴
        $save_data['fixed'] = $this->getFixed();
        //交通个通讯福利
        $save_data['traffic_communication'] = $this->getTrafficCommunication();
        //事故扣款
        $save_data['charge'] = $this->getCharge();
        //应发工资
        $save_data['pay_should'] = round($save_data['pay_subtotal'] - 0 + $save_data['bonus'] + $save_data['fixed'] + $save_data['traffic_communication'] - $save_data['charge'], 5);
        //社保个人部分
        $save_data['social_security_personal'] = round($this->provident_info['social_security_personal'], 5);
        //社保公司部分
        $save_data['social_security_company'] = round($this->provident_info['social_security_company'], 5);
        //公积金个人部分
        $save_data['provident_fund_personal'] = round($this->provident_info['provident_fund_personal'], 5);
        //公积金公司部分
        $save_data['provident_fund_company'] = round($this->provident_info['provident_fund_company'], 5);
        //税前应发小计
        $save_data['pre_tax_subtotal'] = round($save_data['pay_should'] - $save_data['social_security_personal'] - $save_data['provident_fund_personal'], 5);
        //代扣个税
        $save_data['tax_personal'] = $this->getTaxPersonal($save_data['pre_tax_subtotal']);
        //实发工资
        $save_data['pay_real'] = round($save_data['pre_tax_subtotal'] - $save_data['tax_personal'], 5);
        //现金发放
        $save_data['cash'] = $this->getCash();
        //银行发放
        $save_data['pay_bank'] = round($save_data['pay_real'] - $save_data['cash'], 5);
        //公司成本
        $save_data['total_company'] = round($save_data['pay_real'] + $save_data['social_security_personal'] + $save_data['social_security_company'] + $save_data['provident_fund_personal'] + $save_data['provident_fund_company'] + $save_data['tax_personal'], 5);
        //备注
        $save_data['remark'] = $this->getRemark();
        //状态
        $save_data['status'] = 0;
        //创建失败
        $save_data['created_at'] = date('Y-m-d H:i:s', time());
        return $save_data;
    }

    /**
     * 获取每日工作时长
     * @return mixed
     */
    public function getDailyHours()
    {
        $daily_hours = Constant::where('key', 'daily_hours')->value('value');
        $daily_hours = $daily_hours ?: 8;
        return $daily_hours;
    }


    /**
     * 获取每月工作时长
     * @return mixed
     */
    public function getMonthDaily()
    {
        $month_daily = Constant::where('key', 'month_daily')->value('value');
        $month_daily = $month_daily ?: 8;
        return $month_daily;
    }

    /**
     * 获取薪酬
     */
    public function getSalaryInfo()
    {
        $user = $this->user;
        $daily_hours = $this->daily_hours;
        //试用期日薪
        $data['trial_daily_salary'] = round($user['trial_pay'] / $this->month_daily, 5);
        //试用期时新
        $data['trial_hourly_salary'] = round($user['trial_pay'] / $this->month_daily / $daily_hours, 5);
        //正式期日薪
        $data['formal_daily_salary'] = round($user['formal_pay'] / $this->month_daily, 5);
        //正式期日薪
        $data['formal_hourly_salary'] = round($user['formal_pay'] / $this->month_daily / $daily_hours, 5);
        return $data;
    }


    /**
     * 获取备注
     * @return mixed
     */
    public function getRemark()
    {
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $remark = Memo::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->value('remark');
        return $remark;
    }


    /**
     * 获取现金发放
     * @return float
     */
    public function getCash()
    {
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $cash = Memo::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->sum('cash');
        return round($cash, 5);
    }

    /**
     * 获取代扣个税
     * @param $pre_tax_subtotal
     * @return float|int
     */
    public function getTaxPersonal($pre_tax_subtotal)
    {
        $base = 3500;
        //工资小于3500不扣税
        if ($pre_tax_subtotal <= 3500) {
            return 0;
        }
        //应纳税所得
        $value = $pre_tax_subtotal - $base;
        //税率
        $tax_rate = 0.00;
        //扣除数
        $de_num = 0;
        if ($value <= 1500) {
            $tax_rate = 0.03;
        } else if ($value > 1500 && $value <= 4500) {
            $tax_rate = 0.1;
            $de_num = 105;
        } else if ($value > 4500 && $value <= 9000) {
            $tax_rate = 0.2;
            $de_num = 555;
        } else if ($value > 9000 && $value <= 35000) {
            $tax_rate = 0.25;
            $de_num = 1005;
        } else if ($value > 35000 && $value <= 55000) {
            $tax_rate = 0.3;
            $de_num = 2755;
        } else if ($value > 55000 && $value <= 80000) {
            $tax_rate = 0.35;
            $de_num = 5505;
        } else if ($value > 80000) {
            $tax_rate = 0.45;
            $de_num = 13505;
        }
        return round($value * $tax_rate - $de_num, 5);
    }


    /**
     * 获取社保个人部分
     * @return mixed
     */
    public function getProvidentInfo()
    {
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $info = Provident::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->first();
        if (empty($info)) {
            $info['social_security_personal'] = 0;
            $info['social_security_company'] = 0;
            $info['provident_fund_personal'] = 0;
            $info['provident_fund_company'] = 0;
            return $info;
        };
        $info = $info->toArray();
        return $info;
    }


    /**
     * 事故扣款
     * @return float
     */
    public function getCharge()
    {
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $charges = Memo::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->sum('charge');
        return round($charges, 5);
    }

    /**
     * 获取交通通讯补贴
     * @return float
     */
    public function getTrafficCommunication()
    {
        $user = $this->user;
        $driver = $user['driver'];
        $formal = $this->formal;
        $probation = $this->probation;

        //如果是离职员工
        if ($user['status'] === '离职') {
            return round(100 / $this->month_daily * ($formal + $probation), 5);
        }

        $professional_so = $user['professional_so'];
        $info = Welfare::where('professional_so', $professional_so)
            ->first();

        if (empty($info)) {
            return 0;
        }
        $info = $info->toArray();
        if ($driver == '是') {
            $TrafficCommunication = $info['traffic_driver'] - 0 + $info['communication'] + $info['extended_first'] + $info['extended_second'];
            if (($formal + $probation) < 25) {
                $TrafficCommunication = $TrafficCommunication / $this->month_daily * ($formal + $probation);
            }
        } else {
            $TrafficCommunication = $info['traffic_notdriver'] - 0 + $info['communication'] + $info['extended_first'] + $info['extended_second'];
            if ($formal + $probation < 25) {
                $TrafficCommunication = $TrafficCommunication / $this->month_daily * ($formal + $probation);
            }
        }

        return round($TrafficCommunication, 5);
    }

    /**
     * 获取姓名
     * @return mixed
     */
    public function getUsername()
    {
        $job_number = $this->job_number;
        $username = User::where('job_number', $job_number)
            ->value('username');
        return $username;
    }


    /**
     * 获取试用加班天数
     * @return array
     */
    public function getOvertimeProbation()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $overtime_probation_total = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '加班')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) < strtotime($start_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $end_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $overtime_probation_total += $total_at;
        }
        $overtime_probation_total = round($overtime_probation_total, 1);
        $this->overtime_probation = $overtime_probation_total;
        return $overtime_probation_total;
    }

    /**
     * 获取试用病假天数
     * @return array
     */
    public function getSickProbation()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;

        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '病假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) < strtotime($start_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $end_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }

    /**
     * 获取试用产假天数
     * @return array
     */
    public function getMaternityProbation()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;

        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '产假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) < strtotime($start_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $end_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }

    /**
     * 获取正式加班天数
     * @return array
     */
    public function getOvertimeFormal()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $overtime_formal_total = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '加班')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) > strtotime($end_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $start_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $overtime_formal_total += $total_at;
        }

        $overtime_formal_total = round($overtime_formal_total, 1);

        $this->overtime_formal = $overtime_formal_total;
        return $overtime_formal_total;
    }

    /**
     * 获取正式病假天数
     * @return array
     */
    public function getSickFormal()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '病假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();

        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) > strtotime($end_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $start_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }


    /**
     * 获取正式产假天数
     * @return array
     */
    public function getMaternityFormal()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '产假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();

        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) > strtotime($end_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $start_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }


    /**
     * 获取试用事假天数
     * @return array
     */
    public function getThinkProbation()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;

        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '事假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();
        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) < strtotime($start_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $end_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }


    /**
     * 获取正式事假天数
     * @return array
     */
    public function getThinkFormal()
    {
        $user = $this->user;
        $job_number = $this->job_number;
        $limit_date = $this->limit_date;
        $formal_at = $user['formal_at'];
        $total_day = 0;
        $attendances_list = Attendance::where('job_number', $job_number)
            ->where('status', Attendance::STATUS_PASSED)
            ->where('type', '=', '事假')
            ->where('start_at', '<', $limit_date['max_limit_date'])
            ->where('end_at', '>', $limit_date['min_limit_date'])
            ->get()
            ->toArray();

        foreach ($attendances_list as $item) {
            //确认开始时间
            if (strtotime($item['start_at']) < strtotime($limit_date['min_limit_date'])) {
                $start_at = $limit_date['min_limit_date'];
            } else {
                $start_at = $item['start_at'];
            }
            //确认截止时间
            if (strtotime($item['end_at']) > strtotime($limit_date['max_limit_date'])) {
                $end_at = $limit_date['max_limit_date'];
            } else {
                $end_at = $item['end_at'];
            }
            //更具转正日期计算
            if (strtotime($formal_at) > strtotime($end_at)) {
                continue;
            } elseif (strtotime($formal_at) <= strtotime($end_at) && strtotime($formal_at) >= strtotime($start_at)) {
                $start_at = $formal_at;
            }
            //总时长(天数)
            $total_at = (strtotime($end_at) - strtotime($start_at)) / 3600 / 24;
            //增加对应试用加班天数
            $total_day += $total_at;
        }
        $total_day = round($total_day, 1);
        return $total_day;
    }

    /**
     * 获取病假天数
     * @return float
     */
    public function getSick()
    {
        $this->sick = round($this->sick_probation + $this->sick_formal, 1);
        return $this->sick;
    }


    /**
     * 获取病假天数
     * @return float
     */
    public function getMaternity()
    {
        $this->maternity = round($this->maternity_formal + $this->maternity_probation, 1);
        return $this->maternity;
    }


    /**
     * 获取试用期天数
     * @return float|int
     */
    public function getProbation()
    {
        $user = $this->user;
        $limit_date = $this->limit_date;
        //转正时间
        $formal_at = $user['formal_at'];
        //入职时间
        $entry_at = $user['entry_at'];
        //离职时间
        $leave_at = $user['leave_at'];
        //计算试用期天数
        if (strtotime($entry_at) < strtotime($limit_date['min_limit_date'])) {
            $probation_start_at = $limit_date['min_limit_date'];
        } elseif (strtotime($entry_at) > strtotime($limit_date['max_limit_date'])) {
            $probation_start_at = $limit_date['max_limit_date'];
        } else {
            $probation_start_at = $entry_at;
        }
        if (empty($formal_at)) {
            if (strtotime($leave_at) < strtotime($limit_date['max_limit_date'])) {
                $probation_end_at = $leave_at;
            } else {
                $probation_end_at = $limit_date['max_limit_date'];
            }
        } else {
            if (strtotime($formal_at) < strtotime($limit_date['max_limit_date']) && strtotime($formal_at) > strtotime($limit_date['min_limit_date'])) {
                $probation_end_at = $formal_at;
            } else if (strtotime($formal_at) <= strtotime($limit_date['min_limit_date'])) {
                $probation_end_at = $limit_date['min_limit_date'];
            } else {
                $probation_end_at = $limit_date['max_limit_date'];
            }
        }

        //试用期总时长(天数)
        $probation_total_at = ceil((strtotime($probation_end_at) - strtotime($probation_start_at)) / 3600 / 24);
        $probation_total_at = ($probation_total_at <= 0 ? 0 : $probation_total_at);
        $probation_total_at = ($probation_total_at >= $limit_date['day_number'] ? $this->month_daily : $probation_total_at);
        $probation_total_at = round($probation_total_at - $this->sick_probation - $this->maternity_probation - $this->think_probation, 1);
        $this->probation = $probation_total_at;
        return $probation_total_at;
    }


    /**
     * 获取正式期天数
     * @return float|int
     */
    public function getFormal()
    {
        $user = $this->user;
        $limit_date = $this->limit_date;
        //转正时间
        $formal_at = $user['formal_at'];
        //离职时间
        $leave_at = $user['leave_at'];

        //计算正式期天数
        if (strtotime($leave_at) < strtotime($limit_date['max_limit_date'])) {
            $formal_end_at = $leave_at;
        } else {
            $formal_end_at = $limit_date['max_limit_date'];
        }
        if (empty($formal_at)) {
            return 0;
        } else {
            if (strtotime($formal_at) <= strtotime($limit_date['min_limit_date'])) {
                $formal_start_at = $limit_date['min_limit_date'];
            } else if (strtotime($formal_at) > strtotime($limit_date['min_limit_date']) && strtotime($formal_at) < strtotime($limit_date['max_limit_date'])) {
                $formal_start_at = $formal_at;
            } else {
                $formal_start_at = $limit_date['max_limit_date'];
            }
        }
        //正式期期总时长(天数)
        $formal_total_at = ceil((strtotime($formal_end_at) - strtotime($formal_start_at)) / 3600 / 24);
        $formal_total_at = ($formal_total_at <= 0 ? 0 : $formal_total_at);
        $formal_total_at = ($formal_total_at >= $limit_date['day_number'] ? $this->month_daily : $formal_total_at);
        $formal_total_at = round($formal_total_at - $this->sick_formal - $this->maternity_formal - $this->think_formal, 1);
        if ($formal_total_at + $this->probation >= $this->month_daily) {
            $formal_total_at = $this->month_daily - $this->probation;
        }
        $this->formal = $formal_total_at;
        return $formal_total_at;
    }


    /**
     * 获取在岗工资
     * @return mixed
     */
    public function getPayWages()
    {
        //试用期天数
        $probation = $this->probation;
        //正式期天数
        $formal = $this->formal;
        //病假天数
        $sick = $this->sick;
        //产假天数
        $maternity = $this->maternity;
        //病假工资&产假工资
        $sick_pay = floatval(Constant::where('key', 'sick_pay')->value('value'));
        $maternity_pay = floatval(Constant::where('key', 'maternity_pay')->value('value'));
        //每日工作时长
        $daily_hours = $this->daily_hours;

        $pay_wages = $this->salary['trial_daily_salary'] * floor($probation)
            + $this->salary['trial_hourly_salary'] * ($probation - floor($probation))
            + $this->salary['formal_daily_salary'] * floor($formal)
            + $this->salary['formal_hourly_salary'] * ($formal - floor($formal))
            + $sick_pay / $this->month_daily * floor($sick)
            + $sick_pay / $this->month_daily / $daily_hours * ($sick - floor($sick))
            + $maternity_pay / $this->month_daily * floor($maternity)
            + $maternity_pay / $this->month_daily / $daily_hours * ($maternity - floor($maternity));

        return round($pay_wages, 5);
    }


    /**
     * 获取加班工资
     * @return float
     */
    public function getPaySick()
    {
        $overtime_probation = $this->overtime_probation;
        $overtime_formal = $this->overtime_formal;

        $pay_sick = $this->salary['trial_daily_salary'] * floor($overtime_probation)
            + $this->salary['trial_hourly_salary'] * ($overtime_probation - floor($overtime_probation))
            + $this->salary['formal_daily_salary'] * floor($overtime_formal)
            + $this->salary['formal_daily_salary'] * ($overtime_formal - floor($overtime_formal));

        return $pay_sick;
    }

    /**
     * 奖金津贴
     * @return float
     */
    public function getBonus()
    {
        $limit_date = $this->limit_date;
        $job_number = $this->job_number;

        $bonus = Memo::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->sum('bonus');

        $extend = Memo::where('job_number', $job_number)
            ->where('period_at', '<=', $limit_date['max_limit_date'])
            ->where('period_at', '>=', $limit_date['min_limit_date'])
            ->sum('extend');

        return round($bonus - 0 + $extend, 5);
    }

    /**
     * 获取固定福利
     * @return float
     */
    public function getFixed()
    {
        $user = $this->user;
        $formal = $this->formal;
        $probation = $this->probation;
        //如果是离职员工
        if ($user['status'] === '离职') {
            return round(100 / $this->month_daily * ($formal + $probation), 5);
        }

        $professional_so = $user['professional_so'];

        $fixed = Welfare::where('professional_so', $professional_so)
            ->value('fixed');
        if (($formal + $probation) < 25) {
            $fixed = $fixed / $this->month_daily * ($formal + $probation);
        }
        return round($fixed, 5);
    }

    /**
     * 获取限定日期
     */
    public function getLimitDate()
    {
        $today_date = date('Y-m', time());
        $min_limit_date = date('Y-m-d 00:00:00', strtotime(date('Y-m-01', strtotime($today_date)) . ' -1 month'));
        $max_limit_date = date('Y-m-d 23:59:59', strtotime(date('Y-m-d', strtotime($min_limit_date)) . ' +1 month -1 day'));
        //当月天数
        $day_number = intval(date('d', strtotime($max_limit_date)));
        return ['min_limit_date' => $min_limit_date, 'max_limit_date' => $max_limit_date, 'day_number' => $day_number];
    }


}
