<?php

/**
 * lib_sys_controller
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_controller
 *
 * @author jxu
 */
abstract class lib_sys_controller
{

    /**
     * 构造函数
     */
    function __construct()
    {
        // parent::__construct();
    }

    /**
     * 在控制器开始时执行（调度使用）
     */
    function beforeRequest()
    {
        // parent::beforeRequest();
        // do something
        lib_sys_logger::getInstance()->addLog('controller parameter', json_encode(lib_sys_debugger::getInstance()->getAllParams(), JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE), 'parameter');
    }

    /**
     * 在控制器结束时执行（调度使用）
     */
    function afterRequest()
    {
        // do something
        // parent::afterRequest();
    }

    /**
     * 控制器入口函数
     */
    abstract function doRequest();

    /**
     * 获取参数
     *
     * @param string $p_sKey            
     * @param string $p_sMethod            
     * @param string $p_sType            
     * @param mix $p_mDefault            
     * @return mix
     */
    protected function getParam($p_sKey, $p_sMethod, $p_sType = '', $p_mDefault = null)
    {
        $mValue = lib_sys_var::getInstance()->getParam($p_sKey, $p_sMethod);
        if ('' == $p_sType) {
            return $mValue;
        } else {
            if (util_string::chkDataType($mValue, $p_sType)) {
                return $mValue;
            } else {
                return $p_mDefault;
            }
        }
    }

    /**
     * 获取请求时间
     *
     * @param boolean $p_bFloat            
     * @return float/int
     */
    protected function getVisitTime($p_bFloat = false)
    {
        return lib_sys_var::getInstance()->getVisitTime($p_bFloat);
    }

    /**
     * 获取当前时间
     *
     * @param boolean $p_bFloat            
     * @return float/int
     */
    protected function getRealTime($p_bFloat = false)
    {
        return lib_sys_var::getInstance()->getRealTime($p_bFloat);
    }

    /**
     * 添加日志
     *
     * @param string $p_sTitle            
     * @param string $p_sContent            
     * @param string $p_sClass            
     */
    protected function addLog($p_sTitle, $p_sContent, $p_sClass = 'common')
    {
        lib_sys_logger::getInstance()->addLog($p_sTitle, $p_sContent, $p_sClass);
    }

    /**
     * 开始调试
     *
     * @param string $p_sModule            
     */
    protected function startDebug($p_sModule)
    {
        lib_sys_debugger::getInstance()->startDebug($p_sModule);
    }

    /**
     * 发送调试信息
     *
     * @param string $p_sMsg            
     * @param boolean $p_bIsHTML            
     */
    protected function showDebugMsg($p_sMsg, $p_bIsHTML = false)
    {
        lib_sys_debugger::getInstance()->showMsg($p_sMsg, $p_bIsHTML);
    }

    /**
     * 结束调试
     *
     * @param string $p_sModule            
     */
    protected function stopDebug($p_sModule)
    {
        lib_sys_debugger::getInstance()->stopDebug($p_sModule);
    }
}