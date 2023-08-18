<?php
/*
 * @Author: Undercake
 * @Date: 2023-08-17 08:07:33
 * @LastEditTime: 2023-08-17 08:16:09
 * @FilePath: /ahadmin/app/midas/common/MemberInfo.php
 * @Description: get member info
 */

namespace app\midas\common;
class MemberInfo
{
    private $token = '';

    function __construct()
    {
        $this->token = WX::token();
    }

    public function get(string $id = '')
    {
        $curl = curl_init('https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token='. $this->token .'&userid=' . $id);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $rs = curl_exec($curl);
        curl_close($curl);
        return json_decode($rs, true);
    }

    function getList(Array $input = []) {
        if (empty($input)) return [];
        $rs = [];
        foreach($input as $v)
            $rs[] = $this->get($v);
        return $rs;
    }
}

