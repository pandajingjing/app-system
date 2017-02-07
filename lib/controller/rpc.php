<?php

/**
 * controller_sys_rpc
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_rpc
 *
 * @author jxu
 * @todo
 *
 */
abstract class lib_controller_rpc extends lib_controller_http
{

    /**
     * 在控制器开始时执行（调度使用）
     */
    function beforeRequest()
    {
        parent::beforeRequest();
        // 检测接口数据
        $this->verify();
    }

    /**
     * 校验数据合法性
     */
    protected function verify()
    {
        $sClassName = $this->getParam('class_name', 'get');
        $sFuncName = $this->getParam('func_name', 'get');
        $sParam = $this->getParam('param', 'get');
        $iReqTime = $this->getParam('time', 'get', 'int');
    }
}