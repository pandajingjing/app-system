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
 */
class controller_sys_rpc extends controller_sys_http
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

    protected function verify()
    {}

    function doRequest()
    {}
}