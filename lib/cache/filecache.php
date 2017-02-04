<?php
/**
 * cache filecache
 * @package system_common_lib_cache
 */
load_lib('/util/file');
/**
 * 内容是否压缩
 * @var int
 */

define('FILECACHE_COMPRESSED', 2);
/**
 * cache filecache
 * @author jxu
 * @package system_common_lib_cache
 */
class cache_filecache{

	/**
	 * 服务器版本
	 * @var string
	 */
	private static $_sVersion = '0.1';

	/**
	 * 服务器路径
	 * @var array
	 */
	private $_aServerPath = array();

	/**
	 * 服务器路径数量
	 * @var int
	 */
	private $_iServerCnt = 0;

	/**
	 * 缓存数据与过期时间数据分隔符
	 * @var string
	 */
	private static $_sItemSeparator;

	/**
	 * 当前时间
	 * @var int
	 */
	private static $_iCurrentTime;

	/**
	 * 是否打开缓存压缩
	 * @var int
	 */
	private static $_iFileCacheCompressed = 0;

	/**
	 * 构造函数
	 */
	function __construct(){
		self::$_iCurrentTime = sys_variable::getInstance()->getTime();
		self::$_sItemSeparator = chr(26) . chr(26) . chr(26);
	}

	/**
	 * 向服务器中添加项目
	 * @param string $p_sKey
	 * @param mix $p_mValue
	 * @param int $p_iFlag
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function add($p_sKey, $p_mValue, $p_iFlag = 0, $p_iLifeTime = 0){
		if(isset($p_sKey[0])){
			$mValue = $this->get($p_sKey);
			if(false === $mValue){
				return $this->set($p_sKey, $p_mValue, $p_iFlag, $p_iLifeTime);
			}else{
				return false;
			}
		}else{
			trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
			return false;
		}
	}

	/**
	 * 添加一台服务器
	 * @param string $p_sPath
	 * @param int $p_iPort
	 * @param int $p_iWeight
	 * @return true/false
	 */
	function addServer($p_sPath, $p_iPort = 0, $p_iWeight = 0){
		if(!in_array($p_sPath, $this->_aServerPath)){
			if(!is_dir($p_sPath)){
				if(false === util_file::tryMakeDir($p_sPath, 0755, true)){
					trigger_error(__CLASS__ . ': can not create path(' . $p_sPath . ').', E_USER_ERROR);
					return false;
				}
			}
			$this->_aServerPath[] = $p_sPath;
			++$this->_iServerCnt;
		}
		return true;
	}

	/**
	 * 批量添加服务器
	 * @param array $p_aServers
	 * @return true/false
	 */
	function addServers($p_aServers){
		foreach($p_aServers as $sPath){
			$bResult = $this->addServer($sPath);
			if(!$bResult){
				return false;
			}
		}
		return true;
	}

	/**
	 * 删除某个缓存Key
	 * @param string $p_sKey
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function delete($p_sKey, $p_iLifeTime = 0){
		return util_file::tryDeleteFile($this->_dispatchCacheFile($p_sKey));
	}

	/**
	 * 批量删除某些缓存Keys
	 * @param array $p_aKeys
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function deleteMulti($p_aKeys, $p_iLifeTime = 0){
		foreach($p_aKeys as $sKey){
			$bResult = $this->delete($sKey);
			if(!$bResult){
				return false;
			}
		}
		return true;
	}

	/**
	 * 删除服务器中所有项目
	 * @return true/false
	 */
	function flush(){
		foreach($this->_aServerPath as $sServerPath){
			if(!util_file::tryDeleteDir($sServerPath)){
				return false;
			}
		}
		return true;
	}

	/**
	 * 获取某个缓存Key
	 * @param string $p_sKey
	 * @return mix
	 */
	function get($p_sKey){
		if(isset($p_sKey[0])){
			$sCacheFilePath = $this->_dispatchCacheFile($p_sKey);
			if(!file_exists($sCacheFilePath)){
				return false;
			}
			$mCache = util_file::tryReadFile($sCacheFilePath);
			if(false === $mCache){
				return false;
			}else{
				$mCache = $this->_cache2Value($mCache);
				if(false === $mCache){
					return false;
				}else{
					if(self::$_iCurrentTime > $mCache[2]){
						util_file::tryDeleteFile($sCacheFilePath);
						return false;
					}else{
						return $mCache[0];
					}
				}
			}
		}else{
			trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
			return false;
		}
	}

	/**
	 * 批量获取某些缓存Keys
	 * @param array $p_aKeys
	 * @return true/false
	 */
	function getMulti($p_aKeys){
		if(is_array($p_aKeys)){
			$aReturn = array();
			foreach($p_aKeys as $iIndex => $sKey){
				$mTmp = $this->get($sKey);
				if($mTmp){
					$aReturn[$sKey] = $mTmp;
				}
			}
			return $aReturn;
		}else{
			return false;
		}
	}

	/**
	 * 获取服务器版本
	 * @return string
	 */
	function getVersion(){
		return self::$_sVersion;
	}

	/**
	 * 替换服务器中的项目
	 * @param string $p_sKey
	 * @param mix $p_mValue
	 * @param int $p_iFlag
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function replace($p_sKey, $p_mValue, $p_iFlag = 0, $p_iLifeTime = 0){
		if(isset($p_sKey[0])){
			$mValue = $this->get($p_sKey);
			if(false === $mValue){
				return false;
			}
			return $this->set($p_sKey, $p_mValue, $p_iFlag, $p_iLifeTime);
		}else{
			trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
			return false;
		}
	}

	/**
	 * 在服务器中保存项目
	 * @param string $p_sKey
	 * @param mix $p_mValue
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function set($p_sKey, $p_mValue, $p_iLifeTime = 0){
		if(isset($p_sKey[0])){
			$iExpireTime = $this->_getExpireTime($p_iLifeTime);
			$mCache = $this->_value2Cache($p_mValue, self::$_iFileCacheCompressed, $iExpireTime);
			$sFileName = $this->_dispatchCacheFile($p_sKey);
			if(false === util_file::tryMakeDir(dirname($sFileName), 0755, true)){
				return false;
			}else{
				if(false === util_file::tryWriteFile($sFileName, $mCache)){
					return false;
				}else{
					return true;
				}
			}
		}else{
			trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
			return false;
		}
	}

	/**
	 * 在服务器中保存项目
	 * @param array $p_aData
	 * @param int $p_iLifeTime
	 * @return true/false
	 */
	function setMulti($p_aData, $p_iLifeTime = 0){
		if(is_array($p_aData)){
			$aReturn = array();
			foreach($p_aData as $sKey => $mValue){
				$aReturn[$sKey] = $this->_set($sKey, $mValue, self::$_iFileCacheCompressed, $p_iLifeTime);
			}
			return $aReturn;
		}else{
			return false;
		}
	}

	/**
	 * 分配存储路径
	 * @param string $p_sKey
	 * @return string
	 */
	private function _dispatchCacheFile($p_sKey){
		$iKey = abs(crc32($p_sKey));
		$sDir = '';
		while($iKey > 0){
			$sSubDir = $iKey % 100;
			$sDir = $sSubDir . DIRECTORY_SEPARATOR . $sDir;
			$iKey = intval($iKey / 100);
		}
		return $this->_aServerPath[$iKey % $this->_iServerCnt] . DIRECTORY_SEPARATOR . $sDir . $p_sKey;
	}

	/**
	 * 得到缓存过期时间
	 * @param int $p_iLifeTime
	 * @return int
	 */
	private function _getExpireTime($p_iLifeTime){
		return 0 == $p_iLifeTime ? '' : self::$_iCurrentTime + $p_iLifeTime;
	}

	/**
	 * 将数据转换为缓存数据
	 * @param mix $p_mValue
	 * @param int $p_iFlag
	 * @param int $p_iExpireTime
	 * @return mix
	 */
	private function _value2Cache($p_mValue, $p_iFlag, $p_iExpireTime){
		$sValue = urlencode(serialize($p_mValue));
		if(self::$_iFileCacheCompressed == $p_iFlag){
			$sValue = gzcompress($sValue, 9);
		}
		return $sValue . self::$_sItemSeparator . $p_iFlag . self::$_sItemSeparator . $p_iExpireTime;
	}

	/**
	 * 将缓存数据转换为数据
	 * @param mix $p_mCache
	 * @return mix/false
	 */
	private function _cache2Value($p_mCache){
		$aCache = explode(self::$_sItemSeparator, $p_mCache);
		if(3 == count($aCache)){
			if(self::$_iFileCacheCompressed == $aCache[1]){
				$aCache[0] = gzuncompress($aCache[0]);
			}
			$aCache[0] = unserialize(urldecode($aCache[0]));
			return $aCache;
		}else{
			return false;
		}
	}
}