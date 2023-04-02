<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-12 10:28:14
 * @LastEditTime: 2023-03-25 09:00:39
 * @FilePath: /tp6/app/data/controller/Transfer.php
 * @Description: a
 */

namespace app\data\controller;

use Overtrue\Pinyin\Pinyin;
use think\facade\Db;

class Transfer
{

  private $source_table = 'ah_data';
  private $target_table = 'ah_admin';

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

      default:
        return json(['status' => 'error']);
        break;
    }
  }

  private function transfer_core(String $prev_tb_name, String $next_tb_name, $tb_relate, $where = [])
  {

    $ah_data = Db::connect($this->source_table)->table($prev_tb_name);
    if (count($where) > 1) {
      $ah_data = $ah_data->whereTime(...$where);
    }
    $_ENV['count'] = 0;
    $ah_data->chunk(100, function ($data) use ($next_tb_name, $tb_relate) {
      $ah_admin = Db::connect($this->target_table)->table($next_tb_name);
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
    $this->source_table = 'ahjz_ynshendu_co';
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
    $this->source_table = 'ahjz_ynshendu_co';
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
    $this->source_table = 'ahjz_ynshendu_co';
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
    $this->source_table = 'ahjz_ynshendu_co';
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
