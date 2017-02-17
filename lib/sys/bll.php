<?php

/**
 * lib_sys_bll
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_bll
 *
 * @author jxu
 */
class lib_sys_bll
{

    /**
     * 构造函数
     */
    function __construct()
    {}

    /**
     * 返回成功数据
     *
     * @param array $p_aData            
     * @return array
     */
    protected function returnSuccess($p_aData)
    {
        return util_sys_response::returnSuccess($p_aData);
    }

    /**
     * 返回失败数据
     *
     * @param array $p_aErrors            
     * @return array
     */
    protected function returnError($p_aErrors)
    {
        return util_sys_response::returnError($p_aErrors);
    }

    /**
     * 返回列表数据
     *
     * @param array $p_aDataList            
     * @param int $p_iTotal            
     * @return array
     */
    protected function returnList($p_aDataList, $p_iTotal)
    {
        return util_sys_response::returnList($p_aDataList, $p_iTotal);
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
        lib_sys_logger::getInstance()->addLog($p_sTitle, $p_sContent, $p_sClass = 'common');
    }

    /**
     * 筛选数据
     *
     * @param array $p_aAllData            
     * @param string $p_sColumn            
     * @param mix $p_mValue            
     * @param mix $p_mDefault            
     * @return mix
     */
    protected function filterData($p_aAllData, $p_sColumn, $p_mValue, $p_mDefault = null)
    {
        foreach ($p_aAllData as $aData) {
            if ($p_mValue == $aData[$p_sColumn]) {
                return $p_mValue;
            }
        }
        return $p_mDefault;
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