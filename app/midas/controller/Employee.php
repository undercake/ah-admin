<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-04-17 17:20:57
 * @FilePath: /ahadmin/app/midas/controller/Employee.php
 * @Description: 员工相关
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Employee as Emp;

class Employee extends Common
{
  private function listCore($page = 1, int $item = 10, $where = [['deleted', '=', 0]], $order = ['create_time' => 'DESC'])
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('employee')->order($order);
    if ($where[0] == 'or') {
      unset($where[0]);
      $sql = $sql->whereOr($where[1]);
    } else
      $sql = $sql->where($where);
    $rs  = $sql->page($page, $item)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item]);
  }
  public function list(int $page = 1, int $item = 10)
  {
    return $this->listCore($page, $item);
  }

  public function deleted(int $page = 1, int $item = 10)
  {
    return  $this->listCore($page, $item, [['deleted', '>', 0]], ['deleted' => 'DESC']);
  }

  public function search(int $page = 1, int $item = 10)
  {
    $search = trim(Request::post()['search']);
    if ($search == '') return $this->err(['message' => 'Bad Request!']);
    $searchArr = [];
    $searchArr[] = ['name', 'LIKE', '%' . $search . '%'];
    if(preg_match('/([\x81-\xfe][\x40-\xfe])/' ,$search) === 0)
      foreach (['pym', 'pinyin', 'phone'] as $v) {
        $searchArr[] = [$v, 'LIKE', '%' . $search . '%'];
    }
    return $this->listCore($page, $item, ['or', $searchArr], ['deleted' => 'DESC']);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::name('employee')->where(['id' => $id, 'deleted' => 0])->findOrEmpty();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['detail' => $rs]);
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
