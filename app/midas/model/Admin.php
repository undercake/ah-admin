<?php
/*
* @Author: Undercake
* @Date: 2023-03-18 11:01:12
 * @LastEditTime: 2023-10-04 07:48:14
 * @FilePath: /ahadmin/app/midas/model/Admin.php
* @Description: 管理员数据验证器
*/

namespace app\midas\model;

use app\common\CommonValidate;

class Admin extends CommonValidate
{
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [
    'full_name'  => 'require',
    'user_name'  => 'require|alphaNum',
    'user_group' => 'require|integer',
    'email'      => 'email',
    'id'         => 'integer',
    'mobile'     => 'mobile'
  ];

  //full_name, user_name, user_group, email, mobile
  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [
    'full_name'          => '姓名为必填',
    'user_name.require'  => '登录名不能为空',
    'user_name.alphaNum' => '登录名只能为字母和数字组合',
    'user_group.require' => '管理员角色必填',
    'user_group.integer' => '管理员角色只能为整数',
    'email'              => '邮箱格式不正确',
    'mobile'             => '手机号格式不正确'
  ];
}
