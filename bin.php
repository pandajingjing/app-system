<?php

/**
 * system basic function
 * @package global
 */

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
 * 入口函数
 *
 * @param boolean $p_bHttpRequest            
 */
function bin($p_bHttpRequest = true)
{
    ob_start('ob_gzhandler');
    error_reporting(E_ALL);
    
    $oVar = lib_sys_var::getInstance();
    date_default_timezone_set($oVar->getConfig('sTimeZone', 'system'));
    mb_internal_encoding('utf8');
    // register_shutdown_function('Util_Sys_Handle::handleShutdown');
    // set_exception_handler('Util_Sys_Handle::handleException');
    // set_error_handler('Util_Sys_Handle::handleError');
    
    $oRouter = lib_sys_router::getInstance();
    $oRouter->parseURI($oVar->getParam('DISPATCH_PARAM', 'server'));
    $sControllerName = $oRouter->getControllerName();
    $oVar->setRouterParams($oRouter->getRouterParams());
    
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
    
    if ($p_bHttpRequest) {
        $oRelMethod = $oRelClass->getMethod('getDatas');
        $aPageDatas = $oRelMethod->invoke($oRelInstance);
        
        $oTpl = lib_sys_template::getInstance();
        $oTpl->setDatas($aPageDatas);
        $oTpl->render($sPagePath);
    }
}

/**
 * 调试函数
 *
 * 支持任意个参数。
 */
function debug()
{
    $iCnt = func_num_args();
    $aParamList = func_get_args();
    
    if (0 == $iCnt) {
        return;
    } elseif (1 == $iCnt) {
        $mParam = $aParamList[0];
        switch (true) {
            case is_string($mParam):
                echo '<p class="text-success">string(' . mb_strlen($mParam) . '):' . htmlspecialchars($mParam) . '</p>';
                break;
            case is_float($mParam):
                echo '<p class="text-info">float:' . $mParam . '</p>';
                break;
            case is_int($mParam):
                echo '<p class="text-info">int:' . $mParam . '</p>';
                break;
            case is_null($mParam):
                echo '<p class="text-danger">null</p>';
                break;
            case is_bool($mParam):
                echo '<p class="text-warning">' . ($mParam ? 'true' : 'false') . '</p>';
                break;
            case is_array($mParam):
                echo '<pre>' . var_export($mParam, true) . '</pre>';
                break;
            case is_object($mParam):
                echo '<pre>' . var_export($mParam, true) . '</pre>';
                break;
        }
    } else {
        foreach ($aParamList as $mParam) {
            debug($mParam);
        }
    }
}