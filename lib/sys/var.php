<?php

/**
 * lib_sys_var
 * @author jxu
 * @package system_lib_sys
 */

/**
 * 系统变量
 *
 * @author jxu
 *        
 */
class lib_sys_var
{

    /**
     * 实例自身
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * Get数据
     *
     * @var array
     */
    private $_aGet = [];

    /**
     * Post数据
     *
     * @var array
     */
    private $_aPost = [];

    /**
     * File数据
     *
     * @var array
     */
    private $_aFile = [];

    /**
     * 获取的cookie数据
     *
     * @var array
     */
    private $_aGetCookies = [];

    /**
     * 服务器参数
     *
     * @var array
     */
    private $_aServerParam = [];

    /**
     * 路由参数
     *
     * @var array
     */
    private $_aRouterParam = [];

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
    {
        $this->_aGet = util_string::trimString($_GET);
        $this->_aPost = util_string::trimString($_POST);
        $this->_aFile = $_FILES;
        $this->_aGetCookies = util_string::trimString(util_sys_cookie::getCookies());
        if (PANDA_REQUEST_TYPE == PANDA_REQTYPE_CONSOLE) {
            $this->_aServerParam = util_string::trimString($this->_getConsoleParam());
        } else {
            $this->_aServerParam = util_string::trimString($this->_getWebServerParam());
        }
    }

    /**
     * 克隆
     */
    private function __clone()
    {}

    /**
     * 设置路由获取的变量
     *
     * @param array $p_aParam            
     */
    function setRouterParam($p_aParam)
    {
        $this->_aRouterParam = util_string::trimString($p_aParam);
    }

    /**
     * 获取请求时间
     *
     * @param boolean $p_bFloat            
     * @return float/int
     */
    function getVisitTime($p_bFloat = false)
    {
        if ($p_bFloat) {
            return $this->getParam('REQUEST_TIME_FLOAT', 'server');
        } else {
            return $this->getParam('REQUEST_TIME', 'server');
        }
    }

    /**
     * 获取当前时间
     *
     * @param boolean $p_bFloat            
     * @return float/int
     */
    function getRealTime($p_bFloat = false)
    {
        if ($p_bFloat) {
            return microtime(true);
        } else {
            return time();
        }
    }

    /**
     * 获取某个变量
     *
     * @param string $p_sKey            
     * @param string $p_sType            
     * @return mix
     */
    function getParam($p_sKey, $p_sType)
    {
        $aTmp = $this->getParams($p_sType);
        return isset($aTmp[$p_sKey]) ? $aTmp[$p_sKey] : null;
    }

    /**
     * 获取各种变量
     *
     * @param string $p_sType            
     * @return mix
     */
    function getParams($p_sType)
    {
        switch ($p_sType) {
            case 'post':
                return $this->_aPost;
                break;
            case 'get':
                return $this->_aGet;
                break;
            case 'cookie':
                return $this->_aGetCookies;
                break;
            case 'router':
                return $this->_aRouterParam;
                break;
            case 'server':
                return $this->_aServerParam;
                break;
            case 'file':
                return $this->_aFile;
                break;
            default:
                return array_merge($this->_aGetCookies, $this->_aGet, $this->_aPost);
                break;
        }
    }

    /**
     * 获取web服务器变量
     *
     * @return array
     */
    private function _getWebServerParam()
    {
        $aServer = [];
        $sIP = null;
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $sIP = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aIPLists = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $sIP = array_shift($aIPLists);
        } else {
            $sIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        $aServer['CLIENTIP'] = $sIP;
        $aServer['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];
        $aServer['REQUEST_TIME_FLOAT'] = $_SERVER['REQUEST_TIME_FLOAT'];
        $aServer['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $aServer['DISPATCH_PARAM'] = $_SERVER['REQUEST_URI'];
        $aServer['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $aServer['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
        return $aServer;
    }

    /**
     * 获取命令行变量
     *
     * @return array
     */
    private function _getConsoleParam()
    {
        $aCmd = [];
        $aCmd['DISPATCH_PARAM'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        $aCmd['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];
        if ($_SERVER['argc'] > 2) {
            $aCmd['REQUEST_ARGV'] = array();
            for ($iIndex = 2; $iIndex < $_SERVER['argc']; ++ $iIndex) {
                if (isset($_SERVER['argv'][$iIndex]) and isset($_SERVER['argv'][$iIndex + 1])) {
                    if ('-' == substr($_SERVER['argv'][$iIndex], 0, 1)) {
                        $aCmd['REQUEST_ARGV'][strtoupper(substr($_SERVER['argv'][$iIndex], 1))] = $_SERVER['argv'][++ $iIndex];
                    }
                }
            }
        }
        return $aCmd;
    }
}
