<?php

/**
 * client pooling
 * @package system_common_lib_client
 */
/**
 * client pooling
 *
 * @author jxu
 * @package system_common_lib_client
 */
class lib_client_pooling
{

    /**
     * 客户端连接池实例
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 客户端连接池
     *
     * @var array
     */
    private $_aClients = [];

    /**
     * 构造函数
     */
    private function __construct()
    {}

    /**
     * 析构函数
     */
    function __destruct()
    {}

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
     * 获取客户端连接
     *
     * @param string $p_sClientType            
     * @return object
     */
    function getClient($p_sClientType)
    {
        if (! isset($this->_aClients[$p_sClientType])) {
            $this->_aClients[$p_sClientType] = $this->_loadClient($p_sClientType);
        }
        return $this->_aClients[$p_sClientType];
    }

    /**
     * 加载客户端
     *
     * @param string $p_sClientType            
     * @return object
     */
    private function _loadClient($p_sClientType)
    {
        switch ($p_sClientType) {
            case 'curl':
            default:
                $oClient = new lib_client_curl();
                break;
        }
        return $oClient;
    }
}