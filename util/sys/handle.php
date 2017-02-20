<?php

/**
 * util_sys_handle
 *
 * 框架对于异常,错误和结束时进行处理的相关方法,应避免应用直接使用
 *
 * @package util_sys
 */

/**
 * util_sys_handle
 *
 * 框架对于异常,错误和结束时进行处理的相关方法,应避免应用直接使用
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
     * @return true|false
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
     * @return void
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
     *
     * @return void
     */
    static function handleShutdown()
    {
        $oDebugger = lib_sys_debugger::getInstance();
        if ($oDebugger->canDebug()) {
            $oTpl = lib_sys_template::getInstance();
            $oTpl->setPageData([
                'aMessages' => $oDebugger->getMsgs(),
                'aDebugInfo' => $oDebugger->getDebugInfo(),
                'aAllParam' => $oDebugger->getAllParam()
            ]);
            $oTpl->render('component_sys_debug');
        }
        lib_sys_logger::getInstance()->writeLog();
    }
}