<?php
/*
* @Author: Undercake
* @Date: 2023-03-18 11:01:12
 * @LastEditTime: 2023-03-18 11:02:18
 * @FilePath: /tp6/app/midas/model/Admin.php
* @Description: 管理员数据验证器
*/

namespace app\midas\validate;

use think\Validate;

class Admin extends Validate
{
  protected $rule = [
    'name' => 'require|max:25',
    'email' => 'email',
  ];
}
