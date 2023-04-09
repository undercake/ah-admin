<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:21:30
 * @LastEditTime: 2023-04-09 16:21:17
 * @FilePath: /ahadmin/app/midas/model/Customer.php
 * @Description: employee 验证类
 */

namespace app\midas\model;

use think\Validate;

class Customer extends Validate
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
  public function __construct(string $type = '')
  {
    switch ($type) {
      case 'address':
        $this->rule = [
          'id'          => 'require|integer',
          'address'     => 'require|length:1,400',
          'area'        => 'float',
          'customer_id' => 'require|integer'
        ];
        $this->message = [
          'id.require'          => 'id 字段不能为空',
          'id.integer'          => 'id 必须为整数',
          'address.require'     => '地址不能为空',
          'address.length'      => '地址字段过长',
          'area'                => '面积只能为数字',
          'customer_id.require' => 'customer_id 字段不能为空',
          'customer_id.integer' => 'customer_id 必须为整数'
        ];
        break;

      case 'contract':
        $this->rule = [
          'id'            => 'require|integer',
          'contract_code' => 'length:0,30',
          'customer_id'   => 'require|integer',
          'remark'        => 'length:0,280',
          'end_time'      => 'date',
          'start_time'    => 'date'
        ];
        $this->message = [
          'id.require'          => '合同id为必填',
          'id.integer'          => 'id必须为整数',
          'contract_code'       => '合同编号长度过长',
          'start_time'          => '开始时间格式不正确',
          'end_time'            => '到期时间格式不正确',
          'customer_id.require' => 'customer_id 字段不能为空',
          'customer_id.integer' => 'customer_id 必须为整数'
        ];
        break;
        default:
          $this->rule = [
            'id'          => 'require|integer',
            'name'        => 'require|chsDash',
            'mobile'      => 'require',
            'black'       => 'in:0,1',
            'remark'      => 'length:0,280',
            'pinyin'      => 'alphaNum',
            'pym'         => 'alphaNum',
            'total_money' => 'float',
            'total_count' => 'integer',
          ];
          $this->message = [
            'id.require'   => '用户id为必填',
            'id.integer'   => 'id必须为整数',
            'name.require' => '姓名不能为空',
            'name.chsDash' => '姓名只能为汉字',
            'mobile'       => '手机号必填',
            'black'        => '是否拉黑字段格式不正确',
            'remark'       => '备注长度过长',
            'pinyin'       => '拼音必须为字母',
            'pym'          => '拼音码必须为字母',
            'total_money'  => '账户金额必须为数字',
            'total_count'  => '剩余次数必须为整数',
          ];
    }
    parent::__construct();
  }
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}
