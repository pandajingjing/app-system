<?php

/**
 * lib_sys_logger
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_logger
 *
 * @author jxu
 */
class lib_sys_logger
{

    /**
     * 系统日志实例
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 日志文件根目录
     *
     * @var string
     */
    private $_sBaseDir = '';

    /**
     * 日志内容
     *
     * @var array
     */
    private $_aLog = [];

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->_sBaseDir = lib_sys_var::getInstance()->getConfig('base_dir', 'logger');
    }

    /**
     * 构造函数
     */
    private function __clone()
    {}

    /**
     * 获取实例
     *
     * @return object
     */
    static function getInstance()
    {
        if (! (self::$_oInstance instanceof self)) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 添加日志
     *
     * @param string $p_sContent            
     * @param string $p_sClass            
     */
    function addLog($p_sContent, $p_sClass = 'custom')
    {
        if (! isset($this->_aLog[$p_sClass])) {
            $this->_aLog[$p_sClass] = [];
        }
        $this->_aLog[$p_sClass][] = [
            date('YmdHis'),
            $p_sContent
        ];
    }

    /**
     * 记录日志
     */
    function writeLog()
    {
        $sLog = var_export($p_aContent, true);
        $sLog = date('YmdHis', $this->_oVari->getRealTime()) . $sLog . PHP_EOL;
        file_put_contents($sLogFile, $sLog, FILE_APPEND);
    }
}
