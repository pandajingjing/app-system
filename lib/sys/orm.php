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
            'sType' => 'int/tinyint', // 必须
            'bUnsigned' => true, // 非必须,默认true
            'mDefault' => 0, // 非必须,默认0
            'bAutoIncrement' => false/* 非必须,默认false*/
        ],
        'string' => [
            'sType' => 'string', // 必须
            'iLength' => 255, // 必须
            'mDefault' => ''/* 非必须,默认''*/
        ]
    ];

    /**
     * ORM字段结构
     *
     * @var array
     */
    protected $_aORMField = [
        'int/tinyint' => [
            'sType' => 'int/tinyint', // 必须
            'bUnsigned' => true, // 非必须,默认true
            'mDefault' => 0, // 非必须,默认0
            'bAutoIncrement' => false/* 非必须,默认false*/
        ],
        'float' => [
            'sType' => 'float', // 必须
            'bUnsigned' => true, // 非必须,默认true
            'mDefault' => 0/* 非必须,默认0*/
        ],
        'string' => [
            'sType' => 'string', // 必须
            'iLength' => 255, // 必须
            'mDefault' => ''/* 非必须,默认''*/
        ],
        'array' => [
            'sType' => 'array', // 必须
            'mDefault' => []/* 非必须,默认''*/
        ]
    ];

    /**
     * 业务SQL语句
     *
     * @var array
     */
    protected $_aBizSQLs = [
        'lp_list_most_cheap_5' => [
            'sSQL' => 'select iAutoID from iStatus=:iStatus order by iPrice desc',
            'iType' => self::SQL_FETCH_TYPE_LIST
        ]
    ];

    /**
     * 所有执行的SQL语句
     *
     * @var array
     */
    protected static $_aAllSQLs = [];
    
    // 系统属性
    
    /**
     * 类名
     *
     * @var string
     */
    protected $_sClassName = '';

    /**
     * 数据库操作次数
     *
     * @var int
     */
    protected static $_iQueryCnt = 0;

    /**
     * 缓存操作次数
     *
     * @var int
     */
    protected static $_iCacheCnt = 0;

    /**
     * PHP静态变量缓存
     *
     * @var array
     */
    protected static $_aStaticCache = [];

    /**
     * 数据库连接池
     *
     * @var array
     */
    protected static $_aDBPool = [];

    /**
     * 数据库陈述
     *
     * @var object
     */
    protected static $_oDBSTMT = null;

    /**
     * 缓存连接
     *
     * @var object
     */
    protected static $_oCache = null;

    /**
     * 调试对象
     *
     * @var object
     */
    protected static $_oDebug = null;

    /**
     * 用于保存调试信息
     *
     * @var mix
     */
    protected static $_mixDebugResult = null;

    /**
     * 变量绑定占位符
     *
     * @var string
     */
    protected static $_sBindHolder = ':';

    /**
     * ORM数据
     *
     * @var array
     */
    protected $_aORMData = [];

    /**
     * 数据库数据
     *
     * @var array
     */
    protected $_aDBData = [];

    /**
     * 是否开启缓存
     *
     * @var $_bolNeedCache
     */
    protected $_bolNeedCache = true;

    /**
     * 数据缓存时间
     *
     * @var int
     */
    protected $_iDataCacheTime = 86400;

    /**
     * 数据缓存是否压缩
     *
     * @var int
     */
    protected $_iDataCacheCompress = 0;

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
                'sType' => 'int'
            ],
            'iUpdateTime' => [
                'sType' => 'int'
            ],
            'iDeleteTime' => [
                'sType' => 'int'
            ]
        ]);
        $aDefaultDBField = [
            'int' => [
                'bUnsigned' => true,
                'mDefault' => 0,
                'bAutoIncrement' => false
            ],
            'tinyint' => [
                'bUnsigned' => true,
                'mDefault' => 0,
                'bAutoIncrement' => false
            ],
            'string' => [
                'mDefault' => ''
            ],
            'float' => [
                'mDefault' => 0
            ],
            'array' => [
                'mDefault' => []
            ]
        ];
        foreach ($this->_aDBField as $sField => $aConfig) {
            $aDefaultField = $aDefaultDBField[$aConfig['sType']];
            foreach ($aDefaultField as $sKey => $mVal) {
                if (! isset($aConfig[$sKey])) {
                    $this->_aDBField[$sField][$sKey] = $mVal;
                }
            }
        }
        foreach ($this->_aORMField as $sField => $aConfig) {
            $aDefaultField = $aDefaultDBField[$aConfig['sType']];
            foreach ($aDefaultField as $sKey => $mVal) {
                if (! isset($aConfig[$sKey])) {
                    $this->_aORMField[$sField][$sKey] = $mVal;
                }
            }
        }
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
        return self::$_aSQLs;
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
        return self::_clearCacheData(self::_getCacheRowKey($this->_sClassName, $this->dispatchTable($this->_sTblName), $this->_sPKField, $p_mPKVal));
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
        $aSQLParam = self::_joinAddString($this->_aDBField, $aDBData);
        $sSQL = 'insert into ' . $this->dispatchTable($this->_sTblName) . ' (' . $aSQLParam['sFieldStr'] . ')values(' . $aSQLParam['sParamStr'] . ')';
        return self::_insertDBData($sSQL, $aSQLParam['aValue'], $this->dispatchDB($this->_sMasterDBName), $this->_sClassName);
    }

    /**
     * 更新数据
     *
     * @return int
     */
    function updData()
    {
        $aNewData = $this->getSource();
        $aOldData = $this->getRow();
        foreach ($aNewData as $sField => $sValue) {
            if ($sField != $this->_sPKField and $sValue == $aOldData[$sField]) {
                unset($aNewData[$sField]);
            }
        }
        if (1 == count($aNewData)) {
            return 0;
        }
        $aSQLParam = self::_joinUpdString($this->_aTblField, $aNewData, $this->_sPKField);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $aNewData);
        $sSQL = 'update ' . $this->_dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $aPKParam['sFieldStr'];
        $aSQLParam['aValue'] = array_merge($aSQLParam['aValue'], $aPKParam['aValue']);
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
        return self::_updDBData($sSQL, $aSQLParam['aValue']);
    }

    /**
     * 删除数据
     *
     * @return int
     */
    function delData()
    {
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $this->_aData);
        $sSQL = 'delete from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
        $this->clearRowCache($aPKParam['aValue'][$this->_sPKField]);
        return $this->_updDBData($sSQL, $aPKParam['aValue']);
    }

    /**
     * 获取一行数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return array/null
     */
    function getRow($p_bStrictFreshCache = false)
    {
        // $aORMData = $this->beforeRead($aDBData);
        // $this->loadSource($aORMData);
        $aPKParam = self::_joinPKWhereString($this->_sPKField, $this->_aData);
        $sCacheKey = self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKField, $aPKParam['aValue'][$this->_sPKField]);
        if ($p_bStrictFreshCache or ! $this->_bolNeedCache) {
            $aData = false;
        } else {
            $aData = $this->_getCacheData($sCacheKey);
        }
        if (false === $aData) {
            $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aPKParam['sFieldStr'];
            $aData = $this->_getDBData($sSQL, $aPKParam['aValue'], 2);
            if (null === $aData) {
                return null;
            }
            $this->_aData = $aData;
            $this->_setCacheData($sCacheKey, $aData);
        } else {
            $this->_aData = $aData;
        }
        return $this->_aData;
    }

    /**
     * 获取多行数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return array
     */
    function getList($p_sSQLName, $p_bStrictFreshCache = false)
    {
        $sSQL = 'select ' . $this->_sPKField . ' from ' . $this->_dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilter);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        if ('' != $this->_sOrder) {
            $sSQL .= ' order by ' . $this->_sOrder;
            $this->_sOrder = '';
        }
        if ($this->_iFetchRow > 0) {
            if ($this->_iStartRow > 0) {
                $sSQL .= ' limit ' . $this->_iStartRow . ',' . $this->_iFetchRow;
                $this->_iStartRow = 0;
            } else {
                $sSQL .= ' limit ' . $this->_iFetchRow;
            }
            $this->_iFetchRow = 0;
        }
        $aPKIDs = $this->_getDBData($sSQL, $aWhereParam['aValue'], 3);
        if (empty($aPKIDs)) {
            return array();
        }
        $iCnt = count($aPKIDs);
        if (0 < $iCnt) {
            return $this->getListByPKIDs($aPKIDs, $p_bStrictFreshCache);
        } else {
            return array();
        }
    }

    /**
     * 得到统计数据
     *
     * @param boolean $p_bStrictFreshCache            
     * @return int
     */
    function getCnt($p_sSQLName, $p_bStrictFreshCache = false)
    {
        $sSQL = 'select count(*) as cnt from ' . $this->_dispatchTable($this->_sTblName);
        $aWhereParam = self::_joinWhereString($this->_aFilter);
        $sSQL .= ' where ' . $aWhereParam['sFieldStr'];
        return $this->_getDBData($sSQL, $aWhereParam['aValue'], 1);
    }

    /**
     * 根据PKID获取数据
     *
     * @param mix $p_mIDs            
     * @param boolean $p_bStrictFreshCache            
     * @return array
     */
    function getListByPKs($p_mIDs, $p_bStrictFreshCache = false)
    {
        $aPKIDs = self::_rebuildPKIDs($p_mIDs, $this->_sPKField);
        if (empty($aPKIDs)) {
            return array();
        }
        $aRS = array();
        $iCntPKIDs = count($aPKIDs);
        if ($this->_bolNeedCache and ! $p_bStrictFreshCache) {
            $aCacheKey = array();
            $aCacheRS = array();
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                $aCacheKey[] = self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKField, $aPKIDs[$i]);
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
                $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
                $aDBData = $this->_getDBData($sSQL, $aParam, 3);
                $iCntDBData = count($aDBData);
                $aNeedCacheData = array();
                for ($i = 0; $i < $iCntDBData; ++ $i) {
                    $aNeedCacheData[self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKField, $aDBData[$i][$this->_sPKField])] = $aDBData[$i];
                }
                $this->_setCacheDataMulti($aNeedCacheData);
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
            $aPKIDsPattern = '';
            $aParam = array();
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKField . '_' . $i;
                $aParam[$this->_sPKField . '_' . $i] = $aPKIDs[$i];
            }
            $sSQL = 'select ' . self::_joinSelectString($this->_aTblField) . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
            $aDBData = $this->_getDBData($sSQL, $aParam, 3);
            $iCntDBData = count($aDBData);
            for ($i = 0; $i < $iCntDBData; ++ $i) {
                $this->_setCacheData(self::_getCacheRowKey($this->_sClassName, $this->_dispatchTable($this->_sTblName), $this->_sPKField, $aDBData[$i][$this->_sPKField]), $aDBData[$i]);
            }
            for ($i = 0; $i < $iCntPKIDs; ++ $i) {
                for ($k = 0; $k < $iCntDBData; ++ $k) {
                    if ($aPKIDs[$i] == $aDBData[$k][$this->_sPKField]) {
                        $aRS[$i] = $aDBData[$k];
                        continue 2;
                    }
                }
            }
        }
        return $aRS;
    }

    /**
     * 根据PKID更新数据
     *
     * @param mix $p_mIDs            
     * @return int
     */
    function updListByPKs($p_mIDs)
    {
        $aPKIDs = self::_rebuildPKIDs($p_mIDs, $this->_sPKField);
        if (empty($aPKIDs)) {
            return array();
        }
        $iCnt = count($aPKIDs);
        $aSQLParam = self::_joinUpdString($this->_aTblField, $this->_aData, $this->_sPKField);
        $aPKIDsPattern = '';
        $aParam = array();
        for ($i = 0; $i < $iCnt; ++ $i) {
            $this->clearRowCache($aPKIDs[$i]);
            $aPKIDsPattern[] = self::$_sBindHolder . $this->_sPKField . '_' . $i;
            $aParam[$this->_sPKField . '_' . $i] = $aPKIDs[$i];
        }
        $sSQL = 'update ' . $this->_dispatchTable($this->_sTblName) . ' set ' . $aSQLParam['sFieldStr'] . ' where ' . $this->_sPKField . ' in (' . join(' ,', $aPKIDsPattern) . ')';
        return $this->_updDBData($sSQL, array_merge($aSQLParam['aValue'], $aParam));
    }

    /**
     * 执行SQL
     *
     * @param string $p_sSQLName            
     * @param array $p_aParam            
     * @return array/string
     */
    function executeSQL($p_sSQLName, $p_aParam = array())
    {
        if (isset($this->_aRegSQLs[$p_sSQLName])) {
            $aRegSQL = $this->_aRegSQLs[$p_sSQLName];
            $sSQL = 'select ' . $aRegSQL['sField'] . ' from ' . $this->_dispatchTable($this->_sTblName) . ' where ' . $aRegSQL['sWhere'];
            return $this->_getDBData($sSQL, $p_aParam, $aRegSQL['iType']);
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
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->beginTransaction();
    }

    /**
     * 提交事务
     */
    function commit()
    {
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->commit();
    }

    /**
     * 回滚事务
     */
    function rollBack()
    {
        $sDBName = $this->_dispatchDB($this->_sMasterDBName);
        self::_connectDB($sDBName);
        self::$_aDBPool[$sDBName]->rollBack();
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
                            if (! is_numeric($mValue)) {
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
            } else {
                switch ($aConfig['sType']) {
                    case 'int':
                    case 'tinyint':
                        if ($aConfig['bAutoIncrement']) {} else {
                            $p_aData[$sField] = $aConfig['mDefault'];
                        }
                        break;
                    case 'float':
                    case 'string':
                    case 'array':
                        $p_aData[$sField] = $aConfig['mDefault'];
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
        if (isset(self::$_aDBPool[$p_sDBName])) {} else {
            self::$_aDBPool[$p_sDBName] = lib_db_pooling::getInstance()->getConnect($p_sDBName);
        }
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
     * 根据Key删除缓存
     *
     * @param string $p_sCacheKey            
     * @return true/false
     */
    private function _clearCacheData($p_sCacheKey)
    {
        ++ self::$_iCacheCnt;
        $this->_clearStaticCacheData($p_sCacheKey);
        $this->_clearAPCCacheData($p_sCacheKey);
        return $this->_clearMemCacheData($p_sCacheKey);
    }

    /**
     * 根据Key删除静态缓存
     *
     * @param string $p_sCacheKey            
     * @return true
     */
    private function _clearStaticCacheData($p_sCacheKey)
    {
        if (isset(self::$_aStaticCache[$p_sCacheKey])) {
            unset(self::$_aStaticCache[$p_sCacheKey]);
            self::$_mixDebugResult = true;
        } else {
            self::$_mixDebugResult = false;
        }
        if (self::$_bolDebug) {
            self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        }
        return true;
    }

    /**
     * 根据Key删除APC缓存
     *
     * @param string $p_sCacheKey            
     */
    private function _clearAPCCacheData($p_sCacheKey)
    {}

    /**
     * 根据Key删除Memcache
     *
     * @param string $p_sCacheKey            
     * @return true/false
     */
    private function _clearMemCacheData($p_sCacheKey)
    {
        self::_connectCache();
        for ($i = 0; $i < 5; ++ $i) {
            self::$_mixDebugResult = self::$_oCache->delete($p_sCacheKey);
            if (true === self::$_mixDebugResult) {
                break;
            }
        }
        if (self::$_bolDebug) {
            self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Delete: ' . $p_sCacheKey . '|' . var_export(self::$_mixDebugResult, true));
        }
        return self::$_mixDebugResult;
    }

    /**
     * 写入缓存数据
     *
     * @param array $p_aCache            
     * @param int $p_iDeepLevel
     *            1-static, 2-apc, 4-memcache
     */
    private function _setCacheDataMulti($p_aCache, $p_iDeepLevel = 1)
    {
        ++ self::$_iCacheCnt;
        if (0x04 === ($p_iDeepLevel & 0x04)) {
            $this->_setStaticCacheDataMulti($p_aCache);
        }
        if (0x02 === ($p_iDeepLevel & 0x02)) {
            $this->_setAPCCacheDataMulti($p_aCache);
        }
        if (0x01 === ($p_iDeepLevel & 0x01)) {
            $this->_setMemCacheDataMulti($p_aCache);
        }
    }

    /**
     * 写入静态缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private function _setStaticCacheDataMulti($p_aCache)
    {
        if (self::$_bolDebug) {
            foreach ($p_aCache as $sKey => $mValue) {
                self::$_aStaticCache[$sKey] = $mValue;
                self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Set: ' . $sKey . '|true');
            }
        } else {
            foreach ($p_aCache as $sKey => $mValue) {
                self::$_aStaticCache[$sKey] = $mValue;
            }
        }
    }

    /**
     * 写入APC缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private function _setAPCCacheDataMulti($p_aCache)
    {}

    /**
     * 写入Memcache缓存数据
     *
     * @param array $p_aCache            
     * @param mix $p_mData            
     */
    private function _setMemCacheDataMulti($p_aCache)
    {
        self::_connectCache();
        if (self::$_bolDebug) {
            foreach ($p_aCache as $sKey => $mValue) {
                $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
            }
            for ($i = 0; $i < 5; ++ $i) {
                self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
                if (true === self::$_mixDebugResult) {
                    break;
                }
            }
            self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Set: Multi Key|' . var_export($p_aCache, true) . '|' . var_export(self::$_mixDebugResult, true));
            if (false !== self::$_mixDebugResult) {
                self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>Multi Key Create=>' . date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime']) . ' Expire=>' . (0 == $p_aCache[$sKey]['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $p_aCache[$sKey]['iCreateTime'] + $p_aCache[$sKey]['iLifeTime'])));
            }
        } else {
            foreach ($p_aCache as $sKey => $mValue) {
                $p_aCache[$sKey] = self::_implodeCache($mValue, $this->_iDataCacheTime);
            }
            for ($i = 0; $i < 5; ++ $i) {
                self::$_mixDebugResult = self::$_oCache->setMulti($p_aCache, $this->_iDataCacheTime);
                if (true === self::$_mixDebugResult) {
                    break;
                }
            }
        }
    }

    /**
     * 获取缓存数据
     *
     * @param mix $p_mCacheKey            
     * @return mix
     */
    private function _getCacheData($p_mCacheKey)
    {
        ++ self::$_iCacheCnt;
        if (is_array($p_mCacheKey)) {
            $iResultType = 1; // 数组
        } else {
            $iResultType = 2; // 单个
            $p_mCacheKey = array(
                $p_mCacheKey
            );
        }
        $iCnt = count($p_mCacheKey);
        $aMissKey = array();
        $mData = array();
        if (self::$_bolDebug) {
            foreach ($p_mCacheKey as $sCacheKey) {
                if (isset(self::$_aStaticCache[$sCacheKey])) {
                    $mData[$sCacheKey] = self::$_aStaticCache[$sCacheKey];
                    self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|' . var_export(self::$_aStaticCache[$sCacheKey], true));
                } else {
                    $aMissKey[] = $sCacheKey;
                    self::$_oDebug->showMsg($this->_sClassName . '[StaticCache]->Get: ' . $sCacheKey . '|false');
                }
            }
        } else {
            foreach ($p_mCacheKey as $sCacheKey) {
                if (isset(self::$_aStaticCache[$sCacheKey])) {
                    $mData[$sCacheKey] = self::$_aStaticCache[$sCacheKey];
                } else {
                    $aMissKey[] = $sCacheKey;
                }
            }
        }
        if (empty($aMissKey)) {
            if (1 == $iResultType) {
                return $mData;
            } else {
                return $mData[$p_mCacheKey[0]];
            }
        }
        $iCnt = count($aMissKey);
        self::_connectCache();
        $aCacheData = self::$_oCache->getMulti($aMissKey);
        if (self::$_bolDebug) {
            foreach ($aMissKey as $sCacheKey) {
                if (isset($aCacheData[$sCacheKey])) {
                    $aEachCacheData = $aCacheData[$sCacheKey];
                    $mData[$sCacheKey] = $aEachCacheData['mData'];
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|' . var_export($aEachCacheData['mData'], true));
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Info: Key=>' . $sCacheKey . ' Create=>' . date('Y-m-d H:i:s', $aEachCacheData['iCreateTime']) . ' Expire=>' . (0 == $aEachCacheData['iLifeTime'] ? 'unlimit' : date('Y-m-d H:i:s', $aEachCacheData['iCreateTime'] + $aEachCacheData['iLifeTime'])));
                    $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
                } else {
                    self::$_oDebug->showMsg($this->_sClassName . '[Memcache]->Get: ' . $sCacheKey . '|false');
                }
            }
        } else {
            foreach ($aMissKey as $sCacheKey) {
                if (isset($aCacheData[$sCacheKey]) and false !== $aCacheData[$sCacheKey]) {
                    $aEachCacheData = $aCacheData[$sCacheKey];
                    $mData[$sCacheKey] = $aEachCacheData['mData'];
                    $this->_setStaticCacheData($sCacheKey, $aEachCacheData['mData']);
                }
            }
        }
        if (0 == count($mData)) {
            return false;
        } else {
            if (1 == $iResultType) {
                return $mData;
            } else {
                return $mData[$p_mCacheKey[0]];
            }
        }
    }

    /**
     * 获取数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param int $p_iType            
     * @param string $p_sDBName            
     * @param array $p_aTblField            
     * @param string $p_sClassName            
     * @return array/string
     */
    private function _getDBData($p_sSQL, $p_aParam, $p_iType, $p_sDBName, $p_aTblField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aTblField));
        self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        self::$_oDBSTMT->setFetchMode(PDO::FETCH_ASSOC);
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
        if (self::$_bolDebug) {
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Parameter: ' . var_export($p_aParam, true));
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Result: ' . var_export($mData, true));
        }
        return $mData;
    }

    /**
     * 更新数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param string $p_sDBName            
     * @param array $p_aTblField            
     * @param string $p_sClassName            
     * @return int
     */
    private static function _updDBData($p_sSQL, $p_aParam, $p_sDBName, $p_aTblField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aTblField));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastAffectedCnt = self::$_oDBSTMT->rowCount();
        if (self::$_bolDebug) {
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Parameter: ' . var_export($p_aParam, true));
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />Affected row count: ' . $iLastAffectedCnt, true);
        }
        return $iLastAffectedCnt;
    }

    /**
     * 插入数据库数据
     *
     * @param string $p_sSQL            
     * @param array $p_aParam            
     * @param string $p_sDBName            
     * @param array $p_aTblField            
     * @param string $p_sClassName            
     * @return int/false
     */
    private static function _insertDBData($p_sSQL, $p_aParam, $p_sDBName, $p_aTblField, $p_sClassName)
    {
        self::_connectDB($p_sDBName);
        self::$_aSQLs[] = $p_sSQL;
        self::$_oDBSTMT = self::$_aDBPool[$p_sDBName]->prepare($p_sSQL);
        self::_bindData(self::_parseParameter($p_aParam, $p_aTblField));
        self::$_mixDebugResult = self::$_oDBSTMT->execute();
        ++ self::$_iQueryCnt;
        $iLastInsertID = self::$_aDBPool[$p_sDBName]->lastInsertId();
        if (self::$_bolDebug) {
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Execute: ' . $p_sSQL);
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Parameter: ' . var_export($p_aParam, true));
            self::$_oDebug->showMsg($p_sClassName . '[' . $p_sDBName . ']->Result: ' . var_export(self::$_mixDebugResult, true) . '.<br />LastID: ' . $iLastInsertID, true);
        }
        return $iLastInsertID;
    }

    /**
     * 分析SQL参数
     *
     * @param array $p_aParam            
     * @param array $p_aTblFields            
     * @return array
     */
    private static function _parseParameter($p_aParam, $p_aTblFields)
    {
        $aParam = array();
        foreach ($p_aParam as $sField => $mValue) {
            $aField = array();
            preg_match('/([a-zA-Z0-9]+)(\_\d)?/', $sField, $aField);
            $aParam[] = array(
                'sField' => $sField,
                'mValue' => $mValue,
                'sPDOType' => @$p_aTblFields[$aField[1]]['sPDOType']
            );
        }
        return $aParam;
    }

    /**
     * 绑定变量
     *
     * @param array $p_aParam            
     */
    private static function _bindData($p_aParam)
    {
        foreach ($p_aParam as $aParam) {
            $mValue = $aParam['mValue'];
            self::$_oDBSTMT->bindParam(self::$_sBindHolder . $aParam['sField'], $mValue, $aParam['sPDOType']);
            unset($mValue);
        }
    }

    /**
     * 获取ORM获取数据所需SQL信息
     *
     * @param array $p_aTblFields            
     * @param string $p_sClassName            
     * @return string
     */
    private static function _joinSelectString($p_aTblFields, $p_sClassName)
    {
        $sFields = '';
        foreach ($p_aTblFields as $sField => $aFieldSet) {
            $sFields .= ', ' . $sField;
        }
        if (isset($sFields[0])) {
            return substr($sFields, 2);
        } else {
            throw new Exception($p_sClassName . ': your database field(' . var_export($p_aTblFields, true) . ') is empty.');
            return false;
        }
    }

    /**
     * 获取ORM添加信息所需SQL信息
     *
     * @param array $p_aTblFields            
     * @param array $p_aData            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _joinAddString($p_aTblFields, $p_aData, $p_sClassName)
    {
        $sFields = '';
        $sParams = '';
        $aValues = '';
        foreach ($p_aTblFields as $sField => $aFieldSet) {
            if (isset($p_aData[$sField])) {
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
     * @param array $p_aTblFields            
     * @param array $p_aData            
     * @param string $p_sPKField            
     * @param string $p_sClassName            
     * @return array
     */
    private static function _joinUpdString($p_aTblFields, $p_aData, $p_sPKField, $p_sClassName)
    {
        $sFields = '';
        $aValues = [];
        foreach ($p_aTblFields as $sField => $aFieldSet) {
            if ($p_sPKField != $sField and isset($p_aData[$sField])) {
                $sSelfOperator = $iSelfParam = '';
                if (self::_isSelfOperate($sField, $p_aData[$sField], $sSelfOperator, $iSelfParam)) {
                    $sFields .= ', ' . $sField . '=' . $sField . $sSelfOperator . self::$_sBindHolder . $sField;
                    $aValues[$sField] = $iSelfParam;
                } else {
                    $sFields .= ', ' . $sField . '=' . self::$_sBindHolder . $sField . '_update';
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
            throw new Exception($p_sClassName . ': your database fields(' . var_export($p_aTblFields, true) . ') are all primary key(' . $p_sPKField . ') or have no data(' . var_export($p_aData, true) . ') to update.');
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
    private static function _rebuildPKIDs($p_mIDs, $p_sPKField)
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
     * @param string $p_sTblName            
     * @param string $p_sPKField            
     * @param int $p_mPKVal            
     * @return string
     */
    private static function _getCacheRowKey($p_sORMName, $p_sTblName, $p_sPKField, $p_mPKVal)
    {
        return $p_sORMName . '_r_' . $p_sTblName . '_' . $p_sPKField . '_' . $p_mPKVal;
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