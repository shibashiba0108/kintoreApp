<?php

namespace kintore\contents;

require_once '/Applications/MAMP/htdocs/DT/vendor/autoload.php';

class Bootstrap
{
    const DB_HOST = 'localhost';

    const DB_NAME = 'user_db';

    const DB_USER = 'user_user';

    const DB_PASS = 'user_pass';

    const DB_TYPE = 'mysql';

    const APP_DIR = '/Applications/MAMP/htdocs/DT/kintore/';

    const TEMPLATE_DIR = self::APP_DIR . 'templates/';

    const CACHE_DIR = false;

    const APP_URL = 'http://localhost:8888/DT/';

    const ENTRY_URL = self::APP_URL . 'kintore/contents/';

    public function __construct()
    {
        // セッションの有効期限を設定
        ini_set('session.gc_maxlifetime', 1440);  // 24分
        session_set_cookie_params(1440);  // クッキーの有効期限も設定
        session_start();  // セッションを開始
    }

    public static function loadClass($class)
    {
        // 名前空間のプレフィックス 'kintore\' を削除
        $classPath = str_replace('kintore\\', '', $class);
        // パスを構築
        $path = self::APP_DIR . str_replace('\\', '/', $classPath) . '.class.php';

        if (file_exists($path)) {
            require_once $path;
        } else {
            error_log("Class file not found: " . $path);
            throw new \Exception("Class file not found: " . $path);
        }
    }
}

spl_autoload_register([
    'kintore\contents\Bootstrap',
    'loadClass'
]);
