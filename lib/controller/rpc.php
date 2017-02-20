<?php

/**
 * lib_controller_rpc
 *
 * 远程业务逻辑请求控制器基类
 *
 * @package lib_sys
 */

/**
 * 远程业务逻辑请求控制器基类
 *
 * 控制器基类
 */
abstract class lib_controller_rpc extends lib_controller_http
{

    /**
     * 在控制器开始时执行（调度使用）
     *
     * @return void
     */
    function beforeRequest()
    {
        parent::beforeRequest();
        // 检测接口数据
        $this->verify();
    }

    /**
     * 校验数据合法性
     *
     * @return void
     */
    protected function verify()
    {
        $sClassName = $this->getParam('class_name', 'get');
        $sFuncName = $this->getParam('func_name', 'get');
        $sParam = $this->getParam('param', 'get');
        $iReqTime = $this->getParam('time', 'get', 'int');
    }
}