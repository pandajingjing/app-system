<?php

/**
 * util_sys_string
 * @author jxu
 * @package system_util_sys
 */

/**
 * 系统字符工具
 *
 * @author jxu
 *        
 */
class util_string
{

    /**
     * 删除字符串首尾字符
     *
     * @param mix $p_mValue            
     * @param string $p_sCharList            
     * @return mix
     */
    static function trimString($p_mValue, $p_sCharList = ' ')
    {
        if (is_null($p_mValue)) {
            return null;
        } elseif (is_bool($p_mValue)) {
            return $p_mValue;
        } elseif (is_array($p_mValue)) {
            foreach ($p_mValue as $sKey => $mValue) {
                $p_mValue[$sKey] = self::trimString($mValue, $p_sCharList);
            }
            return $p_mValue;
        } else {
            return trim($p_mValue, $p_sCharList);
        }
    }

    /**
     * 判断数据类型是否正确
     *
     * @param mix $p_mData            
     * @param string $p_sDataType            
     * @return true/false
     */
    static function chkDataType($p_mData, $p_sDataType)
    {
        if ('' == $p_mData) {
            return false;
        }
        switch ($p_sDataType) {
            case 'i':
            case 'int':
                return 0 < preg_match('/^-?[1-9]?[0-9]*$/', $p_mData) ? true : false;
            case 'url':
                return 0 < preg_match('/^https?:\/\/([a-z0-9-]+\.)+[a-z0-9]{2,4}.*$/', $p_mData) ? true : false;
            case 'email':
                return 0 < preg_match('/^[a-z0-9_+.-]+\@([a-z0-9-]+\.)+[a-z0-9]{2,4}$/i', $p_mData) ? true : false;
            case 'idcard':
                return 0 < preg_match('/^[0-9]{15}$|^[0-9]{17}[a-zA-Z0-9]/', $p_mData) ? true : false;
            case 'area':
            case 'money':
            case 'length':
                return 0 < preg_match('/^\d+(\.\d{1,2})?$/', $p_mData) ? true : false;
            case 'mobile':
                return 0 < preg_match("/^((1[3-9][0-9])|200)[0-9]{8}$/", $p_mData) ? true : false;
            case 'phone':
                return 0 < preg_match('/^(\d{3,4}-?)?\d{7,8}$/', $p_mData) ? true : false;
            case 'chinese':
                return 0 < preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $p_mData) ? true : false;
            default:
                return false;
        }
    }

    /**
     * 检查字符串长度
     *
     * @param string $p_sData            
     * @param int $p_iMinLength            
     * @param int $p_iMaxLength            
     * @param boolean $p_bMultiByte            
     * @return true/false
     */
    static function chkStrLength($p_sData, $p_iMinLength = 0, $p_iMaxLength = 0, $p_bMultiByte = false)
    {
        if ($p_bMultiByte) {
            $iLen = strlen($p_sData);
        } else {
            $iLen = mb_strlen($p_sData);
        }
        if ($p_iMinLength > 0) {
            if ($p_iMinLength > $iLen) {
                return false;
            }
        }
        if ($p_iMaxLength > 0) {
            if ($p_iMaxLength < $iLen) {
                return false;
            }
        }
        return true;
    }

    /**
     * 默认截取字符串结尾
     * 
     * @var string
     */
    const DEFAULT_SUBFIX = '...';

    /**
     * 截取字符串
     *
     * @param string $p_sData            
     * @param int $p_iLength            
     * @param string $p_sSubfix            
     * @param boolean $p_bMultiByte            
     * @return string
     */
    static function subStr($p_sData, $p_iLength, $p_sSubfix = self::DEFAULT_SUBFIX, $p_bMultiByte = false)
    {
        if ($p_bMultiByte) {
            if (strlen($p_sData) > $p_iLength) {
                return substr($p_sData, 0, $p_iLength - strlen($p_sSubfix)) . $p_sSubfix;
            } else {
                return $p_sData;
            }
        } else {
            if (mb_strlen($p_sData) > $p_iLength) {
                return mb_substr($p_sData, 0, $p_iLength - mb_strlen($p_sSubfix)) . $p_sSubfix;
            } else {
                return $p_sData;
            }
        }
    }

    /**
     * 默认随机字符串类型
     *
     * @var int
     */
    const DEFAULT_RAND_STYLE = 6;

    /**
     * 获取随机字符串
     *
     * @param int $p_iLength            
     * @param int $p_iStyle,1-15            
     */
    static function getRand($p_iLength, $p_iStyle = self::DEFAULT_RAND_STYLE)
    {
        if ($p_iStyle < 1 or $p_iStyle > 15) {
            $p_iStyle = self::DEFAULT_RAND_STYLE;
        }
        $sStyle = substr('000' . decbin($p_iStyle), - 4);
        $aSource = [
            '`-=[]\\;\',./~!@#$%^&*()_+{}|:"<>?',
            '0123456789',
            'abcdefghijklmnopqrstuvwxyz',
            'ABCEDFGHIJKLMNOPQRSTUVWXYZ'
        ];
        $sSource = '';
        for ($iIndex = 0; $iIndex < 4; ++ $iIndex) {
            if (1 == $sStyle[$iIndex]) {
                $sSource .= $aSource[$iIndex];
            }
        }
        return substr(str_shuffle(str_repeat($sSource, $p_iLength)), 0, $p_iLength);
    }
}