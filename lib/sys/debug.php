<?php

/**
 * Lib_Sys_Debug
 * @author jxu
 * @package system_lib_sys
 */

/**
 * 系统调试
 *
 * @author jxu
 *        
 */
class Lib_Sys_Debug
{

    /**
     * 实例自身
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 获取实例
     *
     * @return object
     */
    static function getInstance()
    {
        if (! self::$_oInstance instanceof self) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 实例化
     */
    protected function __construct()
    {}

    /**
     * 克隆
     */
    protected function __clone()
    {}
}