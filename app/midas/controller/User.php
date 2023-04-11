<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:38:59
 * @LastEditTime: 2023-04-11 12:18:45
 * @FilePath: /ahadmin/app/midas/controller/User.php
 * @Description: 登录类
 */

namespace app\midas\controller;

use think\facade\Db;
use think\facade\Request;
use app\midas\common\Common;

class User extends Common
{
  public function login()
  {
    $data = Request::post();

    // 验证码
    $cap = $this->session_get('captcha');
    if ($cap && !password_verify(mb_strtolower(trim($data['captcha'], 'UTF-8')), $cap))
      return $this->err(['message' => '验证码错误', 'c' => $this->session_get('captcha'), $cap]);

    $this->session_del('captcha');
    $sql = Db::name('operator')->where(['user_name' => $data['username'], 'deleted' => 0]);
    if (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
      $sql->whereOr('mobile', $data['username']);
    }
    if (preg_match("/^[A-Za-z0-9\x80-\xff]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/", $data['username'])) {
      $sql->whereOr('email', $data['username']);
    }
    $rs = $sql->findOrEmpty();
    if (empty($rs) || !(sha1($data['passwordMd5'] . $rs['salt']) == $rs['password']))
      return $this->err(['message' => '账号密码不正确']);

    $this->session_set('is_login', true);
    $this->session_set('id', $rs['id']);
    $this->session_set('username', $rs['user_name']);
    $this->session_set('nickname', $rs['full_name']);
    $this->session_set('group', $rs['user_group']);
    $this->session_set('mobile', $rs['mobile']);

    $group = $this->setRights()[1];

    return $this->succ(['message' => '登录成功！', 'nickname' => $rs['full_name'], 'group' => $group['name']]);
  }

  private function setRights()
  {
    $rights_list = Db::name('groups')->field('rights,name')->where('id', $this->session_get('group'))->find();
    $rights = Db::name('rights')->where([
      ['id', 'IN', $rights_list['rights']],
      ['type', '<>', 4]
      ])->order('sort', 'ASC')->select();
    $r = [];
    foreach ($rights as $v) {
      if (in_array($v['type'], [0, 1])) $r[] = $v['path'];
    }
    $this->session_set('rights', $r);
    return [$rights, $rights_list];
  }

  public function getUserSideMenu()
  {
    $rights = $this->setRights()[0];
    foreach ($rights as $k => $v) {
      $rights[$k] = [...$v, 'children' => []];
    }
    return $this->succ(['rights' => $rights]);
  }

  public function logout()
  {
    $this->session_clear();
    return $this->succ(['message' => '已成功退出登录！']);
  }

  public function logged()
  {
    return json(['is_login' => $this->is_logged_in()]);
  }
}
