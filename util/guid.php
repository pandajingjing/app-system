<?php

/**
 * util_guid
 *
 * guid工具,用于生成guid,可以被业务使用
 *
 * @package util
 */

/**
 * util_guid
 *
 * guid工具,用于生成guid,可以被业务使用
 */
class util_guid
{

    /**
     * 获取guid
     *
     * 生成符合规范的guid
     *
     * @param string $p_sJoin            
     * @return string
     */
    static function getGuid($p_sJoin = '')
    {
        mt_srand(lib_sys_var::getInstance()->getRealTime());
        $sRaw = md5(uniqid(rand(), true));
        return substr($sRaw, 0, 8) . $p_sJoin . substr($sRaw, 8, 4) . $p_sJoin . substr($sRaw, 12, 4) . $p_sJoin . substr($sRaw, 16, 4) . $p_sJoin . substr($sRaw, 20);
    }
}