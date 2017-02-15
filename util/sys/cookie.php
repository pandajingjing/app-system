<?php

/**
 * util_sys_cookie
 * @author jxu
 * @package system_util_sys
 */

/**
 * 系统cookie工具
 *
 * @author jxu
 *        
 */
class util_sys_cookie
{

    /**
     * 需要发送的cookie
     *
     * @var array
     */
    private static $_aSendCookies = [];

    /**
     * 获取所有cookie
     *
     * @return array
     */
    static function getCookie()
    {
        return $_COOKIE;
    }

    /**
     * 写cookie
     *
     * @param string $p_sName            
     * @param string $p_sValue            
     * @param int $p_iExpireTime            
     * @param string $p_sPath            
     * @param string $p_sDomain            
     */
    static function setCookie($p_sName, $p_sValue, $p_iExpireTime, $p_sPath = '/', $p_sDomain = '')
    {
        self::$_aSendCookies[] = [
            $p_sName,
            $p_sValue,
            $p_iExpireTime,
            $p_sPath,
            $p_sDomain
        ];
    }

    /**
     * 发送cookie
     */
    static function sendCookies()
    {
        foreach (self::$_aSendCookies as $aCookie) {
            setcookie($aCookie[0], $aCookie[1], $aCookie[2], $aCookie[3], $aCookie[4]);
        }
    }
}