<?php

/**
 * Lib_Sys_Debug
 * @author jxu
 * @package system_lib_sys
 */

/**
 * 系统调试
 *
 * @author jxu
 *        
 */
class lib_sys_debugger
{

    /**
     * 实例自身
     *
     * @var object
     */
    private static $_oInstance = null;

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
    protected function __construct()
    {}

    /**
     * 克隆
     */
    protected function __clone()
    {}
}

/**
 * sys debugger
 * 
 * @package system_kernel_lib_sys
 *         
 */
/**
 * sys debugger
 * 
 * @author jxu
 * @package system_kernel_lib_sys
 *         
 */
class sys_debugger
{

    /**
     * 系统debug实例
     * 
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 需要输出的信息
     * 
     * @var array
     */
    private $_aMessage = array();

    /**
     * 调试信息
     * 
     * @var array
     */
    private $_aDebugInfo = array();

    /**
     * 系统参数
     * 
     * @var array $_aParam
     */
    private $_aParam = array();

    /**
     * 是否开启debug
     * 
     * @var boolean
     */
    private $_bolNeedDebug = false;

    /**
     * 开始时间
     * 
     * @var float
     */
    private $_fStartTime;

    /**
     * 系统变量
     * 
     * @var object
     */
    private $_oVari;

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->_oVari = sys_variable::getInstance();
        $this->_fStartTime = $this->_oVari->getMicroTime();
        $mWeWantDebug = $this->_oVari->getParam('debug');
        if (null !== $mWeWantDebug) {
            if (1 == $mWeWantDebug) {
                $this->_oVari->setCookie('debug', 1, 600);
            } else {
                $this->_oVari->setCookie('debug', 0, 1);
            }
        }
        if (get_config('bDebug') and G_VERSION_TYPE === 'beta' and $mWeWantDebug) {
            $this->_bolNeedDebug = true;
        } else {
            $this->_bolNeedDebug = false;
        }
    }

    /**
     * 构造函数
     */
    private function __clone()
    {}

    /**
     * 获取实例
     * 
     * @return object
     */
    static function getInstance()
    {
        if (! (self::$_oInstance instanceof self)) {
            self::$_oInstance = new self();
        }
        return self::$_oInstance;
    }

    /**
     * 添加输出信息
     * 
     * @param string $p_sMsg            
     * @param boolean $p_bIsHTML            
     */
    function showMsg($p_sMsg, $p_bIsHTML = false)
    {
        if ($this->_bolNeedDebug) {
            $fTmpTime = $this->_oVari->getMicroTime();
            load_lib('/sys/util/string');
            $this->_aMessage[] = array(
                'fTime' => $fTmpTime,
                'fTimeCost' => $fTmpTime - $this->_fStartTime,
                'sMsg' => $p_bIsHTML ? $p_sMsg : sys_util_string::chgHtmlSpecialChars($p_sMsg)
            );
        }
    }

    /**
     * 开始调试信息
     * 
     * @param string $p_sModule            
     */
    function startDebug($p_sModule)
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule]['fStartTime'] = $this->_oVari->getMicroTime();
            $this->_aDebugInfo[$p_sModule]['iStartMemory'] = $this->_getMemoryUsage();
        }
    }

    /**
     * 结束调试信息
     * 
     * @param string $p_sModule            
     */
    function stopDebug($p_sModule = '')
    {
        if ($this->_bolNeedDebug) {
            $this->_aDebugInfo[$p_sModule]['fEndTime'] = $this->_oVari->getMicroTime();
            $this->_aDebugInfo[$p_sModule]['iEndMemory'] = $this->_getMemoryUsage();
        }
    }

    /**
     * 返回输出信息
     * 
     * @return array
     */
    function getMsgs()
    {
        return $this->_aMessage;
    }

    /**
     * 返回调试信息
     * 
     * @return array
     */
    function getDebugInfo()
    {
        return $this->_aDebugInfo;
    }

    /**
     * 设置debug的控制器
     * 
     * @param string $p_sController            
     */
    function setDebugController($p_sController)
    {
        $aExcludeController = get_config('aExcludeController', 'debug');
        if (in_array($p_sController, $aExcludeController)) {
            $this->_bolNeedDebug = false;
        }
    }

    /**
     * 获取系统参数
     * 
     * @return array
     */
    function getParams()
    {
        return array(
            'aPost' => $this->_oVari->getParams('post'),
            'aGet' => $this->_oVari->getParams('get'),
            'aURL' => $this->_oVari->getParams('url'),
            'aCookie' => $this->_oVari->getParams('cookie'),
            'aServer' => $this->_oVari->getParams('server'),
            'aConfig' => $this->_oVari->getParams('config')
        );
    }

    /**
     * 是否能够debug
     * 
     * @return true/false
     */
    function canDebug()
    {
        return $this->_bolNeedDebug;
    }

    /**
     * 获取内存使用量
     */
    private function _getMemoryUsage()
    {
        return function_exists('memory_get_usage') ? memory_get_usage() : 0;
    }

    /**
     * 判断IP是否能够打开Debug
     * 
     * @param unknown_type $p_sIP            
     */
    private function _canDebugIP($p_sIP)
    {
        $aAllowIPs = get_config('aAllowedIps', 'debug');
        $bCan = false;
        foreach ($aAllowIPs as $sPattern) {
            if (preg_match($sPattern, $p_sIP)) {
                $bCan = true;
                break;
            }
        }
        return true;
    }
}

/**
 * 调试函数
 *
 * 用法类似var_dump()
 * 支持任意个参数。
 */
function debug()
{
    $cnt = func_num_args();
    $values = func_get_args();
    
    if ($cnt > 1) {
        foreach ($values as $k => $v) {
            debug($v);
        }
    } else {
        $value = $values[0];
    }
    
    $echo = function ($value, $color, $type) {
        $len = '';
        
        if ($type === 'string') {
            $len = '(' . mb_strlen($value, 'UTF-8') . ')';
        }
        
        echo '<font color="', $color, '" style="font-family: arial;word-wrap: break-word;word-break: normal;"><b>', $type, $len, '</b> : ', $value, '</font><br>';
    };
    
    switch (true) {
        case is_string($value):
            $echo($value, 'red', 'string');
            break;
        
        case is_float($value):
            $echo($value, 'BlueViolet', 'float');
            break;
        
        case is_int($value):
            $echo($value, 'blue', 'int');
            break;
        
        case is_null($value):
            $echo('null', 'Coral ', 'null');
            break;
        
        case is_bool($value):
            $v = ($value) ? 'true' : 'false';
            $echo($v, 'green', 'bool');
            break;
        
        case is_array($value):
            echo '<b style="font-family:arial">array</b>(', count($value);
            echo ')<div style="margin:10px 20px;font-family:arial">';
            
            foreach ($value as $kk => $vv) {
                echo '<font color="#555">', $kk, '</font> => ', see($vv);
            }
            
            echo '</div>';
            break;
    }
}
