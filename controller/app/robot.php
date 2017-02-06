<?php

/**
 * controller_app_crossdomain
 * @author jxu
 * @package system_controller_app
 */
/**
 * controller_app_crossdomain
 *
 * @author jxu
 */
class controller_app_robot extends controller_sys_web
{

    function doRequest()
    {
        $this->addHeader('Content-Type:text/plain');
        return 'app_robot';
    }
}