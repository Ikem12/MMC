<?php
echo "INI File: " . php_ini_loaded_file() . "<br>";
echo "PHP Path: " . PHP_BINARY . "<br>";
echo "<br><b>SQLite check:</b><br>";
echo "pdo_sqlite: " . (extension_loaded('pdo_sqlite') ? '✅ YES' : '❌ NO') . "<br>";
echo "sqlite3: " . (extension_loaded('sqlite3') ? '✅ YES' : '❌ NO') . "<br>";
phpinfo();