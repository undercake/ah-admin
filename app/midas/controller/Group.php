<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-10-09 08:22:36
 * @FilePath: /ahadmin/app/midas/controller/Group.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Group as Grp;

class Group extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $grp = Db::connect('ah_admin')->name('groups')->page($page, 10)->select();
    $count = Db::connect('ah_admin')->name('groups')->count();
    return $this->succ(['data' => $grp, 'current_page' => $page, 'count' => $count, 'count_per_page' => 10]);
  }

  public function all()
  {
    $grp = Db::connect('ah_admin')->name('groups')->select();
    return $this->succ(['data' => $grp]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::connect('ah_admin')->name('groups')->where('id', $id)->find();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['data' => $rs]);
  }

  public function add()
  {
    $data   = Request::put();
    $name   = $data['name'];
    $rights = $data['rights'];
    // 验证数据
    $grp = new Grp();
    $rs = $grp->check($data);
    if (!$rs) return $this->err(['message' => $grp->getError()]);

    $rs     = Db::connect('ah_admin')->name('groups')->insert(['name' => $name, 'rights' => $rights]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    $data   = Request::post();
    $id     = $data['id'];
    $name   = $data['name'];
    $rights = $data['rights'];
    // 验证数据
    $grp = new Grp();
    $rs = $grp->check($data);
    if (!$rs) return $this->err(['message' => $grp->getError()]);

    $rs     = Db::connect('ah_admin')->name('groups')->where('id', (int)$id)->update(['name' => $name, 'rights' => $rights]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    if (Request::isDelete()) return $this->succ(['rs' => Db::connect('ah_admin')->name('groups')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) return $this->succ(['rs' => Db::connect('ah_admin')->name('groups')->whereIn('id', implode(',', Request::post(['ids'])))->update(['deleted' => time()])]);
  }

  public function rights()
  {
    $rs = Db::connect('ah_admin')->name('rights')->where([['type', '<>', 4]])->select();
    return $this->succ(['data' => $rs]);
  }

  // 以下方法暂时不可用
  public function rights_edit()
  {
    return;
    $rs = Db::connect('ah_admin')->name('rights')->select();
    return $this->succ(['data' => $rs]);
  }

  public function rights_add()
  {
    return;
    $rs = Db::connect('ah_admin')->name('rights')->select();
    return $this->succ(['data' => $rs]);
  }

  public function rights_del($id = 0)
  {
    return;
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    if (Request::isDelete()) return $this->succ(['rs' => Db::connect('ah_admin')->name('rights')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) return $this->succ(['rs' => Db::connect('ah_admin')->name('rights')->whereIn('id', implode(',', Request::post(['ids'])))->update(['deleted' => time()])]);
  }
}
