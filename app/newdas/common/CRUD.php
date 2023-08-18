<?php

namespace app\midas\common;

use think\facade\Db;
use app\midas\common\Common;

class CRUD extends Common
{
    protected function Selection(string $dbName, string $tableName, int $page, int $item = 10, $where = [['deleted', '=', 0]], $order = ['last_modify' => 'DESC', 'id' => 'DESC', 'total_money' => 'DESC', 'total_count' => 'DESC'], callable $filter = null)
    {
        // if ($page <= 0) $page = 1;
        // if ($item <= 2) $item = 10;
        // $sql = Db::connect('ah_data')->name($dbName)
        //     ->order($order)
        //     ->where($where);
        // $rs  = $sql->page($page, $item)->select()->toArray();
        // $addr_ids = [];
        // foreach ($rs as $v) {
        //     $addr_ids[] = $v['id'];
        // }
        // $addr = Db::name('customer_addr')->where('customer_id', 'IN', implode(',', $addr_ids))->select();
        // $serv = Db::name('customer_serv')->where([
        //     ['customer_id', 'IN', implode(',', $addr_ids)],
        //     ['type', '<>', 0],
        // ])->order('end_time', 'DESC')->select();
        // $contr = [];
        // foreach ($serv as $v) {
        //     isset($contr[$v['customer_id']]) ? ($contr[$v['customer_id']][] = $v) : ($contr[$v['customer_id']] = [$v]);
        // }
        // foreach ($contr as $key => $value) {
        //     if (count($value) > 1)
        //         foreach ($value as $k => $v) {
        //             if (strpos($v['end_time'], '2222') !== false || strpos($v['end_time'], '0000') !== false)
        //                 if (count($contr[$key]) > 1) unset($contr[$key][$k]);
        //             $contr[$key] = [...$contr[$key]];
        //         }
        // }
        // if ($filter)
        //     [$rs, $addr, $contr] = $filter($rs, $addr, $contr);
        // return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item, 'addr' => $addr, 'services' => $contr]);

        $page = (int)$page;
        if ($page <= 0) $page = 1;
        $sql = Db::connect($dbName)->table($tableName)->order($order);
        if ($where[0] == 'or') {
            unset($where[0]);
            $sql = $sql->whereOr($where[1]);
        } else
            $sql = $sql->where($where);
        $rs  = $sql->page($page, $item)->select()->toArray();
        return $this->succ(['data' => $rs, 'current_page' => $page, 'count' => $sql->count(), 'count_per_page' => $item]);
    }
}
