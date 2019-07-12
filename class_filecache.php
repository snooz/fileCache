<?php
/*
	Written by Peter Palma 2019
	I made this for hosts where you don't have access to memcache, memcached or redis
	MIT License
	Static class version
*/

class fileCache {
	const CACHE_VERSION = '1-'; // Change value to reset cache, must be filesystem friendly.
	const CACHE_FOLDER = '/var/www/cache/'; // Set the filecache folder

	/*
		UTF-8 Encode an array
	*/
    private static function utf8_encode_array($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = self::utf8_encode_array($v);
            }
        } elseif (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

	/*
		UTF-8 Decode an array
	*/
    private static function utf8_decode_array($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = self::utf8_decode_array($v);
            }
        } elseif (is_string ($d)) {
            return utf8_decode($d);
        }
        return $d;
    }

	/*
		Get the cache, if not found returns false
		$key: String value for your cache key
		$ttl: How long the cache is valid
	*/
	public static function get($key, $ttl = 120) {
		if (!file_exists(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json')) {
			return false;
		}
		$fileCacheJson = file_get_contents(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json');
		$fileCache = json_decode($fileCacheJson, true);
		if (strtotime($fileCache['date']) > strtotime('-' . $ttl . ' seconds')) {
			if ($fileCache['utf8']) {
				$fileCache['data'] = self::utf8_decode_array($fileCache['data']);
			}
			if ($fileCache['mode'] == 'object') {
				return json_decode(json_encode($fileCache['data']));
			} else {
				return $fileCache['data'];
			}
		}
		return false;
	}


	/*
		Write the cache, if not found returns false
		$key: String value for your cache key
		$data: The data to store
	*/
	public static function set($key, $data) {
		if (is_object($data)) {
			$mode = 'object';
		} else {
			$mode = 'array';
		}
		$saveData['mode'] = $mode;
		$saveData['utf8'] = false;
		$saveData['data'] = $data;
		$saveData['date'] = date('Y-m-d H:i:s');
		$fileData = json_encode($saveData);
		if (json_last_error() == JSON_ERROR_UTF8) {
			$saveData['utf8'] = true;
			$saveData = self::utf8_encode_array($saveData);
			$fileData = json_encode($saveData);
		}
		if (json_last_error() == JSON_ERROR_NONE) {
			$fp = fopen(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json', 'w');
			fwrite($fp, $fileData);
			fclose($fp);
		}
		return false;
	}


	/*
		Delete a cache file
		$key: Deletes the cache file for this cache
	*/
	public static function delete($key) {
		if (file_exists(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json') && is_writable(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json')) {
			unlink(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json');
			return true;
		}
		return false;
	}

}