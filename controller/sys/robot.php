<?php

/**
 * controller_sys_robot
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_robot
 *
 * @author jxu
 */
class controller_sys_robot extends lib_controller_web
{

    function doRequest()
    {
        $this->addHeader('Content-Type:text/plain');
        return 'app_robot';
    }
}