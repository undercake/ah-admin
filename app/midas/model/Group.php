<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:25:47
 * @LastEditTime: 2023-03-22 14:08:19
 * @FilePath: /tp6/app/midas/model/Group.php
 * @Description: 
 */

namespace app\midas\model;

use think\Validate;

class Group extends Validate
{
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [
    'name'   => 'require',
    'rights' => 'require|array',
    'id'     => 'integer'
  ];

  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [
    'name.require'   => '角色名必填！',
    'rights.require' => '权限必选！',
    'rights.array'   => '权限格式不正确！',
    'id'             => 'id应为整数！',
  ];
}
