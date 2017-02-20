<?php

/**
 * util_sys_cookie
 *
 * 获取和发送cookie的工具,应避免应用直接使用.如有需要可以在这里对读写cookie进行封装
 *
 * @package util_sys
 */

/**
 * util_sys_cookie
 *
 * 获取和发送cookie的工具,应避免应用直接使用.如有需要可以在这里对读写cookie进行封装
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
     * 需要发送给客户端的cookie
     *
     * @param string $p_sName            
     * @param string $p_sValue            
     * @param int $p_iExpireTime            
     * @param string $p_sPath            
     * @param string $p_sDomain            
     * @return void
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
     *
     * 应在页面输出任何内容前发送
     *
     * @return void
     */
    static function sendCookies()
    {
        foreach (self::$_aSendCookies as $aCookie) {
            setcookie($aCookie[0], $aCookie[1], $aCookie[2], $aCookie[3], $aCookie[4]);
        }
    }
}