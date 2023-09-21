<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-16 12:59:48
 * @LastEditTime: 2023-09-15 09:31:27
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
    $contract_user = Db::connect('ah_data')
            ->table('ClientInfo')
            ->order(['EndDate' => 'ASC'])
            ->where([
                ['DelFlag', '=', 0],
                ['EndDate', '>', date('Y-m-d H:i:s')],
                ['EndDate', '<', '2100-01-01'],
                ['UserType', '>', 1]
              ])
            // ->whereOr([
            //   ['FullName', 'LIKE', '%幼儿园%'],
            //   ['FullName', 'LIKE', '%学院%'],
            //   ['FullName', 'LIKE', '%学校%'],
            //   ['FullName', 'LIKE', '%小学%'],
            //   ['FullName', 'LIKE', '%中学%'],
            //   ['FullName', 'LIKE', '%大学%'],
            // ])
            ->field('Tel1,Tel2,Tel3')
            // ->whereBetweenTime('CreateDate', '2021-01-01', date('Y-m-d', time()))
            ->select()->toArray();

    $contract_phone = [];
    foreach ($contract_user as $value) {
      $contract_phone[] = $value['Tel1'];
      $contract_phone[] = $value['Tel2'];
      $contract_phone[] = $value['Tel3'];
    }
    return $this->listCore(
      $page,
      $item,
      [
        ['Tel1', 'IN', $contract_phone],
        ['Tel2', 'IN', $contract_phone],
        ['Tel3', 'IN', $contract_phone],
      ],
      ['Tel1' => 'ASC']
    );
  }

  // 散户查询
  public function other(int $page = 1, int $item = 10)
  {
    // return $this->listCore(
    //   $page,
    //   $item,
    //   [
    //     ['DelFlag', '=', 0],
    //     ['UserType', '<', 2]
    //   ],
    //   ['EndDate' => 'ASC']
    // );

    // $contract_user = Db::connect('ah_data')
    //         ->table('ClientInfo')
    //         ->order(['CreateDate' => 'DESC'])
    //         ->where([
    //             ['DelFlag', '=', 0],
    //             ['EndDate', '>', '2023-08-01'],
    //             ['EndDate', '<', '2100-01-01'],
    //             ['UserType', '>', 1]
    //           ])
    //         ->field('Tel1,Tel2,Tel3')
    //         // ->whereBetweenTime('CreateDate', '2021-01-01', date('Y-m-d', time()))
    //         ->select()->toArray();
    //         // ->fetchSql(true)->select();
    //         // var_dump($sql);
    // $contract_phone = [];
    // foreach ($contract_user as $value) {
    //   $contract_phone[] = $value['Tel1'];
    //   $contract_phone[] = $value['Tel2'];
    //   $contract_phone[] = $value['Tel3'];
    // }
    $other_phone = [13888164897,18313936133,18687162073,13330490112,15877983293,13608867766,13888645662,13577038501,13700693033,13698759558,13708455072,13808732568,13888574055,13658843339,65171883,13769179376,13888336661];
    $where = [];
    foreach($other_phone as $v) {
      $where[] = ['Tel1', 'LIKE', '%' . $v . '%'];
      $where[] = ['Tel2', 'LIKE', '%' . $v . '%'];
      $where[] = ['Tel3', 'LIKE', '%' . $v . '%'];
    }
    $sql = Db::connect('ah_data')
            ->table('ClientInfo')
            // ->whereOr($where)
            ->where([
                ['DelFlag', '=', 0],
                ['UserType', '<=', 1],
                // ['Tel1', 'NOT IN', $contract_phone],
                // ['Tel2', 'NOT IN', $contract_phone],
                // ['Tel3', 'NOT IN', $contract_phone],
                ['Address', 'NOT LIKE', '%不知%'],
                ['FullName', '<>', '员工'],
                ['FullName', 'NOT LIKE', '%不知%'],
                ['FullName', 'NOT LIKE', '%工作手机%'],
                ['FullName', 'NOT LIKE', '%应聘员工%'],
                ['FullName', 'NOT LIKE', '%说是员工%'],
                ['FullName', 'NOT LIKE', '%王加珍 员工%'],
                ['FullName', 'NOT LIKE', '%应聘保姆%'],
                ['FullName', 'NOT LIKE', '%员工应聘%'],
                ['FullName', 'NOT LIKE', '%张丽枝员工%'],
                ['FullName', 'NOT LIKE', '%介绍员工%'],
                ['FullName', 'NOT LIKE', '%公保%'],
                ['FullName', 'NOT LIKE', '%姨妈做员工%'],
                ['FullName', 'NOT LIKE', '%做饭员工%'],
                ['FullName', 'NOT LIKE', '%应聘住家保姆%'],
                ['FullName', 'NOT LIKE', '%张丽枝员工%'],
                ['FullName', 'NOT LIKE', '%员工.不发%'],
                ['FullName', 'NOT LIKE', '%员工不发短信%'],
                ['FullName', 'NOT LIKE', '%李世琴 员工%'],
                ['FullName', 'NOT LIKE', '%陈会琼员工%'],
                ['FullName', 'NOT LIKE', '% 员工不发短信%'],
                ['FullName', 'NOT LIKE', '%严秀芳员工%'],
                ['FullName', 'NOT LIKE', '%陶顺芬.员工%'],
                ['FullName', 'NOT LIKE', '%美团杨路管理人员%'],
                ['FullName', 'NOT LIKE', '%员工医院陪护%'],
                ['FullName', 'NOT LIKE', '%问半天员工%'],
                ['FullName', 'NOT LIKE', '%李世琴 员工%'],
                ['FullName', 'NOT LIKE', '%李东会（员工）%'],
                ['FullName', 'NOT LIKE', '%邓如玲 员工%'],
                ['FullName', 'NOT LIKE', '%毕惠仙%']
              ])
            // ->whereBetweenTime('CreateDate', '2010-01-01', date('Y-m-d', time()))
            ->order('CreateDate', 'DESC')
            ->page($page, $item);
            // ->fetchSql(true)->select();
            // var_dump($sql);
    $rs = $sql->select()->toArray();

    return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item]);

  }
// 搜索
  public function search(int $page = 1, int $item = 10)
  {
    $searchStr = Request::post()['search'];
    return $this->listCore(
      $page,
      $item,
      [
        'or',
        // ['DelFlag', '=', 0],
        // ['UserType', '<', 2],
        [['FullName', 'LIKE', '%' . $searchStr . '%'],
        ['Tel1', 'LIKE', '%' . $searchStr . '%'],
        ['Tel2', 'LIKE', '%' . $searchStr . '%'],
        ['Tel3', 'LIKE', '%' . $searchStr . '%'],
        ['Address', 'LIKE', '%' . $searchStr . '%'],
        ['pym', 'LIKE', '%' . $searchStr . '%'],]
      ],
      ['EndDate' => 'ASC']
    );
    // return json($data);

    // $where = [];
    // if ($type > -1) {
    //   $rs = Db::name('customer_serv')->where('type', $type)->field('customer_id')->order('end_time', 'DESC')->select()->toArray();
    //   if (count ($rs) > 0 ) {
    //     $ids = [];
    //     foreach ($rs as $v) {
    //       $ids[] = $v['customer_id'];
    //     }
    //     $where[] = ['id', 'IN', implode(',', $ids)];
    //   }
    // }
    // return $this->listCore(
    //   $page,
    //   $item,
    //   [
    //     ...$where,
    //     ['mobile', 'LIKE', '%' . $data['mobile'] . '%'],
    //     ['DelFlag', '=', 0]
    //   ]
    // );
  }
// 过期合同户
  public function past(int $page = 1, int $item = 10)
  {
    /*
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
    });*/

  $contract_user = Db::connect('ah_data')
          ->table('ClientInfo')
          ->order(['CreateDate' => 'DESC'])
          ->where([
              ['DelFlag', '=', 0],
              ['EndDate', '>', date('Y-m-d', time())],
              ['EndDate', '<', '2100-01-01'],
              ['UserType', '>', 1]
            ])
          ->field('Tel1,Tel2,Tel3')
          ->whereBetweenTime('CreateDate', '2021-01-01', date('Y-m-d', time()))
          ->select()->toArray();

    $contract_phone = [];
    foreach ($contract_user as $value) {
      $contract_phone[] = $value['Tel1'];
      $contract_phone[] = $value['Tel2'];
      $contract_phone[] = $value['Tel3'];
    }
    return $this->listCore(
      $page,
      $item,
      [
        ['DelFlag', '=', 0],
        ['EndDate', '<', date('Y-m-d H:i:s')],
        ['EndDate', '>', '2021-01-01'],
        ['Tel1', 'NOT IN', $contract_phone],
        ['Tel2', 'NOT IN', $contract_phone],
        ['Tel3', 'NOT IN', $contract_phone],
        ['UserType', '>', 1]
      ],
      ['EndDate' => 'DESC']
    );
  }

  public function detail(int $id = 0)
  {
    if ($id <= 0) return $this->err(['message' => 'bad id', 'id' => $id]);
    // $rs = Db::name('customer')->where(['id' => $id, 'del' => 0])->find();
    // $contract = Db::name('customer_serv')->where('customer_id', $id)->select();
    // $address = Db::name('customer_addr')->where('customer_id', $id)->select();
    // return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['detail' => $rs, 'contract' => $contract, 'address' => $address]);
    $rs = Db::connect('ah_data')
      ->table('ClientInfo')
      ->where('id', $id)
      ->find();
    return count($rs) <= 0 ? $this->err(['message' => '没有找到数据']) : $this->succ(['data' => $rs]);
  }

  public function history(int $page = 1, int $id = 0) {
    // return $this->Selection(
    //   'ah_data',
    //   'ClientInfo',
    //   $page,
    //   $item,
    //   ['ClientInfoOID', '=', function ($query) use ($id) {
    //       $query->table('ClientInfo')->where('id', $id)->field('ClientInfoOID');
    //   }],
    //   ['NeedServiceTime' => 'DESC']
    // );
    $sql = Db::connect('ah_data')
      ->table('TaskInfo')
      ->where([['ClientInfoOID', '=', function ($query) use ($id) {
          $query->table('ClientInfo')->where('id', $id)->field('ClientInfoOID');
      }],
      ['TaskStatus', '<>', '201']])
      ->order(['NeedServiceTime' => 'DESC']);
      $rs = $sql->page($page, 20)
      ->select();

      return $this->succ(['data' => $rs, 'current_page' => $page,'total' => $sql->count(), 'count' => $rs->count(), 'count_per_page' => 20]);
  }
// 回收站
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

  public function get_map()
  {
    $post = Request::post('addr');
    $url = 'https://apis.map.qq.com/ws/place/v1/suggestion?key=DMGBZ-6GSKU-TTHVU-B54EM-QVHZJ-VUFNZ&region=昆明&keyword=' . $post;
    // $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
    $output = $this->get_curl_data($url);
    return json(json_decode($output, true));
  }
}
