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
     * 返回一行数据
     *
     * @param array $p_aRow            
     * @return array
     */
    static function returnRow($p_aRow)
    {
        return [
            'iStatus' => 1,
            'aRow' => $p_aRow
        ];
    }

    /**
     * 返回一个值
     *
     * @param mix $p_mOne            
     * @return array
     */
    static function returnOne($p_mOne)
    {
        return [
            'iStatus' => 1,
            'mOne' => $p_mOne
        ];
    }

    /**
     * 返回主键值
     *
     * @param mix $p_mPrimary            
     * @return array
     */
    static function returnPrimary($p_mPrimary)
    {
        return [
            'iStatus' => 1,
            'mPrimary' => $p_mPrimary
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
     * @param array $p_aList            
     * @param int $p_iTotal            
     * @return array
     */
    static function returnList($p_aList, $p_iTotal)
    {
        return [
            'iStatus' => 1,
            'aList' => $p_aList,
            'iTotal' => $p_iTotal
        ];
    }
}