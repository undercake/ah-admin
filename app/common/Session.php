<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-23 15:19:56
 * @LastEditTime: 2023-03-23 16:00:15
 * @FilePath: /tp6/app/common/Session.php
 * @Description: session类
 */

namespace app\common;

use think\facade\Config;
use think\facade\Db;

class Session
{
  private $cookie;
  private $expire;
  private $divider;
  private $tmpData = [];

  public function __construct($divider = '')
  {
    $config = Config::get('session');
    session_name($config['name']);
    session_start();
    $this->divider = $divider;
    $this->cookie  = session_id();
    $this->expire  = $config['expire'];

    // Db::name('session')->save(['cookie' => $this->cookie . '__' . $this->divider, 'expire' => time() + $this->expire]);
    $data = Db::name('session')->where([['cookie', '=', $this->cookie . '__' . $this->divider], ['expire', '>', time()]])->find();
    $this->tmpData = isset($data['value']) ? json_decode($data['value'], true) : [];
  }

  public function __destruct()
  {
    $db = Db::name('session');
    if (count($this->tmpData) == 0) $rs = $db->where(['cookie' => $this->cookie . '__' . $this->divider])->delete();
    else {
      $count = $db->where(['cookie' => $this->cookie . '__' . $this->divider])->count();
      $data = ['cookie' => $this->cookie . '__' . $this->divider, 'value' => json_encode($this->tmpData), 'expire' => time() + $this->expire];
      $rs = $count == 0 ? $db->insert($data) : $db->update($data);
    }
    Db::name('session')->where([['expire', '<', time() - $this->expire]])->delete();
  }

  public function get($key, $default = null)
  {
    return $this->tmpData[$key] ?? $default;
  }

  public function set($key, $value)
  {
    return $this->tmpData[$key] = $value;
  }

  public function delete($key)
  {
    unset($this->tmpData[$key]);
  }

  public function has($key)
  {
    return is_null($this->tmpData[$key]);
  }

  public function clear()
  {
    $this->tmpData = [];
  }
}
