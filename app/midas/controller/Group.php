<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-03-20 13:05:57
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
    $grp = Db::name('groups')->page($page, 10)->select();
    $count = Db::name('groups')->count();
    return $this->succ(['grp' => $grp, 'current_page' => $page, 'count' => $count, 'count_per_page' => 10]);
  }

  public function all()
  {
    $grp = Db::name('groups')->select();
    return $this->succ(['grp' => $grp]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['msg' => 'bad id', 'id' => $id]);
    $rs = Db::name('groups')->where('id', $id)->find();
    return count($rs) <= 0 ? $this->err(['msg' => '没有找到数据']) : $this->succ(['detail' => $rs]);
  }

  public function add()
  {
    $data   = Request::put();
    $name   = $data['name'];
    $rights = $data['rights'];
    $rs     = Db::name('groups')->insert(['name' => $name, 'rights' => $rights]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    $data   = Request::post();
    $id     = $data['id'];
    $name   = $data['name'];
    $rights = $data['rights'];
    $rs     = Db::name('groups')->where('id', (int)$id)->update(['name' => $name, 'rights' => $rights]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['msg' => 'bad id']);
    $is = Request::isDelete();
    if (!$is) return $this->err(['msg' => 'Bad request!']);
    $rs = Db::name('groups')->where('id', $id)->update(['deleted' => time()]);
    return $this->succ(['rs' => $rs]);
  }

  public function rights()
  {
    $rs = Db::name('rights')->select();
    return $this->succ(['data' => $rs]);
  }

  public function rights_edit()
  {
    $rs = Db::name('rights')->select();
    return $this->succ(['data' => $rs]);
  }

  public function rights_add()
  {
    $rs = Db::name('rights')->select();
    return $this->succ(['data' => $rs]);
  }

  public function rights_del()
  {
    $rs = Db::name('rights')->select();
    return $this->succ(['data' => $rs]);
  }
}
