<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:21:30
 * @LastEditTime: 2023-10-09 04:00:52
 * @FilePath: /ahadmin/app/midas/model/Employee.php
 * @Description: employee 验证类
 */

namespace app\midas\model;

use app\common\CommonValidate;

class Employee extends CommonValidate
{
  
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [
    'FullName'     => 'require|length:2,14',
    'Sex'          => 'require|in:男,女',
    'Address'      => 'length:0,60',
    'Tel'          => 'mobile',
    'Birthday'     => 'length:0,10',
    'Workday'      => 'length:0,10',
    'BlameRecord'  => 'length:0,30',
    'Comment'      => 'length:0,250',
    'Department'   => 'length:0,60',
    'HomeTel'      => 'mobile',
    'IDCode'       => 'idCard',
    'ItemCode'     => 'length:0,30',
    'ItemLevel'    => 'length:0,10',
    'WarrantorTel' => 'mobile',
    'pym'          => 'require|alphaNum|length:2,10',
  ];
  /*
  protected $rule = [
    'name'       => 'require|length:1,14',
    'phone'      => 'mobile',
    'address'    => 'length:3,300',
    'intro'      => 'length:3,300',
    'gender'     => 'in:0,1',
    'id_code'    => 'idCard',
    'pinyin'     => 'require|alpha',
    'pym'        => 'require|alpha',
    'birth_date' => 'date',
    'work_date'  => 'date',
    'grade'      => 'between:0,9',
    'id'         => 'integer'
  ];*/
  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [
    'FullName.require' => '姓名不能为空',
    'FullName.length'  => '姓名长度不正确',
    'Address'          => '地址长度过长',
    'Tel'              => '电话格式不正确',
    'WarrantorTel'     => '担保人电话格式不正确',
    'HomeTel'          => '家庭电话格式不正确',
    'Birthday'         => '出生日期长度过长',
    'Workday'          => '参工日期长度过长',
    'BlameRecord'      => '过失记录长度过长',
    'Comment'          => '说明长度过长',
    'pym.require'      => '拼音码必填',
    'pym.alphaNum'     => '拼音码不能包含其他字符',
    'pym.length'       => '拼音码过长',
    'IDCode'           => '身份证格式不正确',
    'ItemCode'         => '编号过长',
    'ItemLevel'        => '员工等级过长'
  ];
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}
