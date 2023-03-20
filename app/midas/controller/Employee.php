<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-03-20 14:59:55
 * @FilePath: /tp6/app/midas/controller/Admin.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\controller\Common;
use think\facade\Db;
use think\facade\Request;

class Employee extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('operator')->field('id,full_name,user_group,user_name,mobile')->where('deleted', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function all()
  {
    $rs  = Db::name('operator')->order('password', 'ASC')->field('id,full_name,user_name,mobile')->where('deleted', 0)->select()->toArray();
    return $this->succ(['data' => $rs]);
  }

  public function deleted($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::name('groups')->field('name,id')->select();
    $sql = Db::name('operator')->field('id,full_name,user_group,user_name,mobile')->where('deleted', '>', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    foreach ($grp as $v) {
      $grp[$v['id']] = $v;
    }
    foreach ($rs as $key => $value) {
      $rs[$key]['group_name'] = $grp[$value['user_group']]['name'];
    }
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['msg' => 'bad id', 'id' => $id]);
    $rs = Db::name('operator')->field('id,full_name,user_group,user_name,mobile')->where(['id' => $id, 'deleted' => 0])->find();
    return count($rs) <= 0 ? $this->err(['msg' => '没有找到数据']) : $this->succ(['detail' => $rs]);
  }

  public function add()
  {
    $data       = Request::put();
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_group = $data['user_group'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];
    $rs = Db::name('operator')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_group' => $user_group, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    $data       = Request::post();
    $id         = $data['id'];
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_group = $data['user_group'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];
    $rs = Db::name('operator')->where('id', (int)$id)->update(['full_name' => $full_name, 'mobile' => $mobile, 'user_group' => $user_group, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function pass()
  {
    $data = Request::post();
    $id   = $data['id'];
    $pass = $data['pass'];
    $salt = md5(str_shuffle($pass . time()));
    $rs   = Db::name('operator')->where('id', (int)$id)->update(['password' => sha1($pass . $salt), 'salt' => $salt]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['msg' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('operator')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('operator')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function deep_del($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['msg' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('operator')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('operator')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function rec($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['msg' => 'bad id']);
    return $this->succ(['rs' => Db::name('operator')->where('id', $id)->update(['deleted' => 0])]);
  }
}
