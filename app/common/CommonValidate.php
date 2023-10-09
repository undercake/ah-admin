<?php
/*
 * @Author: Undercake
 * @Date: 2023-10-09 03:58:16
 * @LastEditTime: 2023-10-09 05:10:37
 * @FilePath: /ahadmin/app/common/CommonValidate.php
 * @Description: 
 */

namespace app\common;

use think\Validate;

class CommonValidate extends Validate
{
    function __construct()
    {
        parent::__construct();
    }
    /**
     * 定义验证规则
     * 格式：'字段名'=>['规则1','规则2'...]
     *
     * @var array
     */
    protected function telOrMobile($value, $rule, $data = [])
    {
        $tel = '/^(0\d{2,3})?(-)?\d{7,8}$/';
        $mobile = '/^1[3-9]\d{9}$/';
        return preg_match($tel, $value) || preg_match($mobile, $value) ? true : false;
    }
}
