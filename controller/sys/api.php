<?php

/**
 * controller_sys_api
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_api
 *
 * @author jxu
 * @todo
 *
 */
class controller_sys_api extends controller_sys_service
{

    /**
     * 接口字段定义,用于校验文档
     *
     * @var array
     */
    protected $_aField = [];

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