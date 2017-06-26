<?php

namespace App\Http\Controllers;

use App\Http\Method\UsersTemplate;
use Illuminate\Http\Request;

class ExcelController extends Controller
{

    public function exportUsersTemplate(UsersTemplate $export)
    {

        $data = \DB::table('users')
            ->select('belong_company', 'job_number', 'name', 'status', 'contract_at', 'entry_at', 'formal_at', 'leave_at')
            ->get();
        $data=$this->stdClassToArray($data);
        $export->sheet('员工信息表', function ($sheet) use ($data) {
            $sheet->fromArray($data);
        });

        return $export->export('xls');

    }
    //
}
