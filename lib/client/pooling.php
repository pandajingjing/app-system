<?php
/**
 * client pooling
 * @package system_common_lib_client
 */
/**
 * client pooling
 * @author jxu
 * @package system_common_lib_client
 */
class client_pooling{

	/**
	 * 客户端连接池实例
	 * @var object
	 */
	private static $_oInstance = null;

	/**
	 * 客户端连接池
	 * @var array
	 */
	private static $_aClient = array();

	/**
	 * 构造函数
	 */
	private function __construct(){

	}

	/**
	 * 析构函数
	 */
	function __destruct(){

	}

	/**
	 * 构造函数
	 */
	private function __clone(){

	}

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
	 * 获取客户端连接
	 * @param string $p_sClientType
	 * @return object
	 */
	static function getClient($p_sClientType = ''){
		$p_sClientType = '' == $p_sClientType ? get_config('sDefaultType', 'client') : $p_sClientType;
		if(!isset(self::$_aClient[$p_sClientType])){
			self::$_aClient[$p_sClientType] = self::_loadClient($p_sClientType);
		}
		return self::$_aClient[$p_sClientType];
	}

	/**
	 * 加载客户端
	 * @param string $p_sClientType
	 * @return object
	 */
	private static function _loadClient($p_sClientType){
		switch($p_sClientType){
			case 'curl':
			default:
				load_lib('/client/curl');
				$oClient = new client_curl();
				break;
		}
		return $oClient;
	}
}