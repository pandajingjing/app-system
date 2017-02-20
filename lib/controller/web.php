<?php

/**
 * lib_controller_web
 *
 * 网页控制器基类
 *
 * @package lib_sys
 */

/**
 * 网页lib_controller_web
 *
 * 控制器基类
 */
abstract class lib_controller_web extends lib_controller_http
{

    /**
     * 在控制器结束时执行（调度使用）
     *
     * @return void
     */
    function afterRequest()
    {
        util_sys_cookie::sendCookies();
        parent::afterRequest();
    }

    /**
     * 服务器页面跳转
     *
     * @param string $p_sURL            
     * @param boolean $p_bIsTemp            
     * @return void
     */
    protected function redirectURL($p_sURL, $p_bIsTemp = true)
    {
        $this->addHeader('Location:' . $p_sURL, true, $p_bIsTemp ? 302 : 301);
        $this->afterRequest();
        exit();
    }

    /**
     * 获取当前域名的路径
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParam            
     * @return string
     */
    protected function createInURL($p_sControllerName, $p_aRouterParam = [])
    {
        return lib_sys_var::getInstance()->getConfig('sSelfSchemeDomain', 'domain') . lib_sys_router::getInstance()->createURI($p_sControllerName, $p_aRouterParam);
    }

    /**
     * 获取其他域名的路径
     *
     * @param string $p_sDomainKey            
     * @param string $p_sAlias            
     * @param array $p_aRouterParam            
     * @return string
     */
    protected function createOutURL($p_sDomainKey, $p_sAlias, $p_aRouterParam = [])
    {
        return lib_sys_var::getInstance()->getConfig($p_sDomainKey, 'domain') . lib_sys_router::getInstance()->createOutURI($p_sDomainKey, $p_sAlias, $p_aRouterParam);
    }

    /**
     * 设置cookie
     *
     * @param string $p_sName            
     * @param string $p_sValue            
     * @param int $p_iLifeTime            
     * @param string $p_sPath            
     * @return void
     */
    protected function setCookie($p_sName, $p_sValue, $p_iLifeTime, $p_sPath = '/')
    {
        $iExpireTime = 0 == $p_iLifeTime ? 0 : $this->getVisitTime() + $p_iLifeTime;
        $sDomain = lib_sys_var::getInstance()->getConfig('sCookieDomain', 'domain');
        util_sys_cookie::setCookie($p_sName, $p_sValue, $iExpireTime, $p_sPath, $sDomain);
    }
}