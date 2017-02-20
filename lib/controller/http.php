<?php

/**
 * lib_controller_http
 *
 * http协议控制器基类
 *
 * @package lib_sys
 */

/**
 * lib_controller_http
 *
 * http协议控制器基类
 */
abstract class lib_controller_http extends lib_sys_controller
{

    /**
     * 内部变量
     *
     * 存放响应头数据和页面数据
     *
     * @var array
     */
    protected $_aPri = [
        'aPageData' => [],
        'aHeaders' => []
    ];

    /**
     * 在控制器结束时执行（调度使用）
     *
     * @return void
     */
    function afterRequest()
    {
        // 发送头部信息
        foreach ($this->_aPri['aHeaders'] as $aHeader) {
            header($aHeader[0], $aHeader[1], $aHeader[2]);
        }
        lib_sys_logger::getInstance()->addLog('controller page data', json_encode($this->getPageData(), JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE), 'parameter');
        parent::afterRequest();
    }

    /**
     * 添加头部信息
     *
     * @param string $p_sValue            
     * @param boolean $p_bReplace            
     * @param int $p_iCode            
     * @return void
     */
    protected function addHeader($p_sValue, $p_bReplace = true, $p_iCode = null)
    {
        $this->_aPri['aHeaders'][] = array(
            $p_sValue,
            $p_bReplace,
            $p_iCode
        );
    }

    /**
     * 设置页面数据
     *
     * @param string $p_sKey            
     * @param mixed $p_mValue            
     * @return void
     */
    protected function setData($p_sKey, $p_mValue)
    {
        $this->_aPri['aPageData'][$p_sKey] = $p_mValue;
    }

    /**
     * 获取页面数据
     *
     * @param string $p_sKey            
     * @return mix
     */
    protected function getData($p_sKey)
    {
        return $this->_aPri['aPageData'][$p_sKey];
    }

    /**
     * 获取整个页面的数据（调度使用）
     *
     * @return array
     */
    function getPageData()
    {
        return $this->_aPri['aPageData'];
    }
}