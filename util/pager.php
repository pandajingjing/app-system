<?php

/**
 * util_pager
 *
 * 获取分页数据,设置对应url,可以被业务使用
 *
 * @package util
 */

/**
 * util_pager
 *
 * 获取分页数据,设置对应url,可以被业务使用
 */
class util_pager
{

    /**
     * 默认每页数据数量
     *
     * @var int
     */
    const DEFAULT_PAGE_SIZE = 20;

    /**
     * 默认每组页面数
     *
     * @var int
     */
    const DEFAULT_GROUP_SIZE = 10;

    /**
     * 解析翻页参数
     *
     * @param int $p_iPageNum            
     * @param int $p_iTotalCnt            
     * @param int $p_iPageSize            
     * @param int $p_iDefaultGroupSize            
     * @return array
     */
    static function getPager($p_iPageNum, $p_iTotalCnt, $p_iPageSize = self::DEFAULT_PAGE_SIZE, $p_iDefaultGroupSize = self::DEFAULT_GROUP_SIZE)
    {
        $aPager = [
            'aFirstPage' => [
                'iIndex' => 1,
                'sURL' => ''
            ],
            'aCurrentPage' => [
                'iIndex' => $p_iPageNum,
                'sURL' => ''
            ],
            'iPageSize' => $p_iPageSize
        ];
        if (is_numeric($p_iPageNum)) {
            if ($p_iPageNum < 1) {
                $aPager['aCurrentPage']['iIndex'] = 1;
            }
        } else {
            $aPager['aCurrentPage']['iIndex'] = 1;
        }
        if (is_numeric($p_iPageSize)) {
            if ($p_iPageSize < 1) {
                $aPager['iPageSize'] = self::DEFAULT_PAGE_SIZE;
            }
        } else {
            $aPager['iPageSize'] = self::DEFAULT_PAGE_SIZE;
        }
        if (- 1 < $p_iTotalCnt) {
            $aPager['iTotalNum'] = $p_iTotalCnt;
            if (0 == $aPager['iTotalNum'] % $aPager['iPageSize']) {
                $aPager['iTotalPage'] = [
                    'iIndex' => $aPager['iTotalNum'] / $aPager['iPageSize'],
                    'sURL' => ''
                ];
            } else {
                $aPager['iTotalPage'] = [
                    'iIndex' => floor($aPager['iTotalNum'] / $aPager['iPageSize']) + 1,
                    'sURL' => ''
                ];
            }
            if (0 == $aPager['iTotalPage']['iIndex']) {
                $aPager['iTotalPage']['iIndex'] = 1;
            }
            if ($aPager['aCurrentPage']['iIndex'] > $aPager['iTotalPage']['iIndex']) {
                $aPager['aCurrentPage']['iIndex'] = $aPager['iTotalPage']['iIndex'];
            }
            $aPager['aPrePage'] = [
                'iIndex' => $aPager['aCurrentPage']['iIndex'] - 1,
                'sURL' => ''
            ];
            if ($aPager['aPrePage']['iIndex'] < 1) {
                $aPager['aPrePage']['iIndex'] = 1;
            }
            $aPager['aNextPage'] = [
                'iIndex' => $aPager['aCurrentPage']['iIndex'] + 1,
                'sURL' => ''
            ];
            if ($aPager['aNextPage']['iIndex'] > $aPager['iTotalPage']['iIndex']) {
                $aPager['aNextPage']['iIndex'] = $aPager['iTotalPage']['iIndex'];
            }
            if (is_numeric($p_iDefaultGroupSize)) {
                if ($p_iDefaultGroupSize < 1) {
                    $p_iDefaultGroupSize = self::DEFAULT_GROUP_SIZE;
                }
            } else {
                $p_iDefaultGroupSize = self::DEFAULT_GROUP_SIZE;
            }
            $aPager['iGroupSize'] = $p_iDefaultGroupSize;
            $aPager['aPreGPage'] = [
                'iIndex' => $aPager['aCurrentPage']['iIndex'] - $p_iDefaultGroupSize,
                'sURL' => ''
            ];
            if ($aPager['aPreGPage']['iIndex'] < 1) {
                $aPager['aPreGPage']['iIndex'] = 1;
            }
            $aPager['aNextGPage'] = [
                'iIndex' => $aPager['aCurrentPage']['iIndex'] + $p_iDefaultGroupSize,
                'sURL' => ''
            ];
            if ($aPager['aNextGPage']['iIndex'] > $aPager['iTotalPage']['iIndex']) {
                $aPager['aNextGPage']['iIndex'] = $aPager['iTotalPage']['iIndex'];
            }
            $aPager['aNumURLs'] = self::_makeNumURL($aPager['aCurrentPage']['iIndex'], $aPager['iTotalPage']['iIndex'], $aPager['iGroupSize']);
        }
        return $aPager;
    }

    /**
     * 设置翻页控件数据
     *
     * @param array $p_aPager            
     * @param string $p_sControllerName            
     * @param string $p_sColumn            
     * @param array $p_aParam            
     * @return array
     */
    static function setPager($p_aPager, $p_sControllerName, $p_sColumn, $p_aParam = [])
    {
        $sDomain = lib_sys_var::getInstance()->getConfig('sSelfSchemeDomain', 'domain');
        $iCurrentPage = $p_aPager['aCurrentPage']['iIndex'];
        foreach ($p_aPager as $sKey => $aPage) {
            if (is_array($aPage) and isset($aPage['iIndex'])) {
                if ($iCurrentPage == $aPage['iIndex']) {
                    $p_aPager[$sKey]['bLink'] = false;
                } else {
                    $p_aPager[$sKey]['bLink'] = true;
                }
                $p_aPager[$sKey]['sURL'] = $sDomain . lib_sys_router::getInstance()->createURI($p_sControllerName, array_merge($p_aParam, [
                    $p_sColumn => $aPage['iIndex']
                ]));
            }
        }
        foreach ($p_aPager['aNumURLs'] as $iIndex => $aPage) {
            if ($iCurrentPage == $aPage['iIndex']) {
                $p_aPager['aNumURLs'][$iIndex]['bLink'] = false;
            } else {
                $p_aPager['aNumURLs'][$iIndex]['bLink'] = true;
            }
            $p_aPager['aNumURLs'][$iIndex]['sURL'] = $sDomain . lib_sys_router::getInstance()->createURI($p_sControllerName, array_merge($p_aParam, [
                $p_sColumn => $aPage['iIndex']
            ]));
        }
        return $p_aPager;
    }

    /**
     * 获取分组页码数据
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
        $aURLs = [];
        for ($iIndex = $iPageStart; $iIndex < $iPageEnd; ++ $iIndex) {
            $aURLs[] = [
                'iIndex' => $iIndex,
                'sURL' => ''
            ];
        }
        return $aURLs;
    }
}