<?php
/*
 * @Author: Undercake
 * @Date: 2023-08-15 03:09:46
 * @LastEditTime: 2023-08-19 02:19:41
 * @FilePath: /ahadmin/app/autorun/controller/Index.php
 * @Description: 
 */
namespace app\autorun\controller;

use think\facade\Db;
use think\facade\Log;
use app\autorun\common\Base;
use app\autorun\common\ParseCronTab;

class Index extends Base
{
    public function index()
    {
        $time = time();
        $rs = Db::connect('ah')
            ->table('autorun')
            ->select()
            ->toArray();
        if (empty($rs)) return;
        foreach ($rs as $v) {
            if (ParseCronTab::check($time, $v['crontab'])) {
                $rs = $this->run($v['url']);
                Log::info($v['name'] . ' 已执行: ' . $rs);
            }
        }
    }

    function run(string $url) {
        $curl = curl_init('manage.kmahjz.com.cn:99' . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $rs = curl_exec($curl);
        curl_close($curl);
        return $rs;
    }
}
