<?php

/**
 * controller_sys_http
 * @author jxu
 * @package system_controller_sys
 */
/**
 * controller_sys_http
 *
 * @author jxu
 */
abstract class controller_sys_http extends lib_sys_controller
{

    /**
     * 内部变量
     *
     * @var array
     */
    protected $_aPri = [
        'aPageData' => [],
        'aHeader' => []
    ];

    /**
     * 在控制器结束时执行（调度使用）
     */
    function afterRequest()
    {
        // 发送头部信息
        foreach ($this->_aPri['aHeader'] as $aHeader) {
            header($aHeader[0], $aHeader[1], $aHeader[2]);
        }
        parent::afterRequest();
    }

    /**
     * 添加头部信息
     *
     * @param string $p_sValue            
     * @param boolean $p_bReplace            
     * @param int $p_iCode            
     */
    protected function addHeader($p_sValue, $p_bReplace = true, $p_iCode = null)
    {
        $this->_aPri['aHeader'][] = array(
            $p_sValue,
            $p_bReplace,
            $p_iCode
        );
    }

    /**
     * 设置Page数据
     *
     * @param string $p_sKey            
     * @param mixed $p_mValue            
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
     * 获取Page数据（调度使用）
     *
     * @return array
     */
    function getDatas()
    {
        return $this->_aPri['aPageData'];
    }
}