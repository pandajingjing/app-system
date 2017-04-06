<?php

/**
 * lib_controller_cmd
 *
 * 命令行控制器基类
 *
 * @package lib_sys
 */

/**
 * lib_controller_cmd
 *
 * 命令行控制器基类
 */
abstract class lib_controller_cmd extends lib_sys_controller
{

    /**
     * 命令行开始时间
     *
     * @var float
     */
    private $_fStartTime = '';

    /**
     * 命令行结束时间
     *
     * @var float
     */
    private $_fEndTime = '';

    /**
     * 在控制器开始时执行（调度使用）
     *
     * @return void
     */
    function beforeRequest()
    {
        set_time_limit(1800);
        parent::beforeRequest();
        $this->_fStartTime = $this->getRealTime(true);
        $this->stdOut('PHP Console Type Start: ' . get_class($this));
    }

    /**
     * 在控制器结束时执行（调度使用）
     *
     * @return void
     */
    function afterRequest()
    {
        $this->stdOut('PHP Console Type End');
        $this->_fEndTime = $this->getRealTime(true);
        $iResult = ($this->_fEndTime - $this->_fStartTime); // 1秒=1000毫秒=1000000微秒
        if ($iResult > 1) {
            $sUnit = 's';
        } else {
            $iResult = $iResult * 1000;
            if ($iResult > 1) {
                $sUnit = 'ms';
            } else {
                $iResult = $iResult * 1000;
                $sUnit = 'μs';
            }
        }
        $this->stdOut('Execute time: ' . $iResult . ' ' . $sUnit);
        parent::afterRequest();
    }

    /**
     * 输出信息到控制台
     *
     * @param string $p_sMsg            
     * @return void
     */
    protected function stdOut($p_sMsg)
    {
        echo date('Ymd H:i:s') . ' ' . $p_sMsg . PHP_EOL;
    }

    /**
     * 调用系统函数
     *
     * @param string $p_sCmd            
     * @return array
     */
    protected function excuteCmd($p_sCmd)
    {
        $iRetVar = 0;
        $sOutput = '';
        if (function_exists('system')) {
            // system
            ob_start();
            system($p_sCmd, $iRetVar);
            $sOutput = ob_get_contents();
            ob_end_clean();
        } else {
            if (function_exists('passthru')) {
                // passthru
                ob_start();
                passthru($p_sCmd, $iRetVar);
                $sOutput = ob_get_contents();
                ob_end_clean();
            } else {
                if (function_exists('exec')) {
                    // exec
                    exec($p_sCmd, $aOutput, $iRetVar);
                    $sOutput = implode(PHP_EOL, $aOutput);
                } else {
                    if (function_exists('shell_exec')) {
                        // shell_exec
                        $sOutput = shell_exec($p_sCmd);
                    } else {
                        $sOutput = 'Command execution is not possible on this system.';
                        $iRetVar = 1;
                    }
                }
            }
        }
        return [
            'sOutput' => $sOutput,
            'iStatus' => $iRetVar
        ];
    }
}