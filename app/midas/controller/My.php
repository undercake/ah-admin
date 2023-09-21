<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-30 17:09:23
 * @LastEditTime: 2023-09-03 05:48:36
 * @FilePath: /ahadmin/app/midas/controller/My.php
 * @Description: 修改登录数据
 */

namespace app\midas\controller;

use think\facade\Db;
use app\midas\common\Common;
use app\common\WX;
use app\midas\model\My as ValidateMy;
use think\facade\Request;

class My extends Common
{
  public function get()
  {
    $data = Db::connect('ah_admin')->table('operator')->field('full_name,user_name,mobile,email,wx_id')->where('id', $this->session_get('id'))->find();
    $avatar = $this->session_get('avatar', '');
    return $this->succ(['data' => [...$data, 'avatar' => $avatar]]);
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
    $rs = Db::connect('ah_admin')->name('operator')->where('id', $id)->update([
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
    $rs = Db::connect('ah_admin')->name('operator')->field('salt,password')->where('id', $id)->find();
    $data = Request::post();
    if (strlen(trim($data['oldpass'])) !== 32 || strlen(trim($data['newpass'])) !== 32 || strlen(trim($data['newpass_repeat'])) !== 32 || $data['newpass'] !== $data['newpass_repeat'])
      return $this->err(['message' => 'Bad Request', $data]);
    if (!(sha1($data['oldpass'] . $rs['salt']) == $rs['password']))
      return $this->err(['message' => '原密码不正确']);
    $salt = md5($this->rand_str(11));
    $rs = Db::connect('ah_admin')->name('operator')->where('id', $id)->update(['salt' => $salt, 'password' => sha1($data['newpass'] . $salt)]);
    return $this->succ(['rs' => $rs]);
  }

  function get_wx_info() {
    $id = $this->session_get('wx_id');
    $r = random_int(0, 100);
    if ($r < 30) $this->update_wx_info();
    $rs = Db::connect('ah_admin')->name('wx_id')->where('id', $id)->findOrEmpty();
    return $this->succ(['data' => $rs]);
  }

  function update_wx_info() {
    $rs = [];
    $token = WX::token();
    $id = $this->session_get('wx_id');
    $user_id = Db::connect('ah_admin')->table('wx_id')->where('id', $id)->findOrEmpty();
    if (empty($user_id)) return json([]);
    $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token='.$token.'&userid=' . $user_id['userid'];

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $rs = json_decode(curl_exec($curl), true);
    curl_close($curl);

    $updateArr = [
      'avatar',
      'name',
      'mobile',
      'email',
      'biz_mail',
      'gender',
      'position',
    ];
    /*
      userid var_char 64
      avatar var_char 140
      name  var_char 10
      mobile var_char 12
      email 
      biz_mail
      gender 0表示未定义，1表示男性，2表示女性
      position
    */
    return json($rs);
  }

  function set_wx() {
    
  }
}
