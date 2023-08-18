<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-06-01 09:26:53
 * @FilePath: /ahadmin/app/midas/controller/Customer.php
 * @Description: 客户相关
 */

namespace app\midas\controller;

use app\midas\common\CRUD;
use think\facade\Db;
use think\facade\Request;

use app\midas\model\Customer as Cus;

class Customer extends CRUD
{

  private function listCore($page, $item = 10, $where = [['DelFlag', '=', 0]], $order = ['CreateDate' => 'DESC'], $callback = null)
  {
    return $this->Selection('ah_data' ,'ClientInfo', $page, $item, $where, $order, $callback);
  }

  public function list(int $page = 1, int $item = 10)
  {
    return $this->listCore($page, $item, [['DelFlag', '=', 0]], ['LastModiDate' => 'DESC', 'CreateDate' => 'DESC']);
  }

  public function search(int $page = 1, int $type = -1,int $item = 10)
  {
    $data = Request::post();
    $where = [];
    if ($type > -1) {
      $rs = Db::name('customer_serv')->where('type', $type)->field('customer_id')->order('end_time', 'DESC')->select()->toArray();
      if (count ($rs) > 0 ) {
        $ids = [];
        foreach ($rs as $v) {
          $ids[] = $v['customer_id'];
        }
        $where[] = ['id', 'IN', implode(',', $ids)];
      }
    }
    return $this->listCore(
      $page,
      $item,
      [
        ...$where,
        ['mobile', 'LIKE', '%' . $data['mobile'] . '%'],
        ['DelFlag', '=', 0]
      ]
    );
  }

  public function near(int $page, int $item = 10)
  {
    if ($page <= 0) $page = 1;
    $serv = Db::name('customer_serv')
      ->where('type', '<>', '0')
      ->whereTime('end_time', 'BETWEEN', [date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+30 day'))])
      ->select()
      ->toArray();
    $ids = [];
    foreach ($serv as $v) {
      $ids[] = $v['customer_id'];
    }
    return $this->listCore($page, $item, [['id', 'IN', implode(',', $ids)]], ['LastModiDate' => 'DESC'], function ($rs, $addr, $contr) {
      $tmp_c = $contr;
      foreach ($tmp_c as $k => $v) {
        foreach ($v as $key => $val) {
          if (strtotime($val['end_time']) < time()) unset($tmp_c[$k][$key]);
          $tmp_c[$k] = [...$tmp_c[$k]];
        }
      }
      return [$rs, $addr, $tmp_c];
    });
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

  public function deleted(int $page = 1, int $item = 10)
  {
    if ($page <= 0) $page = 1;
    if ($item <= 2) $item = 10;
    return $this->listCore($page, $item, [['DelFlag', '>', 0]], ['LastModiDate' => 'DESC', 'CreateDate' => 'DESC']);
    $sql = Db::name('customer')->where('del', '>', 0);
    $rs  = $sql->page($page, $item)->select()->toArray();
    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item]);
  }

  private function insCore()
  {
    $is_post = Request::isPost();
    $post = $is_post ? Request::post() : Request::put();
    $emp = new Cus;
    $rs = $emp->check($post);
    if (!$rs) return $this->err(['message' => $emp->getError(), $emp]);

    $sideArr = ['address' => [], 'contract' => []];
    foreach ($sideArr as $k => $v) {
      $m = $this->checkData($k, $post[$k]);
      if ($m !== true) return $this->err(['message' => $m]);
      $sideArr[$k] = $post[$k];
    }
    $sideArr['contract_del'] = $post['contract_del'];
    $sideArr['address_del'] = $post['address_del'];
    $id = (int)$post['id'];
    $data = [];
    foreach (['name', 'mobile', 'black', 'pym', 'pinyin', 'remark', 'total_money', 'total_count', 'type'] as $v) {
      $data[$v] = $post[$v];
    }
    $db = Db::name('customer');
    $rs = $is_post ? $db->insertGetId($data) : $db->where('id', $id)->update($data);
    $side = $this->handleInsertOrUpdate($sideArr, $is_post ? $rs : $id);
    return $this->succ(['rs' => $rs, $side]);
  }

  private function checkData(string $type, array $data)
  {
    $emp = new Cus($type);
    $msg = true;
    foreach ($data as $v) {
      $rs = $emp->check($v);
      if (!$rs) return $emp->getError();
    }
    return $msg;
  }

  private function handleInsertOrUpdate(array $data, $altId)
  {
    $db_name = [
      'address' => 'customer_addr',
      'contract' => 'customer_serv',
    ];

    $new_data = ['customer_addr' => ['update' => [], 'insert' => []], 'customer_serv' => ['update' => [], 'insert' => []]];
    $keys = [
      'address' => [
        'address',
        'area',
        'customer_id',
        'id'
      ],
      'contract' => [
        'contract_code',
        'contract_path',
        'CreateDate',
        'customer_id',
        'end_time',
        'id',
        'remark',
        'start_time',
        'type',
      ],
    ];;
    foreach ($data as $k => $v) {
      foreach ($v as $val) {
        $value = [];
        foreach ($keys[$k] as $key) {
          if ($key == 'id' && $val[$key] != 0 || $key != 'id')
            $value[$key] = $val[$key];
        }
        $new_data[$db_name[$k]][$val['id'] == 0 ? 'insert' : 'update'] = [...$value, 'customer_id' => $altId];
      }
    }
    $rtn = [
      Db::name('customer_addr')->data($new_data['customer_addr']['update'])->pk('id')->update(),
      Db::name('customer_addr')->insertAll($new_data['customer_addr']['insert']),
      Db::name('customer_serv')->data($new_data['customer_serv']['update'])->pk('id')->update(),
      Db::name('customer_serv')->insertAll($new_data['customer_serv']['insert'])
    ];
    trim(implode(',',$data['contract_del'])) == '' ? '' : ($rtn[] = Db::name('customer_addr')->where('id', 'IN', trim(implode(',',$data['contract_del']))));
    trim(implode(',',$data['address_del'])) == '' ? '' : ($rtn[] = Db::name('customer_addr')->where('id', 'IN', trim(implode(',',$data['address_del']))));
    return $rtn;
  }

  public function add()
  {
    return $this->insCore();
  }

  public function alter()
  {
    return $this->insCore();
  }

  public function delete(int $id = 0)
  {
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('customer')->where('id', $id)->update(['del' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('customer')->whereIn('id', $data['ids'])->update(['del' => time()])]);
    }
  }

  public function deep_del(int $id = 0)
  {
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('customer')->where('id', $id)->delete()]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('customer')->whereIn('id', $data['ids'])->update(['del' => time()])]);
    }
  }

  public function rec()
  {
    $data = Request::post();
    if (isset($data['id']) || isset($data['ids'])) {
      $db = Db::name('customer');
      $db = isset($data['id']) ? $db->where('id', $data['id']) : $db->whereIn('id', $data['ids']);
    } else return $this->err(['message' => 'Bad Request']);
    return $this->succ(['rs' => $db->update(['del' => 0])]);
  }
  public function addr_del(int $id)
  {
    if ($id < 0) return $this->err(['message' => 'bad id']);
    $is = Request::isDelete();
    if ($is) return $this->succ(['rs' => Db::name('customer_address')->where('id', $id)->update(['del' => time()])]);
    if (Request::isPost()) {
      $data = Request::post();

      return $this->succ(['rs' => Db::name('customer_address')->whereIn('id', $data['ids'])->update(['del' => time()])]);
    }
  }

  public function quick_black(int $id)
  {
    return $this->succ(['rs'=> Db::name('customer')->where('id', $id)->update(['black' => 1])]);
  }

  public function quick_rec_black(int $id)
  {
    return $this->succ(['rs'=> Db::name('customer')->where('id', $id)->update(['black' => 0])]);
  }

  // public function addr(int $id)
  // {
  //   if ($id <= 0) return $this->err(['message' => 'Bad id']);
  //   $rs = Db::name('customer_addr')->where('customer_id', $id)->select();
  //   return $this->succ(['data' => $rs]);
  // }

  // public function contract(int $id)
  // {
  //   if ($id <= 0) return $this->err(['message' => 'Bad id']);
  //   $rs = Db::name('customer_serv')->where('customer_id', $id)->select();
  //   return $this->succ(['data' => $rs]);
  // }
}
