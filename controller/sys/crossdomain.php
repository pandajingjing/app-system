<?php

/**
 * controller_sys_crossdomain
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_crossdomain
 *
 * @author jxu
 */
class controller_sys_crossdomain extends lib_controller_web
{

    /**
     * 允许的域名列表
     *
     * @var array
     */
    const CROSS_DOMAIN_LIST = [
        '*.jxu.home',
        '*.jxulife.com'
    ];

    function doRequest()
    {
        $this->setData('aCrossDomainList', self::CROSS_DOMAIN_LIST);
        $this->addHeader('Content-Type:text/xml');
        return 'app_crossdomain';
    }
}