<?php

/**
 * cache pooling
 * @package system_kernel_lib_cache
 */
/**
 * cache pooling
 *
 * @author jxu
 * @package system_kernel_lib_cache
 */
class lib_cache_pooling
{

    /**
     * 缓存连接池实例
     *
     * @var object
     */
    private static $_oInstance = null;

    /**
     * 缓存连接池
     *
     * @var array
     */
    private $_aCache = [];

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
     * 获取缓存连接
     *
     * @param string $p_sCacheName            
     * @return object
     */
    function getCache($p_sCacheName)
    {
        if (! isset($this->_aCache[$p_sCacheName])) {
            $this->_aCache[$p_sCacheName] = $this->_loadCache($p_sCacheName);
        }
        return $this->_aCache[$p_sCacheName];
    }

    /**
     * 加载缓存
     *
     * @param string $p_sCacheName            
     * @return object
     */
    private function _loadCache($p_sCacheName)
    {
        $aConfig = lib_sys_var::getInstance()->getConfig($p_sCacheName, 'cache');
        switch ($aConfig['sType']) {
            case 'file':
                $oCache = new lib_cache_filecache();
                $oCache->addDirs($aConfig['aDirList']);
                break;
            case 'memcached':
                $oCache = new lib_sys_memcached();
                $oCache->addServers($aConfig['aServerList']);
                break;
            case 'mem':
            default:
                $oCache = new lib_cache_filecache();
                $oCache->addDir($aConfig['aDirList']);
                break;
        }
        return $oCache;
    }
}