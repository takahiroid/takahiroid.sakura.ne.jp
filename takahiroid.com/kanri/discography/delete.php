<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$discographyData = loadDiscographyData();
$deleteIndex = isset($_GET['id']) ? (int)$_GET['id'] : -1;

if ($deleteIndex >= 0 && isset($discographyData[$deleteIndex])) {
    // 画像があれば削除
    if (!empty($discographyData[$deleteIndex]['image'])) {
        deleteDiscographyImage(basename($discographyData[$deleteIndex]['image']));
    }
    
    // データから削除
    array_splice($discographyData, $deleteIndex, 1);
    saveDiscographyData($discographyData);
    
    header('Location: /kanri/discography/?msg=deleted');
    exit;
}

header('Location: /kanri/discography/');
exit;

