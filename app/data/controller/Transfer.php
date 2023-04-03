<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-12 10:28:14
 * @LastEditTime: 2023-04-03 12:23:15
 * @FilePath: /ahadmin/app/data/controller/Transfer.php
 * @Description: 转移数据
 */

namespace app\data\controller;

// use app\common\Pinyin;
use think\facade\Db;
use Overtrue\Pinyin\Pinyin;

class Transfer
{

  private $source_db = 'ah_data';
  private $target_db = 'ah_admin';

  public function customer(int $id)
  {
    # code...
  }

  public function all()
  {
    foreach (['clientInfo', 'operator', 'employee'] as $v) {
      $this->index($v);
    }
  }
  /**
   * @description: 要迁移的表
   * @param String $tb 表名称
   * @return \think\Response
   */
  public function index($tb)
  {
    switch ($tb) {
      case 'clientInfo':
        return;
        $this->transfer_clientInfo();
        break;
      case 'operator':
        return json([]);
        $this->transfer_operator();
        break;
      case 'employee':
        return json([]);
        // $this->transfer_employee();
        break;
      case 'license':
        return json([]);
        $this->transfer_license();
        break;

      case '3':
        return json([]);
        $this->transfer_triple();
        break;
      case 'log':
        return;
        $this->trans_log();
        break;
      case 'ti':
        return;
        $this->trans_TaskInfo();
        break;
      case 'tdd':
        return
          $this->trans_TaskDoDetail();
        break;
      case 'as':
        return;
        $this->trans_Assign();
        break;
      case 'wm':
        return
          $this->trans_WaterMoney();
        break;
      case 'ee':
        return;
        $this->trans_ee();
        break;
      case 'ser':
        return;
        $this->trans_ser();
        break;
      case 'serg':
        return
          $this->trans_serg();
        break;
      case 'sero':
        return;
        $this->trans_sero();
        break;
      case 'c2c':
        return
          $this->c2cHandler();
        break;

      default:
        return json(['status' => 'error']);
        break;
    }
  }
  /*
TRUNCATE TABLE `customer`;
TRUNCATE TABLE `customer_serv`;
TRUNCATE TABLE `customer_addr`;
UPDATE `client_info` SET `transfered`=0 WHERE 1;
*/

  private function c2cHandler()
  {
    ini_set('memory_limit', '5G');
    set_time_limit(0);

    try {
      return $this->client2customer();
    } catch (\Throwable $th) {
      return $th->getMessage() . ';<br> $_ENV:: ' . json_encode($_ENV);
    }
  }

  private function client2customer()
  {
    $db_name = 'ah_admin';
    $ah_data = Db::connect($db_name)->table('client_info')->order('last_modify', 'DESC');
    $_ENV['transfered'] = 0;
    $_ENV['total'] = 0;
    $cursor = $ah_data->cursor();
    foreach ($cursor as $d) {
      $id = $d['id'];
      $phone = str_replace(['１','２','３','４','５','６','７','８','９','０'], ['1','2','3','4','5','6','7','8','9','0'], $d['phone']);
      $mobile = explode(',', $phone);
      $mobile_new = [];

      $contract_code = Db::connect('ah_data')->table('ClientInfo')->where('id', $id)->field('ItemCode')->find()['ItemCode'];
      foreach ($mobile as $v) {
        if (strlen((int)$v) > 6)
          $mobile_new[] = $v;
      }
      if (count($mobile_new) > 0) {
        $db = Db::connect($db_name);
        $cus = $db->name('customer');
        $name = [$d['full_name']];
        $whereMobile = [];
        foreach ($mobile_new as $v) {
          $whereMobile[] = ['mobile', 'LIKE', '%' . $v . '%'];
        }
        $old_data = $cus->whereOr($whereMobile)->findOrEmpty();
        if (count($old_data) > 0) {
          $id = $old_data['id'];
          $name = array_merge($name, explode(';;', $old_data['name']));
          $mobile_new = array_merge($mobile_new, explode(',', $old_data['mobile']));
        }
        $data_name = implode(';;', array_unique($name));
        if (strlen($data_name) > 410) $data_name = mb_substr($data_name, 0, 410) . '...';
        $data_new = [
          'name'        => $data_name,
          'mobile'      => implode(',', array_unique($mobile_new)),
          'black'       => $d['black_flag'],
          'pym'         => $d['pym'],
          'pinyin'      => Pinyin::name($d['full_name'], 'none')->join(''),
          'total_money' => $d['total_money'],
          'total_count' => $d['total_count'],
          'remark'      => $d['special_need'] . '；' . $d['f_region'],
          'del'         => $d['del'],
          'last_modify' => $d['last_modify']
          // 'F1'                  , // => $d['F1'],
        ];
        $_ENV['id_equal'] = $id == $d['id'];
        $_ENV['id'] = $id;
        $_ENV['d_id'] = $d['id'];
        $_ENV['last_data'] = $data_new;
        $id == $d['id'] ? $cus->insert([...$data_new, 'id' => $id]) : $cus->where('id', $id)->update($data_new);

        $addr_sql = $db->name('customer_addr');
        $this_addr_count = $addr_sql->where([
          ['customer_id', '=', $id],
          ['address', 'LIKE', '%' . $d['address'] . '%']
        ])->count();
        $data_addr = [
          'address'     => $d['address'],
          'area'        => $d['house_area'] > 65535 ? 65535 : ($d['house_area'] < 0 ? 0 : $d['house_area']),
          'customer_id' => $id
        ];
        $_ENV['last_addr'] = $data_addr;
        if ($this_addr_count == 0)
          $addr_sql->insert($data_addr);
        $data_serv = [
          'start_time'    => $d['begin_time'],
          'create_time'   => $d['create_time'],
          'end_time'      => $d['end_time'],
          'contract_code' => $contract_code,
          'type'          => $d['type'],
          'remark'        => $d['normal_service_time'],
          'customer_id'   => $id
        ];
        $_ENV['last_serv'] = $data_serv;
        $db->table('customer_serv')->insert($data_serv);
        $db->table('client_info')->where('id', $d['id'])->update(['transfered' => 1]);
        $db->close();
        $_ENV['transfered']++;
        unset($data_new, $db, $cus, $name, $addr_sql, $this_addr_count, $old_data, $whereMobile);
      }
      unset($mobile_new);
      $_ENV['total']++;
      /*   customer
      id	int(10)		UNSIGNED	否	无
	2	name	varchar(70)	utf8mb4_bin		否	无
	3	mobile	varchar(80)	utf8mb4_general_ci		否		英文,分开
	4	black	tinyint(1)			否	0
	5	pym	varchar(35)	utf8mb4_general_ci		否
	6	pinyin	varchar(280)	utf8mb4_bin		否
	7	del	tinyint(1)			否	0
	8	create_time	datetime
	9	last_modify	datetime
	10	remark
      addr
  	id	address	customer_id	area

    customer_serv
	id 主键	int(10)		UNSIGNED	否	无		AUTO_INCREMEN
	2	customer_id	int(10)		UNSIGNED	否	0
	3	create_time	datetime
	4	stat_time	datetime
	5	end_time	datetime
	6	type	tinyint(1)			否	0	7半月卡6月卡5季卡4年卡3包做2包周1钟点0暂无
	7	deleted	bigint(12)			否	0
	8	contract_id	int(10)		UNSIGNED	否	0
	9	remark	v
       */
    };
    return json_encode($_ENV);
  }

  private function transfer_core(String $prev_tb_name, String $next_tb_name, callable $tb_relate, $where = [])
  {
    $ah_data = Db::connect($this->source_db)->table($prev_tb_name);
    if (count($where) > 1) {
      $ah_data = $ah_data->whereTime(...$where);
    }
    $_ENV['count'] = 0;
    $ah_data->chunk(100, function ($data) use ($next_tb_name, $tb_relate) {
      $ah_admin = Db::connect($this->target_db)->table($next_tb_name);
      $new_data = [];
      foreach ($data as $i => $d) {
        $tmp = $tb_relate($d);
        if (!is_null($tmp)) $new_data[$i] = $tmp;
      }
      $ah_admin->insertAll($new_data);
      $_ENV['count'] += count($data);
      echo (count($data) < 100 ? ('<br>' . $_ENV['count']) : '.');
    });
  }

  private function flowData(String $key, String $dataKey, \think\Collection $data)
  {
    $tmpData = [];
    foreach ($data as $v) {
      $tmpData[$v[$key]] = $v[$dataKey];
    }
    return $tmpData;
  }

  private function transfer_clientInfo()
  {
    $this->transfer_core('ClientInfo', 'client_info', function ($d) {
      return [
        'id'                  => $d['id'],
        'full_name'           => $d['FullName'] ?? '',
        'phone'               => implode(',', [$d['Tel1'], $d['Tel2'], $d['Tel3']]),
        'address'             => $d['Address'] ?? '',
        'type'                => $d['UserType'] ?? '',
        'total_money'         => $d['TotalMoney'] ?? '',
        'total_count'         => $d['TotalCount'] ?? '',
        'f_region'            => $d['fRegion'] ?? '',
        'house_area'          => $d['HouseArea'] < 0 ? abs($d['HouseArea']) : ($d['HouseArea'] > 20151000 ? $d['HouseArea'] - 20150000 : $d['HouseArea']),
        'normal_service_time' => $d['NormalServiceTime'] ?? '',
        'special_need'        => $d['SpecialNeed'] ?? '',
        'black_flag'          => $d['BlackFlag'],
        'pym'                 => $d['pym'] ?? '',
        'pinyin'              => Pinyin::name($d['FullName'], 'none')->join(''),
        'F1'                  => $d['F1'],
        'del'                 => $d['DelFlag'],
        'begin_time'          => trim($d['BeginDate']) !== ''  ? $d['BeginDate'] : '00-00-00 00:00:00',
        'end_time'            => trim($d['EndDate']) !== ''  ? $d['EndDate'] : '00-00-00 00:00:00',
        'create_time'         => trim($d['CreateDate']) !== ''  ? $d['CreateDate'] : '00-00-00 00:00:00',
        'last_modify'         => trim($d['LastModiDate']) !== ''  ? $d['LastModiDate'] : '00-00-00 00:00:00',
      ];
    });
  }
  private function transfer_operator()
  {
    $this->transfer_core('Operator', 'operator', function ($d) {
      return [
        'full_name'  => $d['FullName'] ?? '',
        'password'   => sha1(md5($d['Password']) . md5($d['OperatorOID'] . $d['OPCode'])),
        'salt'       => md5($d['OperatorOID'] . $d['OPCode']),
        'user_group' => $d['SystemFlag'] == 0 ? 1 : 2,
        'user_name'  => Pinyin::abbr($d['FullName'], 'none')->join(''),
        'mobile'     => ''
      ];
    });
  }
  private function transfer_license()
  {
    $this->transfer_core('License', 'license', function ($d) {
      return [
        'name' => $d['FullName'],
        'del'  => $d['DelFlag']
      ];
    });
  }
  private function trans_log()
  {
    $Operator = Db::connect('ah_admin')->table('operator')->select();
    $BranchOffice = Db::connect('ah_admin')->table('branch_office')->select();
    $Operator_name = [];
    $BranchOffice_name = [];
    foreach ($Operator as $v) {
      $Operator_name[$v['full_name']] = $v['id'];
    }
    foreach ($BranchOffice as $v) {
      $BranchOffice_name[$v['name']] = $v['id'];
    }
    $this->transfer_core('LogLog', 'sys_log', function ($d) use ($Operator_name, $BranchOffice_name) {
      return [
        'IP'               => $d['IP'],
        'type'             => 0,
        'time'             => $d['LogDate'],
        'operator_id'      => $Operator_name[$d['OperatorFullName']] ?? '',
        'branch_office_id' => $BranchOffice_name[$d['BranchOfficeFullName']] ?? ''
      ];
    }, ['LogDate', '>', '2021-1-1']);
  }
  private function trans_TaskInfo()
  {
    $Operator     = $this->flowData('OperatorOID',     'id', Db::connect('ah_data')->table('Operator')->select());
    $BranchOffice = $this->flowData('BranchOfficeOID', 'id', Db::connect('ah_data')->table('BranchOffice')->select());
    $this->transfer_core('TaskInfo', 'task_info', function ($d) use ($Operator, $BranchOffice) {
      $client = Db::connect('ah_data')->table('ClientInfo')->field('id')->where('ClientInfoOID', $d['ClientInfoOID'])->find();
      return [
        'id'               => $d['id'],
        'operator_id'      => $Operator[$d['OperatorOID']] ?? 0,
        'client_id'        => $client['id'] ?? 0,
        'branch_office_id' => $BranchOffice[$d['BranchOfficeOID']] ?? 0,
        'service_name'     => $d['ServiceContentName'],
        'service_time'     => $d['NeedServiceTime'],
        'comment'          => $d['Comment'],
        'contact_time'     => $d['PhoneDate'],
        'phone_flag'       => $d['PhoneFlag'],
        'task_status'      => $d['TaskStatus'],
        'del'              => 0
      ];
    }, ['PhoneDate', '>', '2021-1-1']);
  }
  private function trans_TaskDoDetail()
  {
    return;
    $Operator     = $this->flowData('OperatorOID',     'id', Db::connect('ah_data')->table('Operator')->select());
    $BranchOffice = $this->flowData('BranchOfficeOID', 'id', Db::connect('ah_data')->table('BranchOffice')->select());
    $this->transfer_core('TaskInfo', 'task_info', function ($d) use ($Operator, $BranchOffice) {
      $c = Db::connect('ah_data')->table('ClientInfo')->field('id')->where('TaskInfoOID', $d['TaskInfoOID'])->whereTime(['PhoneDate', '>', '2021-1-1'])->find();
      if (count($c) < 1) return null;
      return [
        'id'               => $d['id'],
        'operator_id'      => $Operator[$d['OperatorOID']] ?? 0,
        'client_id'        => $c['id'] ?? 0,
        'branch_office_id' => $BranchOffice[$d['BranchOfficeOID']] ?? 0,
        'service_name'     => $d['ServiceContentName'],
        'service_time'     => $d['NeedServiceTime'],
        'comment'          => $d['Comment'],
        'contact_time'     => $d['PhoneDate'],
        'phone_flag'       => $d['PhoneFlag'],
        'task_status'      => $d['TaskStatus'],
        'del'              => 0
      ];
    });
  }
  private function trans_Assign()
  {
    $Operator     = $this->flowData('OperatorOID',     'id', Db::connect('ah_data')->table('Operator')->select());
    $BranchOffice = $this->flowData('BranchOfficeOID', 'id', Db::connect('ah_data')->table('BranchOffice')->select());
    $this->transfer_core('TaskInfoAssign', 'task_assign', function ($d) use ($Operator, $BranchOffice) {
      $c = Db::connect('ah_data')->table('TaskInfo')->field('id')->where('TaskInfoOID', $d['TaskInfoOID'])->find();
      return [
        'id'               => $d['id'],
        'task_info_id'     => $c['id'] ?? 0,
        'operator_id'      => $Operator[$d['OperatorOID']] ?? 0,
        'branch_office_id' => $BranchOffice[$d['BranchOfficeOID']] ?? 0,
        'assign_time'      => $d['AssignDate'],
      ];
    }, ['AssignDate', '>', '2021-1-1']);
  }
  private function trans_WaterMoney()
  {
    $Operator     = $this->flowData('OperatorOID',     'id', Db::connect('ah_data')->table('Operator')->select());

    $this->transfer_core('WaterMoney', 'water_money', function ($d) use ($Operator) {
      $c = Db::connect('ah_data')->table('ClientInfo')->field('id')->where('ClientInfoOID ', $d['ClientInfoOID'])->find();
      return [
        'id'             => $d['id'],
        'client_info_id' => $c['id'] ?? 0,
        'item_code'      => $d['ClientInfo_ItemCode'] ?? '',
        'charge_money'   => $d['ChargeMoney'] ?? 0,
        'now_money'      => $d['NowMoney'] ?? 0,
        'charge_count'   => $d['ChargeCount'] ?? 0,
        'now_count'      => $d['NowCount'] ?? 0,
        'charge_date'    => $d['ChargeDate'] ?? 0,
        'OP_ID'          => $Operator[$d['OP_ID']] ?? 0,
      ];
    }, ['ChargeDate', '>', '2021-1-1']);
  }
  private function transfer_triple()
  {
    $this->transfer_core('BranchOffice', 'branch_office', function ($d) {
      return [
        'name' => $d['FullName'],
        'type' => $d['OfficeType']
      ];
    });
    $this->transfer_core('RightType', 'right_type', function ($d) {
      return [
        'name' => $d['FullName']
      ];
    });
    $this->transfer_core('ServiceContent', 'services', function ($d) {
      return [
        'name' => $d['ServiceContentName']
      ];
    });
  }
  private function trans_ee()
  {
    $this->source_db = 'ahjz_ynshendu_co';
    $this->transfer_core('waiter', 'employee', function ($d) {
      if ($d['is_delete'] == 1) return null;
      return [
        'name'        => $d['nickname'] ?? '',
        'phone'       => $d['tel'] ?? '',
        'img'         => $img_url[1] ?? '',
        'avatar'      => $avatar_url[1] ?? '',
        'intro'       => $d['intro'] ?? '',
        'pinyin'      => Pinyin::name($d['nickname'], 'none')->join(''),
        'pym'         => Pinyin::abbr($d['nickname'], 'none')->join(''),
        'address'     => ($d['province'] ?? '') . ($d['city'] ?? '') . ($d['area'] ?? '') . ($d['street'] ?? ''),
        'create_time' => date('Y-m-d H:i:s', time())
      ];
    });
  }
  private function trans_serg()
  {
    return;
    $this->source_db = 'ahjz_ynshendu_co';
    $this->transfer_core('housekee_sever_class', 'service_category', function ($d) {
      if ($d['is_delete'] == 1) return null;
      return [
        'id'       => $d['id'],
        'name'     => $d['name'] ?? '',
        'sort'     => $d['sort'] ?? '',
        'status'   => $d['state'] ?? ''
      ];
    });
  }
  private function trans_ser()
  {
    $this->source_db = 'ahjz_ynshendu_co';
    $this->transfer_core('housekee_sever', 'services', function ($d) {
      if ($d['is_delete'] == 1) return null;
      return [
        'id'       => $d['id'],
        'name'     => $d['name'] ?? '',
        'intro'    => $d['intro'] ?? '',
        'avatar'   => str_replace('http://ahjz.ynshendu.com', '', $d['icon'] ?? ''),
        'banner'   => is_null($d['banner']) ? '' : str_replace('http://ahjz.ynshendu.com', '', implode(',', json_decode($d['banner']))),
        'details'  => $d['details'] ?? '',
        'prompt'   => $d['prompt'] ?? '',
        'sort'     => $d['sort'] ?? '',
        'status'   => $d['state'] ?? '',
        'class_id' => $d['class_id'] ?? ''
      ];
    });
  }
  private function trans_sero()
  {
    $this->source_db = 'ahjz_ynshendu_co';
    $this->transfer_core('housekee_sever_spec', 'service_options', function ($d) {
      // if ($d['is_delete'] == 1) return null;
      return [
        'id'          => $d['id'],
        'name'        => $d['name'] ?? '',
        'price'       => $d['price'] ?? '',
        'image'       => explode('upload', $d['image'])[1] ?? '',
        'price_intro' => $d['price_intro'] ?? '',
        'service_id'  => $d['ser_id'] ?? '',
        'min_num'     => $d['min_unm'] ?? '',
        'wai_num'     => $d['wai_num'] ?? ''
      ];
    });
  }
}
