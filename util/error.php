<?php

/**
 * util_error
 * @author jxu
 * @package system_util
 */
/**
 * util_error
 *
 * @author jxu
 */
class util_error
{

    /**
     * 错误类型-不在某个可选范围内
     *
     * @var string
     */
    const TYPE_INVALID = 'Invalid';

    /**
     * 错误类型-格式不对
     *
     * @var string
     */
    const TYPE_FORMAT_ERROR = 'Format_Error';

    /**
     * 错误类型-为空
     *
     * @var string
     */
    const TYPE_EMPTY = 'Empty';

    /**
     * 错误类型-未找到
     *
     * @var string
     */
    const TYPE_NOT_FOUND = 'Not_Found';

    /**
     * 错误类型-长度太长
     *
     * @var string
     */
    const TYPE_LENGTH_LONG = 'Length_Long';

    /**
     * 错误类型-长度太短
     *
     * @var string
     */
    const TYPE_LENGTH_SHORT = 'Length_Short';

    /**
     * 错误类型-值过大
     *
     * @var string
     */
    const TYPE_VALUE_BIG = 'Value_Big';

    /**
     * 错误类型-值过小
     *
     * @var string
     */
    const TYPE_VALUE_SMALL = 'Value_Small';

    /**
     * 错误类型-未知错误
     *
     * @var string
     */
    const TYPE_UNKNOWN_ERROR = 'Unknown_Error';

    /**
     * 错误池
     *
     * @var array
     */
    private static $_aErr = array();

    /**
     * 添加业务错误
     *
     * @param string $p_sErrField            
     * @param string $p_sErrType            
     * @param mix $p_mErrValue            
     */
    static function addBizError($p_sErrField, $p_sErrType, $p_mErrValue = '')
    {
        self::$_aErr[$p_sErrField] = [
            'tag' => 'Biz_' . $p_sErrType,
            'val' => $p_mErrValue
        ];
    }

    /**
     * 添加字段检验错误
     *
     * @param string $p_sErrField            
     * @param string $p_sErrType            
     * @param mix $p_mErrValue            
     */
    static function addFieldError($p_sErrField, $p_sErrType, $p_mErrValue = '')
    {
        self::$_aErr[$p_sErrField] = [
            'tag' => 'Field_' . $p_sErrType,
            'val' => $p_mErrValue
        ];
    }

    /**
     * 添加系统错误
     *
     * @param string $p_sErrField            
     * @param string $p_sErrType            
     * @param mix $p_mErrValue            
     */
    static function addSysError($p_sErrField, $p_sErrType, $p_mErrValue = '')
    {
        self::$_aErr[$p_sErrField] = [
            'tag' => 'Sys_' . $p_sErrType,
            'val' => $p_mErrValue
        ];
    }

    /**
     * 是否有错
     *
     * @return true/false
     */
    static function isError()
    {
        if (empty(self::$_aErr)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取所有错误
     *
     * @return array
     */
    static function getErrors()
    {
        return self::$_aErr;
    }

    /**
     * 获取最后一个错误
     *
     * @return string
     */
    static function getLastError()
    {
        return self::$_aErr[count(self::$_aErr) - 1];
    }

    /**
     * 清空错误
     */
    static function initError()
    {
        self::$_aErr = array();
    }
}