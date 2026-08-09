<?php
echo 'INI File: ' . php_ini_loaded_file() . '<br>';
echo 'PHP Path: ' . PHP_BINARY . '<br><br>';
echo '<strong>SQLite check:</strong><br>';
echo 'pdo_sqlite: ' . (extension_loaded('pdo_sqlite') ? '&#9989; YES' : '&#10060; NO') . '<br>';
echo 'sqlite3: ' . (extension_loaded('sqlite3') ? '&#9989; YES' : '&#10060; NO') . '<br><br>';
phpinfo();