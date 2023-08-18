<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-06-01 06:17:10
 * @FilePath: /ahadmin/app/midas/common/Common.php
 * @Description: 公共类
 */

namespace app\midas\common;

use app\BaseController;
use app\common\Session as CommonSession;
use think\facade\Request;

class Common extends BaseController
{
  protected $controller = '';
  protected $action = '';
  protected $ip = '';
  protected $cookie_divider = '__midas__';
  protected $sess;

  public function __construct()
  {
    $this->sess = new CommonSession($this->cookie_divider);
    $this->controller = Request::controller(true);
    $this->action = Request::action(true);
    $this->ip = $_SERVER['HTTP_X_REAL_IP'];
    $logged = $this->is_logged_in();
    // 未登录
    $do_not_need_login = [
      'user/login',
      'user/logged',
      'cap/get',
    ];
    if (!$logged && !in_array($this->controller . '/' . $this->action, $do_not_need_login)) {
      die(json_encode(['code' => -2, 'is_login' => false, 'message' => '您尚未登录，请登录后再试！']));
    }
    $rights = $this->session_get('rights');
    $controllers_do_not_need_right = [
      'my',
      'user'
    ];
    if (
      $logged &&
      !in_array($this->controller, $controllers_do_not_need_right) &&
      !in_array('/' . $this->controller . '/' . $this->action, $rights)
    ) {
      die(json_encode([
        'code'       => -3,
        'has_rights' => false,
        'message'    => '您没有权限',
        'path'       => $this->controller . '/' . $this->action,
        'ip'         => $this->ip
      ]));
    }
  }

  protected function sys_log(Int $type, String $log)
  {
    //0登录 1退出 2新增 3删除 4修改 5彻底删除
  }

  private function has_rights()
  {
    $rights = $this->sess->get('rights');
  }

  protected function session_get($key, $val = null)
  {
    return $this->sess->get($key, $val);
  }

  protected function session_set($key, $val)
  {
    return $this->sess->set($key, $val);
  }

  protected function session_has($key)
  {
    return $this->sess->has($key);
  }

  protected function session_del($key)
  {
    return $this->sess->delete($key);
  }

  protected function session_clear()
  {
    return $this->sess->clear();
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
