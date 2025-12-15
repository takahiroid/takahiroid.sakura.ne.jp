<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: /kanri/dashboard.php');
    exit;
}

$id = (int)$_GET['id'];
$newsData = loadNewsData();

if (isset($newsData[$id])) {
    unset($newsData[$id]);
    $newsData = array_values($newsData); // インデックスを再構築
    saveNewsData($newsData);
    header('Location: /kanri/dashboard.php?msg=deleted');
} else {
    header('Location: /kanri/dashboard.php');
}
exit;
?>

