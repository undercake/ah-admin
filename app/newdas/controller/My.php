<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-30 17:09:23
 * @LastEditTime: 2023-04-02 02:34:07
 * @FilePath: /ahadmin/app/midas/controller/My.php
 * @Description: 修改登录数据
 */

namespace app\midas\controller;

use think\facade\Db;
use app\midas\common\Common;
use app\midas\model\My as ValidateMy;
use think\facade\Request;

class My extends Common
{
  public function get()
  {
    $data = Db::connect('ah_admin')->table('operator')->field('full_name,user_name,mobile,email')->where('id', $this->session_get('id'))->find();
    return $this->succ(['data' => $data]);
  }
  public function set()
  {
    $id = $this->session_get('id');
    $data = Request::post();
    $v = new ValidateMy;
    $rs = $v->check($data);
    if (!$rs) return $this->err(['message' => $v->getError()]);
    $full_name = $data['full_name'];
    $user_name = $data['user_name'];
    $mobile = $data['mobile'];
    $email = $data['email'];
    $rs = Db::name('operator')->where('id', $id)->update([
      'full_name' => $full_name,
      'user_name' => $user_name,
      'mobile' => $mobile,
      'email' => $email
    ]);
    return $this->succ(['rs' => $rs]);
  }

  public function set_pass()
  {
    $id = $this->session_get('id');
    $rs = Db::name('operator')->field('salt,password')->where('id', $id)->find();
    $data = Request::post();
    if (strlen(trim($data['oldpass'])) !== 32 || strlen(trim($data['newpass'])) !== 32 || strlen(trim($data['newpass_repeat'])) !== 32 || $data['newpass'] !== $data['newpass_repeat'])
      return $this->err(['message' => 'Bad Request', $data]);
    if (!(sha1($data['oldpass'] . $rs['salt']) == $rs['password']))
      return $this->err(['message' => '原密码不正确']);
    $salt = md5($this->rand_str(11));
    $rs = Db::name('operator')->where('id', $id)->update(['salt' => $salt, 'password' => sha1($data['newpass'] . $salt)]);
    return $this->succ(['rs' => $rs]);
  }
}
