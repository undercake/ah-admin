<?php

namespace app\common;
class WX
{

    public static function token($s = 'access_token')
    {
        $curl = curl_init('workwx.kmahjz.com.cn:99/get/' . $s);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $rs = curl_exec($curl);
        curl_close($curl);
        return $rs;
    }
}
