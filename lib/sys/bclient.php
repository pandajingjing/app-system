<?php

/**
 * lib_sys_bclient
 *
 * 业务服务客户端基类
 *
 * @package lib_sys
 */

/**
 * lib_sys_bclient
 *
 * 业务服务客户端基类
 */
class lib_sys_bclient
{

    /**
     * 远程调用业务逻辑
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param array $p_aFuncParams            
     * @todo 完成函数体
     *      
     * @return array
     */
    static private function _remoteCall($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {}

    /**
     * 本地调用业务逻辑
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param array $p_aFuncParams            
     * @throws Exception
     * @return array
     */
    static private function _localCall($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {
        $aTmp = explode('_', $p_sClassName);
        $aTmp[0] = 'bll';
        $sBllName = join('_', $aTmp);
        $sFuncKey = $sBllName . '::' . $p_sFuncName;
        
        if (isset(self::$_aBllPool[$sBllName])) {
            if (isset(self::$_aFuncPool[$sFuncKey])) {} else {
                $oRelClass = new ReflectionClass($sBllName);
                self::$_aFuncPool[$sFuncKey] = $oRelClass->getMethod($p_sFuncName);
            }
            return self::$_aFuncPool[$sFuncKey]->invokeArgs(self::$_aBllPool[$sBllName], $p_aFuncParams);
        } else {
            if (class_exists($sBllName)) {
                $oRelClass = new ReflectionClass($sBllName);
                self::$_aBllPool[$sBllName] = $oRelClass->newInstance();
                self::$_aFuncPool[$sFuncKey] = $oRelClass->getMethod($p_sFuncName);
                return self::$_aFuncPool[$sFuncKey]->invokeArgs(self::$_aBllPool[$sBllName], $p_aFuncParams);
            } else {
                throw new Exception(__CLASS__ . ': can not find bll class(' . $sBllName . ').');
            }
        }
    }

    /**
     * 用于存放本地调用时的业务逻辑函数
     * 
     * @var array
     */
    private static $_aFuncPool = [];

    /**
     * 用于存放本地调用时的业务逻辑类实例
     *
     * @var array
     */
    private static $_aBllPool = [];

    /**
     * 调用业务逻辑
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param array $p_aFuncParams            
     * @return array
     */
    static protected function _call($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {
        return self::_localCall($p_sClassName, $p_sFuncName, $p_aFuncParams);
    }
}