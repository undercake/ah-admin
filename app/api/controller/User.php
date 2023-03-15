<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:38:59
 * @LastEditTime: 2023-03-04 16:39:00
 * @FilePath: /tp6/app/api/controller/User.php
 * @Description:
 */

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use app\api\controller\Common;

class User extends Common
{
  public function login()
  {
    $data = Request::post();

    if (!($this->session_get('Session_captcha', false) && $this->session_get('Session_captcha') != trim($data['captcha']))) {
      $this->session_del('Session_captcha');
      return json(['status' => 'error', 'msg' => '验证码错误']);
    }
    $this->session_del('Session_captcha');
    $sql = Db::name('admin')->where('username', $data['username']);
    if (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
      $sql->whereOr('mobile', $data['username']);
    }
    $rs = $sql->find();

    if (!(sha1($data['passwordMd5'] . $rs['salt']) == $rs['password']))
      return $this->err(['msg' => '账号密码不正确']);

    $this->session_set('is_login', true);
    $this->session_set('username', $rs['username']);
    $this->session_set('nickname', $rs['nickname']);
    $this->session_set('group', $rs['admin_group']);
    $this->session_set('mobile', $rs['mobile']);

    $group = Db::name('group')->where('id', $rs['admin_group'])->find();
    $rights = Db::name('rights')->whereIn('id', $group['rights'])->select();
    $this->session_set('rights', $rights);

    return $this->succ(['msg' => '登录成功！', 'nickname' => $rs['nickname'], 'group' => $group['name'], 'rights' => $rights]);
  }

  public function getUserSideMenu()
  {
    return $this->succ($this->session_get('rights'));
  }

  public function logout()
  {
    $array = ['is_login', 'username', 'nickname', 'group', 'mobile', 'rights',];
    foreach ($array as $k) {
      $this->session_del($k);
    }
    return $this->succ(['msg' => '已成功退出登录！']);
  }

  public function logged()
  {
    return json(['is_login' => $this->is_logged_in()]);
  }
}