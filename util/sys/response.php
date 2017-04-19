<?php

/**
 * util_sys_response
 *
 * 规范框架相关输出的格式,包括列表,数组和错误,应避免应用直接使用
 *
 * @package util_sys
 */

/**
 * util_sys_response
 *
 * 规范框架相关输出的格式,包括列表,数组和错误,应避免应用直接使用
 */
class util_sys_response
{

    /**
     * 返回数组数据
     *
     * @param array $p_aData            
     * @return array
     */
    static function returnSuccess($p_aData)
    {
        return [
            'iStatus' => 1,
            'aData' => $p_aData
        ];
    }

    /**
     * 返回错误数据
     *
     * @param array $p_aErrors            
     * @return array
     */
    static function returnErrors($p_aErrors)
    {
        return [
            'iStatus' => 0,
            'aErrors' => $p_aErrors
        ];
    }

    /**
     * 返回列表数据
     *
     * @param array $p_aDataList            
     * @param int $p_iCnt            
     * @return array
     */
    static function returnList($p_aDataList, $p_iCnt)
    {
        return [
            'iStatus' => 1,
            'aDataList' => $p_aDataList,
            'iTotal' => $p_iCnt
        ];
    }
}