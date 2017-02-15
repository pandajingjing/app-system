<?php

/**
 * lib_sys_bclient
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_bclient
 *
 * @author jxu
 */
class lib_sys_bclient
{

    /**
     * 远程调用
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param string $p_aFuncParams            
     * @todo
     *
     * @return array
     */
    static private function _remoteCall($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {}

    /**
     * 本地调用
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param string $p_aFuncParams            
     * @throws Exception
     * @return array
     */
    static private function _localCall($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {
        $aTmp = explode('_', $p_sClassName);
        $aTmp[0] = 'bll';
        $sBllName = join('_', $aTmp);
        if (class_exists($sBllName)) {
            $oRelClass = new ReflectionClass($sBllName);
            $oRelInstance = $oRelClass->newInstance();
            $oRelMethod = $oRelClass->getMethod($p_sFuncName);
            return $oRelMethod->invokeArgs($oRelInstance, $p_aFuncParams);
        } else {
            throw new Exception(__CLASS__ . ': can not find bll class(' . $sBllName . ').');
        }
    }

    /**
     * 调用业务逻辑
     *
     * @param string $p_sClassName            
     * @param string $p_sFuncName            
     * @param string $p_aFuncParams            
     * @return array
     */
    static protected function _call($p_sClassName, $p_sFuncName, $p_aFuncParams)
    {
        return self::_localCall($p_sClassName, $p_sFuncName, $p_aFuncParams);
    }
}