<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-24 13:59:15
 * @LastEditTime: 2023-03-24 15:29:42
 * @FilePath: /tp6/app/midas/controller/Uploader.php
 * @Description: 
 */

namespace app\midas\controller;

use app\midas\common\Common;
use think\facade\Filesystem;

class Uploader extends Common
{
  private $file;
  public function __construct()
  {
    $this->file = request()->file('file');
  }
  public function public()
  {
    $file_path = Filesystem::disk('public')->putFile(date('Ym', time()), $this->file, 'md5');
    return $this->succ(['path' => '/upload' . '/' . $file_path]);
  }
  public function private()
  {
    $file_path = Filesystem::disk('private')->putFile(date('Ym', time()), $this->file, 'md5');
    return $this->succ(['path' => '/upload' . '/' . $file_path]);
  }
}
