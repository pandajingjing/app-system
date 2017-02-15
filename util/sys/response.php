<?php

/**
 * 提供返回数据格式化
 * @author jxu
 *
 */
class util_sys_response
{

    /**
     * 返回成功数据
     *
     * @param array $p_aData            
     * @return string
     */
    static function returnSuccess($p_aData)
    {
        return [
            'iStatus' => 1,
            'aData' => $p_aData
        ];
    }

    /**
     * 返回失败数据
     *
     * @param array $p_aErrors            
     * @return string
     */
    static function returnError($p_aErrors)
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
     * @return string
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