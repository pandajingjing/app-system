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
     * @param array $p_aError            
     * @return array
     */
    protected function returnError($p_aError)
    {
        return util_sys_response::returnError($p_aError);
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
     * @param string $p_aContent            
     * @param string $p_sClass            
     */
    protected function addLog($p_aContent, $p_sClass = 'common')
    {}

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
}