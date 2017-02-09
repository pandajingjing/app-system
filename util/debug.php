<?php

/**
 * util_debug
 * @author jxu
 * @package system_util
 */

/**
 * util_debug
 *
 * @author jxu
 */
class util_debug
{

    /**
     * 调试函数
     *
     * 用法类似var_dump()
     * 支持任意个参数。
     */
    static function debug()
    {
        $iCnt = func_num_args();
        $aParamList = func_get_args();
        
        if (0 == $iCnt) {
            return;
        } elseif (1 == $iCnt) {
            $mParam = $aParamList[0];
            switch (true) {
                case is_string($mParam):
                    echo '<p class="text-success">string(' . mb_strlen($mParam) . '):' . htmlspecialchars($mParam) . '</p>';
                    break;
                case is_float($mParam):
                    echo '<p class="text-info">float:' . $mParam . '</p>';
                    break;
                case is_int($mParam):
                    echo '<p class="text-info">int:' . $mParam . '</p>';
                    break;
                case is_null($mParam):
                    echo '<p class="text-danger">null</p>';
                    break;
                case is_bool($mParam):
                    echo '<p class="text-warning">' . ($mParam ? 'true' : 'false') . '</p>';
                    break;
                case is_array($mParam):
                    echo '<pre>';
                    print_r($mParam);
                    echo '</pre>';
                    break;
            }
        } else {
            foreach ($aParamList as $mParam) {
                self::debug($mParam);
            }
        }
    }
}