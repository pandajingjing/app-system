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
class controller_sys_rpc extends lib_controller_rpc
{

    function doRequest()
    {
        if (util_error::isError()) {
            $this->setData('mJData', util_sys_response::returnError(util_error::getErrors()));
        } else {
            $this->setData('mJData', util_sys_response::returnSuccess(['this is a rpc request.']));
        }
        return 'service_json';
    }
}