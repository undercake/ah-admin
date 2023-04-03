<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:21:30
 * @LastEditTime: 2023-03-30 10:54:11
 * @FilePath: /tp6/app/midas/model/Service.php
 * @Description: employee 验证类
 */

namespace app\midas\model;

use think\Validate;

class Service extends Validate
{
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [];
  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [];

  public function __construct($key = '')
  {
    switch ($key) {
      case 'cat':
        break;
      case 'opt':
        break;
      default:
        $this->rule = [
          'id'         => 'integer',
          'name'       => 'require|length:1,30',
          'avatar'     => 'require',
          'class_id'   => 'require|integer',
          'details'    => 'require',
          'intro'      => 'require|length:3,300',
          'banner'     => 'array',
          'prompt'     => 'length:1,300',
          'status'     => 'in:0,1',
        ];
        $this->message = [
          'name.require'     => '“服务名称”不能为空',
          'name.length'      => '“服务名称”长度过长',
          'id'               => 'id格式错误',
          'avatar'           => '“封面”必须上传',
          'class_id.require' => '“服务类目”必须选择',
          'class_id.length'  => '“服务类目”格式不正确',
          'intro.require'    => '“服务简介”必须填写',
          'intro.length'     => '“服务简介”长度过长',
          'banner'           => '“轮播图”格式不正确',
          'details'          => '“详情”不能为空',
          'prompt'           => '“温馨提示”长度过长'
        ];
        break;
    }
  }
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}
