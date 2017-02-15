<?php

/**
 * lib_sys_orm
 * @author jxu
 * @package system_lib_sys
 */

/**
 * lib_sys_orm
 *
 * @author jxu
 */
abstract class lib_sys_orm
{

    /**
     * Master数据库连接名,在子类中配置
     *
     * @var string
     */
    protected $_sMasterDBName = '';

    /**
     * Slave数据库连接名,在子类中配置
     *
     * @var string
     */
    protected $_sSlaveDBName = '';

    /**
     * 表名称
     *
     * @var string
     */
    protected $_sTblName = '';

    /**
     * 主键字段
     *
     * @var string
     */
    protected $_sPKField = '';

    /**
     * 数据库表结构
     *
     * @var array
     */
    protected $_aDBField = [
        'int/tinyint' => [
            'sType' => 'int/tinyint',
            'bUnsigned' => true
        ],
        'string' => [
            'sType' => 'string',
            'iLength' => 255
        ]
    ];

    /**
     * ORM字段结构
     *
     * @var array
     */
    protected $_aORMField = [
        'int/tinyint' => [
            'sType' => 'int/tinyint',
            'bUnsigned' => true
        ],
        'float' => [
            'sType' => 'float',
            'bUnsigned' => true
        ],
        'string' => [
            'sType' => 'string',
            'iLength' => 255
        ],
        'array' => [
            'sType' => 'array'
        ]
    ];

    /**
     * 业务SQL语句
     *
     * @var array
     */
    protected $_aBizSQLs = [
        'itemname_57' => 'iBuyTime>:iBuyTime ORDER BY iAutoID asc'
    ];
    
    // 系统属性
    
    /**
     * 所有执行的SQL语句
     *
     * @var array
     */
    private static $_aAllSQLs = [];

    /**
     * 类名
     *
     * @var string
     */
    private $_sClassName = '';

    /**
     * 数据库操作次数
     *
     * @var int
     */
    private static $_iQueryCnt = 0;

    /**
     * 缓存操作次数
     *
     * @var int
     */
    private static $_iCacheCnt = 0;

    /**
     * PHP静态变量缓存
     *
     * @var array
     */
    private static $_aStaticCaches = [];

    /**
     * 数据库连接池
     *
     * @var array
     */
    private static $_aDBPools = [];

    /**
     * 数据库陈述
     *
     * @var object
     */
    private static $_oDBSTMT = null;

    /**
     * 缓存连接
     *
     * @var object
     */
    private static $_oCache = null;

    /**
     * 调试对象
     *
     * @var object
     */
    private static $_oDebug = null;

    /**
     * 用于保存调试信息
     *
     * @var mix
     */
    private static $_mixDebugResult = null;

    /**
     * 变量绑定占位符
     *
     * @var string
     */
    private static $_sBindHolder = ':';

    /**
     * ORM数据
     *
     * @var array
     */
    private $_aORMData = [];

    /**
     * 是否开启缓存
     *
     * @var $_bolNeedCache
     */
    private $_bolNeedCache = true;

    /**
     * 数据缓存时间
     *
     * @var int
     */
    private static $_iDataCacheTime = 86400;

    /**
     * 是否物理删除数据
     *
     * @var boolean
     */
    private static $_bPhyDelete = false;

    /**
     * 排序
     *
     * @var string
     */
    private $_sOrder = '';

    /**
     * 开始行数
     *
     * @var int
     */
    private $_iStartRow = 0;

    /**
     * 获取行数
     *
     * @var int
     */
    private $_iFetchRow = 20;

    /**
     * 过滤条件
     *
     * @var array
     */
    private $_aFilters = [];

    /**
     * 查询获取数据类型-一列
     *
     * @var int
     */
    const SQL_FETCH_TYPE_COLUMN = 1;

    /**
     * 查询获取数据类型-一行
     *
     * @var int
     */
    const SQL_FETCH_TYPE_ROW = 2;

    /**
     * 查询获取数据类型-多行
     *
     * @var int
     */
    const SQL_FETCH_TYPE_LIST = 3;

    /**
     * 创建实例
     *
     * @param string $p_sTblName            
     * @param boolean $p_bStrictMaster            
     */
    function __construct($p_bStrictMaster = false)
    {
        $this->_sClassName = get_class($this);
        self::$_oDebug = lib_sys_debugger::getInstance();
        if ($p_bStrictMaster) {
            $this->_sSlaveDBName = $this->_sMasterDBName;
        }
        $this->_aDBField = array_merge($this->_aDBField, [
            'iCreateTime' => [
                'sType' => 'int',
                'bUnsigned' => true,
                'bAutoIncrement' => false
            ],
            'iUpdateTime' => [
                'sType' => 'int',
                'bUnsigned' => true,
                'bAutoIncrement' => false
            ],
            'iDeleteTime' => [
                'sType' => 'int',
                'bUnsigned' => true,
                'bAutoIncrement' => false
            ]
        ]);
    }

    /**
     * 析构实例
     */
    function __destruct()
    {
        self::$_oDebug->showMsg($this->_sClassName . ': Query time: ' . self::$_iQueryCnt . '. Cache time: ' . self::$_iCacheCnt);
    }

    /**
     * 得到所有执行的SQL语句
     *
     * @return array;
     */
    static function getAllSQL()
    {
        return self::$_aAllSQLs;
    }

    /**
     * 返回数据库操作次数
     *
     * @return int
     */
    static function getQueryCnt()
    {
        return self::$_iQueryCnt;
    }

    /**
     * 返回缓存操作次数
     *
     * @return int
     */
    static function getCacheCnt()
    {
        return self::$_iCacheCnt;
    }

    /**
     * 设置排序
     *
     * @param string $p_sOrder            
     */
    function setOrder($p_sOrder)
    {
        $this->_sOrder = $p_sOrder;
    }

    /**
     * 设置开始行数
     *
     * @param int $p_iStart            
     */
    function setStartRow($p_iStartRow)
    {
        $this->_iStartRow = $p_iStartRow;
    }

    /**
     * 设置获取行数
     *
     * @param int $p_iFetchRow            
     */
    function setFetchRow($p_iFetchRow)
    {
        $this->_iFetchRow = $p_iFetchRow;
    }

    /**
     * 添加过滤器
     *
     * @param string $p_sDBField            
     * @param string $p_sOperator            
     * @param mix $p_mValue            
     */
    function addFilter($p_sDBField, $p_sOperator, $p_mValue)
    {
        $p_sOperator = util_string::trimString($p_sOperator);
        if (isset($this->_aDBField[$p_sDBField])) {
            if (in_array($p_sOperator, array(
                '=',
                '!=',
                '<',
                '>',
                '<=',
                '>=',
                'in',
                'like'
            ))) {
                $this->_aFilters[] = array(
                    'sField' => $p_sDBField,
                    'sOperator' => $p_sOperator,
                    'mValue' => $p_mValue
                );
            } else {
                throw new Exception($this->_sClassName . ': you use an unexpected operator(' . $p_sOperator . ') of ORM instance.');
                return false;
            }
        } else {
            throw new Exception($this->_sClassName . ': you add an unexpected filter(' . $p_sDBField . ') to ORM instance.');
            return false;
        }
    }

    /**
     * 清除过滤器
     */
    function initFilter()
    {
        $this->_aFilters = [];
    }

    /**
     * 获取ORM数据
     *
     * @return array
     */
    function getSource()
    {
        foreach ($this->_aORMField as $sField => $aConfig) {
            if (null !== $this->$sField) {
                $this->_aORMData[$sField] = $this->$sField;
            }
        }
        return $this->_aORMData;
    }

    /**
     * ORM从数组加载数据
     *
     * @param array $p_aData            
     * @return object
     */
    function loadSource($p_aData)
    {
        foreach ($this->_aORMField as $sField => $aConfig) {
            if (isset($p_aData[$sField])) {
                $this->$sField = $this->_aORMData[$sField] = $p_aData[$sField];
            }
        }
        return $this;
    }

    /**
     * 关闭缓存功能
     */
    function disableCache()
    {
        $this->_bolNeedCache = false;
    }

    /**
     * 根据主键删除ORM单行缓存
     *
     * @param mix $p_mPK            
     * @return true/false
     */
    function clearRowCache($p_mPKVal)
    {
        return self::_clearCacheData(self::_getCacheRowKey($this->_sClassName, $p_mPKVal));
    }

    /**
     * 添加数据
     *
     * @return int/false
     */
    function addData()
    {
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData['iCreateTime'] = lib_sys_var::getInstance()->getRealTime();
        $aDBData = self::_checkField($aDBData, $this->_aDBField, $this->_sClassName);
        $aSQLParam = self::_joinAddString($this->_aDBField, $aDBData, $this->_sPKField, $this->_sClassName);
        $sSQL = 'insert into ' . $this->dispatchTable($this->_sTblName) . ' (' . $aSQLParam['sFieldStr'] . ')values(' . $aSQLParam['sParamStr'] . ')';
        return self::_insertDBData($sSQL, $aSQLParam['aValue'], $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
    }

    /**
     * 更新数据
     *
     * @return int
     */
    function updData()
    {
        $aNewORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aOldORMData = $this->getRow()->getSource();
        $aNewDBData = $this->beforeSave($aNewORMData);
        $aOldDBData = $this->beforeSave($aOldORMData);
        foreach ($aNewDBData as $sDBField => $sValue) {
            if ($sDBField != $this->_sPKField and $sValue == $aOldDBData[$sDBField]) {
                unset($aNewDBData[$sDBField]);
            }
        }
        if (1 == count($aNewDBData)) {
            return 0;
        }
        $aNewDBData['iUpdateTime'] = lib_sys_var::getInstance()->getRealTime();
        $aNewDBData = self::_checkField($aNewDBData, $this->_aDBField, $this->_sClassName);
        $aSQLParam = self::_joinUpdString($this->_aDBField, $aNewDBData, $this->_sPKField, $this->_sClassName);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aNewDBData);
        $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
        return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParam['aValue']), $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
    }

    /**
     * 删除数据
     *
     * @return int
     */
    function delData()
    {
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData['iDeleteTime'] = lib_sys_var::getInstance()->getRealTime();
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aDBData);
        if (self::$_bPhyDelete) {
            $sSQL = 'delete from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return $this->_updDBData($sSQL, $aPKParam['aValue'], $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
        } else {
            $aSQLParam = self::_joinUpdString($this->_aDBField, $aDBData, $this->_sPKField, $this->_sClassName);
            $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
            $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParam['aValue']), $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
        }
    }

    /**
     * 获取一行数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return object/null
     */
    function getRow($p_bStrictFreshCache = false)
    {
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData = self::_checkField($aDBData, $this->_aDBField, $this->_sClassName);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aDBData);
        $sCacheKey = self::_getCacheRowKey($this->_sClassName, $aPKParam['aValue'][$this->_sPKField]);
        if ($p_bStrictFreshCache or ! $this->_bolNeedCache) {
            $aORMData = false;
        } else {
            $aORMData = $this->_getCacheData($sCacheKey);
        }
        if (false === $aORMData) {
            $sSQL = 'select ' . self::_joinSelectString($this->_aDBField, $this->_sClassName) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $aDBData = self::_getDBData($sSQL, $aPKParam['aValue'], self::SQL_FETCH_TYPE_ROW, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
            if (null === $aDBData) {
                return null;
            }
            $aORMData = $this->beforeRead($aDBData);
            $this->_setCacheData([
                $sCacheKey => $aORMData
            ]);
        }
        return $this->loadSource($aORMData);
    }

    /**
     * 获取多行数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return array
     */
    function getList($p_bStrictFreshCache = false)
    {
        $sSQL = 'select ' . $this->_sPKField . ' from ' . $this->dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilters);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        if ('' == $this->_sOrder) {
            $sSQL .= ' order by ' . $this->_sPKField . ' desc';
        } else {
            $sSQL .= ' order by ' . $this->_sOrder;
            $this->_sOrder = '';
        }
        if ($this->_iFetchRow > 0) {
            if ($this->_iStartRow > 0) {
                $sSQL .= ' limit :iStartRow, :iFetchRow';
                $aWhereParam['aValue']['iStartRow'] = $this->_iStartRow;
                $aWhereParam['aValue']['iFetchRow'] = $this->_iFetchRow;
                $this->_iStartRow = 0;
            } else {
                $sSQL .= ' limit :iFetchRow';
                $aWhereParam['aValue']['iFetchRow'] = $this->_iFetchRow;
            }
            $this->_iFetchRow = 20;
        }
        $aPKIDs = self::_getDBData($sSQL, $aWhereParam['aValue'], self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
        if (empty($aPKIDs)) {
            return [];
        } else {
            return $this->getListByPKs($aPKIDs, $p_bStrictFreshCache);
        }
    }

    /**
     * 得到统计数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return int
     */
    function getCnt($p_bStrictFreshCache = false)
    {
        $sSQL = 'select count(*) as cnt from ' . $this->dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilters);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        return $this->_getDBData($sSQL, $aWhereParam['aValue'], self::SQL_FETCH_TYPE_COLUMN, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
    }

    /**
     * 根据PK获取数据
     *
     * @param mix $p_mPKs            
     * @param boolean $p_bStrictFreshCache            
     * @return array
     */
    function getListByPKs($p_mPKs, $p_bStrictFreshCache = false)
    {
        $aPKs = self::_rebuildPKs($p_mPKs, $this->_sPKField);
        if (empty($aPKs)) {
            return [];
        }
        $aRS = [];
        $iCntPKs = count($aPKs);
        if ($this->_bolNeedCache and ! $p_bStrictFreshCache) {
            $aCacheKey = array();
            $aCacheRS = array();
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                $aCacheKey[] = self::_getCacheRowKey($this->_sClassName, $aPKIDs[$i]);
            }
            $aCacheRS = $this->_getCacheData($aCacheKey);
            $aCacheMissIDs = array();
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                if (isset($aCacheRS[$aCacheKey[$i]])) {} else {
                    $aCacheMissIDs[] = $aPKIDs[$i];
                }
            }
            $iCacheMissIDsCnt = count($aCacheMissIDs);
            if (0 < $iCacheMissIDsCnt) {
                $aPKIDsPattern = '';
                $aParam = array();
                for ($i = 0; $i < $iCacheMissIDsCnt; ++ $i) {
                    $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKField . '_' . $i;
                    $aParam[$this->_sPKField . '_' . $i] = $aCacheMissIDs[$i];
                }
                $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
                $aDBData = $this->_getDBData($sSQL, $aParam, 3);
                $iCntDBData = count($aDBData);
                $aNeedCacheData = array();
                for ($i = 0; $i < $iCntDBData; ++ $i) {
                    $aNeedCacheData[self::_getCacheRowKey($this->_sClassName, $aDBData[$i][$this->_sPKField])] = $aDBData[$i];
                }
                $this->_setCacheData($aNeedCacheData);
            }
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                if (! empty($aCacheRS)) {
                    foreach ($aCacheRS as $aCacheData) {
                        if ($aPKIDs[$i] == $aCacheData[$this->_sPKField]) {
                            $aRS[$i] = $aCacheData;
                            continue 2;
                        }
                    }
                }
                for ($k = 0; $k < $iCacheMissIDsCnt; ++ $k) {
                    if ($aPKIDs[$i] == @$aDBData[$k][$this->_sPKField]) {
                        $aRS[$i] = @$aDBData[$k];
                        continue 2;
                    }
                }
            }
        } else {
            $aPKsHolders = '';
            $aPKParams = [];
            for ($iIndex = 0; $iIndex < $iCntPKs; ++ $iIndex) {
                $aPKsHolders[] = self::$_sBindHolder . $this->_sPKField . '_' . $iIndex;
                $aPKParams[$this->_sPKField . '_' . $iIndex] = $aPKs[$iIndex];
            }
            $sSQL = 'select ' . self::_joinSelectString($this->_aDBField, $this->_sClassName) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKsHolders) . ')';
            $aDBDatas = self::_getDBData($sSQL, $aParam, self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
            $iCntDBDatas = count($aDBDatas);
            for ($iIndex = 0; $iIndex < $iCntDBDatas; ++ $iIndex) {
                // $this->_setCacheData(self::_getCacheRowKey($this->_sClassName, $aDBData[$i][$this->_sPKField]), $aDBData[$i]);
            }
            for ($iIndex = 0; $iIndex < $iCntPKs; ++ $iIndex) {
                for ($iKndex = 0; $iKndex < $iCntDBDatas; ++ $iKndex) {
                    if ($aPKs[$iIndex] == $aDBData[$iKndex][$this->_sPKField]) {
                        $aRS[$iIndex] = $aDBData[$iKndex];
                        continue 2;
                    }
                }
            }
        }
        return $aRS;
    }

    /**
     * 根据PK删除数据
     *
     * @param mix $p_mIDs            
     * @return int
     */
    function delDataByPKs($p_mPKs)
    {
        $aPKs = self::_rebuildPKs($p_mPKs, $this->_sPKField);
        if (empty($aPKs)) {
            return 0;
        }
        $aRS = [];
        $iCntPKs = count($aPKs);
        $aPKsHolders = '';
        $aPKParams = [];
        for ($iIndex = 0; $iIndex < $iCntPKs; ++ $iIndex) {
            $aPKsHolders[] = self::$_sBindHolder . $this->_sPKField . '_' . $iIndex;
            $aPKParams[$this->_sPKField . '_' . $iIndex] = $aPKs[$iIndex];
        }
        
        if (self::$_bPhyDelete) {
            $sSQL = 'delete from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKsHolders) . ')';
            // $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return $this->_updDBData($sSQL, $aPKParams, $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
        } else {
            $aDBData = [];
            $aDBData['iDeleteTime'] = lib_sys_var::getInstance()->getRealTime();
            $aSQLParam = self::_joinUpdString($this->_aDBField, $aDBData, $this->_sPKField, $this->_sClassName);
            $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKsHolders) . ')';
            // $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParams), $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
        }
    }

    /**
     * 根据PK更新数据
     *
     * @param mix $p_mIDs            
     * @return int
     */
    function updListByPKs($p_mPKs)
    {
        $aPKs = self::_rebuildPKs($p_mPKs, $this->_sPKField);
        if (empty($aPKs)) {
            return [];
        }
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $iCnt = count($aPKs);
        $aSQLParam = self::_joinUpdString($this->_aDBField, $aDBData, $this->_sPKField, $this->_sClassName);
        $aPKsPattern = '';
        $aParams = [];
        for ($iIndex = 0; $iIndex < $iCnt; ++ $iIndex) {
            // $this->clearRowCache($aPKIDs[$i]); // 应该先操作db后删cache
            $aPKsPattern[] = self::$_sBindHolder . $this->_sPKField . '_' . $iIndex;
            $aParams[$this->_sPKField . '_' . $iIndex] = $aPKs[$iIndex];
        }
        $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKsPattern) . ')';
        return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aParams), $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
    }

    /**
     * 获取复杂业务的数据列表
     *
     * @param string $p_sSQLName            
     * @param array $p_aParam            
     * @throws Exception
     * @return array/string
     */
    function getBizList($p_sSQLName, $p_aParams = [])
    {
        if (isset($this->_aBizSQLs[$p_sSQLName])) {
            $sSQL = 'select ' . self::_joinSelectString($this->_aDBField, $this->_sClassName) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where iDeleteTime=:iDeleteTime and ' . $this->_aBizSQLs[$p_sSQLName] . ' limit :iStartRow, :iFetchRow';
            $p_aParams['iDeleteTime'] = 0;
            $p_aParams['iStartRow'] = $this->_iStartRow;
            $p_aParams['iFetchRow'] = $this->_iFetchRow;
            return $this->_getDBData($sSQL, $p_aParams, self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
        } else {
            throw new Exception($this->_sClassName . ': you gave an invalid SQL name(' . $p_sSQLName . ')');
            return false;
        }
    }

    /**
     * 获取复杂业务的统计数字
     *
     * @param string $p_sSQLName            
     * @param array $p_aParams            
     * @throws Exception
     * @return array|string|boolean
     */
    function getBizCnt($p_sSQLName, $p_aParams = [])
    {
        if (isset($this->_aBizSQLs[$p_sSQLName])) {
            $sSQL = 'select count(*) as cnt from ' . $this->dispatchTable($this->_sTblName) . ' where iDeleteTime=:iDeleteTime and ' . $this->_aBizSQLs[$p_sSQLName];
            $p_aParams['iDeleteTime'] = 0;
            return $this->_getDBData($sSQL, $p_aParams, self::SQL_FETCH_TYPE_COLUMN, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
        } else {
            throw new Exception($this->_sClassName . ': you gave an invalid SQL name(' . $p_sSQLName . ')');
            return false;
        }
    }

    /**
     * 开始一个事务
     */
    function beginTransaction()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPools[$sDBName]->beginTransaction();
    }

    /**
     * 提交事务
     */
    function commit()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPools[$sDBName]->commit();
    }

    /**
     * 回滚事务
     */
    function rollBack()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPools[$sDBName]->rollBack();
    }

    /**
     * 分配DB
     *
     * @param string $p_sDBName            
     * @return string
     */
    protected function dispatchDB($p_sDBName)
    {
        return $p_sDBName;
    }

    /**
     * 分配表
     *
     * @param string $p_sTblName            
     * @return string
     */
    protected function dispatchTable($p_sTblName)
    {
        return $p_sTblName;
    }

    /**
     * 在保存数据前的钩子
     *
     * @param array $p_aORMData            
     * @return array
     */
    protected function beforeSave($p_aORMData)
    {
        return $p_aORMData;
    }

    /**
     * 在读取数据前的钩子
     *
     * @param array $p_aDBData            
     * @return array
     */
    protected function beforeRead($p_aDBData)
    {
        return $p_aDBData;
    }

    /**
     * 检查字段内容
     *
     * @param array $p_aData            
     * @param array $p_aField            
     * @param array $p_sClassName            
     * @return array
     */
    private static function _checkField($p_aData, $p_aField, $p_sClassName)
    {
        // debug($p_aData, $p_aField);
        foreach ($p_aField as $sField => $aConfig) {
            if (isset($p_aData[$sField])) {
                $mValue = $p_aData[$sField];
                switch ($aConfig['sType']) {
                    case 'int':
                    case 'tinyint':
                    case 'float':
                        $o_sOperator = $o_iParam = '';
                        if (! self::_isSelfOperate($sField, $mValue, $o_sOperator, $o_iParam)) {
                            if (is_numeric($mValue)) {} else {
                                throw new Exception($p_sClassName . ': you gave a nonnumeric value(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which need a number, maybe is ' . gettype($mValue) . '.');
                                return false;
                            }
                        }
                        break;
                    case 'string':
                        if (is_string($mValue)) {
                            $iLength = mb_strlen($mValue);
                            if ($iLength > $aConfig['iLength']) {
                                throw new Exception($p_sClassName . ': you gave an overlength(' . $iLength . ') string(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which max length is ' . $aConfig['iLength']);
                                return false;
                            }
                        } else {
                            throw new Exception($p_sClassName . ': you gave a non-string value(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which needed a string, maybe is ' . gettype($mValue) . '.');
                        }
                        break;
                    case 'array':
                        if (! is_array($mValue)) {
                            throw new Exception($p_sClassName . ': you gave a non-array value(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which needed an array, maybe is ' . gettype($mValue) . '.');
                        }
                        break;
                }
            }
        }
        // debug($p_aData);
        return $p_aData;
    }

    /**
     * 获取缓存连接
     */
    private static function _connectCache()
    {
        if (null == self::$_oCache) {
            self::$_oCache = lib_cache_pooling::getInstance()->getCache('orm');
        }
    }

    /**
     * 获取数据库连接
     *
     * @param
     *            string 数据库连接名
     */
    private static function _connectDB($p_sDBName)
    {
        if (isset(self::$_aDBPools[$p_sDBName])) {} else {
            self::$_aDBPools[$p_sDBName] = lib_db_pooling::getInstance()->getConnect($p_sDBName);
        }
    }

    /**
     * 获取数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param int $p_iType            
     * @param string $p_sDBName            
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @return array|string
     */
    private static function _getDBData($p_sSQL, $p_aParam, $p_iType, $p_sDBName, $p_aDBField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aAllSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPools[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aDBField, $p_sClassName));
        self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        switch ($p_iType) {
            case self::SQL_FETCH_TYPE_COLUMN:
                $mData = self::$_oDBSTMT->fetchColumn();
                break;
            case self::SQL_FETCH_TYPE_ROW:
                $mData = self::$_oDBSTMT->fetch();
                if (false === $mData) {
                    $mData = null;
                }
                break;
            case self::SQL_FETCH_TYPE_LIST:
                $mData = self::$_oDBSTMT->fetchAll();
                break;
        }
        self::$_oDebug->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebug->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebug->showMsg($p_sClassName . '->Result: ' . var_export($mData, true));
        return $mData;
    }

    /**
     * 更新数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param string $p_sDBName            
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @return int
     */
    private static function _updDBData($p_sSQL, $p_aParam, $p_sDBName, $p_aDBField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aAllSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPools[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aDBField, $p_sClassName));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastAffectedCnt = self::$_oDBSTMT->rowCount();
        self::$_oDebug->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebug->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebug->showMsg($p_sClassName . '->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />Affected row count: ' . $iLastAffectedCnt, true);
        return $iLastAffectedCnt;
    }

    /**
     * 插入数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param string $p_sDBName            
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @return int/false
     */
    private static function _insertDBData($p_sSQL, $p_aParam, $p_sDBName, $p_aDBField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aAllSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPools[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aDBField, $p_sClassName));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastInsertID = self::$_aDBPools[$p_sDBName]->lastInsertId();
        self::$_oDebug->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebug->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebug->showMsg($p_sClassName . '->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />LastID: ' . $iLastInsertID, true);
        return $iLastInsertID;
    }

    /**
     * 根据Key删除缓存
     *
     * @param string $p_sCacheKey            
     * @return true/false
     */
    private static function _clearCacheData($p_sCacheKey)
    {
        // ++ self::$_iCacheCnt;
        // $this->_clearStaticCacheData($p_sCacheKey);
        // $this->_clearAPCCacheData($p_sCacheKey);
        // return $this->_clearMemCacheData($p_sCacheKey);
    }

    /**
     * 根据Key删除静态缓存
     *
     * @param string $p_sCacheKey            
     * @return true
     */
    private static function _clearStaticCacheData($p_sCacheKey)
    {
        // if (isset(self::$_aStaticCaches[$p_sCacheKey])) {
        // unset(self::$_aStaticCaches[$p_sCacheKey]);
        // self::$_mixDebugResult = true;
        // } else {
        // self::$_mixDebugResult = false;
        // }
        // if (self::$_bolDebug) {
        // self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        // }
        // return true;
    }

    /**
     * 根据Key删除APC缓存
     *
     * @param string $p_sCacheKey            
     */
    private static function _clearAPCCacheData($p_sCacheKey)
    {}

    /**
     * 根据Key删除Memcache
     *
     * @param string $p_sCacheKey            
     * @return true/false
     */
    private static function _clearMemCacheData($p_sCacheKey)
    {
        // self::_connectCache();
        // for ($i = 0; $i < 5; ++ $i) {
        // self::$_mixDebugResult = self::$_oCache->delete($p_sCacheKey);
        // if (true === self::$_mixDebugResult) {
        // break;
        // }
        // }
        // if (self::$_bolDebug) {
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        // }
        // return self::$_mixDebugResult;
    }

    /**
     * 写入缓存数据
     *
     * @param array $p_aCache            
     * @param int $p_iDeepLevel
     *            1-static, 2-apc, 4-memcache
     */
    private static function _setCacheData($p_aCache, $p_iDeepLevel = 1)
    {
        // ++ self::$_iCacheCnt;
        // if (0x04 === ($p_iDeepLevel & 0x04)) {
        // $this->_setStaticCacheData($p_aCache);
        // }
        // if (0x02 === ($p_iDeepLevel & 0x02)) {
        // $this->_setAPCCacheData($p_aCache);
        // }
        // if (0x01 === ($p_iDeepLevel & 0x01)) {
        // $this->_setMemCacheData($p_aCache);
        // }
    }

    /**
     * 写入静态缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private static function _setStaticCacheData($p_aCache)
    {
        // if (self::$_bolDebug) {
        // foreach ($p_aCache as $sKey => $mValue) {
        // self::$_aStaticCaches[$sKey] = $mValue;
        // self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Set: ' . $sKey . '|true');
        // }
        // } else {
        // foreach ($p_aCache as $sKey => $mValue) {
        // self::$_aStaticCaches[$sKey] = $mValue;
        // }
        // }
    }

    /**
     * 写入APC缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private static function _setAPCCacheData($p_aCache)
    {}

    /**
     * 写入Memcache缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private static function _setMemCacheData($p_aCache)
    {
        // self::_connectCache();
        // if (self::$_bolDebug) {
        // foreach ($p_aCache as $sKey => $mValue) {
        // $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
        // }
        // for ($i = 0; $i < 5; ++ $i) {
        // self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
        // if (true === self::$_mixDebugResult) {
        // break;
        // }
        // }
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Set: Multi Key|' . var_export($p_aCache, true) . '|' . var_export(self::$_mixDebugResult, true));
        // if (false !== self::$_mixDebugResult) {
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>Multi Key Create=>' . date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime']) . ' Expire=>' . (0 == $p_aCache[$sKey]['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime'] + $p_aCache[$sKey]['iLifeTime'])));
        // }
        // } else {
        // foreach ($p_aCache as $sKey => $mValue) {
        // $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
        // }
        // for ($i = 0; $i < 5; ++ $i) {
        // self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
        // if (true === self::$_mixDebugResult) {
        // break;
        // }
        // }
        // }
    }

    /**
     * 获取缓存数据
     *
     * @param mix $p_mCacheKey            
     * @return mix
     */
    private static function _getCacheData($p_mCacheKey)
    {
        // ++ self::$_iCacheCnt;
        // if (is_array($p_mCacheKey)) {
        // $iResultType = 1; // 数组
        // } else {
        // $iResultType = 2; // 单个
        // $p_mCacheKey = array(
        // $p_mCacheKey
        // );
        // }
        // $iCnt = count($p_mCacheKey);
        // $aMissKey = array();
        // $mData = array();
        // if (self::$_bolDebug) {
        // foreach ($p_mCacheKey as $sCacheKey) {
        // if (isset(self::$_aStaticCaches[$sCacheKey])) {
        // $mData[$sCacheKey] = self::$_aStaticCaches[$sCacheKey];
        // self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|' . var_export(self::$_aStaticCaches[$sCacheKey], true));
        // } else {
        // $aMissKey[] = $sCacheKey;
        // self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|false');
        // }
        // }
        // } else {
        // foreach ($p_mCacheKey as $sCacheKey) {
        // if (isset(self::$_aStaticCaches[$sCacheKey])) {
        // $mData[$sCacheKey] = self::$_aStaticCaches[$sCacheKey];
        // } else {
        // $aMissKey[] = $sCacheKey;
        // }
        // }
        // }
        // if (empty($aMissKey)) {
        // if (1 == $iResultType) {
        // return $mData;
        // } else {
        // return $mData[$p_mCacheKey[0]];
        // }
        // }
        // $iCnt = count($aMissKey);
        // self::_connectCache();
        // $aCacheData = self::$_oCache->getMulti($aMissKey);
        // if (self::$_bolDebug) {
        // foreach ($aMissKey as $sCacheKey) {
        // if (isset($aCacheData[$sCacheKey])) {
        // $aEachCacheData = $aCacheData[$sCacheKey];
        // $mData[$sCacheKey] = $aEachCacheData['mData'];
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|' . var_export($aEachCacheData['mData'], true));
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>' . $sCacheKey . ' Create=>' . date('Y-m-d H:i:s', $aEachCacheData['iCreateTime']) . ' Expire=>' . (0 == $aEachCacheData['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $aEachCacheData['iCreateTime'] + $aEachCacheData['iLifeTime'])));
        // $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
        // } else {
        // self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|false');
        // }
        // }
        // } else {
        // foreach ($aMissKey as $sCacheKey) {
        // if (isset($aCacheData[$sCacheKey]) and false !== $aCacheData[$sCacheKey]) {
        // $aEachCacheData = $aCacheData[$sCacheKey];
        // $mData[$sCacheKey] = $aEachCacheData['mData'];
        // $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
        // }
        // }
        // }
        // if (0 == count($mData)) {
        // return false;
        // } else {
        // if (1 == $iResultType) {
        // return $mData;
        // } else {
        // return $mData[$p_mCacheKey[0]];
        // }
        // }
        return false;
    }

    /**
     * 分析SQL参数
     *
     * @param array $p_aParam            
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _parseParameter($p_aParams, $p_aDBField, $p_sClassName)
    {
        $aParams = [];
        $iPDOType = 0;
        $p_aDBField['iStartRow'] = $p_aDBField['iFetchRow'] = [
            'sType' => 'int'
        ];
        foreach ($p_aParams as $sField => $mValue) {
            $aField = [];
            if (0 < preg_match('/([a-zA-Z0-9]+)(\_\d)?/', $sField, $aField)) {
                switch ($p_aDBField[$aField[1]]['sType']) {
                    case 'int':
                    case 'tinyint':
                        $iPDOType = PDO::PARAM_INT;
                        break;
                    case 'string':
                        $iPDOType = PDO::PARAM_STR;
                        break;
                    default:
                        throw new Exception($p_sClassName . ': you have an unknown database field(' . $sField . ') type(' . $p_aDBField[$sField]['sType'] . ').');
                        break;
                }
                $aParams[] = array(
                    'sField' => $sField,
                    'mValue' => $mValue,
                    'iPDOType' => $iPDOType
                );
            } else {
                throw new Exception($p_sClassName . ': you have an invalid database field(' . $sField . ').');
                break;
            }
        }
        return $aParams;
    }

    /**
     * 绑定变量
     *
     * @param array $p_aParams            
     */
    private static function _bindData($p_aParams)
    {
        foreach ($p_aParams as $aParam) {
            $mValue = $aParam['mValue'];
            self::$_oDBSTMT->bindParam(self::$_sBindHolder . $aParam['sField'], $mValue, $aParam['iPDOType']);
            unset($mValue);
        }
    }

    /**
     * 获取ORM获取数据所需SQL信息
     *
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @return string
     */
    private static function _joinSelectString($p_aDBField, $p_sClassName)
    {
        $sFields = '';
        foreach ($p_aDBField as $sField => $aFieldSet) {
            $sFields .= ', ' . $sField;
        }
        if (isset($sFields[0])) {
            return substr($sFields, 2);
        } else {
            throw new Exception($p_sClassName . ': your database field(' . var_export($p_aDBField, true) . ') is empty.');
            return false;
        }
    }

    /**
     * 获取查询条件
     *
     * @param array $p_aFilters            
     * @return array
     */
    private static function _joinWhereString($p_aFilters)
    {
        $sFields = '';
        $aIndexs = $aValues = [];
        foreach ($p_aFilters as $aFilter) {
            if (! isset($aIndexs[$aFilter['sField']])) {
                $aIndexs[$aFilter['sField']] = 0;
            }
            if ('in' == $aFilter['sOperator']) {
                $aHolders = [];
                $aIDs = self::_rebuildFilterIDs($aFilter['mValue']);
                $iCnt = count($aIDs);
                for ($iIndex = 0; $iIndex < $iCnt; ++ $iIndex) {
                    $sHolder = $aFilter['sField'] . '_' . ++ $aIndexs[$aFilter['sField']];
                    $aHolders[] = self::$_sBindHolder . $sHolder;
                    $aValues[$sHolder] = $aIDs[$iIndex];
                }
                $sFields .= ' and ' . $aFilter['sField'] . ' ' . $aFilter['sOperator'] . ' (' . join(',', $aHolders) . ')';
            } else {
                $sHolder = $aFilter['sField'] . '_' . ++ $aIndexs[$aFilter['sField']];
                $sFields .= ' and ' . $aFilter['sField'] . $aFilter['sOperator'] . self::$_sBindHolder . $sHolder;
                $aValues[$sHolder] = $aFilter['mValue'];
            }
        }
        if (isset($sFields[0])) {
            return array(
                'sFieldStr' => substr($sFields, 5),
                'aValue' => $aValues
            );
        } else {
            throw new Exception('ORM do not allowed to get all data.');
            return false;
        }
    }

    /**
     * 获取ORM添加信息所需SQL信息
     *
     * @param array $p_aDBField            
     * @param array $p_aData            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _joinAddString($p_aDBField, $p_aData, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $sParams = '';
        $aValues = '';
        foreach ($p_aDBField as $sField => $aFieldSet) {
            if (isset($p_aData[$sField]) and $p_sPKField != $sField) {
                $sFields .= ', ' . $sField;
                $sParams .= ', ' . self::$_sBindHolder . $sField;
                $aValues[$sField] = $p_aData[$sField];
            }
        }
        if (isset($sFields[0])) {
            return array(
                'sFieldStr' => substr($sFields, 2),
                'sParamStr' => substr($sParams, 2),
                'aValue' => $aValues
            );
        } else {
            throw new Exception($p_sClassName . ': you have no data(' . var_export($p_aData, true) . ') to insert.');
            return false;
        }
    }

    /**
     * 获取ORM更新信息所需SQL信息
     *
     * @param array $p_aDBField            
     * @param array $p_aData            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _joinUpdString($p_aDBField, $p_aData, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $aValues = [];
        foreach ($p_aDBField as $sField => $aFieldSet) {
            if ($p_sPKField != $sField and isset($p_aData[$sField])) {
                $sSelfOperator = $iSelfParam = '';
                if (self::_isSelfOperate($sField, $p_aData[$sField], $sSelfOperator, $iSelfParam)) {
                    $sFields .= ', ' . $sField . '=' . $sField . $sSelfOperator . self::$_sBindHolder . $sField;
                    $aValues[$sField] = $iSelfParam;
                } else {
                    $sFields .= ', ' . $sField . '=' . self::$_sBindHolder . $sField;
                    $aValues[$sField] = $p_aData[$sField];
                }
            }
        }
        if (isset($sFields[0])) {
            return array(
                'sFieldStr' => substr($sFields, 2),
                'aValue' => $aValues
            );
        } else {
            throw new Exception($p_sClassName . ': your database fields(' . var_export($p_aDBField, true) . ') are all primary key(' . $p_sPKField . ') or have no data(' . var_export($p_aData, true) . ') to update.');
            return false;
        }
    }

    /**
     * 判断是否为自运算
     *
     * @param string $p_sField            
     * @param mix $p_mValue            
     * @param string $o_sOperator            
     * @param int $o_iParam            
     * @return true/false
     */
    private static function _isSelfOperate($p_sField, $p_mValue, &$o_sOperator, &$o_iParam)
    {
        $sPattern = '/^' . $p_sField . '([+\-*\/])(\d+)$/i';
        $aResult = [];
        if (1 == preg_match($sPattern, $p_mValue, $aResult)) {
            $o_sOperator = $aResult[1];
            $o_iParam = $aResult[2];
            return true;
        } else {
            return false;
        }
    }

    /**
     * 重新生成新的主键列表
     *
     * @param array $p_mIDs            
     * @return array
     */
    private static function _rebuildPKs($p_mIDs, $p_sPKField)
    {
        if (is_array($p_mIDs)) {
            if (empty($p_mIDs)) {
                return array();
            } else {
                $mPKID = array_pop($p_mIDs);
                if (is_array($mPKID)) {
                    $aPKIDs = array();
                    foreach ($p_mIDs as $aID) {
                        $aPKIDs[] = $aID[$p_sPKField];
                    }
                    $aPKIDs[] = $mPKID[$p_sPKField];
                } else {
                    $aPKIDs = $p_mIDs;
                    $aPKIDs[] = $mPKID;
                }
                array_unique($aPKIDs);
                return $aPKIDs;
            }
        } else {
            return array_unique(explode(',', $p_mIDs));
        }
    }

    /**
     * 重新生成新的查询ID列表
     *
     * @param mix $p_mIDs            
     * @return array
     */
    private static function _rebuildFilterIDs($p_mIDs)
    {
        if (is_array($p_mIDs)) {
            return array_unique($p_mIDs);
        } else {
            return array_unique(explode(',', $p_mIDs));
        }
    }

    /**
     * 根据主键数据生成where条件
     *
     * @param string $p_sPKField            
     * @param array $p_aData            
     * @return array
     */
    private static function _joinPKWhereString($p_sPKField, $p_aData)
    {
        if (isset($p_aData[$p_sPKField])) {
            return array(
                'sFieldStr' => $p_sPKField . '=' . self::$_sBindHolder . $p_sPKField,
                'aValue' => array(
                    $p_sPKField => $p_aData[$p_sPKField]
                )
            );
        } else {
            throw new Exception('You missed ORM PKID(' . $p_sPKField . ').');
            return false;
        }
    }

    /**
     * 获取ORM数据缓存Key
     *
     * @param string $p_sORMName            
     * @param int $p_mPKVal            
     * @return string
     */
    private static function _getCacheRowKey($p_sORMName, $p_mPKVal)
    {
        return $p_sORMName . '_r_' . $p_mPKVal;
    }

    /**
     * 生成cache的数据
     *
     * @param mix $p_mValue            
     * @param int $p_iLifeTime            
     * @return array
     */
    private static function _implodeCache($p_mValue, $p_iLifeTime)
    {
        return [
            'mData' => $p_mValue,
            'iCreateTime' => lib_sys_var::getInstance()->getRealTime(),
            'iLifeTime' => $p_iLifeTime
        ];
    }
}