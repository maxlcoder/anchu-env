<?php

namespace Anchu\Env;

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class Env
{

    /**
     * encode/decode the env
     * @param $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return false|string
     */
    private static function run($string, string $operation = 'DECODE')
    {
        if (!is_string($string)) return '';
        return $operation == 'DECODE' ? self::decrypt($string) : self::encrypt($string);
    }

    // 加密
    private static function encrypt($data = ''): string
    {
        return Crypt::encrypt($data);
    }

    // 解密
    private static function decrypt($data = '')
    {
        try {
            return Crypt::decrypt($data);
        } catch (\Exception $e) {
            return '';
        }

    }

    /**
     * check the state of encode or not
     * @param $password
     * @return bool
     */
    private static function hasEncode($password)
    {
        return !empty(self::run($password, 'DECODE'));
    }

    /**
     * encode the .env file in /
     * only for password
     */
    private static function encodeEnv()
    {
        $configs = file(base_path('.env'));
        $result = "";
        foreach ($configs as $config) {
            if (strpos($config, 'PASSWORD') !== false) {
                $items = explode('=', $config);
                $password = $items[1] ?? '';
                if (!self::hasEncode($password)) {
                    $password = self::trim($password);
                    $secret = self::run($password, 'ENCODE');
                    $secret = $secret == '' ? $password : $secret;
                    $config = $items[0] . '=' . $secret . PHP_EOL;
                }
            }
            $result .= $config;
        }
        file_put_contents(base_path('.env'), $result);
    }


    /**
     * encode the .env.* in env/ dir
     */
    private static function encodeEnvs()
    {
        $envs = ['local', 'dev', 'pre', 'live', 'all'];
        foreach ($envs as $env) {
            $file = base_path() . '/env/.env.' . $env;
            if (!file_exists($file)) {
                continue;
            }
            $configs = file($file);
            $result = "";
            foreach ($configs as $config) {
                if (strpos($config, '=') !== false) {
                    $items = explode('=', $config);
                    $value = isset($items[1]) ? trim($items[1]) : '';
                    if (!self::hasEncode($value)) {
                        $value = self::trim($value);
                        $secret = self::run($value, 'ENCODE');
                        $secret = $secret == '' ? $value : $secret;
                        $config = $items[0] . '=' . $secret . PHP_EOL;
                        var_dump($env . '.' . $items[0] . '.' . $value . ' = ' . $secret);
                    }
                }
                $result .= $config;
            }
            file_put_contents($file, $result);
        }
    }

    /**
     * 去除密码的单引号和双引号
     */
    private static function trim($password = '')
    {
        $password = trim($password);
        $password = trim($password, '\'');
        return trim($password, '"');
    }

    /**
     * encode all .env files
     */
    public static function encode()
    {
        self::encodeEnv();
        self::encodeEnvs();
    }

    public static function decode()
    {
        self::decodeConfig();
        self::decodeSysEnv();
    }

    /**
     * 解密config配置，但是没有在configs/目录下的配置无法解密
     * @param string $path
     */
    public static function decodeConfig($path = '/')
    {
        if ($path == '/') {
            $configs = Config::all();
        } else {
            $configs = Config::get($path);
        }

        foreach ($configs as $name => $config) {
            $newPath = $path == '/' ? $name : $path . '.' . $name;

            if (is_array($config)) {
                self::decodeConfig($newPath);
            } else {
                try {
                    $password = self::run(Config::get($newPath), 'DECODE');
                    if (!empty($password)) {
                        Config::set($newPath, trim($password));
                    }
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                }
            }
        }
    }

    /**
     * 使用env()函数获取解密后的配置
     */
    private static function decodeSysEnv()
    {
        self::loadEnv();
        $configs = $_ENV;
        foreach ($configs as $name => $value) {
            try {
                $value = self::run($value, 'DECODE');
                if (!empty($value)) {
                    $_SERVER[$name] = $value;
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }
    }

    /**
     * load env to system
     */
    public static function loadEnv()
    {
        $envs = [env('APP_ENV', 'local'), 'all'];
        foreach ($envs as $env) {
            $file = base_path() . '/env/' . '.env.' . $env;
            if (file_exists($file)) {
                $dotenv = Dotenv::createImmutable(base_path() . '/env/', '.env.' . $env);
                $dotenv->load();
            }
        }
    }

    public static function printDecodeEnvs()
    {
        $configs = $_ENV;
        foreach ($configs as $name => $value) {
            try {
                $value = self::run($value, 'DECODE');
                if (!empty($value)) {
                    $configs[$name] = trim($value);
                }
            } catch (\Exception $e) {

            }
        }
        print_r($configs);
    }

    /**
     * 生成app_key，放置在.env文件中，需要在开发、生成中保持一致
     * @param string $cipher
     * @return string
     */
    public static function generateKey(string $cipher = 'aes-256-cbc'): string
    {
        return 'base64:' . base64_encode(Crypt::generateKey($cipher));
    }
}
