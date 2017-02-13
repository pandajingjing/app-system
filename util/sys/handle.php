<?php

/**
 * util_sys_handle
 * @author jxu
 * @package system_util_sys
 */

/**
 * 系统处理工具
 *
 * @author jxu
 *        
 */
class util_sys_handle
{

    /**
     * 错误处理函数
     *
     * @param int $p_iErrNo            
     * @param string $p_sErrStr            
     * @param string $p_sErrFile            
     * @param int $p_iErrLine            
     * @param array $p_aErrContext            
     * @return boolean
     */
    static function handleError($p_iErrNo, $p_sErrStr, $p_sErrFile, $p_iErrLine, $p_aErrContext)
    {
        if (E_STRICT == $p_iErrNo) {
            return true;
        }
        $aLevelName = array(
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT'
        );
        $aErrInfo = array(
            'sInfo' => $aLevelName[$p_iErrNo],
            'sMsg' => $p_sErrStr,
            'sFile' => $p_sErrFile,
            'iLine' => $p_iErrLine
        );
        $aDebugTrace = debug_backtrace();
        print_r($aErrInfo);
        print_r($aDebugTrace);
        // send_trace($aErrInfo, $aDebugTrace);
        $aStopLevel = array(
            E_ERROR,
            E_WARNING,
            E_CORE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_USER_ERROR,
            E_USER_WARNING
        );
        if (in_array($p_iErrNo, $aStopLevel)) {
            exit();
        } else {
            return true;
        }
    }

    /**
     * 异常处理函数
     *
     * @param object $p_oException            
     */
    static function handleException($p_oException)
    {
        $aErrInfo = array(
            'sInfo' => get_class($p_oException),
            'sMsg' => $p_oException->getMessage(),
            'sFile' => $p_oException->getFile(),
            'iLine' => $p_oException->getLine()
        );
        print_r($p_oException);
        // send_trace($aErrInfo, $p_oException->getTrace());
        exit();
    }

    /**
     * 进程结束处理函数
     */
    static function handleShutdown()
    {
        // $oDebugger = sys_debugger::getInstance();
        // if ($oDebugger->canDebug()) {
        // $oDebugger->stopDebug('System');
        // $sComponentPath = '/sys/debug';
        // load_component($sComponentPath);
        // $sComponentName = path_to_componentname($sComponentPath);
        // $oRelClass = new ReflectionClass($sComponentName);
        // $oRelInstance = $oRelClass->newInstance();
        // $oRelMethod = $oRelClass->getMethod('doComponent');
        // $oRelMethod->invoke($oRelInstance, $sComponentPath);
        // }
        // // 发送黑盒子日志到RabbitMQ服务器
        // load_lib('/bll/blackbox');
        // if (class_exists('bll_blackbox')) {
        // bll_blackbox::commit();
        // }
        lib_sys_logger::getInstance()->writeLog();
    }
}