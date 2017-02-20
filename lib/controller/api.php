<?php

/**
 * lib_controller_api
 *
 * 外部接口控制器基类
 *
 * @package lib_controller
 */

/**
 * lib_controller_api
 *
 * a外部接口控制器基类
 */
abstract class lib_controller_api extends lib_controller_service
{

    /**
     * 接口字段定义,用于校验文档
     *
     * @var array
     */
    protected $_aFields = [];

    /**
     * 在控制器开始时执行（调度使用）
     *
     * 进行数据合法性校验
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
     * 接口数据合法性校验
     */
    protected function verify()
    {}
}