<?php
/*
	Written by Peter Palma 2019-2023
	I made this for hosts where you don't have access to memcache, memcached or redis
	MIT License
	Static class version
*/

class FileCache {
    const CACHE_VERSION = '1-'; // Change value to reset cache, must be filesystem friendly.
    const CACHE_FOLDER = '/var/www/cache/'; // Set the file cache folder

    /*
        UTF-8 Encode an array
    */
    public static function get(string $key, int $ttl = 120): mixed {
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
        } else {
            self::delete($key);
        }
        return false;
    }

    /*
        UTF-8 Decode an array
    */

    private static function utf8_decode_array(string|array $data): string|array|false {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::utf8_decode_array($value);
            }
        } elseif (is_string($data)) {
            $encoding = mb_detect_encoding($data, 'UTF-8, ISO-8859-1');
            return mb_convert_encoding($data, 'ISO-8859-1', $encoding);
        }
        return $data;
    }

    /*
        Get the cache, if not found returns false
        $key: String value for your cache key
        $ttl: How long the cache is valid
    */

    public static function set(string $key, mixed $data): bool {
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
            if (file_exists(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json')) {
                return true;
            }
        }
        return false;
    }


    /*
        Write the cache, if not found returns false
        $key: String value for your cache key
        $data: The data to store
    */

    private static function utf8_encode_array(string|array $data): string|array|false {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::utf8_encode_array($value);
            }
        } elseif (is_string($data)) {
            $encoding = mb_detect_encoding($data, 'UTF-8, ISO-8859-1');
            return mb_convert_encoding($data, 'UTF-8', $encoding);
        }
        return $data;
    }

    /*
        Delete a cache file
        $key: Deletes the cache file for this cache
    */

    public static function delete(string $key): bool {
        if (file_exists(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json') && is_writable(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json')) {
            unlink(self::CACHE_FOLDER . self::CACHE_VERSION . $key . '.json');
            return true;
        }
        return false;
    }

}
