<?php
/*
 * @Author: Undercake
 * @Date: 2023-08-18 02:37:15
 * @LastEditTime: 2023-08-18 02:41:21
 * @FilePath: /ahadmin/app/autorun/controller/Test.php
 * @Description: 
 */

namespace app\autorun\controller;

use app\autorun\common\BaseRun;
use app\autorun\common\ParseCronTab;

class Test extends BaseRun
{
    public function index()
    {
        var_dump(ParseCronTab::check(time(), '* * * * 5'));
    }
}