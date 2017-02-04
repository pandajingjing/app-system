<?php
/**
 * cache pooling
 * @package system_kernel_lib_cache
 */
/**
 * cache pooling
 * @author jxu
 * @package system_kernel_lib_cache
 */
class cache_pooling{

	/**
	 * 缓存连接池实例
	 * @var object
	 */
	private static $_oInstance = null;

	/**
	 * 缓存连接池
	 * @var array
	 */
	private static $_aCache = array();

	/**
	 * 构造函数
	 */
	private function __construct(){}

	/**
	 * 析构函数
	 */
	function __destruct(){}

	/**
	 * 构造函数
	 */
	private function __clone(){}

	/**
	 * 获取实例
	 * @return object
	 */
	static function getInstance(){
		if(!(self::$_oInstance instanceof self)){
			self::$_oInstance = new self();
		}
		return self::$_oInstance;
	}

	/**
	 * 获取缓存连接
	 * @param string $p_sCacheName
	 * @return object
	 */
	static function getCache($p_sCacheName){
		if(!isset(self::$_aCache[$p_sCacheName])){
			self::$_aCache[$p_sCacheName] = self::_loadCache($p_sCacheName);
		}
		return self::$_aCache[$p_sCacheName];
	}

	/**
	 * 加载缓存
	 * @param string $p_sCacheName
	 * @return object
	 */
	private static function _loadCache($p_sCacheName){
		$aConfig = get_config($p_sCacheName, 'cache');
		switch($aConfig['sType']){
			case 'file':
				load_lib('/cache/filecache');
				$oCache = new cache_filecache();
				$oCache->addServers($aConfig['aServer']);
				break;
			case 'memd':
				load_lib('/cache/memcached');
				$oCache = new cache_memcached();
				$oCache->addServers($aConfig['aServer']);
				break;
			case 'mem':
			default:
				load_lib('/cache/memcache');
				$oCache = new cache_memcache();
				foreach($aConfig['aServer'] as $aServer){
					$oCache->addServer($aServer['sIP'], $aServer['iPort']);
				}
				break;
		}
		return $oCache;
	}
}