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
        $oVar = lib_sys_var::getInstance();
        $sRaw = md5(strtolower($oVar->getParam('HTTP_USER_AGENT', 'server') . '/' . $oVar->getParam('CLIENTIP', 'server')) . ':' . $oVar->getVisitTime(true) . ':' . self::_getLong());
        return substr($sRaw, 0, 8) . $p_sJoin . substr($sRaw, 8, 4) . $p_sJoin . substr($sRaw, 12, 4) . $p_sJoin . substr($sRaw, 16, 4) . $p_sJoin . substr($sRaw, 20);
    }

    /**
     * 获取整长型数
     *
     * @return long
     */
    private static function _getLong()
    {
        $tmp = rand(0, 1) ? '-' : '';
        return $tmp . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(100, 999) . rand(100, 999);
    }
}