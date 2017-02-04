<?php

/**
 * lib_sys_router
 * @author jxu
 * @package system_lib_sys
 */

/**
 * 系统路由
 *
 * @author jxu
 *        
 */
class lib_sys_router
{

    /**
     * 实例自身
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 路由参数
     *
     * @var array
     */
    private $_aRouterParams = [];

    /**
     * 控制器
     *
     * @var string
     */
    private $_sControllerName = '';

    /**
     * 字符串参数分隔符
     *
     * @var string
     */
    private $_sParamSeperator = '.';

    /**
     * 获取实例
     *
     * @return object
     */
    static function getInstance()
    {
        if (! self::$_oInstance instanceof self) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 实例化
     */
    private function __construct()
    {}

    /**
     * 克隆
     */
    private function __clone()
    {}

    /**
     * 解析路由规则
     *
     * @param string $p_sDispatchParam            
     */
    function parseRoute($p_sDispatchParam)
    {
        $aDispatchParams = parse_url($p_sDispatchParam);
        $aTmp = explode('/', $aDispatchParams['path']);
        $sParam = array_pop($aTmp);
        if (1 == count($aTmp)) {
            $sControllerName = 'controller_home_home';
        } else {
            $aTmp[0] = 'controller';
            $sControllerName = join('_', $aTmp);
        }
        if (class_exists($sControllerName)) { // 默认路由规则
            $oRelClass = new ReflectionClass($sControllerName);
            if ($oRelClass->isInstantiable()) {
                $this->_sControllerName = $sControllerName;
                $this->_aRouterParams = $this->_parseParam($sParam);
                return true;
            } else {
                $this->_sControllerName = 'controller_home_404';
            }
        } else { // 自定义路由规则
            $aRoutes = get_config('route', 'router');
            $aTmpParams = [];
            $bFound = false;
            foreach ($aRoutes as $sPattern => $aRoute) {
                if (preg_match($sPattern, $aDispatchParams['path'], $aTmpParams)) {
                    $bFound = true;
                    break;
                }
            }
            if ($bFound) {
                $this->_sControllerName = $aRoute[0];
                if (isset($aRoute[1])) {
                    $aParamList = [];
                    $iIndex = 0;
                    foreach ($aRoute[1] as $sKey) {
                        $aParamList[$sKey] = $aTmpParams[++ $iIndex];
                    }
                    $this->_aRouteParams = $aParamList;
                }
            } else {
                $this->_sControllerName = 'controller_home_404';
                $this->_aRouterParams['sURL'] = $sParam;
            }
        }
    }

    /**
     * 生成URL
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParams            
     * @return boolean|string
     */
    function createURL($p_sControllerName, $p_aRouterParams)
    {
        $sURL = '';
        if (class_exists($p_sControllerName)) { // 默认路由规则
            if ('controller_home_home' == $p_sControllerName) {
                $aURLParam = [];
            } else {
                $aURLParam = explode('_', $p_sControllerName);
                $aURLParam[0] = '';
            }
            $sParam = $this->_createParam($p_aRouterParams);
            $aURLParam[] = $sParam;
            $sURL = join('/', $aURLParam);
            return $sURL;
        } else { // 自定义路由规则
            $aRoutes = get_config('route', 'router');
            $aTmpParams = [];
            $bFound = false;
            foreach ($aRoutes as $sPattern => $aRoute) {
                if (preg_match($sPattern, $aDispatchParams['path'], $aTmpParams)) {
                    $bFound = true;
                    break;
                }
            }
            if ($bFound) {
                $this->_sControllerName = $aRoute[0];
                if (isset($aRoute[1])) {
                    $aParamList = [];
                    $iIndex = 0;
                    foreach ($aRoute[1] as $sKey) {
                        $aParamList[$sKey] = $aTmpParams[++ $iIndex];
                    }
                    $this->_aRouteParams = $aParamList;
                }
            } else {
                return false;
            }
        }
    }

    /**
     * 根据URL获取参数
     *
     * @param string $p_sURL            
     * @return array
     */
    protected function _parseParam($p_sParam)
    {
        $aTmp = explode($this->_sParamSeperator, $p_sParam);
        $iCnt = count($aTmp);
        for ($i = 0;;) {
            if (isset($aTmp[$i + 1]) and isset($aTmp[$i + 2])) {
                $aParam[$aTmp[++ $i]] = $aTmp[++ $i];
            } else {
                break;
            }
        }
        return $aParam;
    }

    /**
     * 根据参数得到URL
     *
     * @param string $p_sAction            
     * @param array $p_aParam            
     * @param string $p_sSuffix            
     * @return string
     */
    protected function _createParam($p_aParam)
    {
        ksort($p_aParam);
        $sParam = '';
        foreach ($p_aParam as $sKey => $sValue) {
            $sParam .= $this->_sParamSeperator . urlencode($sKey) . $this->_sParamSeperator . urlencode($sValue);
        }
        if (isset($sParam[0])) {
            $sParam = substr($sParam, 1);
        }
        return $sParam;
    }

    /**
     * 获取路由参数
     *
     * @return array
     */
    function getRouterParam()
    {
        return $this->_aRouterParams;
    }

    /**
     * 获取控制器名称
     *
     * @return string
     */
    function getControllerName()
    {
        return $this->_sControllerName;
    }
}