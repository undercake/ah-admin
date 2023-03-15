<?php
/*
 * @Author: Undercake
 * @Date: 2023-03-12 11:11:39
 * @LastEditTime: 2023-03-12 11:23:19
 * @FilePath: /tp6/app/tool/controller/Pinyin.php
 * @Description: 拼音
 */

namespace app\tool\controller;

use Overtrue\Pinyin\Pinyin as PinyinPinyin;
use think\facade\Request;

class Pinyin
{
  public function generate()
  {
    $han = Request::post()['han'];
    $py = PinyinPinyin::sentence($han, 'none')->join('');
    $nm = PinyinPinyin::name($han, 'none')->join('');
    $ca = PinyinPinyin::abbr($han)->join('');
    return json(['status' => 'success', 'py' => $py, 'name' => $nm, 'cap' => $ca]);
  }
}