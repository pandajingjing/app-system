<?php

/**
 * controller_sys_web
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_web
 *
 * @author jxu
 */
abstract class controller_sys_web extends controller_sys_http
{

    /**
     * 在控制器结束时执行（调度使用）
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
     * @param string $p_sAlias            
     * @param array $p_aData            
     *
     * @return string
     */
    protected function createInURL($p_sControllerName, $p_aRouterParams = [])
    {
        return lib_sys_router::getInstance()->createURL($p_sControllerName, $p_aRouterParams);
    }

    /**
     * 获取其他域名的路径
     *
     * @param string $p_sChannel            
     * @param string $p_sAlias            
     * @param array $p_aRouterParam            
     * @todo
     *
     * @return string
     */
    protected function createOutURL($p_sChannel, $p_sAlias, $p_aRouterParam = [])
    {}

    /**
     * 设置cookie
     *
     * @param string $p_sName            
     * @param string $p_sValue            
     * @param int $p_iLifeTime            
     */
    protected function setCookie($p_sName, $p_sValue, $p_iLifeTime)
    {
        $iExpireTime = 0 == $p_iLifeTime ? 0 : $this->getVisitTime() + $p_iLifeTime;
        util_sys_cookie::setCookie($p_sName, $p_sValue, $iExpireTime);
    }
}