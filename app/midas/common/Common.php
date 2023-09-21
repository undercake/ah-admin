<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-09-03 06:25:27
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
    $this->shall_pass();
  }

  private function shall_pass() {
    $do_not_need_login = [
      'user/wx_before_login_redirect',
      'user/wx_after_login_redirect',
      'user/is_wx_scanned',
      'user/is_wx_loggedin',
      'user/wx_redirect',
      'user/wx_login',
      'user/login',
      'user/logged',
      'user/test',
      'cap/get',
    ];

    $controllers_do_not_need_right = [
      'my',
      'user'
    ];

    if (in_array($this->controller . '/' . $this->action, $do_not_need_login) ) {
      return true;
    }

    $logged = $this->is_logged_in();
    if (!$logged) {
      die(json_encode(['code' => -2, 'is_login' => false, 'message' => '您尚未登录，请登录后再试！']));
    } else if(in_array($this->controller, $controllers_do_not_need_right)) {
      return true;
    }

    $rights = $this->session_get('rights');
    if (!in_array('/' . $this->controller . '/' . $this->action, $rights))
      die(json_encode([
        'code'       => -3,
        'has_rights' => false,
        'message'    => '您没有权限',
        'path'       => $this->controller . '/' . $this->action,
        'ip'         => $this->ip
      ]));
  }

  protected function is_logged_in()
  {
    return $this->session_get('is_login', false);
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

  protected function get_curl_data($url, $data = null)
  {
      // $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      if (!is_null($data)) {
          curl_setopt($curl, CURLOPT_POST, 1);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
          // curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
      }
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($curl);
      curl_close($curl);
      return $output;
  }
}
