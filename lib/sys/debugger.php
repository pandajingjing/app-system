<?php

/**
 * lib_sys_debugger
 *
 * 系统调试器类
 *
 * @package lib_sys
 */

/**
 * lib_sys_debugger
 *
 * 系统调试器类
 */
class lib_sys_debugger
{

    /**
     * 系统debug实例
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 需要输出的信息
     *
     * @var array
     */
    private $_aMessages = [];

    /**
     * 调试信息
     *
     * @var array
     */
    private $_aDebugInfo = [];

    /**
     * 是否开启debug
     *
     * @var boolean
     */
    private $_bolNeedDebug = false;

    /**
     * 系统变量
     *
     * @var object
     */
    private $_oVari;

    /**
     * 构造函数
     *
     * @return void
     */
    private function __construct()
    {
        $this->_oVari = lib_sys_var::getInstance();
        if ($this->_oVari->getConfig('bDebug', 'debugger')) { // 系统配置
            $aAllowIPs = $this->_oVari->getConfig('aAllowedIpList', 'debugger'); // ip过滤
            $sIP = $this->_oVari->getParam('CLIENTIP', 'server');
            $bCanIP = false;
            foreach ($aAllowIPs as $sPattern) {
                if (preg_match($sPattern, $sIP)) {
                    $bCanIP = true;
                    break;
                }
            }
            if ($bCanIP) {
                $iCanCookie = $this->_oVari->getParam('debug', 'cookie', 'int'); // cookie过滤
                $iCanGet = $this->_oVari->getParam('debug', 'get', 'int'); // get过滤
                $iExpireTime = $this->_oVari->getRealTime() + 60;
                if (null === $iCanCookie) {
                    if (5 == $iCanGet) {
                        util_sys_cookie::setCookie('debug', 1, $iExpireTime);
                        $this->_bolNeedDebug = true;
                    } else {
                        $this->_bolNeedDebug = false;
                    }
                } else {
                    if (1 == $iCanCookie) {
                        if (null === $iCanGet) {
                            $this->_bolNeedDebug = true;
                            util_sys_cookie::setCookie('debug', 1, $iExpireTime);
                        } else {
                            if (5 == $iCanGet) {
                                $this->_bolNeedDebug = true;
                                util_sys_cookie::setCookie('debug', 1, $iExpireTime);
                            } else {
                                $this->_bolNeedDebug = false;
                                util_sys_cookie::setCookie('debug', 0, $iExpireTime);
                            }
                        }
                    } else {
                        $this->_bolNeedDebug = false;
                    }
                }
            } else {
                $this->_bolNeedDebug = false;
            }
        } else {
            $this->_bolNeedDebug = false;
        }
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
     * 添加输出信息
     *
     * @param string $p_sMsg            
     * @param boolean $p_bIsHTML            
     * @return void
     */
    function showMsg($p_sMsg, $p_bIsHTML = false)
    {
        if ($this->_bolNeedDebug) {
            $this->_aMessages[] = [
                'bIsHTML' => $p_bIsHTML,
                'sMsg' => $p_sMsg
            ];
        }
    }

    /**
     * 开始模块调试信息
     *
     * @param string $p_sModule            
     * @return void
     */
    function startDebug($p_sModule)
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule] = [
                'fStartTime' => $this->_oVari->getRealTime(true),
                'iStartMemory' => $this->_getMemoryUsage()
            ];
        }
    }

    /**
     * 结束模块调试信息
     *
     * @param string $p_sModule            
     * @return void
     */
    function stopDebug($p_sModule = '')
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule]['fEndTime'] = $this->_oVari->getRealTime(true);
            $this->_aDebugInfo[$p_sModule]['iEndMemory'] = $this->_getMemoryUsage();
        }
    }

    /**
     * 获取输出信息
     *
     * @return array
     */
    function getMsgs()
    {
        return $this->_aMessages;
    }

    /**
     * 获取调试信息
     *
     * @return array
     */
    function getDebugInfo()
    {
        return $this->_aDebugInfo;
    }

    /**
     * 获取内存使用量
     *
     * @return int
     */
    private function _getMemoryUsage()
    {
        return function_exists('memory_get_usage') ? memory_get_usage() : 0;
    }

    /**
     * 是否能够debug
     *
     * @return true|false
     */
    function canDebug()
    {
        return $this->_bolNeedDebug;
    }

    /**
     * 获取系统参数
     *
     * @return array
     */
    function getAllParam()
    {
        return [
            'aPost' => $this->_oVari->getAllParams('post'),
            'aGet' => $this->_oVari->getAllParams('get'),
            'aRouter' => $this->_oVari->getAllParams('router'),
            'aCookie' => $this->_oVari->getAllParams('cookie'),
            'aServer' => $this->_oVari->getAllParams('server'),
            'aConfig' => $this->_oVari->getAllParams('config')
        ];
    }
}