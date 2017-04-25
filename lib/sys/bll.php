<?php

/**
 * lib_sys_bll
 *
 * 业务服务基类
 *
 * @package lib_sys
 */

/**
 * lib_sys_bll
 *
 * 业务服务基类
 */
class lib_sys_bll
{

    /**
     * 构造函数
     *
     * @return void
     */
    function __construct()
    {}

    /**
     * 返回一行数据
     *
     * @param array $p_aRow            
     * @return array
     */
    protected function returnRow($p_aRow)
    {
        return util_sys_response::returnRow($p_aRow);
    }

    /**
     * 返回一个值
     *
     * @param mix $p_mOne            
     * @return array
     */
    protected function returnOne($p_mOne)
    {
        return util_sys_response::returnOne($p_mOne);
    }

    /**
     * 返回主键值
     *
     * @param mix $p_mPrimary            
     * @return array
     */
    protected function returnPrimary($p_mPrimary)
    {
        return util_sys_response::returnPrimary($p_mPrimary);
    }

    /**
     * 返回错误数据
     *
     * @param array $p_aErrors            
     * @return array
     */
    protected function returnErrors($p_aErrors)
    {
        return util_sys_response::returnErrors($p_aErrors);
    }

    /**
     * 返回列表数据
     *
     * @param array $p_aList            
     * @param int $p_iTotal            
     * @return array
     */
    protected function returnList($p_aList, $p_iTotal)
    {
        return util_sys_response::returnList($p_aList, $p_iTotal);
    }

    /**
     * 添加日志
     *
     * @param string $p_sTitle            
     * @param string $p_sContent            
     * @param string $p_sClass            
     * @return void
     */
    protected function addLog($p_sTitle, $p_sContent, $p_sClass = 'common')
    {
        lib_sys_logger::getInstance()->addLog($p_sTitle, $p_sContent, $p_sClass);
    }

    /**
     * 筛选数据
     *
     * 筛选<var>$p_aAllDatas</var>中是否有<var>$p_mValue</var>,如果存在则返回,否则返回<var>$p_mDefault</var>
     *
     * @param array $p_aAllDatas            
     * @param string $p_sColumn            
     * @param mix $p_mValue            
     * @param mix $p_mDefault            
     * @return mix
     */
    protected function filterData($p_aAllDatas, $p_sColumn, $p_mValue, $p_mDefault = null)
    {
        foreach ($p_aAllDatas as $aData) {
            if ($p_mValue == $aData[$p_sColumn]) {
                return $p_mValue;
            }
        }
        return $p_mDefault;
    }

    /**
     * 获取配置信息
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
     * 开始模块调试
     *
     * @param string $p_sModule            
     * @return void
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
     * @return void
     */
    protected function showDebugMsg($p_sMsg, $p_bIsHTML = false)
    {
        lib_sys_debugger::getInstance()->showMsg($p_sMsg, $p_bIsHTML);
    }

    /**
     * 结束模块调试
     *
     * @param string $p_sModule            
     * @return void
     */
    protected function stopDebug($p_sModule)
    {
        lib_sys_debugger::getInstance()->stopDebug($p_sModule);
    }
}