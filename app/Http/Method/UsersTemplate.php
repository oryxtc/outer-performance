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
    const HEAD_lIST=[
        'belong_company'=>'所属公司',
        'job_number'=>'工号',
        'name'=>'姓名',
        'status'=>'状态',
        'property'=>'性质',
        'contract_at'=>'合同到期时间',
        'entry_at'=>'入职时间',
        'formal_at'=>'转正时间',
        'leave_at'=>'离职时间',
        'email'=>'企业邮箱',
        'trial_pay'=>'试用薪酬',
        'formal_pay'=>'转正薪酬',
        'bank_number'=>'银行卡号',
        'bank'=>'银行',
        'post_name'=>'岗位名称',
        'part_name'=>'部门名称',
        'part_first_name'=>'部门一级',
        'part_second_name'=>'部门二级',
        'part_third_name'=>'部门三级',
        'report_relation'=>'汇报关系',
        'tutor_name'=>'导师名称',
        'hrbp'=>'HRBP',
        'area'=>'区域',
        'professional_rank'=>'专业职级',
        'professional_title'=>'专业称谓',
        'professional_so'=>'专业子等',
        'management_rank'=>'管理级别',
        'management_title'=>'管理称谓',
        'graduate_school'=>'毕业学校',
        'graduate_at'=>'毕业时间',
        'education'=>'学历',
        'sex'=>'性别',
        'nation'=>'名族',
        'born_at'=>'出生日期',
        'address'=>'住址',
        'id_card'=>'身份证号',
        'issuing_authority'=>'签发机关',
        'effective_start_at'=>'有效起始时间',
        'effective_end_at'=>'有效结束时间',
        'phone'=>'手机号',
        'wechat'=>'微信号',
        'personal_qq'=>'个人QQ',
        'company_qq'=>'企业QQ',
        'father'=>'父亲',
        'father_phone'=>'父亲手机号',
        'father_at'=>'父亲生日',
        'mother'=>'母亲',
        'mother_phone'=>'母亲手机号',
        'mother_at'=>'母亲生日',
        'parents_address'=>'父母住址',
    ];

    /**
     * Get file
     * @return string
     */
    public function getFilename()
    {
        return '员工信息表';
    }
}