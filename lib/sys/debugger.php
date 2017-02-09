<?php

/**
 * lib_sys_debugger
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_debugger
 *
 * @author jxu
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
    private $_aMessage = array();

    /**
     * 调试信息
     *
     * @var array
     */
    private $_aDebugInfo = array();

    /**
     * 是否开启debug
     *
     * @var boolean
     */
    private $_bolNeedDebug = false;

    /**
     * 开始时间
     *
     * @var float
     */
    private $_fStartTime;

    /**
     * 系统变量
     *
     * @var object
     */
    private $_oVari;

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->_oVari = lib_sys_var::getInstance();
        if ($this->_oVari->getConfig('debug', 'debugger')) { // 系统配置
            $aAllowIPs = $this->_oVari->getConfig('aAllowedIps', 'debugger'); // ip过滤
            $sIP = $this->_oVari->getParam('CLIENTIP', 'server');
            $bCanIP = false;
            foreach ($aAllowIPs as $sPattern) {
                if (preg_match($sPattern, $sIP)) {
                    $bCanIP = true;
                    break;
                }
            }
            if ($bCanIP) {
                $iCanCookie = $this->_oVari->getParam('debug', 'cookie'); // cookie过滤
                if (null === $iCanCookie) {
                    $this->_bolNeedDebug = false;
                } else {
                    if (1 == $iCanCookie) {
                        $iCanGet = $this->_oVari->getParam('debug', 'get'); // get过滤
                        if (null === $iCanGet) {
                            $this->_bolNeedDebug = false;
                        } else {
                            $this->_bolNeedDebug = true;
                            $this->_fStartTime = $this->_oVari->getRealTime(true);
                            util_sys_cookie::setCookie('debug', 1, 60);
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
     * 添加输出信息
     *
     * @param string $p_sMsg            
     * @param boolean $p_bIsHTML            
     */
    function showMsg($p_sMsg, $p_bIsHTML = false)
    {
        if ($this->_bolNeedDebug) {
            $this->_aMessage[] = array(
                'fTime' => $this->_oVari->getRealTime(true),
                'bIsHTML' => $p_bIsHTML,
                'sMsg' => $p_sMsg
            );
        }
    }

    /**
     * 开始调试信息
     *
     * @param string $p_sModule            
     */
    function startDebug($p_sModule)
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule]['fStartTime'] = $this->_oVari->getMicroTime();
            $this->_aDebugInfo[$p_sModule]['iStartMemory'] = $this->_getMemoryUsage();
        }
    }

    /**
     * 结束调试信息
     *
     * @param string $p_sModule            
     */
    function stopDebug($p_sModule = '')
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule]['fEndTime'] = $this->_oVari->getMicroTime();
            $this->_aDebugInfo[$p_sModule]['iEndMemory'] = $this->_getMemoryUsage();
        }
    }

    /**
     * 返回输出信息
     *
     * @return array
     */
    function getMsgs()
    {
        return $this->_aMessage;
    }

    /**
     * 返回调试信息
     *
     * @return array
     */
    function getDebugInfo()
    {
        return $this->_aDebugInfo;
    }

    /**
     * 获取内存使用量
     */
    private function _getMemoryUsage()
    {
        return function_exists('memory_get_usage') ? memory_get_usage() : 0;
    }

    /**
     * 是否能够debug
     *
     * @return true/false
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
    function getParams()
    {
        return array(
            'aPost' => $this->_oVari->getParams('post'),
            'aGet' => $this->_oVari->getParams('get'),
            'aRouter' => $this->_oVari->getParams('router'),
            'aCookie' => $this->_oVari->getParams('cookie'),
            'aServer' => $this->_oVari->getParams('server'),
            'aConfig' => $this->_oVari->getParams('config')
        );
    }
}