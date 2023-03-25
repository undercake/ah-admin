<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-03-25 10:51:35
 * @FilePath: /tp6/app/midas/controller/Services.php
 * @Description: 服务编辑
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Service as Svs;

class Services extends Common
{
  public function list($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('services')->where('deleted', 0);
    $rs  = $sql->page($page, 10)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function detail($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    $rs = Db::name('services')->where(['id' => $id, 'deleted' => 0])->find();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['detail' => $rs]);
  }

  public function deleted($page = 1)
  {
    $page = (int)$page;
    if ($page <= 0) $page = 1;
    $sql = Db::name('services')->where('deleted', '>', 0);
    $rs  = $sql->page($page, 10)->select();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function category()
  {
    return $this->succ(['data' => Db::name('service_category')->select()]);
  }

  public function options($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    return $this->succ(['data' => Db::name('service_options')->where('service_id', $id)->select()]);
  }

  public function quick_edit()
  {
    $data   = Request::post();
    $status = (int)$data['status'];
    $rs     = Db::name('services');
    if (isset($data['id'])) {
      $id = (int)$data['id'];
      $rs = $rs->where('id', $id);
    } else if (isset($data['ids'])) {
      $tmp = [];
      foreach (explode(',', $data['ids']) as $k => $v) {
        $tmp[$k] = (int)$v;
      }
      $rs = $rs->whereIn('id', implode(',', $tmp));
    }
    $rs = $rs->update(['status' => $status]);
    return $this->succ(['rs' => $rs]);
  }

  public function add()
  {
    return $this->err(['code' => -1, 'message' => 'test', 'data' => Request::post()]);
    $data      = Request::put();
    $full_name = $data['full_name'];
    $mobile    = $data['mobile'];
    $user_name = $data['user_name'];
    $email     = $data['email'];

    $emp = new Svs;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('services')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    return $this->err(['code' => -1, 'message' => 'test', 'data' => Request::post()]);
    $data       = Request::post();
    $id         = $data['id'];
    $full_name  = $data['full_name'];
    $mobile     = $data['mobile'];
    $user_name  = $data['user_name'];
    $email      = $data['email'];

    $emp = new Svs;
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('services')->where('id', (int)$id)->update(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function delete($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('services')->where('id', $id)->update(['deleted' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('services')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function deep_del($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('services')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('services')->whereIn('id', $data['ids'])->update(['deleted' => time()])]);
    }
  }

  public function rec($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id']);
    return $this->succ(['rs' => Db::name('services')->where('id', $id)->update(['deleted' => 0])]);
  }
}
