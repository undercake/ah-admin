<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-04-04 16:34:11
 * @FilePath: /ahadmin/app/midas/controller/Employee.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Employee as Emp;

class Employee extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('employee')->where('deleted', 0)->order('create_time', 'DESC');
    $rs  = $sql->page($page, 10)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::name('employee')->where(['id' => $id, 'deleted' => 0])->findOrEmpty();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['detail' => $rs]);
  }

  public function deleted($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('employee')->where('deleted', '>', 0);
    $rs  = $sql->page($page, 10)->select()->order('deleted', 'DESC')->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function add()
  {
    $data       = Request::put();
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];

    $emp = new Emp;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('employee')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    $data       = Request::post();
    $id         = $data['id'];
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];

    $emp = new Emp;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('employee')->where('id', (int)$id)->update(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('employee')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('employee')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function deep_del($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('employee')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('employee')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function rec($id = 0)
  {
    $id = (int)$id;
    $data = Request::post();
    if (isset($data['id']) || isset($data['ids'])) {
      $db = Db::name('services');
      $db = isset($data['id']) ? $db->where('id', $data['id']) : $db->whereIn('id', $data['ids']);
    } else return $this->err(['message' => 'Bad Request']);
    return $this->succ(['rs' => $db->update(['deleted' => 0])]);
  }
}
