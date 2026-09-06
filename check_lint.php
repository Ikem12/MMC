<?php
$files = glob(__DIR__ . '/*.php');
foreach ($files as $f) {
    $code = file_get_contents($f);
    try {
        @token_get_all($code, TOKEN_PARSE);
    } catch (ParseError $e) {
        echo "PARSE ERROR in " . basename($f) . ": " . $e->getMessage() . " on line " . $e->getLine() . "\n";
    }
}
echo "Lint check completed.\n";
