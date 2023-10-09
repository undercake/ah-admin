<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-22 11:21:30
 * @LastEditTime: 2023-10-09 03:43:05
 * @FilePath: /ahadmin/app/midas/model/Customer.php
 * @Description: employee 验证类
 */

namespace app\midas\model;

use app\common\CommonValidate;

class Customer extends CommonValidate
{
  /**
   * 定义验证规则
   * 格式：'字段名'=>['规则1','规则2'...]
   *
   * @var array
   */
  protected $rule = [
    'FullName'          => 'require|chsDash|max:70',
    'Tel1'              => 'require|telOrMobile',
    'Tel2'              => 'telOrMobile',
    'Tel3'              => 'telOrMobile',
    'Address'           => 'require|max:100',
    'fRegion'           => 'max:20',
    'F1'                => 'require|in:0,1,2',
    'HouseArea'         => 'integer',
    'NormalServiceTime' => 'max:20',
    'SpecialNeed'       => 'max:100',
  ];
  /**
   * 定义错误信息
   * 格式：'字段名.规则名'=>'错误信息'
   *
   * @var array
   */
  protected $message = [
    'FullName.require'      => '姓名不能为空',
    'FullName.chsDash'      => '姓名只能为汉字、字母、数字和下划线_及破折号-',
    'FullName.max'          => '姓名长度过长',
    'Tel1.require'          => '电话1不能为空',
    'Tel1.telOrMobile'      => '电话1格式不正确',
    'Tel2.telOrMobile'      => '电话2格式不正确',
    'Tel3.telOrMobile'      => '电话3格式不正确',
    'Address.require'       => '地址不能为空',
    'Address.max'           => '地址长度过长',
    'fRegion.max'           => '区域长度过长',
    'F1.require'            => '重要程度不能为空',
    'F1.in'                 => '重要程度格式不正确',
    'HouseArea.integer'     => '房屋面积必须为整数',
    'NormalServiceTime.max' => '服务时间长度过长',
    'SpecialNeed.max'       => '特殊需求长度过长',
  ];

  public function __construct(string $type = '')
  {
    $type === 'charge' ? $this->charge() : '';
    parent::__construct();
  }

  private function charge() {
    $this->rule = [
    'TotalMoney' => 'float',
    'TotalCount' => 'integer',
    'BeginDate'  => 'date',
    'UserType'   => 'require|in:0,1,2,3,4,5,6,7',
    'EndDate'    => 'date'
    ];
    $this->message = [
    'TotalMoney'       => '账户金额必须为数字',
    'TotalCount'       => '剩余次数必须为整数',
    'BeginDate'        => '开始时间格式不正确',
    'UserType.require' => '用户类型不能为空',
    'UserType.in'      => '用户类型格式不正确',
    'EndDate'          => '到期时间格式不正确'
    ];
  }

  // public function __construct(string $type = '')
  // {
  //   switch ($type) {
  //     case 'address':
  //       $this->rule = [
  //         'id'          => 'require|integer',
  //         'address'     => 'require|length:1,400',
  //         'area'        => 'float',
  //         'customer_id' => 'require|integer'
  //       ];
  //       $this->message = [
  //         'id.require'          => 'id 字段不能为空',
  //         'id.integer'          => 'id 必须为整数',
  //         'address.require'     => '地址不能为空',
  //         'address.length'      => '地址字段过长',
  //         'area'                => '面积只能为数字',
  //         'customer_id.require' => 'customer_id 字段不能为空',
  //         'customer_id.integer' => 'customer_id 必须为整数'
  //       ];
  //       break;

  //     case 'contract':
  //       $this->rule = [
  //         'id'            => 'require|integer',
  //         'contract_code' => 'length:0,30',
  //         'customer_id'   => 'require|integer',
  //         'remark'        => 'length:0,280',
  //         'end_time'      => 'date',
  //         'start_time'    => 'date'
  //       ];
  //       $this->message = [
  //         'id.require'          => '合同id为必填',
  //         'id.integer'          => 'id必须为整数',
  //         'contract_code'       => '合同编号长度过长',
  //         'start_time'          => '开始时间格式不正确',
  //         'end_time'            => '到期时间格式不正确',
  //         'customer_id.require' => 'customer_id 字段不能为空',
  //         'customer_id.integer' => 'customer_id 必须为整数'
  //       ];
  //       break;
  //       default:
  //         $this->rule = [
  //           'id'          => 'require|integer',
  //           'name'        => 'require|chsDash',
  //           'mobile'      => 'require',
  //           'black'       => 'in:0,1',
  //           'remark'      => 'length:0,280',
  //           'pinyin'      => 'alphaNum',
  //           'pym'         => 'alphaNum',
  //           'total_money' => 'float',
  //           'type'        => 'between:0,2',
  //           'total_count' => 'integer',
  //         ];
  //         $this->message = [
  //           'id.require'   => '用户id为必填',
  //           'id.integer'   => 'id必须为整数',
  //           'name.require' => '姓名不能为空',
  //           'name.chsDash' => '姓名只能为汉字',
  //           'mobile'       => '手机号必填',
  //           'black'        => '是否拉黑字段格式不正确',
  //           'remark'       => '备注长度过长',
  //           'pinyin'       => '拼音必须为字母',
  //           'pym'          => '拼音码必须为字母',
  //           'type'         => '客户类型只能为0-2',
  //           'total_money'  => '账户金额必须为数字',
  //           'total_count'  => '剩余次数必须为整数',
  //         ];
  //   }
  //   parent::__construct();
  // }
  /**
   * 定义验证场景
   * 格式：'场景名'=>['规则1','规则2',...]
   *
   * @var array
   */
  protected $scene = [];
}
