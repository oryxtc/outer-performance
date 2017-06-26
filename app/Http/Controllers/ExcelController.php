<?php

namespace App\Http\Controllers;

use App\Http\Method\UsersTemplate;
use Illuminate\Http\Request;

class ExcelController extends Controller
{

    public function exportUsersTemplate(UsersTemplate $export)
    {
        $head_list=UsersTemplate::HEAD_lIST;
        $head_list_value = array_values($head_list);
        //导出数据
        $export->sheet('员工信息表', function ($sheet) use ($head_list_value) {
            $sheet->setAutoSize(true);
            $sheet->setWidth('A',10);
            //填充头部
            $sheet->prependRow($head_list_value);
        });

        return $export->export('xlsx');

    }
    //
}
