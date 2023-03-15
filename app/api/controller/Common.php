<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-03-04 16:43:32
 * @FilePath: /tp6/app/api/controller/common/Common.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;

use app\BaseController;
use think\cache\driver\Redis;
use think\facade\Config;
use think\facade\Request;

class Common extends BaseController
{
  protected $redis = false;
  protected $cookie = false;
  protected $controller = '';
  protected $action = '';
  private $do_not_need_login = [
    'user/login',
    'user/logged',
    'cap/get',
  ];

  public function __construct()
  {
    session_start();
    $this->controller = Request::controller(true);
    $this->action = Request::action(true);
    $logged = $this->is_logged_in();
    if (!$logged && !in_array($this->controller . '/' . $this->action, $this->do_not_need_login)) {
      halt($this->err(['is_login' => false, 'msg' => '您尚未登录，请登录后再试！']));
    }
  }

  protected function session_get($key, $val = null)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->has($this->cookie . '__' . $key) ? $this->redis->get($this->cookie . '__' . $key) : $val;
  }

  protected function session_set($key, $val, $exp = null)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->set($this->cookie . '__' . $key, $val, $exp);
  }

  protected function session_has($key)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->has($this->cookie . '__' . $key);
  }

  protected function session_del($key)
  {
    if (!$this->redis) $this->get_redis();
    return $this->redis->delete($this->cookie . '__' . $key);
  }

  protected function is_logged_in()
  {
    return $this->session_get('is_login', false);
  }

  protected function err($data)
  {
    return json(['status' => 'error', ...$data]);
  }

  protected function succ($data)
  {
    return json(['status' => 'success', ...$data]);
  }

  private function get_redis()
  {
    $this->redis = new Redis(Config::get('redis'));
    $this->cookie = $_COOKIE['PHPSESSID'];
  }
}