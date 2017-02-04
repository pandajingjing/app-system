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
    private static $_aSendCookies = array();

    /**
     * 获取所有cookie
     *
     * @return array
     */
    static function getCookies()
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
     */
    static function setCookie($p_sName, $p_sValue, $p_iExpireTime, $p_sPath = '/')
    {
        self::$_aSendCookies[] = array(
            $p_sName,
            $p_sValue,
            $p_iExpireTime,
            $p_sPath
        );
    }

    /**
     * 发送cookie
     */
    static function sendCookies()
    {
        foreach (self::$_aSendCookies as $aCookie) {
            setcookie($aCookie[0], $aCookie[1], $aCookie[2], $aCookie[3]);
        }
    }
}