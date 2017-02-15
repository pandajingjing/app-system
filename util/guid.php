<?php

/**
 * util_guid
 * @author jxu
 * @package system_util
 */

/**
 * util_guid
 *
 * @author jxu
 *        
 */
class util_guid
{

    /**
     * 获取GUID
     *
     * @return string
     */
    static function getGuid($p_sJoin = '')
    {
        mt_srand(lib_sys_var::getInstance()->getRealTime());
        $sRaw = md5(uniqid(rand(), true));
        return substr($sRaw, 0, 8) . $p_sJoin . substr($sRaw, 8, 4) . $p_sJoin . substr($sRaw, 12, 4) . $p_sJoin . substr($sRaw, 16, 4) . $p_sJoin . substr($sRaw, 20);
    }
}