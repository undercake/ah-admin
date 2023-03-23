<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:38:59
 * @LastEditTime: 2023-03-22 15:34:16
 * @FilePath: /tp6/app/midas/controller/User.php
 * @Description:
 */

namespace app\midas\controller;

use think\facade\Db;
use think\facade\Config;
use think\facade\Request;
use think\facade\Session;
use app\midas\common\Common;

class User extends Common
{
  public function login()
  {
    $data = Request::post();

    if (strtolower(trim($data['captcha'])) != $this->session_get('captcha')) {
      return $this->err(['message' => '验证码错误', 'data' => $data['captcha'], 'cap' => $this->session_get('captcha')]);
    }
    $sql = Db::name('operator')->where(['user_name' => $data['username'], 'deleted' => 0]);
    if (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
      $sql->whereOr('mobile', $data['username']);
    }
    if (preg_match("/^[A-Za-z0-9\x80-\xff]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/", $data['username'])) {
      $sql->whereOr('email', $data['username']);
    }
    $rs = $sql->find();

    if (!(sha1($data['passwordMd5'] . $rs['salt']) == $rs['password']))
      return $this->err(['message' => '账号密码不正确']);

    $this->session_set('is_login', true);
    $this->session_set('id', $rs['id']);
    $this->session_set('username', $rs['user_name']);
    $this->session_set('nickname', $rs['full_name']);
    $this->session_set('group', $rs['user_group']);
    $this->session_set('mobile', $rs['mobile']);

    $group  = Db::name('groups')->where('id', $rs['user_group'])->find();
    $rights = Db::name('rights')->whereIn('id', $group['rights'])->select();
    $this->session_set('rights', $rights);

    return $this->succ(['message' => '登录成功！', 'nickname' => $rs['full_name'], 'group' => $group['name'], 'rights' => $rights]);
  }

  public function getUserSideMenu()
  {
    $rights_list = Db::name('groups')->field('rights')->where('id', $this->session_get('group'))->find();
    $rights = Db::name('rights')->where('id', 'IN', $rights_list['rights'])->order('sort', 'ASC')->select();
    $r = [];
    foreach ($rights as $v) {
      if ($v['type'] == 1) $r[] = $v['path'];
    }
    $this->session_set('rights', $r);
    return $this->succ(['rights' => $rights]);
  }

  public function logout()
  {
    $array = ['is_login', 'user_name', 'nickname', 'group', 'mobile', 'rights',];
    foreach ($array as $k) {
      $this->session_del($k);
    }
    return $this->succ(['message' => '已成功退出登录！']);
  }

  public function logged()
  {
    return json(['is_login' => $this->is_logged_in()]);
  }
}
