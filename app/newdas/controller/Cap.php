<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-23 13:49:36
 * @LastEditTime: 2023-03-23 16:06:36
 * @FilePath: /tp6/app/midas/controller/Cap.php
 * @Description: 验证码
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\facade\Session;

class Cap extends Common
{
  public function get()
  {
    $str = $this->rand_str();
    $this->session_set('captcha', strtolower($str));
    $cap = Captcha::create($str);
    $this->sess->set('captcha', Session::get('captcha')['key']);
    return $cap;
  }
}
