<?php

/**
 * util_pager
 * @author jxu
 * @package system_util
 */
/**
 * util_pager
 *
 * @author jxu
 */
class util_pager
{

    /**
     * 解析翻页参数
     *
     * @param int $p_iPageNum            
     * @param int $p_iPageSize            
     * @param int $p_iTotalCnt            
     * @return array
     */
    static function getPager($p_iPageNum, $p_iPageSize, $p_iTotalCnt = 0, $p_iDefaultGroupSize = 10)
    {
        $aPage = array(
            'aFirstPage' => array(
                'iIndex' => 1,
                'sURL' => ''
            ),
            'aCurrentPage' => array(
                'iIndex' => $p_iPageNum,
                'sURL' => ''
            ),
            'iPageSize' => $p_iPageSize
        );
        if (is_numeric($p_iPageNum)) {
            if ($p_iPageNum < 1) {
                $aPage['aCurrentPage']['iIndex'] = 1;
            }
        } else {
            $aPage['aCurrentPage']['iIndex'] = 1;
        }
        if (is_numeric($p_iPageSize)) {
            if ($p_iPageSize < 1) {
                $aPage['iPageSize'] = 20;
            }
        } else {
            $aPage['iPageSize'] = 20;
        }
        if (- 1 < $p_iTotalCnt) {
            $aPage['iTotalNum'] = $p_iTotalCnt;
            if (0 == $aPage['iTotalNum'] % $aPage['iPageSize']) {
                $aPage['iTotalPage'] = array(
                    'iIndex' => $aPage['iTotalNum'] / $aPage['iPageSize'],
                    'sURL' => ''
                );
            } else {
                $aPage['iTotalPage'] = array(
                    'iIndex' => floor($aPage['iTotalNum'] / $aPage['iPageSize']) + 1,
                    'sURL' => ''
                );
            }
            if (0 == $aPage['iTotalPage']['iIndex']) {
                $aPage['iTotalPage']['iIndex'] = 1;
            }
            if ($aPage['aCurrentPage']['iIndex'] > $aPage['iTotalPage']['iIndex']) {
                $aPage['aCurrentPage']['iIndex'] = $aPage['iTotalPage']['iIndex'];
            }
            $aPage['aPrePage'] = array(
                'iIndex' => $aPage['aCurrentPage']['iIndex'] - 1,
                'sURL' => ''
            );
            if ($aPage['aPrePage']['iIndex'] < 1) {
                $aPage['aPrePage']['iIndex'] = 1;
            }
            $aPage['aNextPage'] = array(
                'iIndex' => $aPage['aCurrentPage']['iIndex'] + 1,
                'sURL' => ''
            );
            if ($aPage['aNextPage']['iIndex'] > $aPage['iTotalPage']['iIndex']) {
                $aPage['aNextPage']['iIndex'] = $aPage['iTotalPage']['iIndex'];
            }
            if (is_numeric($p_iDefaultGroupSize)) {
                if ($p_iDefaultGroupSize < 1) {
                    $p_iDefaultGroupSize = 20;
                }
            } else {
                $p_iDefaultGroupSize = 20;
            }
            $aPage['iGroupSize'] = $p_iDefaultGroupSize;
            $aPage['aPreGPage'] = array(
                'iIndex' => $aPage['aCurrentPage']['iIndex'] - $p_iDefaultGroupSize,
                'sURL' => ''
            );
            if ($aPage['aPreGPage']['iIndex'] < 1) {
                $aPage['aPreGPage']['iIndex'] = 1;
            }
            $aPage['aNextGPage'] = array(
                'iIndex' => $aPage['aCurrentPage']['iIndex'] + $p_iDefaultGroupSize,
                'sURL' => ''
            );
            if ($aPage['aNextGPage']['iIndex'] > $aPage['iTotalPage']['iIndex']) {
                $aPage['aNextGPage']['iIndex'] = $aPage['iTotalPage']['iIndex'];
            }
            $aPage['aNumURL'] = self::_makeNumURL($aPage['aCurrentPage']['iIndex'], $aPage['iTotalPage']['iIndex'], $aPage['iGroupSize']);
        }
        return $aPage;
    }

    /**
     * 设置翻页控件数据
     *
     * @param array $p_aPage            
     * @param string $p_sControllerName            
     * @param string $p_sColumn            
     * @param array $p_aParam            
     * @return array
     */
    static function setPager($p_aPage, $p_sControllerName, $p_sColumn, $p_aParam = [])
    {
        $sDomain = lib_sys_var::getInstance()->getConfig('sSelfSchemeDomain', 'domain');
        $iCurrentPage = $p_aPage['aCurrentPage']['iIndex'];
        foreach ($p_aPage as $sKey => $aPage) {
            if (is_array($aPage) and isset($aPage['iIndex'])) {
                if ($iCurrentPage == $aPage['iIndex']) {
                    $p_aPage[$sKey]['bLink'] = false;
                } else {
                    $p_aPage[$sKey]['bLink'] = true;
                }
                $p_aPage[$sKey]['sURL'] = $sDomain . lib_sys_router::getInstance()->createURI($p_sControllerName, array_merge($p_aParam, array(
                    $p_sColumn => $p_aPage[$sKey]['iIndex']
                )));
            }
        }
        $iCnt = count($p_aPage['aNumURL']);
        for ($i = 0; $i < $iCnt; ++ $i) {
            if ($iCurrentPage == $p_aPage['aNumURL'][$i]['iIndex']) {
                $p_aPage['aNumURL'][$i]['bLink'] = false;
            } else {
                $p_aPage['aNumURL'][$i]['bLink'] = true;
            }
            $p_aPage['aNumURL'][$i]['sURL'] = $sDomain . lib_sys_router::getInstance()->createURI($p_sControllerName, array_merge($p_aParam, array(
                $p_sColumn => $p_aPage['aNumURL'][$i]['iIndex']
            )));
        }
        return $p_aPage;
    }

    /**
     * 获取页码数据
     *
     * @param int $p_iCurrentPage            
     * @param int $p_iTotalPage            
     * @param int $p_iGroupSize            
     * @return array
     */
    private static function _makeNumURL($p_iCurrentPage, $p_iTotalPage, $p_iGroupSize)
    {
        $iTmp = $p_iGroupSize / 2;
        if ($p_iGroupSize & 1) { // 奇数
            $iLeft = floor($iTmp);
            $iRight = $p_iGroupSize - $iLeft - 1;
        } else {
            $iLeft = $iTmp;
            $iRight = $p_iGroupSize - $iLeft - 1;
        }
        if ($p_iCurrentPage + $iRight > $p_iTotalPage) {
            $iLeft = $iRight + $p_iCurrentPage - $p_iTotalPage + $iLeft;
        }
        if ($p_iCurrentPage - $iLeft < 1) {
            $iRight = $iLeft - $p_iCurrentPage + $iRight + 1;
        }
        $iPageStart = $p_iCurrentPage - $iLeft;
        if ($iPageStart < 1) {
            $iPageStart = 1;
        }
        $iPageEnd = $p_iCurrentPage + $iRight;
        if ($iPageEnd > $p_iTotalPage) {
            $iPageEnd = $p_iTotalPage;
        }
        $iPageEnd = $iPageEnd + 1;
        $aURL = array();
        for ($i = $iPageStart; $i < $iPageEnd; ++ $i) {
            $aURL[] = array(
                'iIndex' => $i,
                'sURL' => ''
            );
        }
        return $aURL;
    }
}