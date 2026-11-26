<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$id = isset($_GET['id']) ? (string)$_GET['id'] : '';
$newsData = loadNewsData();
$index = $id === '' ? null : findNewsIndex($newsData, $id);

if ($index === null) {
    header('Location: /kanri/news/');
    exit;
}

deleteUploadedImage($newsData[$index]['image'] ?? '');
deleteUploadedImage($newsData[$index]['thumbnail'] ?? '');
unset($newsData[$index]);
saveNewsData($newsData);
header('Location: /kanri/news/?msg=deleted');
exit;
