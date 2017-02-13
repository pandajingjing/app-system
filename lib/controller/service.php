<?php

/**
 * controller_sys_service
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_service
 *
 * @author jxu
 */
abstract class lib_controller_service extends lib_controller_http
{

    /**
     * 在控制器结束时执行（调度使用）
     *
     * @see lib_sys_controller::afterRequest()
     */
    function afterRequest()
    {
        $this->addHeader('Content-type: application/json;charset=utf-8');
        parent::afterRequest();
    }

    /**
     * 返回成功数据
     *
     * @param array $p_aData            
     * @return string
     */
    protected function returnSuccess($p_aData)
    {
        $this->setData('mJData', util_sys_response::returnSuccess($p_aData));
        return 'service_json';
    }

    /**
     * 返回失败数据
     *
     * @param array $p_aErrors            
     * @return string
     */
    protected function returnError($p_aErrors)
    {
        $this->setData('mJData', util_sys_response::returnError($p_aErrors));
        return 'service_json';
    }

    /**
     * 返回列表数据
     *
     * @param array $p_aList            
     * @param int $p_iCnt            
     * @return string
     */
    protected function returnList($p_aList, $p_iCnt)
    {
        $this->setData('mJData', util_sys_response::returnList($p_aList, $p_iCnt));
        return 'service_json';
    }
}