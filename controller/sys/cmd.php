<?php

/**
 * controller_sys_cmd
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_cmd
 *
 * @author jxu
 */
abstract class controller_sys_cmd extends lib_sys_controller
{

    /**
     * 程序开始时间
     *
     * @var float
     */
    private $_fStartTime = '';

    /**
     * 程序结束时间
     *
     * @var float
     */
    private $_fEndTime = '';

    /**
     * 构造函数
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 在控制器开始时执行（调度使用）
     *
     * @todo 要修改脚本部分
     */
    function beforeRequest()
    {
        parent::beforeRequest();
        $this->_fStartTime = $this->getRealTime(true);
        $this->stdOut('PHP Console Type Start: ' . get_class($this));
    }

    /**
     * 在控制器结束时执行（调度使用）
     */
    function afterRequest()
    {
        $this->stdOut('PHP Console Type End');
        $this->_fEndTime = $this->getRealTime(true);
        $this->stdOut('Execute time: ' . ($this->_fEndTime - $this->_fStartTime));
        parent::afterRequest();
    }

    /**
     * 输出信息到控制台
     *
     * @param string $p_sMsg            
     */
    protected function stdOut($p_sMsg)
    {
        $sContent = date('Ymd H:i:s') . ' ' . $p_sMsg . PHP_EOL;
        echo $sContent;
    }

    /**
     * 调用系统函数
     *
     * @param string $p_sCmd            
     * @return array
     */
    protected function terminal($p_sCmd)
    {
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
                    exec($p_sCmd, $sOutput, $iRetVar);
                    $sOutput = implode(PHP_EOL, $sOutput);
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
        return array(
            'output' => $sOutput,
            'status' => $iRetVar
        );
    }
}