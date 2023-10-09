<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-10-06 08:48:59
 * @FilePath: /ahadmin/app/common/LoginController.php
 * @Description: 公共类
 */

namespace app\common;

use app\BaseController;
use app\common\Session as CommonSession;
use think\facade\Request;

class LoginController extends BaseController
{
    protected $app                           = '';
    protected $controller                    = '';
    protected $action                        = '';
    protected $ip                            = '';
    protected $cookie_divider                = '';
    protected $do_not_need_login             = [];
    protected $controllers_do_not_need_right = [];
    protected $sess;

    /**
     * @description: 构造函数
     * @param string $App 应用名
     * @param string $cookie_divider cookie分隔符
     * @param array $do_not_need_login 不需要登录的控制器
     * @param array $controllers_do_not_need_right 不需要权限的控制器
     * @return void
     */
    public function __construct(
        string $App,
        string $cookie_divider = '',
        array $do_not_need_login = [],
        array $controllers_do_not_need_right = []
    ) {
        $this->cookie_divider = $cookie_divider;
        $this->app = $App;
        $this->do_not_need_login = $do_not_need_login;
        $this->controllers_do_not_need_right = $controllers_do_not_need_right;
        $this->controller = Request::controller(true);
        $this->action = Request::action(true);
        $this->ip = $_SERVER['HTTP_X_REAL_IP'];
        $this->sess = new CommonSession($this->cookie_divider);
        $this->shall_pass();
    }

    /**
     * @description: 检查是否允许访问
     * @param void|true
     */
    private function shall_pass()
    {
        if (in_array($this->controller . '/' . $this->action, $this->do_not_need_login)) {
            return true;
        }

        $logged = $this->is_logged_in();
        if (!$logged) {
            die(json_encode(['code' => -2, 'is_login' => false, 'message' => '您尚未登录，请登录后再试！']));
        } else if (in_array($this->controller, $this->controllers_do_not_need_right)) {
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
