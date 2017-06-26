<?php

namespace App\Http\Method;
use Maatwebsite\Excel\Files\NewExcelFile;

/**
 * Created by PhpStorm.
 * User: oryxt
 * Date: 2017/6/26
 * Time: 10:48
 */
class UsersTemplate extends NewExcelFile
{

    /**
     * Get file
     * @return string
     */
    public function getFilename()
    {
        return '员工信息表';
    }
}