<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-03-22 15:11:41
 * @FilePath: /tp6/app/midas/common/Common.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\midas\common;

use app\BaseController;
use think\facade\Config;
use think\facade\Request;
use think\cache\driver\Redis;
use think\facade\Session;

class Common extends BaseController
{
  protected $controller = '';
  protected $action = '';
  protected $ip = '';
  protected $cookie_divider = '__midas__';

  private $do_not_need_login = [
    'user/login',
    'user/logged',
    'cap/get',
  ];

  public function __construct()
  {
    \think\middleware\SessionInit::class;
    session_start();
    $this->controller = Request::controller(true);
    $this->action = Request::action(true);
    $logged = $this->is_logged_in();
    // 未登录
    if (!$logged && !in_array($this->controller . '/' . $this->action, $this->do_not_need_login)) {
      die(json_encode(['code' => -2, 'is_login' => false, 'message' => '您尚未登录，请登录后再试！']));
    }
    // 无权限
    if ($logged) {
    }
  }

  protected function sys_log(String $log)
  {
  }

  protected function session_get($key, $val = null)
  {
    Session::get($this->cookie_divider . $key, $val);
  }

  protected function session_set($key, $val, $exp = null)
  {
    Session::set($this->cookie_divider . $key, $val, $exp);
  }

  protected function session_has($key)
  {
    Session::has($this->cookie_divider . $key);
  }

  protected function session_del($key)
  {
    Session::delete($this->cookie_divider . $key);
  }

  protected function is_logged_in()
  {
    return $this->session_get('is_login', false);
  }

  protected function err($data)
  {
    return json(['code' => -1, ...$data]);
  }

  protected function succ($data)
  {
    return json(['code' => 0, ...$data]);
  }

  protected function rand_str($len = 5, $chars = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY')
  {
    return substr(str_shuffle($chars), 0, $len);
  }
}
