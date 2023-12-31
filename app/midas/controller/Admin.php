<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-10-04 09:26:57
 * @FilePath: /ahadmin/app/midas/controller/Admin.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;
use app\midas\model\Admin as Am;

class Admin extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::connect('ah_admin')->name('groups')->field('name,id')->select();
    $sql = Db::connect('ah_admin')->name('operator')->field('id,full_name,user_group,user_name,mobile')->where('deleted', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    foreach ($grp as $v) {
      $grp[$v['id']] = $v;
    }
    foreach ($rs as $key => $value) {
      $rs[$key]['group_name'] = $grp[$value['user_group']]['name'];
    }
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function all()
  {
    $rs  = Db::connect('ah_admin')->name('operator')->field('id,full_name,user_name,mobile')->where('deleted', 0)->select()->toArray();
    $grp = Db::connect('ah_admin')->name('groups')->field('id,name')->select()->toArray();
    return $this->succ(['data' => $rs, 'group' => $grp]);
  }

  public function deleted($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::connect('ah_admin')->name('groups')->field('name,id')->select();
    $sql = Db::connect('ah_admin')->name('operator')->field('id,full_name,user_group,user_name,mobile,deleted')->where('deleted', '>', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    foreach ($grp as $v) {
      $grp[$v['id']] = $v;
    }
    foreach ($rs as $key => $value) {
      $rs[$key]['group_name'] = $grp[$value['user_group']]['name'];
      $rs[$key]['deleted'] = date('Y-m-d H:i:s', $rs[$key]['deleted']);
    }
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function detail(int $id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::connect('ah_admin')->name('operator')->field('id,full_name,user_group,user_name,mobile')->where(['id' => $id, 'deleted' => 0])->find();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['data' => $rs]);
  }
  public function add()
  {
    $data       = Request::put();
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_group = $data['user_group'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];
    $rs         = '';

    // 验证数据
    $am = new Am();
    $res = $am->check($data);
    if (!$res) return $this->err(['message' => $am->getError()]);

    try {
      $rs = Db::connect('ah_admin')->table('operator')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_group' => $user_group, 'user_name' => $user_name, 'email' => $email]);
    } catch (\Throwable $th) {
      $msg = $th->getMessage();
      $pos = strpos('Duplicate', $msg) >= 0;
      return $this->err(['message' => $pos ? '登录名与现有数据重复！' : '未知错误']);
    }
    return $this->succ(['rs' => $rs]);
  }

  public function alter(int $id)
  {
    $data       = Request::post();
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_group = $data['user_group'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];

    // 验证数据
    $am = new Am();
    $res = $am->check($data);
    if (!$res) return $this->err(['message' => $am->getError()]);

    $rs = Db::connect('ah_admin')->table('operator')->where('id', $id)->update([
      'full_name'  => $full_name,
      'mobile'     => $mobile,
      'user_group' => $user_group,
      'user_name'  => $user_name,
      'email'      => $email
    ]);
    return $this->succ(['rs' => $rs]);
  }

  public function pass()
  {
    $data = Request::post();
    $id   = $data['id'];
    $pass = $data['pass'];
    if (strlen($pass) !== 32) return $this->err(['message' => '密码格式不正确']);
    $salt = md5(str_shuffle($pass . time()));
    $rs   = Db::connect('ah_admin')->name('operator')->where('id', (int)$id)->update(['password' => sha1($pass . $salt), 'salt' => $salt]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    if (Request::isDelete()) return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();
      return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->whereIn('id', implode(',', $data['ids']))->update(['deleted' => time()])]);
    }
  }

  public function deep_del($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();
      // return json($data);
      return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->whereIn('id', implode(',', $data['ids']))->delete()]);
    }
  }

  public function rec($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    if ($id > 0) return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->where('id', $id)->update(['deleted' => 0]), 'method' => Request::isPost()]);
    if (Request::isPost()) {
      $data = Request::post();
      return $this->succ(['rs' => Db::connect('ah_admin')->name('operator')->whereIn('id', implode(',', $data['ids']))->update(['deleted' => 0]), 'data' => [$data, implode(',', $data['ids'])]]);
    }
  }
}
