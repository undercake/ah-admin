<?php

namespace app\api\controller;

use think\facade\Config;
use app\api\controller\Common;
use think\captcha\facade\Captcha;
use think\facade\Session;

class Cap extends Common
{
  public function get()
  {
    $conf = Config::get('captcha');
    $char = $this->rand($conf['codeSet'], $conf['length']);
    $this->session_set('Session_captcha', $char, $conf['expire']);
    return Captcha::create($char);
    // return json([$conf, $char, $r]);
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