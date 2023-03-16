<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-03-16 14:53:14
 * @FilePath: /tp6/app/midas/controller/Admin.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\controller\Common;
use think\facade\Db;

class Admin extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::name('group')->field('name,id')->select();
    $sql = Db::name('operator')->field('id,full_name,user_group,user_name,mobile');
    $rs  = $sql->page($page, 10)->select()->toArray();
    foreach ($grp as $v) {
      $grp[$v['id']] = $v;
    }
    foreach ($rs as $key => $value) {
      $rs[$key]['group_name'] = $grp[$value['user_group']]['name'];
    }
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function group($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::name('group')->page($page, 10)->select();
    $count = Db::name('group')->count();
    return $this->succ(['grp' => $grp, 'current_page' => $page, 'count' => $count, 'count_per_page' => 10]);
  }
  public function AllGroup()
  {;
    $grp = Db::name('group')->select();
    return $this->succ(['grp' => $grp]);
  }
}
