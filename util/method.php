<?php
/**
 * util method
 * @package system_common_lib_util
 */
/**
 * util method
 * @author jxu
 * @package system_common_lib_util
 */
class util_method{
	static private $_libCache = array();

	/**
	 * 筛选数据
	 * @param array $p_aAllData
	 * @param string $p_sColumn
	 * @param mix $p_mValue
	 * @param mix $p_mDefault
	 * @return mix
	 */
	static function filterData($p_aAllData, $p_sColumn, $p_mValue, $p_mDefault = null){
		foreach($p_aAllData as $aData){
			if($p_mValue == $aData[$p_sColumn]){
				return $p_mValue;
			}
		}
		return $p_mDefault;
	}
	/**
	 * 筛选数据
	 * @param array $aDefault 默认参数
	 * @param array $aParams
	 * @return array
	 */
	static function mergeParams($aDefault, $aParams)
	{
	  foreach($aParams as $key => $value)
	  {
		if(array_key_exists($key, $aDefault) && is_array($aDefault[$key]) && is_array($value))
		  $aDefault[$key] = self::mergeParams($aDefault[$key], $aParams[$key]);
		else
		  $aDefault[$key] = $value;
	  }
	  return $aDefault;
	}

	/**
	 * 直接获取库中的类或者对象实例
	 * @param array $aDefault 默认参数
	 * @param array $aParams
	 * @return array
	 */	
	static function factory($sPath, $mParam=null, $bCache=true)
	{
		if (!empty(self::$_libCache[$sPath]) && $bCache) {
			return self::$_libCache[$sPath];
		}
		load_lib($sPath);

		$sClassName = str_replace('/', '_', trim($sPath, '/'));
		$sType = substr($sClassName, 0, strpos($sClassName, '_'));

		if ($sType=='dao') {
			$mRet = $sClassName;
		} else if ($sType=='bll') {
			$mRet = new $sClassName($mParam);
		} else {
			$mRet = new $sClassName($mParam);
		}
		self::$_libCache[$sPath] = $mRet;

		return self::$_libCache[$sPath];
	}

}