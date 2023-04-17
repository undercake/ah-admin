<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-04-15 14:52:10
 * @FilePath: /ahadmin/app/midas/controller/Services.php
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

  public function quick_change_class(int $id = 0)
  {
    $class_id = (int)Request::post()['class_id'];
    if ($id < 1 || $class_id < 1) {
      return $this->err(['message' => 'Bad id']);
    }
    return $this->succ(['rs' => Db::name('services')->where('id', $id)->update(['class_id' => $class_id])]);
  }

  public function add()
  {
    return $this->err(['code' => -1, 'message' => 'test', 'data' => Request::post()]);
    $data      = Request::put();
    $full_name = $data['full_name'];
    $mobile    = $data['mobile'];
    $user_name = $data['user_name'];
    $email     = $data['email'];

    $emp = new Svs();
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $rs = Db::name('services')->insert(['full_name' => $full_name, 'mobile' => $mobile, 'user_name' => $user_name, 'email' => $email]);
    return $this->succ(['rs' => $rs]);
  }

  public function alter()
  {
    $emp = new Svs();
    $data = Request::post();
    $data['banner'] = explode(',', $data['banner']);
    $rs = $emp->check($data);
    if (!$rs) return $this->err(['message' => $emp->getError()]);
    $tmpArr = ['id', 'name', 'avatar', 'class_id', 'details', 'intro', 'banner', 'prompt', 'status'];
    $insert = [];
    foreach ($tmpArr as $v) {
      $insert[$v] = $data[$v];
    }

    $rs = Db::name('services')->where('id', (int)$data['id'])->update($insert);
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

  public function deep_del(int $id = 0)
  {
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
    $data = Request::post();
    if (isset($data['id']) || isset($data['ids'])) {
      $db = Db::name('services');
      $db = isset($data['id']) ? $db->where('id', $data['id']) : $db->whereIn('id', $data['ids']);
    } else return $this->err(['message' => 'Bad Request']);
    return $this->succ(['rs' => $db->update(['deleted' => 0])]);
  }

  public function options($id = 0)
  {
    $id = (int)$id;
    if ($id < 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    if ($id == 0) $data = Db::name('service_options')->select();
    else $data = Db::name('service_options')->where([['service_id', '=', $id], ['deleted', '=', 0]])->select();
    return $this->succ(['data' => $data]);
  }
  public function opt_del($id = 0)
  {
    $id = (int)$id;
    if ($id <= 0 || !Request::isDelete()) return $this->err(['message' => 'bad request!']);
    return $this->succ(['data' => Db::name('service_options')->where('id', $id)->delete()]);
  }
  public function opt_add()
  {
    $data = Request::put();
    // 添加 id 为服务id  其余均为服务项目 ID
    $id = (int)$data['id'];
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    return $this->succ(['data' => Db::name('service_options')->insert()]);
  }
  public function opt_edit()
  {
    $data = Request::put();
    $id = (int)$data['id'];
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    return $this->succ(['data' => Db::name('service_options')->where([['service_id', '=', $id], ['deleted', '=', 0]])->select()]);
  }

  public function category(int $page = 1)
  {
    if ($page < 0) return $this->err(['message' => 'bad page']);
    $sql = Db::name('service_category');
    $data = $sql->page($page, 10)->select();
    return $this->succ(['data' => $data, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => 10]);
  }

  public function cat_detail($id)
  {
    $id = (int)$id;
    if ($id <= 0) return $this->err(['message' => 'bad request!']);
    return $this->succ(['data' => Db::name('service_category')->where(['id', $id])->find()]);
  }

  public function cat_quick_edit()
  {
    $data   = Request::post();
    $status = (int)$data['status'];
    $rs     = Db::name('service_category');
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

  public function cat_del($id)
  {
    $id = (int)$id;
    if ($id <= 0 || !Request::isDelete()) return $this->err(['message' => 'bad request!']);
    return $this->succ(['data' => Db::name('service_category')->where('id', $id)->delete()]);
  }

  public function cat_add()
  {
    $data = Request::post();
    return $this->succ(['data' => Db::name('service_options')->insert($data)]);
  }

  public function cat_name()
  {
    $data = Request::post();
    $id = $data['id'];
    if ($id <= 0) return $this->err(['message' => 'bad request!']);
    return $this->succ(['data' => Db::name('service_category')->where(['id' => $id])->update(['name' => $data['name']])]);
  }
}
