<?php
/*
 * @Author: Undercake
 * @Date: 2023-09-20 05:21:33
 * @LastEditTime: 2023-10-06 08:27:28
 * @FilePath: /ahadmin/app/employee/common/Base.php
 * @Description: 
 */


namespace app\employee\common;

use app\BaseController;
use app\common\Session;

class Base extends BaseController
{
    protected $session;
    function __construct()
    {
        $this->session = new Session('employee');
    }
}
