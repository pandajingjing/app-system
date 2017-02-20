<?php

/**
 * controller_sys_rpc
 *
 * rpc controller
 *
 * @package controller_sys
 */

/**
 * controller_sys_rpc
 *
 * rpc controller
 */
class controller_sys_rpc extends lib_controller_rpc
{

    /**
     * 控制器入口函数
     *
     * @return string|lib_sys_controller
     */
    function doRequest()
    {
        if (util_error::isError()) {
            $this->setData('mJData', util_sys_response::returnError(util_error::getErrors()));
        } else {
            $this->setData('mJData', util_sys_response::returnSuccess([
                'this is a rpc request.'
            ]));
        }
        return 'service_json';
    }
}