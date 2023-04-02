<?php
/*
 * @Author: Undercake
 * @Date: 2023-04-02 02:13:07
 * @LastEditTime: 2023-04-02 02:37:01
 * @FilePath: /ahadmin/app/midas/model/My.php
 * @Description: 自己修改模型
 */
namespace app\midas\model;

use think\Validate;

class My extends Validate
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
      'email'      => 'email',
      'mobile'     => 'mobile'
    ];

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
    'email'              => '邮箱格式不正确',
    'mobile'             => '手机号格式不正确',
  ];
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}

