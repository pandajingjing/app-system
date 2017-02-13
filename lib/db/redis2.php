<?php
/**
 * util redis
 * @package system_common_lib_util
 */

class util_redis{
	private static $_aCache;
	static function getInstance($sInstance)
	{
		if (!empty(self::$_aCache[$sInstance])) {
			return self::$_aCache[$sInstance];
		}

		$oClient = new Redis;
		self::$_aCache[$sInstance] = $oClient;
		$aConf = get_config($sInstance, 'redis');

		$i = 0; //todo get load balance;
		@$oClient->connect($aConf[$i]['host'], $aConf[$i]['port']);

		// If seted database in config, change the selected database
		if(isset($aConf[$i]['database'])) {
			@$oClient->select($aConf[$i]['database']);
		}

		return self::$_aCache[$sInstance];
	}
}
