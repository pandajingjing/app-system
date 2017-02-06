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
        $sPath = $aDispatchParams['path'];
        // 自定义路由规则
        $aRoutes = get_config('route', 'router');
        $aTmpParams = [];
        $bFound = false;
        foreach ($aRoutes as $sPattern => $aRoute) {
            if (preg_match($sPattern, $sPath, $aTmpParams)) {
                $bFound = true;
                break;
            }
        }
        $sControllerName = '';
        $aRouteParams = [];
        if ($bFound) {
            $sControllerName = $aRoute[0];
            if (isset($aRoute[1])) {
                $aParamList = [];
                $iIndex = 0;
                foreach ($aRoute[1] as $sKey) {
                    $aParamList[$sKey] = $aTmpParams[++ $iIndex];
                }
                $aRouteParams = $aParamList;
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
                return true;
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
     * 生成URL
     *
     * @param string $p_sControllerName            
     * @param array $p_aRouterParams            
     * @return boolean|string
     */
    function createURL($p_sControllerName, $p_aRouterParams)
    {
        $sURL = '';
        // 自定义路由规则
        $aRoutes = get_config('route', 'router');
        $bFound = false;
        foreach ($aRoutes as $sPattern => $aRoute) {
            if ($aRoute[0] == $p_sControllerName) {
                $bFound = true;
                break;
            }
        }
        if ($bFound) {
            $aSearchKey = $aReplaceVal = $aNormalParam = [];
            foreach ($aRoute[1] as $sKey) {
                $aSearchKey[] = '{' . $sKey . '}';
                $aReplaceVal[] = $p_aRouterParams[$sKey];
                unset($p_aRouterParams[$sKey]);
            }
            if (empty($p_aRouterParams)) {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aRoute[2]);
            } else {
                $sURL = str_replace($aSearchKey, $aReplaceVal, $aRoute[2]) . '?' . http_build_query($p_aRouterParams);
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
                throw new Exception('controller(' . $p_sControllerName . ') is lost.');
            }
        }
        
        return get_config('self_domain', 'domain') . $sURL;
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