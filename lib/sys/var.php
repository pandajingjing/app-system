<?php

/**
 * lib_sys_var
 *
 * 系统变量类,保存了系统所有允许被访问的变量
 *
 * @package lib_sys
 */

/**
 * lib_sys_var
 *
 * 系统变量类,保存了系统所有允许被访问的变量
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
     * 配置数据
     *
     * @var array
     */
    private $_aConfig = [];

    /**
     * 获取的cookie数据
     *
     * @var array
     */
    private $_aGetCookie = [];

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
     * 实例化函数
     *
     * @return void
     */
    private function __construct()
    {
        $this->_aGet = util_string::trimString($_GET);
        $this->_aPost = util_string::trimString($_POST);
        $this->_aFile = $_FILES;
        $this->_aGetCookie = util_string::trimString(util_sys_cookie::getCookie());
        if (PANDA_REQUEST_TYPE == PANDA_REQTYPE_CONSOLE) {
            $this->_aServerParam = util_string::trimString($this->_getConsoleParam());
        } else {
            $this->_aServerParam = util_string::trimString($this->_getWebServerParam());
        }
    }

    /**
     * 克隆函数
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * 设置路由获取的变量
     *
     * @param array $p_aParam            
     * @return void
     */
    function setRouterParam($p_aParam)
    {
        $this->_aRouterParam = util_string::trimString($p_aParam);
    }

    /**
     * 获取请求时间
     *
     * @param boolean $p_bFloat            
     * @return float|int
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
     * @return float|int
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
        $aTmp = $this->getAllParam($p_sType);
        return $aTmp[$p_sKey] ?? null;
    }

    /**
     * 获取各种变量
     *
     * @param string $p_sType            
     * @return mix
     */
    function getAllParam($p_sType)
    {
        switch ($p_sType) {
            case 'post':
                return $this->_aPost;
                break;
            case 'get':
                return $this->_aGet;
                break;
            case 'cookie':
                return $this->_aGetCookie;
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
            case 'config':
                return $this->_aConfig;
                break;
            default:
                return array_merge($this->_aGetCookie, $this->_aGet, $this->_aPost);
                break;
        }
    }

    /**
     * 获取配置
     *
     * @param string $p_sKey            
     * @param string $p_sClass            
     * @throws Exception
     * @return mix
     */
    function getConfig($p_sKey, $p_sClass)
    {
        if (! isset($this->_aConfig[$p_sClass])) {
            $this->_aConfig[$p_sClass] = [];
            global $G_CONFIG_DIR;
            foreach ($G_CONFIG_DIR as $sConfigDir) {
                $sConfigFilePath = $sConfigDir . DIRECTORY_SEPARATOR . $p_sClass . '.php';
                if (file_exists($sConfigFilePath)) {
                    $aConfig = include $sConfigFilePath;
                    $this->_aConfig[$p_sClass] = array_merge($this->_aConfig[$p_sClass], $aConfig);
                }
            }
        }
        if (isset($this->_aConfig[$p_sClass][$p_sKey])) {
            return $this->_aConfig[$p_sClass][$p_sKey];
        } else {
            throw new Exception(__CLASS__ . ': can not found config key (' . $p_sKey . ') in class (' . $p_sClass . ').');
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
            $sIP = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        $aServer['CLIENTIP'] = $sIP;
        $aServer['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];
        $aServer['REQUEST_TIME_FLOAT'] = $_SERVER['REQUEST_TIME_FLOAT'];
        $aServer['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $aServer['DISPATCH_PARAM'] = $_SERVER['REQUEST_URI'];
        $aServer['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'] ?? null;
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
        $aCmd['DISPATCH_PARAM'] = $_SERVER['argv'][1] ?? null;
        $aCmd['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];
        if ($_SERVER['argc'] > 2) {
            $aCmd['REQUEST_ARGV'] = [];
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
