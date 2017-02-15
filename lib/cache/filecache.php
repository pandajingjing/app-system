<?php

/**
 * lib_cache_filecache
 * @package system_lib
 */

/**
 * lib_cache_filecache
 *
 * @author jxu
 * @package system_lib
 */
class lib_cache_filecache
{

    /**
     * 服务器版本
     *
     * @var string
     */
    private static $_sVersion = '0.1';

    /**
     * 服务器路径
     *
     * @var array
     */
    private $_aServerPathList = [];

    /**
     * 服务器路径数量
     *
     * @var int
     */
    private $_iServerCnt = 0;

    /**
     * 构造函数
     */
    function __construct()
    {}

    /**
     * 向服务器中添加项目
     *
     * @param string $p_sKey            
     * @param mix $p_mValue            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function add($p_sKey, $p_mValue, $p_iLifeTime = 0)
    {
        if (isset($p_sKey[0])) {
            $mValue = $this->get($p_sKey);
            if (false === $mValue) {
                return $this->set($p_sKey, $p_mValue, $p_iLifeTime);
            } else {
                return false;
            }
        } else {
            trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
            return false;
        }
    }

    /**
     * 添加一台服务器
     *
     * @param string $p_sPath            
     * @param int $p_iWeight            
     * @return true/false
     */
    function addDir($p_sPath, $p_iWeight = 0)
    {
        $this->_aServerPathList = array_merge($this->_aServerPathList, array_fill(0, $p_iWeight, $p_sPath));
        $this->_iServerCnt += $p_iWeight;
        if (! in_array($p_sPath, $this->_aServerPathList)) {
            if (! is_dir($p_sPath)) {
                if (false === util_file::tryMakeDir($p_sPath, 0755, true)) {
                    throw new Exception(__CLASS__ . ': can not create path(' . $p_sPath . ').');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 批量添加缓存路径
     *
     * @param array $p_aDirList            
     * @return true/false
     */
    function addDirs($p_aDirList)
    {
        foreach ($p_aDirList as $aDir) {
            $bResult = $this->addDir($aDir[0], $aDir[1]);
            if (! $bResult) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除某个缓存Key
     *
     * @param string $p_sKey            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function delete($p_sKey, $p_iLifeTime = 0)
    {
        if ($p_iLifeTime > 0) {
            return $this->set($p_sKey, $this->get($p_sKey), $p_iLifeTime);
        } else {
            return util_file::tryDeleteFile($this->_dispatchCacheFile($p_sKey));
        }
    }

    /**
     * 批量删除某些缓存Keys
     *
     * @param array $p_aKeyLists            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function deleteMulti($p_aKeyLists, $p_iLifeTime = 0)
    {
        foreach ($p_aKeyLists as $sKey) {
            $bResult = $this->delete($sKey, $p_iLifeTime);
            if (! $bResult) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除服务器中所有项目
     *
     * @return true/false
     */
    function flush()
    {
        $aUniqServerPathList = array_unique($this->_aServerPathList);
        foreach ($aUniqServerPathList as $sServerPath) {
            if (! util_file::tryDeleteDir($sServerPath)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取某个缓存Key
     *
     * @param string $p_sKey            
     * @return mix
     */
    function get($p_sKey)
    {
        if (isset($p_sKey[0])) {
            $sCacheFilePath = $this->_dispatchCacheFile($p_sKey);
            if (! file_exists($sCacheFilePath)) {
                return false;
            }
            $mCache = util_file::tryReadFile($sCacheFilePath);
            if (false === $mCache) {
                return false;
            } else {
                $mCache = $this->_cache2Value($mCache);
                if (false === $mCache) {
                    return false;
                } else {
                    if (0 == $mCache['iExpireTime']) {
                        return $mCache['mData'];
                    } else {
                        if (lib_sys_var::getInstance()->getRealTime() > $mCache['iExpireTime']) {
                            util_file::tryDeleteFile($sCacheFilePath);
                            return false;
                        } else {
                            return $mCache['mData'];
                        }
                    }
                }
            }
        } else {
            trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
            return false;
        }
    }

    /**
     * 批量获取某些缓存Keys
     *
     * @param array $p_aKeyList            
     * @return true/false
     */
    function getMulti($p_aKeyList)
    {
        if (is_array($p_aKeyList)) {
            $aReturnList = [];
            foreach ($p_aKeyList as $iIndex => $sKey) {
                $mTmp = $this->get($sKey);
                if (false !== $mTmp) {
                    $aReturnList[$sKey] = $mTmp;
                }
            }
            return $aReturnList;
        } else {
            return false;
        }
    }

    /**
     * 获取服务器版本
     *
     * @return string
     */
    function getVersion()
    {
        return self::$_sVersion;
    }

    /**
     * 替换服务器中的项目
     *
     * @param string $p_sKey            
     * @param mix $p_mValue            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function replace($p_sKey, $p_mValue, $p_iLifeTime = 0)
    {
        if (isset($p_sKey[0])) {
            $mValue = $this->get($p_sKey);
            if (false === $mValue) {
                return false;
            } else {
                return $this->set($p_sKey, $p_mValue, $p_iLifeTime);
            }
        } else {
            trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
            return false;
        }
    }

    /**
     * 在服务器中保存项目
     *
     * @param string $p_sKey            
     * @param mix $p_mValue            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function set($p_sKey, $p_mValue, $p_iLifeTime = 0)
    {
        if (isset($p_sKey[0])) {
            $mCache = $this->_value2Cache($p_mValue, $this->_getExpireTime($p_iLifeTime));
            $sFileName = $this->_dispatchCacheFile($p_sKey);
            if (false === util_file::tryWriteFile($sFileName, $mCache, LOCK_EX)) {
                if (false === util_file::tryMakeDir(dirname($sFileName), 0755, true)) {
                    return false;
                } else {
                    return util_file::tryWriteFile($sFileName, $mCache);
                }
                return false;
            } else {
                return true;
            }
        } else {
            trigger_error(__CLASS__ . ': key cannot be empty.', E_USER_ERROR);
            return false;
        }
    }

    /**
     * 在服务器中保存项目
     *
     * @param array $p_aData            
     * @param int $p_iLifeTime            
     * @return true/false
     */
    function setMulti($p_aData, $p_iLifeTime = 0)
    {
        if (is_array($p_aData)) {
            $aReturnList = [];
            foreach ($p_aData as $sKey => $mValue) {
                $aReturnList[$sKey] = $this->set($sKey, $mValue, $p_iLifeTime);
            }
            return $aReturnList;
        } else {
            return false;
        }
    }

    /**
     * 分配存储路径
     *
     * @param string $p_sKey            
     * @return string
     */
    private function _dispatchCacheFile($p_sKey)
    {
        $iKey = abs(crc32($p_sKey));
        $sDir = '';
        while ($iKey > 0) {
            $sSubDir = $iKey % 100;
            $sDir = $sSubDir . DIRECTORY_SEPARATOR . $sDir;
            $iKey = intval($iKey / 100);
        }
        return $this->_aServerPathList[$iKey % $this->_iServerCnt] . DIRECTORY_SEPARATOR . $sDir . $p_sKey;
    }

    /**
     * 得到缓存过期时间
     *
     * @param int $p_iLifeTime            
     * @return int
     */
    private function _getExpireTime($p_iLifeTime)
    {
        if ($p_iLifeTime > 2591999) {
            return $p_iLifeTime;
        } else {
            return 0 == $p_iLifeTime ? 0 : lib_sys_var::getInstance()->getRealTime() + $p_iLifeTime;
        }
    }

    /**
     * 将数据转换为缓存数据
     *
     * @param mix $p_mValue            
     * @param int $p_iExpireTime            
     * @return mix
     */
    private function _value2Cache($p_mValue, $p_iExpireTime)
    {
        return gzcompress(serialize([
            'mData' => $p_mValue,
            'iExpireTime' => $p_iExpireTime
        ]), 9);
    }

    /**
     * 将缓存数据转换为数据
     *
     * @param mix $p_mCache            
     * @return mix/false
     */
    private function _cache2Value($p_mCache)
    {
        $mTmp = unserialize(gzuncompress($p_mCache));
        if (is_array($mTmp) and isset($mTmp['mData']) and isset($mTmp['iExpireTime'])) {
            return $mTmp;
        } else {
            return false;
        }
    }
}