<?php

namespace app\api\controller;

use think\facade\Config;
use app\api\controller\Common;
use think\captcha\facade\Captcha as Ca;
use think\facade\Session;

class Captcha extends Common
{
  public function get()
  {
    $conf = Config::get('captcha');
    $char = $this->rand($conf['codeSet'], $conf['length']);
    $this->session_set('Session_captcha', $char);
    $this->session_set('Session_expire', time() + $conf['expire']);
    // return json([$conf, $char]);
    return Ca::create($char);
  }

  private function rand($chars, $len)
  {
    $string = time();
    for ($length = $len; $len >= 1; $len--) {
      $position = rand() % strlen($chars);
      $position2 = rand() % strlen($string);
      $string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
    }
    return str_split($string, $length)[0];
  }
}