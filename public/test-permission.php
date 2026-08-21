<?php

echo "User: " . get_current_user() . "<br>";
echo "UID: " . getmyuid() . "<br>";

$file = "/var/www/quiz-app/storage/pail/php-test.pail";

echo "Writable directory: ";
var_dump(is_writable("/var/www/quiz-app/storage/pail"));

echo "<br>File writable/existing: ";
var_dump(is_writable($file));

echo "<br>Append test: ";

$result = file_put_contents($file, "PHP test\n", FILE_APPEND);

var_dump($result);
