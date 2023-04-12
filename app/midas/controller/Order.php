<?php
/*
 * @Author: Undercake
 * @Date: 2023-04-11 09:37:30
 * @LastEditTime: 2023-04-12 17:28:54
 * @FilePath: /ahadmin/app/midas/controller/Order.php
 * @Description: 派单相关
 */
namespace app\midas\controller;

use think\facade\Db;
use think\facade\Request;
use app\midas\common\Common;

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
    return json($data);
  }

  public function get_remain()
  {
    return $this->succ(['data' => Db::name('operator')->where('id', $this->session_get('id'))->field('emp_limit_remain')->find()['emp_limit_remain']]);
  }

  public function get_emp()
  {
    $query_data = Request::post();
    foreach (['age' => 'between', 'gender' => '=', 'name' => 'LIKE', 'pym' => 'LIKE', 'pinyin' => 'LIKE'] as $k => $v) {
      if (isset($query_data[$k])) {
        $where[] = [$k, $v, $query_data[$k]];
      }
    }
  }
}
