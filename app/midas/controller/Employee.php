<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-08-07 00:47:24
 * @FilePath: /ahadmin/app/midas/controller/Employee.php
 * @Description: 员工相关
 */

namespace app\midas\controller;

use app\midas\common\CRUD;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Employee as Emp;

class Employee extends CRUD
{

  protected function listCore(int $page = 1, int $item = 10, $where = [['DelFlag', '=', 0]], $order = ['CreateDate' => 'DESC', 'LastModiDate' => 'DESC'])
  {
    return $this->Selection('ah_data', 'Employee', $page, $item, $where, $order, null);
  //   $page = (int)$page;
  //   if ($page <= 0) $page = 1;
  //   $sql = Db::name('employee')->order($order);
  //   if ($where[0] == 'or') {
  //     unset($where[0]);
  //     $sql = $sql->whereOr($where[1]);
  //   } else
  //     $sql = $sql->where($where);
  //   $rs  = $sql->page($page, $item)->select()->toArray();
  //   return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item]);
  }
  public function list(int $page = 1, int $item = 10)
  {
    return $this->listCore($page, $item);
  }

  public function deleted(int $page = 1, int $item = 10)
  {
    return  $this->listCore($page, $item, [['DelFlag', '>', 0]]);
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
    return $this->listCore($page, $item, ['or', $searchArr]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::connect('ah_data')->table('Employee')->where(['id' => $id, 'DelFlag' => 0])->findOrEmpty();
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
    $update = [];

    foreach (['address',
    'avatar',
    'birth_date',
    'gender',
    'grade',
    'id_code',
    'intro',
    'name',
    'note',
    'origin',
    'phone',
    'pinyin',
    'pym',
    'work_date',
    'workee'] as $v) {
      $update[$v] = $data[$v];
    }

    $emp = new Emp;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('employee')->where('id', (int)$id)->update($update);
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

  public function rec(int $id = 0)
  {
    $data = Request::post();
    $rs = 0;
    if ($id > 0 || isset($data['ids'])) {
      $db = Db::name('employee');
      $db = $id > 0 ? $db->where('id', $id) : $db->whereIn('id', $data['ids']);
      $rs = $db->update(['deleted' => 0]);
    } else return $this->err(['message' => 'Bad Request']);
    return $this->succ(['rs' => $rs]);
  }
}
