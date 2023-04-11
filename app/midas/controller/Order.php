<?php
/*
 * @Author: Undercake
 * @Date: 2023-04-11 09:37:30
 * @LastEditTime: 2023-04-11 09:40:23
 * @FilePath: /ahadmin/app/midas/controller/Order.php
 * @Description: 派单相关
 */
namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Request;

class Order extends Common
{
  private function processClient()
  {
  }

  private function processAddress()
  {
  }
  private function processService()
  {
  }

  public function add()
  {
    $data = Request::post();
    return view();
  }
}
