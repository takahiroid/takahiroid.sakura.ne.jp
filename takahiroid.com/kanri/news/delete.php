<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: /kanri/news/');
    exit;
}

$id = (int)$_GET['id'];
$newsData = loadNewsData();

if (isset($newsData[$id])) {
    // 画像も削除
    if (!empty($newsData[$id]['image'])) {
        deleteImage(basename($newsData[$id]['image']));
    }
    unset($newsData[$id]);
    $newsData = array_values($newsData); // インデックスを再構築
    saveNewsData($newsData);
    header('Location: /kanri/news/?msg=deleted');
} else {
    header('Location: /kanri/news/');
}
exit;
?>

