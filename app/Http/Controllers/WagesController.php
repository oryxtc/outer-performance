<?php

namespace App\Http\Controllers;


use App\Attendance;
use App\User;

class WagesController extends Controller
{
    public $user                = null;
    public $job_number          = null;
    public $limit_date          = [];
    public $overtime_probation  = 0; //试用期加班天数
    public $overtime_formal     = 0; //正式加班天数
    public $sick_probation      = 0; //试用病假天数
    public $sick_formal         = 0; //正式病假天数
    public $sick                = 0;//病假天数
    public $maternity_probation = 0; //试用产假天数
    public $maternity_formal    = 0; //正式产假天数
    public $maternity           = 0; //产假天数
    public $probation           = 0; //试用期天数
    public $formal              = 0; //正式期天数

    public function __construct($user)
    {
        $this->user = $user;
        $this->job_number = $user['job_number'];
        $this->limit_date = $this->getLimitDate();

        $this->sick_probation = $this->getSickProbation();
        $this->sick_formal = $this->getSickFormal();

        $this->maternity_probation = $this->getMaternityProbation();
        $this->maternity_formal = $this->getMaternityFormal();


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

        return $save_data;
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
        $overtime_probation_total = round($overtime_probation_total);
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
        $overtime_formal_total = round($overtime_formal_total);
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
            ->whereDate('start_at', '<', $limit_date['max_limit_date'])
            ->whereDate('end_at', '>', $limit_date['min_limit_date'])
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
        return $total_day;
    }


    /**
     * 获取病假天数
     * @return float
     */
    public function getSick()
    {
        return ceil($this->sick_probation + $this->sick_formal);
    }


    /**
     * 获取病假天数
     * @return float
     */
    public function getMaternity()
    {
        return ceil($this->maternity_formal + $this->maternity_probation);
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
            if (strtotime($formal_at) < strtotime($limit_date['max_limit_date'])) {
                $probation_end_at = $formal_at;
            } else {
                $probation_end_at = $limit_date['max_limit_date'];
            }
        }
        //试用期总时长(天数)
        $probation_total_at = (strtotime($probation_end_at) - strtotime($probation_start_at)) / 3600 / 24;
        $probation_total_at = floor($probation_total_at - $this->sick_probation - $this->maternity_probation);
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
            if (strtotime($formal_at) < strtotime($limit_date['min_limit_date'])) {
                $formal_start_at = $limit_date['min_limit_date'];
            } else {
                $formal_start_at = $formal_at;
            }
        }
        //正式期期总时长(天数)
        $formal_total_at = (strtotime($formal_end_at) - strtotime($formal_start_at)) / 3600 / 24;
        $formal_total_at =  floor($formal_total_at - $this->sick_formal - $this->maternity_formal);
        $this->formal = $formal_total_at;
        return $formal_total_at;
    }

    /**
     * 获取限定日期
     */
    public function getLimitDate()
    {
        $today_date = date('Y-m', time());
        $min_limit_date = date('Y-m-d 00:00:00', strtotime(date('Y-m-01', strtotime($today_date)) . ' -1 month'));
        $max_limit_date = date('Y-m-d 23:59:59', strtotime(date('Y-m-d', strtotime($min_limit_date)) . ' +1 month -1 day'));
        return ['min_limit_date' => $min_limit_date, 'max_limit_date' => $max_limit_date];
    }


}
