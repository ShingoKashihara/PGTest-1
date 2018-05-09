<?php
require_once 'Lib/Crawl.php';

$url = 'https://no1s.biz/';
$file_path = './result.txt';

$crawl = new Crawl($url, $file_path);

echo "処理開始\n";

$result = $crawl->execute();

echo "処理終了\n";
exit;
