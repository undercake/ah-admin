<?php
/*
 * @Author: Undercake
 * @Date: 2023-04-07 14:26:04
 * @LastEditTime: 2023-04-07 14:28:11
 * @FilePath: /ahadmin/app/midas/controller/Autorun.php
 * @Description: 执行自动任务
 */
namespace app\midas\controller;

use app\BaseController;
use think\facade\Request;

class Autorun extends BaseController
{
  public function index()
  {
    return json([
      Request::ip()]);
  }
}
