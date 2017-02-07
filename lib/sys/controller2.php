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
     * 获取配置
     *
     * @param string $p_sKey            
     * @param string $p_sClass            
     * @return mix
     */
    protected function getConfig($p_sKey, $p_sClass = 'common')
    {
        return lib_sys_var::getInstance()->getConfig($p_sKey, $p_sClass);
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
     * @param string $p_aContent            
     * @param string $p_sClass            
     */
    protected function addLog($p_aContent, $p_sClass = 'common')
    {}
    
    /**
     * 开始调试
     * @param string $p_sModule
     */
    protected function startDebug($p_sModule){
        self::$_aAllPri['oDebugger']->startDebug($p_sModule);
    }
    
    /**
     * 发送调试信息
     * @param string $p_sMsg
     */
    protected function showDebugMsg($p_sMsg){
        self::$_aAllPri['oDebugger']->showMsg($p_sMsg);
    }
    
    /**
     * 结束调试
     * @param string $p_sModule
     */
    protected function stopDebug($p_sModule){
        self::$_aAllPri['oDebugger']->stopDebug($p_sModule);
    }
}