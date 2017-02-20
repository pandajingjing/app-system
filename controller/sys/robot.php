<?php

/**
 * controller_sys_robot
 *
 * robot controller
 *
 * @package controller_sys
 */

/**
 * controller_sys_robot
 *
 * robot controller
 */
class controller_sys_robot extends lib_controller_web
{

    /**
     * 控制器入口函数
     *
     * @return string|lib_sys_controller
     */
    function doRequest()
    {
        $this->addHeader('Content-Type:text/plain');
        return 'app_robot';
    }
}