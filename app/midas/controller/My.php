<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-30 17:09:23
 * @LastEditTime: 2023-03-30 17:18:45
 * @FilePath: /tp6/app/midas/controller/My.php
 * @Description: 修改登录数据
 */

namespace app\midas\controller;

use think\facade\Db;
use app\midas\common\Common;

class My extends Common
{
  public function get()
  {
    $data = Db::name('operator')->field('full_name,user_name,mobile,email')->where('id', $this->session_get('id'))->find();
    return $this->succ(['data' => $data]);
  }
  public function set()
  {
    return view();
  }
}
