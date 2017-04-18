<?php

/**
 * lib_sys_orm
 *
 * 系统数据关系映射类
 *
 * @package lib_sys
 */

/**
 * lib_sys_orm
 *
 * 系统数据关系映射类
 * 不允许跨表查询,每个表均有且仅有一个主键
 * 写入缓存的数据室ormdata的数组格式,不包含保留字段数据
 *
 * @todo debug内容的调整,memcached::delmulti的debug返回
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
     * 缓存连接名
     *
     * @var string
     */
    protected $_sCacheName = 'ormcache';

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
    protected $_aBizSQL = [
        'itemname_57' => 'iBuyTime>:iBuyTime ORDER BY iAutoID asc'
    ];
    
    // 系统属性,子类不允许修改
    
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
    private static $_aStaticCache = [];

    /**
     * 数据库连接池
     *
     * @var array
     */
    private static $_aDBPool = [];

    /**
     * 数据库陈述
     *
     * @var object
     */
    private static $_oDBSTMT = null;

    /**
     * 最后准备的SQL
     *
     * @var string
     */
    private static $_sLastPreparedSQL = '';

    /**
     * 缓存连接池
     *
     * @var array
     */
    private static $_aCachePool = [];

    /**
     * 默认缓存深度
     *
     * @var int
     */
    const DEFAULT_CACHE_LEVEL = 7;

    /**
     * 默认缓存时间
     *
     * @var int
     */
    const DEFAULT_CACHE_TIME = 86400;

    /**
     * 操作缓存最大尝试次数
     *
     * @var int
     */
    const MAX_CACHE_TRY = 5;

    /**
     * 调试对象
     *
     * @var object
     */
    private static $_oDebugger = null;

    /**
     * 用于保存调试信息
     *
     * @var mix
     */
    private static $_mDebugResult = null;

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
     * @var boolean
     */
    private $_bolNeedCache = true;

    /**
     * 是否物理删除数据
     *
     * @var boolean
     */
    private static $_bPhyDelete = false;

    /**
     * 过滤条件操作符
     *
     * @var array
     */
    private static $_aFilterOperators = [
        '=',
        '!=',
        '<',
        '>',
        '<=',
        '>=',
        'in',
        'like'
    ];

    /**
     * 排序
     *
     * @var string
     */
    private $_sOrder = '';

    /**
     * 默认列表开始行数
     *
     * @var int
     */
    const DEFAULT_START_ROW = 0;

    /**
     * 默认列表获取行数
     *
     * @var int
     */
    const DEFAULT_FETCH_ROW = 20;

    /**
     * 开始行数
     *
     * @var int
     */
    private $_iStartRow = self::DEFAULT_START_ROW;

    /**
     * 获取行数
     *
     * @var int
     */
    private $_iFetchRow = self::DEFAULT_FETCH_ROW;

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
     * 系统保留字段
     *
     * @var array
     */
    private static $_aReservedField = [
        'iCreateTime' => [
            'sType' => 'int',
            'bUnsigned' => true
        ],
        'iUpdateTime' => [
            'sType' => 'int',
            'bUnsigned' => true
        ],
        'iDeleteTime' => [
            'sType' => 'int',
            'bUnsigned' => true
        ]
    ];

    /**
     * 所有数据库表字段
     *
     * @var array
     */
    private $_aAllDBField = [];

    /**
     * 创建实例
     *
     * @param boolean $p_bStrictMaster            
     * @return void
     */
    function __construct($p_bStrictMaster = false)
    {
        $this->_sClassName = get_class($this);
        self::$_oDebugger = lib_sys_debugger::getInstance();
        if ($p_bStrictMaster) {
            $this->_sSlaveDBName = $this->_sMasterDBName;
        }
        $this->_aAllDBField = array_merge($this->_aDBField, self::$_aReservedField);
        self::$_oDebugger->showMsg($this->_sClassName . '->Info: sMasterName: ' . $this->_sMasterDBName . ', sSlaveName: ' . $this->_sSlaveDBName . ', sCacheName: ' . $this->_sCacheName);
    }

    /**
     * 析构函数
     *
     * @return void
     */
    function __destruct()
    {
        self::$_oDebugger->showMsg($this->_sClassName . '->Info: Query time: ' . self::$_iQueryCnt . ', Cache time: ' . self::$_iCacheCnt);
    }

    /**
     * 得到所有执行的SQL语句
     *
     * @return array;
     */
    static function getAllSQLs()
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
     * @return void
     */
    function setOrder($p_sOrder)
    {
        $this->_sOrder = $p_sOrder;
    }

    /**
     * 设置开始行数
     *
     * @param int $p_iStart            
     * @return void
     */
    function setStartRow($p_iStartRow)
    {
        $this->_iStartRow = $p_iStartRow;
    }

    /**
     * 设置获取行数
     *
     * @param int $p_iFetchRow            
     * @return void
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
     * @throws Exception
     * @return void
     */
    function addFilter($p_sDBField, $p_sOperator, $p_mValue)
    {
        $p_sOperator = util_string::trimString($p_sOperator);
        if (isset($this->_aDBField[$p_sDBField])) {
            if (in_array($p_sOperator, self::$_aFilterOperators)) {
                $this->_aFilters[] = [
                    'sField' => $p_sDBField,
                    'sOperator' => $p_sOperator,
                    'mValue' => $p_mValue
                ];
            } else {
                throw new Exception($this->_sClassName . ': you use an unexpected operator(' . $p_sOperator . ') of ORM instance.');
            }
        } else {
            throw new Exception($this->_sClassName . ': you add an unexpected filter(' . $p_sDBField . ') to ORM instance.');
        }
    }

    /**
     * 清除过滤器
     *
     * @return void
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
        $this->_aORMData = [];
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
     * @param boolean $p_bNew            
     * @return object
     */
    function loadSource($p_aData, $p_bNew = false)
    {
        if ($p_bNew) {
            $oORM = new $this();
        } else {
            $oORM = $this;
        }
        foreach ($oORM->_aORMField as $sField => $aConfig) {
            if (isset($p_aData[$sField])) {
                $oORM->$sField = $oORM->_aORMData[$sField] = $p_aData[$sField];
            } else {
                $oORM->$sField = null;
                unset($oORM->_aORMData[$sField]);
            }
        }
        return $oORM;
    }

    /**
     * 关闭缓存功能
     *
     * @return void
     */
    function disableCache()
    {
        $this->_bolNeedCache = false;
    }

    /**
     * 根据主键删除ORM单行缓存
     *
     * @param mix $p_mPKVal            
     * @return true|false
     */
    function clearRowCache($p_mPKVal)
    {
        return self::_clearCacheData([
            self::_getCacheRowKey($this->_sClassName, $p_mPKVal)
        ], $this->_sCacheName, $this->_sClassName);
    }

    /**
     * 添加数据
     *
     * @return int|false
     */
    function addData()
    {
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData['iCreateTime'] = lib_sys_var::getInstance()->getRealTime();
        $aDBData = self::_checkField($aDBData, $this->_aAllDBField, $this->_sClassName);
        $aSQLParam = self::_joinAddString($this->_aAllDBField, $aDBData, $this->_sPKField, $this->_sClassName);
        $sSQL = 'insert into ' . $this->dispatchTable($this->_sTblName) . ' (' . $aSQLParam['sFieldStr'] . ')values(' . $aSQLParam['sParamStr'] . ')';
        return self::_insertDBData($sSQL, $aSQLParam['aValue'], $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
    }

    /**
     * 获取一行数据
     *
     * @param boolean $p_bNeedFake            
     * @param boolean $p_bStrictFreshCache            
     * @return object|null
     */
    function getRow($p_bNeedFake = false, $p_bStrictFreshCache = false)
    {
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData = self::_checkField($aDBData, $this->_aAllDBField, $this->_sClassName);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aDBData, $this->_sClassName, $p_bNeedFake);
        $sCacheKey = self::_getCacheRowKey($this->_sClassName, $aPKParam['aValue'][$this->_sPKField]);
        if ($p_bStrictFreshCache or ! $this->_bolNeedCache) {
            $aORMData = [];
        } else {
            $aCacheDatas = $this->_getCacheData([
                $sCacheKey
            ], $this->_sCacheName, $this->_sClassName);
            if (empty($aCacheDatas)) {
                $aORMData = [];
            } else {
                $aORMData = $aCacheDatas[$sCacheKey];
            }
        }
        if (empty($aORMData)) {
            $sSQL = 'select ' . self::_joinSelectString($this->_aDBField, $this->_sClassName) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $aDBData = self::_getDBData($sSQL, $aPKParam['aValue'], self::SQL_FETCH_TYPE_ROW, $this->dispatchDB($this->_sSlaveDBName), $this->_aAllDBField, $this->_sClassName);
            if (null === $aDBData) {
                return null;
            }
            $aORMData = $this->beforeRead($aDBData);
            if (! $p_bNeedFake) {
                $this->_setCacheData([
                    $sCacheKey => $aORMData
                ], self::DEFAULT_CACHE_LEVEL, $this->_sCacheName, $this->_sClassName);
            }
        }
        return $this->loadSource($aORMData);
    }

    /**
     * 更新数据
     *
     * @return int
     */
    function updData()
    {
        $aNewORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $oOldORM = $this->getRow();
        if (null === $oOldORM) {
            return 0;
        }
        $aOldORMData = $oOldORM->getSource();
        $aNewDBData = $this->beforeSave($aNewORMData);
        $aOldDBData = $this->beforeSave($aOldORMData);
        foreach ($aNewDBData as $sDBField => $sValue) {
            if ($sDBField != $this->_sPKField and $sValue == $aOldDBData[$sDBField]) {
                unset($aNewDBData[$sDBField]);
            }
        }
        $this->loadSource(array_merge($aOldORMData, $aNewORMData));
        if (1 == count($aNewDBData)) {
            return 0;
        }
        $aNewDBData['iUpdateTime'] = lib_sys_var::getInstance()->getRealTime();
        $aNewDBData = self::_checkField($aNewDBData, $this->_aAllDBField, $this->_sClassName);
        $aSQLParam = self::_joinUpdString($this->_aAllDBField, $aNewDBData, $this->_sPKField, $this->_sClassName);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aNewDBData, $this->_sClassName);
        $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
        return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParam['aValue']), $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
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
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aDBData, $this->_sClassName);
        if (self::$_bPhyDelete) {
            $sSQL = 'delete from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return $this->_updDBData($sSQL, $aPKParam['aValue'], $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
        } else {
            $aSQLParam = self::_joinUpdString($this->_aAllDBField, $aDBData, $this->_sPKField, $this->_sClassName);
            $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
            $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
            return self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParam['aValue']), $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
        }
    }

    /**
     * 获取多行数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @throws Exception
     * @return array
     */
    function getList($p_bStrictFreshCache = false)
    {
        $sSQL = 'select ' . $this->_sPKField . ' from ' . $this->dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilters, $this->_sPKField, $this->_sClassName);
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
                $this->_iStartRow = self::DEFAULT_START_ROW;
            } else {
                $sSQL .= ' limit :iFetchRow';
                $aWhereParam['aValue']['iFetchRow'] = $this->_iFetchRow;
            }
            $this->_iFetchRow = self::DEFAULT_FETCH_ROW;
        } else {
            throw new Exception($this->_sClassName . ': orm do not allowed get all data.');
        }
        $aPKVals = self::_getDBData($sSQL, $aWhereParam['aValue'], self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aAllDBField, $this->_sClassName);
        if (empty($aPKVals)) {
            return [];
        } else {
            return self::_orderPKORMList($aPKVals, $this->getListByPKVals($aPKVals, $p_bStrictFreshCache), $this->_sPKField);
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
        $aWhereParam = self::_joinWhereString($this->_aFilters, $this->_sPKField, $this->_sClassName);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        return $this->_getDBData($sSQL, $aWhereParam['aValue'], self::SQL_FETCH_TYPE_COLUMN, $this->dispatchDB($this->_sSlaveDBName), $this->_aAllDBField, $this->_sClassName);
    }

    /**
     * 根据PK获取数据
     *
     * @param mix $p_mPKVals            
     * @param boolean $p_bStrictFreshCache            
     * @return array
     */
    function getListByPKVals($p_mPKVals, $p_bStrictFreshCache = false)
    {
        $aPKVals = self::_rebuildPKVals($p_mPKVals, $this->_sPKField);
        if (empty($aPKVals)) {
            return [];
        }
        $aResults = [];
        if ($this->_bolNeedCache and ! $p_bStrictFreshCache) {
            $aCacheMissPKVals = $aCacheKeys = [];
            foreach ($aPKVals as $mPKVal) {
                $aCacheKeys[] = self::_getCacheRowKey($this->_sClassName, $mPKVal);
            }
            $aCacheData = self::_getCacheData($aCacheKeys, $this->_sCacheName, $this->_sClassName);
            foreach ($aPKVals as $mPKVal) {
                $sCacheKey = self::_getCacheRowKey($this->_sClassName, $mPKVal);
                if (isset($aCacheData[$sCacheKey])) {} else {
                    $aCacheMissPKVals[] = $mPKVal;
                }
            }
        } else {
            $aCacheMissPKVals = $aPKVals;
            $aCacheData = [];
        }
        if (empty($aCacheMissPKVals)) {
            $aDBCacheData = [];
        } else {
            $aPKValsHolders = $aPKParam = $aDBCacheData = [];
            foreach ($aCacheMissPKVals as $iIndex => $mVal) {
                $sHolder = $this->_sPKField . '_' . $iIndex;
                $aPKValsHolders[] = self::$_sBindHolder . $sHolder;
                $aPKParam[$sHolder] = $mVal;
            }
            $sSQL = 'select ' . self::_joinSelectString($this->_aDBField, $this->_sClassName) . ' from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKValsHolders) . ')';
            $aDBDatas = self::_getDBData($sSQL, $aPKParam, self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aDBField, $this->_sClassName);
            foreach ($aDBDatas as $aData) {
                $sCacheKey = self::_getCacheRowKey($this->_sClassName, $aData[$this->_sPKField]);
                $aDBCacheData[$sCacheKey] = $aData;
            }
            $this->_setCacheData($aDBCacheData, self::DEFAULT_CACHE_LEVEL, $this->_sCacheName, $this->_sClassName);
        }
        $aORMs = [];
        foreach ($aCacheData as $aData) {
            $aORMs[] = $this->loadSource($aData, true);
        }
        foreach ($aDBCacheData as $aData) {
            $aORMs[] = $this->loadSource($aData, true);
        }
        return $aORMs;
    }

    /**
     * 根据PK删除数据
     *
     * @param mix $p_mPKVals            
     * @return int
     */
    function delDataByPKVals($p_mPKVals)
    {
        $aPKVals = self::_rebuildPKVals($p_mPKVals, $this->_sPKField);
        if (empty($aPKVals)) {
            return 0;
        }
        $aPKValsHolders = $aCacheKeys = $aPKParam = [];
        foreach ($aPKVals as $iIndex => $mVal) {
            $sHolder = $this->_sPKField . '_' . $iIndex;
            $aPKValsHolders[] = self::$_sBindHolder . $sHolder;
            $aPKParam[$sHolder] = $mVal;
            $aCacheKeys[] = self::_getCacheRowKey($this->_sClassName, $mVal);
        }
        if (self::$_bPhyDelete) {
            $sSQL = 'delete from ' . $this->dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKValsHolders) . ')';
            $iLastAffectedCnt = $this->_updDBData($sSQL, $aPKParam, $this->dispatchDB($this->_sMasterDBName), $this->_aDBField, $this->_sClassName);
        } else {
            $aDBData = [];
            $aDBData['iDeleteTime'] = lib_sys_var::getInstance()->getRealTime();
            $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set iDeleteTime=:iDeleteTime where ' . $this->_sPKField . ' in (' . join(' ,', $aPKValsHolders) . ')';
            $iLastAffectedCnt = self::_updDBData($sSQL, array_merge($aDBData, $aPKParam), $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
        }
        self::_clearCacheData($aCacheKeys, $this->_sCacheName, $this->_sClassName);
        return $iLastAffectedCnt;
    }

    /**
     * 根据PK更新数据
     *
     * @param mix $p_mPKVals            
     * @return int
     */
    function updListByPKVals($p_mPKVals)
    {
        $aPKVals = self::_rebuildPKVals($p_mPKVals, $this->_sPKField);
        if (empty($aPKVals)) {
            return [];
        }
        $aORMData = self::_checkField($this->getSource(), $this->_aORMField, $this->_sClassName);
        $aDBData = $this->beforeSave($aORMData);
        $aDBData['iUpdateTime'] = lib_sys_var::getInstance()->getRealTime();
        $aSQLParam = self::_joinUpdString($this->_aAllDBField, $aDBData, $this->_sPKField, $this->_sClassName);
        $aPKValsHolders = $aCacheKeys = $aPKParam = [];
        foreach ($aPKVals as $iIndex => $mVal) {
            $sHolder = $this->_sPKField . '_' . $iIndex;
            $aPKValsHolders[] = self::$_sBindHolder . $sHolder;
            $aPKParam[$sHolder] = $mVal;
            $aCacheKeys[] = self::_getCacheRowKey($this->_sClassName, $mVal);
        }
        $sSQL = 'update ' . $this->dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKValsHolders) . ')';
        $iLastAffectedCnt = self::_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aPKParam), $this->dispatchDB($this->_sMasterDBName), $this->_aAllDBField, $this->_sClassName);
        self::_clearCacheData($aCacheKeys, $this->_sCacheName, $this->_sClassName);
        return $iLastAffectedCnt;
    }

    /**
     * 获取复杂业务的数据列表
     *
     * @param string $p_sSQLName            
     * @param array $p_aParam            
     * @param boolean $p_bStrictFreshCache            
     * @throws Exception
     * @return array
     */
    function getBizList($p_sSQLName, $p_aParam = [], $p_bStrictFreshCache = false)
    {
        if (isset($this->_aBizSQL[$p_sSQLName])) {
            $sSQL = 'select ' . $this->_sPKField . ' from ' . $this->dispatchTable($this->_sTblName) . ' where iDeleteTime=:iDeleteTime and ' . $this->_aBizSQL[$p_sSQLName];
            $aDBData = $this->beforeSave($p_aParam);
            $aDBData['iDeleteTime'] = 0;
            if ($this->_iFetchRow > 0) {
                if ($this->_iStartRow > 0) {
                    $sSQL .= ' limit :iStartRow, :iFetchRow';
                    $aDBData['iStartRow'] = $this->_iStartRow;
                    $aDBData['iFetchRow'] = $this->_iFetchRow;
                    $this->_iStartRow = self::DEFAULT_START_ROW;
                } else {
                    $sSQL .= ' limit :iFetchRow';
                    $aDBData['iFetchRow'] = $this->_iFetchRow;
                }
                $this->_iFetchRow = self::DEFAULT_FETCH_ROW;
            } else {
                throw new Exception($this->_sClassName . ': orm do not allowed get all data.');
            }
            $aPKVals = $this->_getDBData($sSQL, $aDBData, self::SQL_FETCH_TYPE_LIST, $this->dispatchDB($this->_sSlaveDBName), $this->_aAllDBField, $this->_sClassName);
            if (empty($aPKVals)) {
                return [];
            } else {
                return self::_orderPKORMList($aPKVals, $this->getListByPKVals($aPKVals, $p_bStrictFreshCache), $this->_sPKField);
            }
        } else {
            throw new Exception($this->_sClassName . ': you gave an invalid SQL name(' . $p_sSQLName . ').');
        }
    }

    /**
     * 获取复杂业务的统计数字
     *
     * @param string $p_sSQLName            
     * @param array $p_aParam            
     * @throws Exception
     * @return array|string
     */
    function getBizCnt($p_sSQLName, $p_aParam = [])
    {
        if (isset($this->_aBizSQL[$p_sSQLName])) {
            $sSQL = 'select count(*) as cnt from ' . $this->dispatchTable($this->_sTblName) . ' where iDeleteTime=:iDeleteTime and ' . $this->_aBizSQL[$p_sSQLName];
            $aDBData = $this->beforeSave($p_aParam);
            $aDBData['iDeleteTime'] = 0;
            return $this->_getDBData($sSQL, $aDBData, self::SQL_FETCH_TYPE_COLUMN, $this->dispatchDB($this->_sSlaveDBName), $this->_aAllDBField, $this->_sClassName);
        } else {
            throw new Exception($this->_sClassName . ': you gave an invalid SQL name(' . $p_sSQLName . ').');
        }
    }

    /**
     * 开始一个事务
     *
     * @return void
     */
    function beginTransaction()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return void
     */
    function commit()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->commit();
    }

    /**
     * 回滚事务
     *
     * @return void
     */
    function rollBack()
    {
        $sDBName = $this->dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->rollBack();
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
     * @throws Exception
     * @return array
     */
    private static function _checkField($p_aData, $p_aField, $p_sClassName)
    {
        $aData = [];
        foreach ($p_aField as $sField => $aConfig) {
            if (isset($p_aData[$sField])) {
                $mValue = $aData[$sField] = $p_aData[$sField];
                switch ($aConfig['sType']) {
                    case 'int':
                    case 'tinyint':
                    case 'float':
                        $o_sOperator = $o_iParam = '';
                        if (! self::_isSelfOperate($sField, $mValue, $o_sOperator, $o_iParam)) {
                            if (is_numeric($mValue)) {} else {
                                throw new Exception($p_sClassName . ': you gave a nonnumeric value(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which need a number, maybe is ' . gettype($mValue) . '.');
                            }
                        }
                        break;
                    case 'string':
                        if (is_string($mValue)) {
                            $iLength = mb_strlen($mValue);
                            if ($iLength > $aConfig['iLength']) {
                                throw new Exception($p_sClassName . ': you gave an overlength(' . $iLength . ') string(' . var_export($mValue, true) . ') to an attribute(' . $sField . ') which max length is ' . $aConfig['iLength'] . '.');
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
        return $aData;
    }

    /**
     * 获取缓存连接
     *
     * @param string $p_sCacheName            
     * @return void
     */
    private static function _connectCache($p_sCacheName)
    {
        if (isset(self::$_aCachePool[$p_sCacheName])) {} else {
            self::$_aCachePool[$p_sCacheName] = lib_data_pooling::getInstance()->getConnect($p_sCacheName);
        }
    }

    /**
     * 获取数据库连接
     *
     * @param string $p_sDBName            
     * @return void
     */
    private static function _connectDB($p_sDBName)
    {
        if (isset(self::$_aDBPool[$p_sDBName])) {} else {
            self::$_aDBPool[$p_sDBName] = lib_data_pooling::getInstance()->getConnect($p_sDBName);
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
        if (self::$_sLastPreparedSQL == $p_sSQL) {} else {
            self::$_sLastPreparedSQL = $p_sSQL;
            self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        }
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
        self::$_oDebugger->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebugger->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebugger->showMsg($p_sClassName . '->Result: ' . var_export($mData, true));
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
        if (self::$_sLastPreparedSQL == $p_sSQL) {} else {
            self::$_sLastPreparedSQL = $p_sSQL;
            self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        }
        self::_bindData(self::_parseParameter($p_aParam, $p_aDBField, $p_sClassName));
        self::$_mDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastAffectedCnt = self::$_oDBSTMT->rowCount();
        self::$_oDebugger->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebugger->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebugger->showMsg($p_sClassName . '->Result: ' . var_export(self::$_mDebugResult, true));
        self::$_oDebugger->showMsg($p_sClassName . '->Affected row count: ' . $iLastAffectedCnt);
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
     * @return int|false
     */
    private static function _insertDBData($p_sSQL, $p_aParam, $p_sDBName, $p_aDBField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aAllSQLs[] = $p_sSQL;
        if (self::$_sLastPreparedSQL == $p_sSQL) {} else {
            self::$_sLastPreparedSQL = $p_sSQL;
            self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        }
        self::_bindData(self::_parseParameter($p_aParam, $p_aDBField, $p_sClassName));
        self::$_mDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastInsertID = self::$_aDBPool[$p_sDBName]->lastInsertId();
        self::$_oDebugger->showMsg($p_sClassName . '->Execute: ' . $p_sSQL);
        self::$_oDebugger->showMsg($p_sClassName . '->Parameter: ' . var_export($p_aParam, true));
        self::$_oDebugger->showMsg($p_sClassName . '->Result: ' . var_export(self::$_mDebugResult, true));
        self::$_oDebugger->showMsg($p_sClassName . '->LastID: ' . $iLastInsertID);
        return $iLastInsertID;
    }

    /**
     * 根据Key删除缓存
     *
     * @param array $p_aCacheKeys            
     * @param string $p_sCacheName            
     * @param string $p_sClassName            
     * @return true|false
     */
    private static function _clearCacheData($p_aCacheKeys, $p_sCacheName, $p_sClassName)
    {
        ++ self::$_iCacheCnt;
        self::_clearStaticCacheData($p_aCacheKeys, $p_sClassName);
        self::_clearAPCCacheData($p_aCacheKeys, $p_sClassName);
        return self::_clearMemCacheData($p_aCacheKeys, $p_sCacheName, $p_sClassName);
    }

    /**
     * 根据Key删除静态缓存
     *
     * @param array $p_aCacheKeys            
     * @param string $p_sClassName            
     * @return true
     */
    private static function _clearStaticCacheData($p_aCacheKeys, $p_sClassName)
    {
        foreach ($p_aCacheKeys as $sKey) {
            unset(self::$_aStaticCache[$sKey]);
            self::$_oDebugger->showMsg($p_sClassName . '[StaticCache]->Delete: ' . $sKey . '|true');
        }
        return true;
    }

    /**
     * 根据Key删除APC缓存
     *
     * @param array $p_aCacheKeys            
     * @param string $p_sClassName            
     * @return true
     */
    private static function _clearAPCCacheData($p_aCacheKeys, $p_sClassName)
    {
        return true;
    }

    /**
     * 根据Key删除Memcache
     *
     * @param array $p_aCacheKeys            
     * @param string $p_sCacheName            
     * @param string $p_sClassName            
     * @todo
     *
     * @return true|false
     */
    private static function _clearMemCacheData($p_aCacheKeys, $p_sCacheName, $p_sClassName)
    {
        self::_connectCache($p_sCacheName);
        for ($iIndex = 0; $iIndex < self::MAX_CACHE_TRY; ++ $iIndex) {
            $bFoundErr = false;
            self::$_mDebugResult = self::$_aCachePool[$p_sCacheName]->deleteMulti($p_aCacheKeys);
            self::$_oDebugger->showMsg($p_sClassName . '[Memcache]->Delete: Set: Multi Key|' . var_export($p_aCacheKeys, true) . '|' . var_export(self::$_mDebugResult, true));
            foreach (self::$_mDebugResult as $sKey => $mResult) {
                if (true === $mResult) {} else {
                    if (Memcached::RES_NOTFOUND == $mResult) {} else {
                        $aErrKeys[] = $sKey;
                    }
                }
            }
            if (empty($aErrKeys)) {
                return true;
            } else {
                $p_aCacheKeys = $aErrKeys;
            }
        }
        return false;
    }

    /**
     * 写入缓存数据
     *
     * @param array $p_aCache            
     * @param int $p_iDeepLevel            
     * @param string $p_sCacheName            
     * @param string $p_sClassName            
     * @return void
     */
    private static function _setCacheData($p_aCache, $p_iDeepLevel, $p_sCacheName, $p_sClassName)
    {
        ++ self::$_iCacheCnt;
        if ($p_iDeepLevel < 1 or $p_iDeepLevel > 7) {
            $p_iDeepLevel = self::DEFAULT_CACHE_LEVEL;
        }
        $sStyle = substr('00' . decbin($p_iDeepLevel), - 3);
        if (1 == $sStyle[2]) {
            self::_setStaticCacheData($p_aCache, $p_sClassName);
        }
        if (1 == $sStyle[1]) {
            self::_setAPCCacheData($p_aCache, $p_sClassName);
        }
        if (1 == $sStyle[0]) {
            self::_setMemCacheData($p_aCache, $p_sCacheName, $p_sClassName);
        }
    }

    /**
     * 写入静态缓存数据
     *
     * @param array $p_aCache            
     * @param string $p_sClassName            
     * @return void
     */
    private static function _setStaticCacheData($p_aCache, $p_sClassName)
    {
        foreach ($p_aCache as $sKey => $mValue) {
            self::$_aStaticCache[$sKey] = $mValue;
            self::$_oDebugger->showMsg($p_sClassName . '[StaticCache]->Set: ' . $sKey . '|true');
        }
    }

    /**
     * 写入APC缓存数据
     *
     * @param array $p_aCache            
     * @param string $p_sClassName            
     * @return void
     */
    private static function _setAPCCacheData($p_aCache, $p_sClassName)
    {}

    /**
     * 写入Memcache缓存数据
     *
     * @param array $p_aCache            
     * @param string $p_sCacheName            
     * @param string $p_sClassName            
     * @return void
     */
    private static function _setMemCacheData($p_aCache, $p_sCacheName, $p_sClassName)
    {
        self::_connectCache($p_sCacheName);
        foreach ($p_aCache as $sKey => $mValue) {
            $p_aCache[$sKey] = self::_implodeCache($mValue, self::DEFAULT_CACHE_TIME);
        }
        for ($iIndex = 0; $iIndex < self::MAX_CACHE_TRY; ++ $iIndex) {
            self::$_mDebugResult = self::$_aCachePool[$p_sCacheName]->setMulti($p_aCache, self::DEFAULT_CACHE_TIME);
            if (true === self::$_mDebugResult) {
                break;
            }
        }
        self::$_oDebugger->showMsg($p_sClassName . '[Memcache]->Set: Multi Key|' . var_export($p_aCache, true) . '|' . var_export(self::$_mDebugResult, true));
    }

    /**
     * 获取缓存数据
     *
     * @param array $p_aCacheKeys            
     * @param string $p_sCacheName            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _getCacheData($p_aCacheKeys, $p_sCacheName, $p_sClassName)
    {
        ++ self::$_iCacheCnt;
        $aMissKeys = $mData = [];
        foreach ($p_aCacheKeys as $sCacheKey) {
            if (isset(self::$_aStaticCache[$sCacheKey])) {
                $mData[$sCacheKey] = self::$_aStaticCache[$sCacheKey];
                self::$_oDebugger->showMsg($p_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|' . var_export(self::$_aStaticCache[$sCacheKey], true));
            } else {
                $aMissKeys[] = $sCacheKey;
                self::$_oDebugger->showMsg($p_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|false');
            }
        }
        if (empty($aMissKeys)) {
            return $mData;
        }
        self::_connectCache($p_sCacheName);
        $mCacheData = self::$_aCachePool[$p_sCacheName]->getMulti($aMissKeys);
        if (false !== $mCacheData) {
            foreach ($aMissKeys as $sCacheKey) {
                if (isset($mCacheData[$sCacheKey])) {
                    $aEachCacheData = $mCacheData[$sCacheKey];
                    $mData[$sCacheKey] = $aEachCacheData['mData'];
                    self::$_oDebugger->showMsg($p_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|' . var_export($aEachCacheData['mData'], true));
                    self::$_oDebugger->showMsg($p_sClassName . '[Memcache]->Info: Key=>' . $sCacheKey . ' Create=>' . date('Y-m-d H:i:s', $aEachCacheData['iCreateTime']) . ' Expire=>' . (0 == $aEachCacheData['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $aEachCacheData['iCreateTime'] + $aEachCacheData['iLifeTime'])));
                    self::_setStaticCacheData([
                        $sCacheKey => $aEachCacheData['mData']
                    ], $p_sClassName);
                } else {
                    self::$_oDebugger->showMsg($p_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|false');
                }
            }
        }
        return $mData;
    }

    /**
     * 分析SQL参数
     *
     * @param array $p_aParam            
     * @param array $p_aDBField            
     * @param string $p_sClassName            
     * @throws Exception
     * @return array
     */
    private static function _parseParameter($p_aParam, $p_aDBField, $p_sClassName)
    {
        $aParams = [];
        $iPDOType = 0;
        $p_aDBField['iStartRow'] = $p_aDBField['iFetchRow'] = [
            'sType' => 'int',
            'bUnsigned' => true
        ];
        foreach ($p_aParam as $sField => $mValue) {
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
                        throw new Exception($p_sClassName . ': you gave an unknown database field(' . $sField . ') type(' . $p_aDBField[$sField]['sType'] . ').');
                        break;
                }
                $aParams[] = [
                    'sField' => $sField,
                    'mValue' => $mValue,
                    'iPDOType' => $iPDOType
                ];
            } else {
                throw new Exception($p_sClassName . ': you gave an invalid database field(' . $sField . ').');
                break;
            }
        }
        return $aParams;
    }

    /**
     * 绑定变量
     *
     * @param array $p_aParams            
     * @return void
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
     * @throws Exception
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
        }
    }

    /**
     * 获取查询条件
     *
     * @param array $p_aFilters            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @throws Exception
     * @return array
     */
    private static function _joinWhereString($p_aFilters, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $aIndex = $aValue = [];
        $p_aFilters[] = [
            'sField' => 'iDeleteTime',
            'sOperator' => '=',
            'mValue' => 0
        ];
        foreach ($p_aFilters as $aFilter) {
            if (! isset($aIndex[$aFilter['sField']])) {
                $aIndex[$aFilter['sField']] = 0;
            }
            if ('in' == $aFilter['sOperator']) {
                $aHolders = [];
                $aPKVals = self::_rebuildPKVals($aFilter['mValue'], $p_sPKField);
                foreach ($aPKVals as $mPKVal) {
                    $sHolder = $aFilter['sField'] . '_' . ++ $aIndex[$aFilter['sField']];
                    $aHolders[] = $sHolder;
                    $aValue[$sHolder] = $mPKVal;
                }
                $sFields .= ' and ' . $aFilter['sField'] . ' ' . $aFilter['sOperator'] . ' (' . join(',', $aHolders) . ')';
            } else {
                $sHolder = $aFilter['sField'] . '_' . ++ $aIndex[$aFilter['sField']];
                $sFields .= ' and ' . $aFilter['sField'] . $aFilter['sOperator'] . self::$_sBindHolder . $sHolder;
                $aValue[$sHolder] = $aFilter['mValue'];
            }
        }
        if (isset($sFields[0])) {
            return [
                'sFieldStr' => substr($sFields, 5),
                'aValue' => $aValue
            ];
        } else {
            throw new Exception($p_sClassName . ': orm do not allowed get all data.');
        }
    }

    /**
     * 获取ORM添加信息所需SQL信息
     *
     * @param array $p_aDBField            
     * @param array $p_aData            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @throws Exception
     * @return array
     */
    private static function _joinAddString($p_aDBField, $p_aData, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $sParams = '';
        $aValue = [];
        foreach ($p_aDBField as $sField => $aFieldSet) {
            if ($p_sPKField != $sField and isset($p_aData[$sField])) {
                $sFields .= ', ' . $sField;
                $sParams .= ', ' . self::$_sBindHolder . $sField;
                $aValue[$sField] = $p_aData[$sField];
            }
        }
        if (isset($sFields[0])) {
            return [
                'sFieldStr' => substr($sFields, 2),
                'sParamStr' => substr($sParams, 2),
                'aValue' => $aValue
            ];
        } else {
            throw new Exception($p_sClassName . ': you have no data(' . var_export($p_aData, true) . ') to insert.');
        }
    }

    /**
     * 获取ORM更新信息所需SQL信息
     *
     * @param array $p_aDBField            
     * @param array $p_aData            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @throws Exception
     * @return array
     */
    private static function _joinUpdString($p_aDBField, $p_aData, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $aValue = [];
        foreach ($p_aDBField as $sField => $aFieldSet) {
            if ($p_sPKField != $sField and isset($p_aData[$sField])) {
                $sSelfOperator = $iSelfParam = '';
                if (self::_isSelfOperate($sField, $p_aData[$sField], $sSelfOperator, $iSelfParam)) {
                    $sFields .= ', ' . $sField . '=' . $sField . $sSelfOperator . self::$_sBindHolder . $sField;
                    $aValue[$sField] = $iSelfParam;
                } else {
                    $sFields .= ', ' . $sField . '=' . self::$_sBindHolder . $sField;
                    $aValue[$sField] = $p_aData[$sField];
                }
            }
        }
        if (isset($sFields[0])) {
            return [
                'sFieldStr' => substr($sFields, 2),
                'aValue' => $aValue
            ];
        } else {
            throw new Exception($p_sClassName . ': your database fields(' . var_export($p_aDBField, true) . ') are all primary key(' . $p_sPKField . ') or have no data(' . var_export($p_aData, true) . ') to update.');
        }
    }

    /**
     * 判断是否为自运算
     *
     * @param string $p_sField            
     * @param mix $p_mValue            
     * @param string $o_sOperator            
     * @param int $o_iParam            
     * @return true|false
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
     * @param array $p_mPKVals            
     * @param string $p_sPKField            
     * @return array
     */
    private static function _rebuildPKVals($p_mPKVals, $p_sPKField)
    {
        if (is_array($p_mPKVals)) {
            if (empty($p_mPKVals)) {
                return [];
            } else {
                $mPKVal = array_pop($p_mPKVals);
                if (is_array($mPKVal)) {
                    $aPKVals = [];
                    foreach ($p_mPKVals as $aPKVal) {
                        $aPKVals[] = $aPKVal[$p_sPKField];
                    }
                    $aPKVals[] = $mPKVal[$p_sPKField];
                } else {
                    $aPKVals = $p_mPKVals;
                    $aPKVals[] = $mPKVal;
                }
                array_unique($aPKVals);
                return $aPKVals;
            }
        } else {
            return array_unique(explode(',', $p_mPKVals));
        }
    }

    /**
     * 根据主键顺序重新排序
     *
     * @param array $p_aPKVals            
     * @param array $p_aORMs            
     * @param string $p_sPKField            
     * @return array
     */
    private static function _orderPKORMList($p_aPKVals, $p_aORMs, $p_sPKField)
    {
        $aResults = [];
        foreach ($p_aPKVals as $aPKVal) {
            foreach ($p_aORMs as $oORM) {
                if ($aPKVal[$p_sPKField] == $oORM->$p_sPKField) {
                    $aResults[] = $oORM;
                    break;
                }
            }
        }
        return $aResults;
    }

    /**
     * 根据主键数据生成where条件
     *
     * @param string $p_sPKField            
     * @param array $p_aData            
     * @param string $p_sClassName            
     * @param boolean $p_bNeedFake            
     * @throws Exception
     * @return array
     */
    private static function _joinPKWhereString($p_sPKField, $p_aData, $p_sClassName, $p_bNeedFake = false)
    {
        if (isset($p_aData[$p_sPKField])) {
            if ($p_bNeedFake) {
                return [
                    'sFieldStr' => $p_sPKField . '=' . self::$_sBindHolder . $p_sPKField,
                    'aValue' => [
                        $p_sPKField => $p_aData[$p_sPKField]
                    ]
                ];
            } else {
                return [
                    'sFieldStr' => $p_sPKField . '=' . self::$_sBindHolder . $p_sPKField . ' and iDeleteTime=:iDeleteTime_99',
                    'aValue' => [
                        $p_sPKField => $p_aData[$p_sPKField],
                        'iDeleteTime_99' => 0
                    ]
                ];
            }
        } else {
            throw new Exception($p_sClassName . ': you missed ORM primary key value(' . $p_sPKField . ').');
        }
    }

    /**
     * 获取ORM数据缓存Key
     *
     * @param string $p_sORMName            
     * @param mix $p_mPKVal            
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