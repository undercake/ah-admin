<?php
/*
 * @Author: Undercake
 * @Date: 2023-08-15 03:31:35
 * @LastEditTime: 2023-09-11 02:11:24
 * @FilePath: /ahadmin/app/autorun/controller/Bots.php
 * @Description: 
 */

namespace app\autorun\controller;

use app\autorun\common\Base;
use app\autorun\common\BaseRun;
use app\midas\common\MemberInfo;
use app\common\WX;
use think\facade\Cache;
use think\facade\Db;

// class Bots extends Base
class Bots extends Base
{
    private function get_curl_data($url, $data = null)
    {
        // $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!is_null($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            // curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    // 企业微信机器人 获取流水
    public function fin()
    {
        $has = Cache::get('fin_daily_executed', 0);
        if ($has == 1) return '今天执行过了';
        $fund_token = WX::token('fund');
        $date = date('Y-m-d', strtotime('-1day'));
        // $date = '2023-08-30';

        $billUrl = 'https://qyapi.weixin.qq.com/cgi-bin/externalpay/get_fund_flow?access_token=' . $fund_token;

        $output = null;
        for ($i=0; $i < 5; $i++) {
            sleep(8);
            $output = json_decode($this->get_curl_data($billUrl, json_encode([
                'begin_time' => strtotime($date . ' 00:00:00'),
                'end_time'   => strtotime($date . ' 23:59:59'),
            ])), true);
            if (!is_null($output)) break;
        }

        $userIds = [];
        if (!isset($output['fund_flow_list']) || is_null($output['fund_flow_list']) || empty($output['fund_flow_list']))
            return json(['无可奉告！', $output, $fund_token]);

        foreach ($output['fund_flow_list'] as $v)
            if(!is_null($v) && isset($v['operator_userid']) && !is_null($v['operator_userid']))$userIds[] = $v['operator_userid'];

        $info = (new MemberInfo())->getList($userIds);

        // $token = WX::token();
        $userIds = [];
        foreach($info as $v) {
            $userIds[$v['userid']] = $v['name'];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setCellValue('A1', '交易时间');
        $worksheet->setCellValue('B1', '交易类型');
        $worksheet->setCellValue('C1', '收支');
        $worksheet->setCellValue('D1', '金额');
        $worksheet->setCellValue('E1', '操作人');
        $worksheet->setCellValue('F1', '操作后余额');
        $worksheet->setCellValue('G1', '备注');

        foreach ($output['fund_flow_list'] as $k => $v) {
            $richText = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $subTxt = $richText->createTextRun($v['transaction_amount'] / 100);

            $subTxt->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(
                $v['fund_flow_type'] == 1 ?
                \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKGREEN :
                \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED
                ));
            $worksheet->setCellValue('A' . $k + 2, date('Y-m-d H:i:s', $v['timestamp']));
            $worksheet->setCellValue('B' . $k + 2, ['', '收入', '支出'][$v['fund_flow_type']]);
            $worksheet->setCellValue('C' . $k + 2, ['', '退款', '交易手续费', '收款', '提现', '其他'][$v['transaction_type']]);
            $worksheet->getCell('D' . $k + 2)->setValue($richText);
            // $worksheet->setCellValue('D' . $k + 2, $v['transaction_amount'] / 100);
            $worksheet->setCellValue('E' . $k + 2, isset($v['operator_userid']) ? $userIds[$v['operator_userid']] : '');
            $worksheet->setCellValue('F' . $k + 2, $v['account_balance'] / 100);
            $worksheet->setCellValue('G' . $k + 2, $v['remark']);
        }
        $worksheet->getColumnDimension('A')->setWidth(3.4, 'cm');
        $worksheet->getColumnDimension('B')->setWidth(2.2, 'cm');
        $worksheet->getColumnDimension('D')->setWidth(2.2, 'cm');
        $worksheet->getColumnDimension('F')->setWidth(2.3, 'cm');
        $worksheet->getColumnDimension('G')->setWidth(7, 'cm');
        $worksheet->getStyle('A1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        // 删除临时文件
        $dir = dir(root_path() . '/temp/');
        if ($dir) {
            while (($file = $dir->read()) !== false) {
                if (strpos($file, 'xlsx') !== false) {
                    unlink(root_path() . '/temp/' . $file);
                }
            }
            $dir->close();
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(root_path() . '/temp/' . $date . '.xlsx');

        $rs = json_decode(
            $this->get_curl_data(
                'https://qyapi.weixin.qq.com/cgi-bin/webhook/upload_media?key=381b427e-f7f9-4f6d-9bdd-23a192a954f8&type=file',
                ['media' => new \CURLFile(root_path() . '/temp/' . $date . '.xlsx')])
                , true);
        $bot_url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=381b427e-f7f9-4f6d-9bdd-23a192a954f8';
        $rss = $this->get_curl_data($bot_url, json_encode(['msgtype' => 'file', 'file' => ['media_id' => $rs['media_id']]]));

        Cache::set('fin_daily_executed', 1, 43200);
        return json([$rs['media_id'], $rss]);
        // return download(root_path() . '/temp/' . $date . '.xlsx', $date . '.xlsx');
    }

    // 收款通知机器人
    function fund_massager() {
        $time = time();
        $last_time = Cache::get('fund_massager_last_time', $time - 3660);
        $token = WX::token();
        $bot_url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=1c1ae47c-6ccf-4b52-8bba-ee6c0b0d5211';
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/externalpay/get_bill_list?access_token=' . $token;
        $rs = json_decode($this->get_curl_data($url, json_encode([
            'begin_time' => $last_time,
            'end_time' => $time,
        ])), true);
        if (empty($rs['bill_list'])) return json(['无可奉告！', $rs, $last_time]);
        $user_ids = [];
        foreach ($rs['bill_list'] as $v) {
            $user_ids[] = $v['payee_userid'];
        }
        $info = (new MemberInfo())->getList($user_ids);
        $user_ids = [];
        foreach ($info as $v) {
            $user_ids[$v['userid']] = $v['name'];
        }
        $msg = '';
        $last_time = 0;
        foreach ($rs['bill_list'] as $v) {
            $v['pay_time'] > $last_time && $last_time = $v['pay_time'];
            $msg .= ['收款', '退款'][$v['bill_type']] . '通知：' . date('Y-m-d H:i:s', $v['pay_time']) . ' ' . $user_ids[$v['payee_userid']] . ' ' . ['收款', '退款'][$v['bill_type']] .'：'. $v['total_fee'] / 100 . '元' . "\n";
        }
        $at_arr = array_keys($user_ids);
        array_push($at_arr, 'FangYanKanShiJie', 'Yu7', 'AHuiJiaZhengZhu18183811470');
        $data = json_encode([
            'msgtype' => 'text',
            'text' => [
                'content' => $msg,
                'mentioned_list' => $at_arr,
            ]
        ]);
        $rss = $this->get_curl_data($bot_url, $data);
        Cache::set('fund_massager_last_time', $last_time + 1, 3660);
        return json([$rss, $at_arr]);
    }
}
