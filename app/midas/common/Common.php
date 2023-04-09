<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-04-09 11:11:46
 * @FilePath: /ahadmin/app/midas/common/Common.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
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

  private $do_not_need_login = [
    'user/login',
    'user/logged',
    'cap/get',
  ];

  public function __construct()
  {
    $this->sess = new CommonSession($this->cookie_divider);
    $this->controller = Request::controller(true);
    $this->action = Request::action(true);
    $logged = $this->is_logged_in();
    // 未登录
    if (!$logged && !in_array($this->controller . '/' . $this->action, $this->do_not_need_login)) {
      die(json_encode(['code' => -2, 'is_login' => false, 'message' => '您尚未登录，请登录后再试！']));
    }
    if (($logged && $this->controller !== 'user') && (1)) {
    }
  }

  protected function sys_log(String $log)
  {
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
