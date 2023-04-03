<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-04-03 17:12:00
 * @FilePath: /ahadmin/app/midas/controller/Customer.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Customer as Cus;

class Customer extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('customer')
            ->order('last_modify', 'DESC')
            ->where('del', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    $addr_ids = [];
    foreach ($rs as $v) {
      $addr_ids[] = $v['id'];
    }
    $addr = Db::name('customer_addr')->where('customer_id', 'IN', implode(',', $addr_ids))->select();
    $serv = Db::name('customer_serv')->where('customer_id', 'IN', implode(',', $addr_ids))->order('end_time', 'DESC')->select();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10, 'addr' => $addr, 'services' => $serv]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::name('customer')->where(['id' => $id, 'del' => 0])->find();
    $contract = Db::name('customer_serv')->where('customer_id', $id)->select();
    $address = Db::name('customer_addr')->where('customer_id', $id)->select();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['detail' => $rs, 'contract' => $contract, 'address' => $address]);
  }

  public function addr(int $id)
  {
    if ($id <= 0) return $this->err(['message' => 'Bad id']);
    $rs = Db::name('customer_addr')->where('customer_id', $id)->select();
    return $this->succ(['data' => $rs]);
  }

  public function contract(int $id)
  {
    if ($id <= 0) return $this->err(['message' => 'Bad id']);
    $rs = Db::name('customer_serv')->where('customer_id', $id)->select();
    return $this->succ(['data' => $rs]);
  }

  public function deleted($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('customer')->where('del', '>', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function add()
  {
    $data      = Request::put();
    $full_name = $data['full_name'];
    $mobile    = $data['mobile'];
    $user_name = $data['user_name'];
    $email     = $data['email'];

    $emp = new Cus;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('customer')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
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

    $emp = new Cus;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('customer')->where('id', (int)$id)->update(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('customer')->where('id', $id)->update(['del' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('customer')->whereIn('id', $data['ids'])->update(['del' => time()])]);
    }
  }

  public function deep_del($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('customer')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('customer')->whereIn('id', $data['ids'])->update(['del' => time()])]);
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
    return $this->succ(['rs' => $db->update(['del' => 0])]);
  }
}
