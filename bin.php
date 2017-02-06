<?php
/**
 * system basic function
 * @package global
 */
/**
 * 配置
 *
 * @var array
 */
$G_CONFIG = [];

/**
 * 自动加载函数
 *
 * @param string $p_sClassName            
 * @return boolean
 */
function __autoload($p_sClassName)
{
    global $G_PHP_DIR;
    $aTmp = explode('_', $p_sClassName);
    $sSubPath = join(DIRECTORY_SEPARATOR, $aTmp);
    foreach ($G_PHP_DIR as $sLoadDir) {
        $sLoadFilePath = $sLoadDir . DIRECTORY_SEPARATOR . $sSubPath . '.php';
        if (file_exists($sLoadFilePath)) {
            include $sLoadFilePath;
            return true;
            break;
        }
    }
    return false;
}

/**
 * 加载配置信息
 *
 * @param string $p_sKey            
 * @param string $p_sClass            
 */
function get_config($p_sKey, $p_sClass = 'common')
{
    global $G_CONFIG_DIR, $G_CONFIG;
    if (! isset($G_CONFIG[$p_sClass])) {
        foreach ($G_CONFIG_DIR as $sConfigDir) {
            $sConfigFilePath = $sConfigDir . DIRECTORY_SEPARATOR . $p_sClass . '.php';
            if (file_exists($sConfigFilePath)) {
                include $sConfigFilePath;
            }
        }
    }
    if (isset($G_CONFIG[$p_sClass][$p_sKey])) {
        return $G_CONFIG[$p_sClass][$p_sKey];
    } else {
        throw new Exception('Miss Config Key (' . $p_sKey . ') in class (' . $p_sClass . ').', 0);
    }
}

/**
 * 入口函数
 */
function bin()
{
    error_reporting(E_ALL);
    ob_start('ob_gzhandler');
    date_default_timezone_set(get_config('timezone', 'system'));
    mb_internal_encoding('utf8');
    // register_shutdown_function(get_config('shutdown_handle', 'system'));
    // set_exception_handler(get_config('exception_handle', 'system'));
    // set_error_handler(get_config('error_handle', 'system'));
    
    $oVar = lib_sys_var::getInstance();
    $oRouter = lib_sys_router::getInstance();
    
    $oRouter->parseRoute($oVar->getParam('DISPATCH_PARAM', 'server'));
    
    $sControllerName = $oRouter->getControllerName();
    $oVar->setRouterParam($oRouter->getRouterParam());
    
    while (true) {
        $oRelClass = new ReflectionClass($sControllerName);
        $oRelInstance = $oRelClass->newInstance();
        $oRelMethod = $oRelClass->getMethod('beforeRequest');
        $oRelMethod->invoke($oRelInstance);
        $oRelMethod = $oRelClass->getMethod('doRequest');
        $mReturn = $oRelMethod->invoke($oRelInstance);
        $oRelMethod = $oRelClass->getMethod('afterRequest');
        $oRelMethod->invoke($oRelInstance);
        if (class_exists($mReturn)) { // 判断是否返回的是另外一个controller
            $sControllerName = $mReturn;
        } else {
            $sPagePath = $mReturn;
            break;
        }
    }
    
    $oRelMethod = $oRelClass->getMethod('getDatas');
    $aPageDatas = $oRelMethod->invoke($oRelInstance);
    
    $oTpl = lib_sys_template::getInstance();
    $oTpl->setPageData($aPageDatas);
    $oTpl->render($sPagePath);
}