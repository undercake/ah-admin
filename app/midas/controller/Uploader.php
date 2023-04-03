<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-24 13:59:15
 * @LastEditTime: 2023-03-27 17:08:39
 * @FilePath: /tp6/app/midas/controller/Uploader.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Filesystem;
use think\facade\Request;

class Uploader extends Common
{
  private $file;
  public function __construct()
  {
    $this->file = request()->file('file');
  }
  public function public()
  {
    try {
      $file_path = Filesystem::disk('public')->putFile(date('Ym', time()), $this->file, 'md5');
    } catch (\Throwable $th) {
      return $this->err(['data' => Request::post(), 'req' => request(), 'message' => $th->getMessage()]);
    }
    return $this->succ(['path' => '/upload' . '/' . $file_path]);
  }
  public function private()
  {
    $file_path = Filesystem::disk('private')->putFile(date('Ym', time()), $this->file, 'md5');
    return $this->succ(['path' => '/upload' . '/' . $file_path]);
  }
}
