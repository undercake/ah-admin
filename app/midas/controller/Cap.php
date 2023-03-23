<?php

namespace app\midas\controller;

use app\midas\common\Common;
use think\captcha\facade\Captcha;

class Cap extends Common
{
  public function get()
  {
    $str = $this->rand_str();
    $this->session_set('captcha', strtolower($str));
    return Captcha::create($str);
  }
}
