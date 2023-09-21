<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:38:59
 * @LastEditTime: 2023-09-07 02:04:57
 * @FilePath: /ahadmin/app/midas/controller/User.php
 * @Description: 登录类
 */

namespace app\midas\controller;

use think\facade\Db;
use app\common\QRcode;
use app\common\WX;
use think\facade\Config;
use think\facade\Request;
use app\midas\common\Common;

class User extends Common
{
    private function get_last_sync() {
        return Db::connect('ah')->table('last_sync')->where('id', 1)->findOrEmpty()['time'];
    }

    private function change_other_session(string $cookie, $data = []) {
        $sql = Db::connect('ah')->table('session')->where('cookie', $cookie . '____midas__');

        $beforeSql = $sql->findOrEmpty();
        if (empty($beforeSql)) return false;

        $beforeInfo = json_decode($beforeSql['value'], true);
        foreach ($data as $k => $v) {
            $beforeInfo[$k] = $v;
        }
        return $sql->update(['value' => json_encode($beforeInfo)]);
    }

    public function login()
    {
        $data = Request::post();

        // 验证码
        $captcha = trim($data['captcha']);
        $cap = $this->session_get('captcha');
        if (!$cap || $captcha == '' || !password_verify(mb_strtolower($data['captcha'], 'UTF-8'), $cap)) {
            return $this->err(['message' => '验证码错误', 'c' => $this->session_get('captcha'), $cap]);
        }

        $this->session_del('captcha');
        $sql = Db::connect('ah_admin')->name('operator')->where(['user_name' => $data['username'], 'deleted' => 0]);
        if (preg_match("/^1[3456789]\d{9}$/", $data['username'])) {
            $sql->whereOr('mobile', $data['username']);
        }
        if (preg_match("/^[A-Za-z0-9\x80-\xff]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/", $data['username'])) {
            $sql->whereOr('email', $data['username']);
        }
        $rs = $sql->findOrEmpty();
        if (empty($rs) || !(sha1($data['passwordMd5'] . $rs['salt']) == $rs['password'])) {
            return $this->err(['message' => '账号密码不正确']);
        }
        if ($rs['wx_id'] != 0);
            $rs_wx = Db::connect('ah_admin')->table('wx_id')->where('id', $rs['wx_id'])->findOrEmpty();
        $this->session_set('avatar', $rs['wx_id'] == 0 ? '' : $rs_wx['avatar']);
        $this->session_set('is_login', true);
        $this->session_set('id', $rs['id']);
        $this->session_set('username', $rs['user_name']);
        $this->session_set('nickname', $rs['full_name']);
        $this->session_set('group', $rs['user_group']);
        $this->session_set('mobile', $rs['mobile']);
        $this->session_set('wx_id', $rs['wx_id']);

        $group = $this->setRights()[1];

        return $this->succ(['message' => '登录成功！', 'nickname' => $rs['full_name'], 'group' => $group['name']]);
    }

    private function setRights()
    {
        $rights_list = Db::connect('ah_admin')->name('groups')->field('rights,name')->where('id', $this->session_get('group'))->find();
        $rights = Db::connect('ah_admin')->name('rights')->where([
            ['id', 'IN', $rights_list['rights']],
            ['type', '<>', 4]
        ])->order('sort', 'ASC')->select();
        $r = [];
        foreach ($rights as $v) {
            if (in_array($v['type'], [0, 1])) {
                $r[] = $v['path'];
            }
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
        return $this->succ(['rights' => $rights, 'last_sync' => $this->get_last_sync()]);
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

    public function heart_beat()
    {
        return $this->succ(['message' => 'ok', 'last_sync' => $this->get_last_sync()]);
    }

    public function wx_login() {
        $this->session_set('is_login_wx_scanned', false);
        $this->session_set('wx_scanned_id', false);
        $url = 'https://manage.kmahjz.com.cn/midas/user/wx_before_login_redirect/key/' . $this->sess->key();
        return (new QRcode($url, 500))->logo(root_path() . '/asserts/wxwork.png')->print();
    }

    // 扫码指向这里，确认扫码后跳转微信
    public function wx_before_login_redirect(string $key) {
        $corpId = Config::get('workwx.corpid');

        if (!$this->change_other_session($key, ['is_login_wx_scanned' => true])) return json(['参数错误', $key]);

        $redirect_uri = urlencode('https://workwx.kmahjz.com.cn/redir');
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='. $corpId . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_privateinfo&state='.$key.'&agentid=1000003#wechat_redirect';
        return redirect($url);
    }
    // 微信跳转回来
    public function wx_after_login_redirect(string $code, string $state) {
        $token = WX::token();
        $rs = json_decode($this->get_curl_data('https://qyapi.weixin.qq.com/cgi-bin/auth/getuserinfo?access_token=' . $token . '&code=' . $code), true);
        if ($rs['errcode'] === 0) {
            $rs = json_decode($this->get_curl_data('https://qyapi.weixin.qq.com/cgi-bin/auth/getuserdetail?access_token=' . $token, json_encode(['user_ticket' => $rs['user_ticket']])), true);
            $rs['exter'] = json_decode($this->get_curl_data('https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=' . $token . '&userid=' . $rs['userid']), true);
        }
        $rs_sql = '';
        if ($rs['errcode'] !== 0) return '出错了：' . $rs['errcode'];
        $sql = Db::connect('ah_admin')
            ->table('wx_id');
        $count = $sql->
            where('userid', $rs['userid'])
            ->count();
        if ($count >= 1) {
            $rs_sql = $sql
            ->where('userid', $rs['userid'])
            ->update([
                'avatar'   => $rs['avatar'],
                'mobile'   => $rs['mobile'],
                'gender'   => $rs['gender'],
                'email'    => $rs['email'],
                'biz_mail' => $rs['biz_mail'],
                'name'     => $rs['exter']['name'],
                'position' => $rs['exter']['position'],
            ]);
        } else {
            $rs_sql = $sql->insert([
                'avatar'   => $rs['avatar'],
                'userid'   => $rs['userid'],
                'mobile'   => $rs['mobile'],
                'gender'   => $rs['gender'],
                'email'    => $rs['email'],
                'biz_mail' => $rs['biz_mail'],
                'name'     => $rs['exter']['name'],
                'position' => $rs['exter']['position'],
            ]);
        }

        $rs_sql = $sql->where('userid', $rs['userid'])->field('id')->findOrEmpty();
        $this->change_other_session($state, ['wx_scanned_id' => $rs_sql['id']]);

        return view();
    }

    function is_wx_scanned() {
        $rs = $this->session_get('is_login_wx_scanned', false);
        return $this->succ(['status' => $rs]);
    }

    public function is_wx_loggedin()
    {
        $rs = $this->session_get('wx_scanned_id', false);
        if ($rs === false) return $this->succ(['logged_in' => false]);
        $rs_operator = Db::connect('ah_admin')->table('operator')->where('wx_id', $rs)->findOrEmpty();
        if (empty($rs_operator)) {
            return $this->err(['message' => '此微信账号未绑定任何管理员账号！']);
        }

        $rs_wx = Db::connect('ah_admin')->table('wx_id')->where('id', $rs)->findOrEmpty();
        $this->session_del('is_login_wx_scanned');
        $this->session_del('wx_scanned_id');
        $this->session_del('is_login_wx_scanned');
        $this->session_del('is_login_wx_scanned');
        $this->session_set('avatar', $rs_wx['avatar']);
        $this->session_set('is_login', true);
        $this->session_set('id', $rs_operator['id']);
        $this->session_set('username', $rs_operator['user_name']);
        $this->session_set('nickname', $rs_operator['full_name']);
        $this->session_set('group', $rs_operator['user_group']);
        $this->session_set('mobile', $rs_operator['mobile']);
        $this->session_set('wx_id', $rs_operator['wx_id']);
        return $this->succ(['logged_in' => true]);
    }
}