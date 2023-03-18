<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-03-18 16:59:50
 * @FilePath: /tp6/app/midas/controller/Group.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\controller\Common;
use think\facade\Db;
use think\facade\Request;

class Group extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::name('group')->page($page, 10)->select();
    $count = Db::name('group')->count();
    return $this->succ(['grp' => $grp, 'current_page' => $page, 'count' => $count, 'count_per_page' => 10]);
  }

  public function all()
  {
    $grp = Db::name('group')->select();
    return $this->succ(['grp' => $grp]);
  }

  public function rights()
  {
    $rs = Db::name('rights')->order('sort', 'ASC')->select();
    return $this->succ(['data' => $rs]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['msg' => 'bad id', 'id' => $id]);
    $rs = Db::name('group')->where('id', $id)->find();
    return count($rs) <= 0 ? $this->err(['msg' => '没有找到数据']) : $this->succ(['detail' => $rs]);
  }

  public function add()
  {
    return;
    $data = Request::put();
    // $rs = Db::name('operator')->where()->find();
    return $this->succ($data);
  }

  public function alter()
  {
    return;
    $data       = Request::post();
    $id         = $data['id'];
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_group = $data['user_group'];
    $user_name  = $data['user_name'];
    $rs = Db::name('operator')->where('id', (int)$id)->update(['full_name' => $full_name, 'mobile' => $mobile, 'user_group' => $user_group, 'user_name' => $user_name]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    return;
    $id = (int)$id;
    if ($id <= 0) return $this->err(['msg' => 'bad id']);
    // $rs = Db::name('operator')->where()->find();
    $data = Request::delete();
    return $this->succ($data);
  }
}
