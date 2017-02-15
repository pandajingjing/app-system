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
    function parseURI($p_sDispatchParam)
    {
        $aDispatchParams = parse_url($p_sDispatchParam);
        $sPath = $aDispatchParams['path'];
        $sControllerName = '';
        $aRouteParams = [];
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
                $aParams = [];
                $iIndex = 0;
                foreach ($aConfig[1] as $sKey) {
                    $aParams[$sKey] = $aTmpParams[++ $iIndex];
                }
                $aRouteParams = $aParams;
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
            $aRouteParams = $this->_parseParam($sParam);
        }
        if (class_exists($sControllerName)) { // 默认路由规则
            $oRelClass = new ReflectionClass($sControllerName);
            if ($oRelClass->isInstantiable()) {
                $this->_sControllerName = $sControllerName;
                $this->_aRouterParams = $aRouteParams;
            } else {
                $this->_sControllerName = 'controller_home_404';
                $this->_aRouterParams['sURL'] = $sPath;
            }
        } else {
            $this->_sControllerName = 'controller_home_404';
            $this->_aRouterParams['sURL'] = $sPath;
        }
    }

    /**
     * 生成URI
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParams            
     * @return boolean|string
     */
    function createURI($p_sControllerName, $p_aRouterParams = [])
    {
        $sURL = '';
        // 自定义路由规则
        $aRoutes = lib_sys_var::getInstance()->getConfig('aRouteList', 'router');
        if (isset($aRoutes[$p_sControllerName])) {
            $aSearchKey = $aReplaceVal = [];
            $aNormalParams = $p_aRouterParams;
            foreach ($aRoutes[$p_sControllerName][1] as $sKey) {
                $aSearchKey[] = '{' . $sKey . '}';
                $aReplaceVal[] = $p_aRouterParams[$sKey];
                unset($aNormalParams[$sKey]);
            }
            if (empty($aNormalParams)) {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aRoutes[$p_sControllerName][2]);
            } else {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aRoutes[$p_sControllerName][2]) . '?' . http_build_query($aNormalParams);
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
                $sParam = $this->_createParam($p_aRouterParams);
                $aURLParam[] = $sParam;
                $sURL = join('/', $aURLParam);
            } else {
                throw new Exception(__CLASS__ . ': can not found controller(' . $p_sControllerName . ').');
            }
        }
        return $sURL;
    }

    /**
     * 生成外站
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParams            
     * @return boolean|string
     */
    function createOutURI($p_sDomainKey, $p_sAlias, $p_aRouterParams = [])
    {
        $aDomainURIList = lib_sys_var::getInstance()->getConfig($p_sDomainKey, 'uri');
        if (isset($aDomainURIList[$p_sAlias])) {
            $aSearchKey = $aReplaceVal = [];
            $aNormalParams = $p_aRouterParams;
            foreach ($aDomainURIList[$p_sAlias][1] as $sKey) {
                $aSearchKey[] = '{' . $sKey . '}';
                $aReplaceVal[] = $p_aRouterParams[$sKey];
                unset($aNormalParams[$sKey]);
            }
            if (empty($aNormalParams)) {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aDomainURIList[$p_sAlias][0]);
            } else {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aDomainURIList[$p_sAlias][0]) . '?' . http_build_query($aNormalParams);
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
        $aParams = [];
        $aTmp = explode($this->_sParamSeperator, $p_sParam);
        for ($iIndex = 0;;) {
            if (isset($aTmp[$iIndex + 1]) and isset($aTmp[$iIndex + 2])) {
                $aParams[$aTmp[++ $iIndex]] = $aTmp[++ $iIndex];
            } else {
                break;
            }
        }
        return $aParams;
    }

    /**
     * 根据参数得到URL
     *
     * @param string $p_sAction            
     * @param array $p_aParams            
     * @param string $p_sSuffix            
     * @return string
     */
    protected function _createParam($p_aParams)
    {
        ksort($p_aParams);
        $sParam = '';
        foreach ($p_aParams as $sKey => $sValue) {
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
    function getRouterParams()
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