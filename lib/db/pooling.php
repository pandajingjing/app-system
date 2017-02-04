<?php
/**
 * db pooling
 * @package system_common_lib_db
 */
/**
 * db pooling
 * @author jxu
 * @package system_common_lib_db
 */
class db_pooling{

	/**
	 * 数据库连接池实例
	 * @var object
	 */
	private static $_oInstance = null;

	/**
	 * 数据库连接池
	 * @var array
	 */
	private static $_aConnect = array();

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
	 * 获取数据库连接
	 * @param string $p_sDBName
	 * @return object
	 */
	static function getConnect($p_sDBName){
		if(!isset(self::$_aConnect[$p_sDBName])){
			self::$_aConnect[$p_sDBName] = self::_loadDB($p_sDBName);
		}
		return self::$_aConnect[$p_sDBName];
	}

	/**
	 * 加载数据库连接
	 * @param string $p_sDBName
	 * @return object
	 */
	private static function _loadDB($p_sDBName){
		load_lib('/db/pdo');
		$aConfig = get_config($p_sDBName, 'database');
		$oDatabase = new db_pdo($aConfig['sDSN'], $aConfig['sUsername'], $aConfig['sUserpwd']);
		foreach($aConfig['aInitsql'] as $sSQL){
			$oDatabase->exec($sSQL);
		}
		return $oDatabase;
	}
}