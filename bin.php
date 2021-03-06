<?php

/**
 * bin
 * 
 * 框架入口文件,定义了自动加载函数,入口函数和简单的调试函数
 * 
 * @package global
 */

/**
 * 自动加载函数
 *
 * 根据<var>$G_PHP_DIR</var>加载对应类
 *
 * @param string $p_sClassName            
 * @return true|false
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
 * 框架引导文件会调用此函数进入框架,完成所有流程
 *
 * @return void
 */
function bin()
{
    ob_start('ob_gzhandler');
    error_reporting(E_ALL);
    
    $oDebugger = lib_sys_debugger::getInstance();
    $oDebugger->startDebug('Proccess');
    
    $oVar = lib_sys_var::getInstance();
    date_default_timezone_set($oVar->getConfig('sTimeZone', 'system'));
    mb_internal_encoding('utf8');
    register_shutdown_function('util_sys_handle::handleShutdown');
    // set_exception_handler('Util_Sys_Handle::handleException');
    // set_error_handler('Util_Sys_Handle::handleError');
    
    $oDebugger->startDebug('Parse Route');
    $oRouter = lib_sys_router::getInstance();
    $oRouter->parseURI($oVar->getParam('DISPATCH_PARAM', 'server'));
    $sControllerName = $oRouter->getControllerName();
    $oDebugger->showMsg('router find controller: ' . $sControllerName);
    $oVar->setRouterParam($oRouter->getRouterParam());
    $oDebugger->stopDebug('Parse Route');
    
    while (true) {
        $oDebugger->startDebug('Handle Controller: ' . $sControllerName);
        $oRelClass = new ReflectionClass($sControllerName);
        $oRelInstance = $oRelClass->newInstance();
        $oRelMethod = $oRelClass->getMethod('beforeRequest');
        $oRelMethod->invoke($oRelInstance);
        $oRelMethod = $oRelClass->getMethod('doRequest');
        $mReturn = $oRelMethod->invoke($oRelInstance);
        $oDebugger->showMsg('controller return: ' . $mReturn);
        $oRelMethod = $oRelClass->getMethod('afterRequest');
        $oRelMethod->invoke($oRelInstance);
        $oDebugger->stopDebug('Handle Controller: ' . $sControllerName);
        if (class_exists($mReturn)) { // 判断是否返回的是另外一个controller
            $sControllerName = $mReturn;
        } else {
            $sPagePath = $mReturn;
            break;
        }
    }
    
    $oDebugger->startDebug('Render Page: ' . $mReturn);
    $oRelMethod = $oRelClass->getMethod('getPageData');
    $aPageData = $oRelMethod->invoke($oRelInstance);
    
    $oTpl = lib_sys_template::getInstance();
    $oTpl->setPageData($aPageData);
    $oTpl->render($sPagePath);
    $oDebugger->stopDebug('Render Page: ' . $mReturn);
    
    $oDebugger->stopDebug('Proccess');
}

/**
 * 入口函数
 *
 * 框架引导文件会调用此函数进入框架,完成所有流程
 *
 * @return void
 */
function bin_cmd()
{
    error_reporting(E_ALL);
    
    $oDebugger = lib_sys_debugger::getInstance();
    $oDebugger->startDebug('Proccess');
    
    $oVar = lib_sys_var::getInstance();
    date_default_timezone_set($oVar->getConfig('sTimeZone', 'system'));
    mb_internal_encoding('utf8');
    register_shutdown_function('util_sys_handle::handleShutdown');
    // set_exception_handler('Util_Sys_Handle::handleException');
    // set_error_handler('Util_Sys_Handle::handleError');
    
    $oDebugger->startDebug('Parse Route');
    $oRouter = lib_sys_router::getInstance();
    $oRouter->parseURI($oVar->getParam('DISPATCH_PARAM', 'server'));
    $sControllerName = $oRouter->getControllerName();
    $oDebugger->showMsg('router find controller: ' . $sControllerName);
    $oVar->setRouterParam($oRouter->getRouterParam());
    $oDebugger->stopDebug('Parse Route');
    
    while (true) {
        $oDebugger->startDebug('Handle Controller: ' . $sControllerName);
        $oRelClass = new ReflectionClass($sControllerName);
        $oRelInstance = $oRelClass->newInstance();
        $oRelMethod = $oRelClass->getMethod('beforeRequest');
        $oRelMethod->invoke($oRelInstance);
        $oRelMethod = $oRelClass->getMethod('doRequest');
        $mReturn = $oRelMethod->invoke($oRelInstance);
        $oRelMethod = $oRelClass->getMethod('afterRequest');
        $oRelMethod->invoke($oRelInstance);
        $oDebugger->stopDebug('Handle Controller: ' . $sControllerName);
        if (class_exists($mReturn)) { // 判断是否返回的是另外一个controller
            $sControllerName = $mReturn;
        } else {
            $sPagePath = $mReturn;
            break;
        }
    }
    
    $oDebugger->stopDebug('Proccess');
}

/**
 * 调试函数
 *
 * 支持任意个参数,根据不同类型输出显示.配合bootstrap样式更佳.
 *
 * @return void
 */
function debug()
{
    $iCnt = func_num_args();
    $aParams = func_get_args();
    
    if (0 == $iCnt) {
        $aTmp = debug_backtrace();
        $aTmp = $aTmp[0];
        debug($aTmp['file'] . '::' . $aTmp['line']);
    } elseif (1 == $iCnt) {
        $mParam = $aParams[0];
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
        foreach ($aParams as $mParam) {
            debug($mParam);
        }
    }
}