<?php
/*
 * @Author: undercake
 * @Date: 2023-03-04 16:43:31
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2023-10-06 08:45:18
 * @FilePath: /ahadmin/app/midas/common/Common.php
 * @Description: 公共类
 */

namespace app\midas\common;

use app\common\LoginController;

class Common extends LoginController
{

  public function __construct()
  {
    parent::__construct(
      'midas',
      '__midas__',
      [
        'user/wx_before_login_redirect',
        'user/wx_after_login_redirect',
        'user/is_wx_scanned',
        'user/is_wx_loggedin',
        'user/wx_redirect',
        'user/wx_login',
        'user/login',
        'user/logged',
        'user/test',
        'cap/get',
      ],
      [
        'my',
        'user'
      ]
    );
  }
}
