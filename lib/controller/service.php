<?php

/**
 * lib_controller_service
 *
 * 内部服务控制器基类
 *
 * @package lib_sys
 */

/**
 * lib_controller_service
 *
 * 内部服务控制器基类
 */
abstract class lib_controller_service extends lib_controller_http
{

    /**
     * 在控制器结束时执行（调度使用）
     *
     * @return void
     */
    function afterRequest()
    {
        $this->addHeader('Content-type: application/json;charset=utf-8');
        parent::afterRequest();
    }

    /**
     * 返回一行数据
     *
     * @param array $p_aRow            
     * @return array
     */
    protected function returnRow($p_aRow)
    {
        $this->setData('mJData', util_sys_response::returnRow($p_aRow));
        return 'service_json';
    }

    /**
     * 返回一个值
     *
     * @param mix $p_mOne            
     * @return array
     */
    protected function returnOne($p_mOne)
    {
        $this->setData('mJData', util_sys_response::returnOne($p_mOne));
        return 'service_json';
    }

    /**
     * 返回主键值
     *
     * @param mix $p_mPrimary            
     * @return array
     */
    protected function returnPrimary($p_mPrimary)
    {
        $this->setData('mJData', util_sys_response::returnPrimary($p_mPrimary));
        return 'service_json';
    }

    /**
     * 设置错误数据,并返回模版名称
     *
     * @param array $p_aErrors            
     * @return string
     */
    protected function returnErrors($p_aErrors)
    {
        $this->setData('mJData', util_sys_response::returnErrors($p_aErrors));
        return 'service_json';
    }

    /**
     * 设置列表数据,并返回模版名称
     *
     * @param array $p_aList            
     * @param int $p_iCnt            
     * @return string
     */
    protected function returnList($p_aList, $p_iTotal)
    {
        $this->setData('mJData', util_sys_response::returnList($p_aList, $p_iTotal));
        return 'service_json';
    }
}