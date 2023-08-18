<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:21:30
 * @LastEditTime: 2023-04-20 17:11:19
 * @FilePath: /ahadmin/app/midas/model/Employee.php
 * @Description: employee 验证类
 */

namespace app\midas\model;

use think\Validate;

class Employee extends Validate
{
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  // name,phone,address,intro,gender,id_code,pinyin,pym,birth_date,work_date,grade,id
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
  ];
  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [
    'name.require'   => '姓名不能为空',
    'name.length'    => '姓名长度不合理',
    'phone'          => '手机号格式不对',
    'address'        => '地址长度不应小于3个字符，且不应长于300字符',
    'intro'          => '简介长度不应小于3个字符，且不应长于300字符',
    'gender'         => '性别选项不正确',
    'id_code'        => '身份证格式不正确',
    'pinyin.require' => '拼音为必填',
    'pinyin.alpha'   => '拼音为不能包含其他字符',
    'pym.require'    => '拼音码必填',
    'pym.alpha'      => '拼音码不能包含其他字符',
    'birth_date'     => '出生日期格式不正确',
    'work_date'      => '入职日期格式不正确',
    'grade'          => '学历选项不正确',
    'id'             => 'id应为数字'
  ];
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}
