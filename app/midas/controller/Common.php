<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-03-18 11:15:16
 * @FilePath: /tp6/app/midas/controller/Common.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\midas\controller;

use think\facade\Db;
use app\BaseController;
use think\facade\Config;
use think\facade\Request;
use think\cache\driver\Redis;

class Common extends BaseController
{
  protected $redis = false;
  protected $cookie = false;
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
    session_start();
    $this->cookie = session_id();
    $this->controller = Request::controller(true);
    $this->action = Request::action(true);
    $logged = $this->is_logged_in();
    if (!$logged && !in_array($this->controller . '/' . $this->action, $this->do_not_need_login)) {
      die(json_encode(['code' => -2, 'is_login' => false, 'msg' => '您尚未登录，请登录后再试！']));
    }
  }

  protected function sys_log(String $log)
  {
    // Db::name('sys_log')->insert(['type' => 0, 'time' => time(), 'IP' => $this->ip, 'operator_id' => $this->session_get('id')]);
  }

  protected function session_get($key, $val = null)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->has($this->cookie . $this->cookie_divider . $key) ? $this->redis->get($this->cookie . $this->cookie_divider . $key) : $val;
  }

  protected function session_set($key, $val, $exp = null)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->set($this->cookie . $this->cookie_divider . $key, $val, $exp);
  }

  protected function session_has($key)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->has($this->cookie . $this->cookie_divider . $key);
  }

  protected function session_del($key)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->delete($this->cookie . $this->cookie_divider . $key);
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

  private function get_redis()
  {
    $this->redis = new Redis(Config::get('redis'));
  }
}
