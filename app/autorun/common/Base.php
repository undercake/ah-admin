<?php
/*
 * @Author: Undercake
 * @Date: 2023-08-15 03:08:54
 * @LastEditTime: 2023-08-15 03:10:51
 * @FilePath: /ahadmin/app/autorun/common/Base.php
 * @Description: 
 */
namespace app\autorun\common;

use app\BaseController;

class Base extends BaseController
{
    function __construct()
    {
        isset($_SERVER['HTTP_X_FORWARDED_FOR']) && halt('Access denied');
    }
}