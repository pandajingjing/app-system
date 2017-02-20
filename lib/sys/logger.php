<?php

/**
 * lib_sys_logger
 *
 * 系统日志类
 *
 * @package lib_sys
 */

/**
 * lib_sys_logger
 *
 * 系统日志类
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
     *
     * @return void
     */
    private function __construct()
    {
        $this->_sBaseDir = lib_sys_var::getInstance()->getConfig('sBaseDir', 'logger');
    }

    /**
     * 克隆函数
     *
     * @return void
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
     * @param string $p_sTitle            
     * @param string $p_sContent            
     * @param string $p_sClass            
     * @return void
     */
    function addLog($p_sTitle, $p_sContent, $p_sClass = 'common')
    {
        if (isset($this->_aLog[$p_sClass])) {
            $this->_aLog[$p_sClass][] = [
                date('YmdHis'),
                $p_sTitle,
                $p_sContent
            ];
        } else {
            $this->_aLog[$p_sClass] = [
                [
                    date('YmdHis'),
                    $p_sTitle,
                    $p_sContent
                ]
            ];
        }
    }

    /**
     * 记录日志
     *
     * @return void
     */
    function writeLog()
    {
        foreach ($this->_aLog as $sClass => $aLogs) {
            $sDir = $this->_sBaseDir . DIRECTORY_SEPARATOR . PANDA_LOADER . '_' . PANDA_REQUEST_TYPE;
            if (! is_dir($sDir)) {
                util_file::tryMakeDir($sDir, 0755, true);
            }
            $sFileName = $sDir . DIRECTORY_SEPARATOR . $sClass . '.log';
            foreach ($aLogs as $aLog) {
                util_file::tryWriteFile($sFileName, $aLog[0] . "\t" . $aLog[1] . ': ' . $aLog[2] . PHP_EOL);
            }
        }
    }
}
