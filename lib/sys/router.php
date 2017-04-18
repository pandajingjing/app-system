<?php

/**
 * lib_sys_router
 *
 * 系统路由类
 *
 * @package lib_sys
 */

/**
 * lib_sys_router
 *
 * 系统路由类
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
    private $_aRouterParam = [];

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
     * 实例化函数
     *
     * @return void
     */
    private function __construct()
    {}

    /**
     * 克隆函数
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * 解析路由规则
     *
     * @param string $p_sDispatchParam            
     * @return void
     */
    function parseCMD($p_sDispatchParam)
    {
        $aDispatchParams = parse_url($p_sDispatchParam);
        $sPath = $aDispatchParams['path'];
        $sControllerName = '';
        $aRouteParam = [];
        
        $aTmp = explode('/', $sPath);
        $sParam = array_pop($aTmp);
        if (1 == count($aTmp)) {
            $sControllerName = 'cmd_home_home';
        } else {
            $aTmp[0] = 'cmd';
            $sControllerName = join('_', $aTmp);
        }
        $aRouteParam = $this->_parseParam($sParam);
        
        if (class_exists($sControllerName)) { // 默认路由规则
            $oRelClass = new ReflectionClass($sControllerName);
            if ($oRelClass->isInstantiable()) {
                $this->_sControllerName = $sControllerName;
                $this->_aRouterParam = $aRouteParam;
            } else {
                $this->_sControllerName = 'cmd_home_404';
                $this->_aRouterParam['sURL'] = $sPath;
            }
        } else {
            $this->_sControllerName = 'cmd_home_404';
            $this->_aRouterParam['sURL'] = $sPath;
        }
    }

    /**
     * 解析路由规则
     *
     * @param string $p_sDispatchParam            
     * @return void
     */
    function parseURI($p_sDispatchParam)
    {
        $aDispatchParams = parse_url($p_sDispatchParam);
        $sPath = $aDispatchParams['path'];
        $sControllerName = '';
        $aRouteParam = [];
        // 自定义路由规则
        $aRoutes = lib_sys_var::getInstance()->getConfig('aRouteList', 'router');
        $aTmpParams = [];
        $bFound = false;
        foreach ($aRoutes as $sCtrlName => $aConfig) {
            if (preg_match($aConfig[0], $sPath, $aTmpParams)) {
                $bFound = true;
                $sControllerName = $sCtrlName;
                break;
            }
        }
        if ($bFound) {
            if (isset($aConfig[1])) {
                $aParam = [];
                $iIndex = 0;
                foreach ($aConfig[1] as $sKey) {
                    $aParam[$sKey] = $aTmpParams[++ $iIndex];
                }
                $aRouteParam = $aParam;
            }
        } else {
            $aTmp = explode('/', $sPath);
            $sParam = array_pop($aTmp);
            if (1 == count($aTmp)) {
                $sControllerName = 'controller_home_home';
            } else {
                $aTmp[0] = 'controller';
                $sControllerName = join('_', $aTmp);
            }
            $aRouteParam = $this->_parseParam($sParam);
        }
        if (class_exists($sControllerName)) { // 默认路由规则
            $oRelClass = new ReflectionClass($sControllerName);
            if ($oRelClass->isInstantiable()) {
                $this->_sControllerName = $sControllerName;
                $this->_aRouterParam = $aRouteParam;
            } else {
                $this->_sControllerName = 'controller_home_404';
                $this->_aRouterParam['sURL'] = $sPath;
            }
        } else {
            $this->_sControllerName = 'controller_home_404';
            $this->_aRouterParam['sURL'] = $sPath;
        }
    }

    /**
     * 生成URI
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParam            
     * @throws Exception
     * @return string
     */
    function createURI($p_sControllerName, $p_aRouterParam = [])
    {
        $sURL = '';
        // 自定义路由规则
        $aRoutes = lib_sys_var::getInstance()->getConfig('aRouteList', 'router');
        if (isset($aRoutes[$p_sControllerName])) {
            $aSearchKeyss = $aReplaceValss = [];
            $aNormalParam = $p_aRouterParam;
            foreach ($aRoutes[$p_sControllerName][1] as $sKey) {
                $aSearchKeys[] = '{' . $sKey . '}';
                $aReplaceVals[] = $p_aRouterParam[$sKey];
                unset($aNormalParam[$sKey]);
            }
            if (empty($aNormalParam)) {
                $sURL = str_replace($aSearchKeys, $aReplaceVals, $aRoutes[$p_sControllerName][2]);
            } else {
                $sURL = str_replace($aSearchKeys, $aReplaceVals, $aRoutes[$p_sControllerName][2]) . '?' . http_build_query($aNormalParam);
            }
        } else {
            if (class_exists($p_sControllerName)) { // 默认路由规则
                if ('controller_home_home' == $p_sControllerName) {
                    $aURLParam = [
                        ''
                    ];
                } else {
                    $aURLParam = explode('_', $p_sControllerName);
                    $aURLParam[0] = '';
                }
                $sParam = $this->_createParam($p_aRouterParam);
                $aURLParam[] = $sParam;
                $sURL = join('/', $aURLParam);
            } else {
                throw new Exception(__CLASS__ . ': can not found controller(' . $p_sControllerName . ').');
            }
        }
        return $sURL;
    }

    /**
     * 生成外站URL
     *
     * @param string $p_sDomainKey            
     * @param string $p_sAlias            
     * @param array $p_aRouterParam            
     * @throws Exception
     * @return string
     */
    function createOutURI($p_sDomainKey, $p_sAlias, $p_aRouterParam = [])
    {
        $aDomainURIList = lib_sys_var::getInstance()->getConfig($p_sDomainKey, 'uri');
        if (isset($aDomainURIList[$p_sAlias])) {
            $aSearchKeys = $aReplaceVals = [];
            $aNormalParam = $p_aRouterParam;
            foreach ($aDomainURIList[$p_sAlias][1] as $sKey) {
                $aSearchKeys[] = '{' . $sKey . '}';
                $aReplaceVals[] = $p_aRouterParam[$sKey];
                unset($aNormalParam[$sKey]);
            }
            if (empty($aNormalParam)) {
                $sURL = str_replace($aSearchKeys, $aReplaceVals, $aDomainURIList[$p_sAlias][0]);
            } else {
                $sURL = str_replace($aSearchKeys, $aReplaceVals, $aDomainURIList[$p_sAlias][0]) . '?' . http_build_query($aNormalParam);
            }
        } else {
            throw new Exception(__CLASS__ . ': can not found alias(' . $p_sAlias . ') in domain(' . $p_sDomainKey . ').');
        }
        return $sURL;
    }

    /**
     * 根据URL获取参数
     *
     * @param string $p_sURL            
     * @return array
     */
    protected function _parseParam($p_sParam)
    {
        $aParam = [];
        $aTmp = explode($this->_sParamSeperator, $p_sParam);
        for ($iIndex = 0;;) {
            if (isset($aTmp[$iIndex]) and isset($aTmp[$iIndex + 1])) {
                $aParam[$aTmp[$iIndex]] = $aTmp[++ $iIndex];
                ++ $iIndex;
            } else {
                break;
            }
        }
        return $aParam;
    }

    /**
     * 根据参数得到URL
     *
     * @param array $p_aParam            
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
        return $this->_aRouterParam;
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