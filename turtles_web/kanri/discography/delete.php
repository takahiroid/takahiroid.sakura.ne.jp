<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$id = isset($_GET['id']) ? (string)$_GET['id'] : '';
$discographyData = loadDiscographyData();
$index = $id === '' ? null : findDiscographyIndex($discographyData, $id);

if ($index === null) {
    header('Location: /kanri/discography/');
    exit;
}

deleteDiscographyImage($discographyData[$index]['image'] ?? '');
unset($discographyData[$index]);
saveDiscographyData($discographyData);
header('Location: /kanri/discography/?msg=deleted');
exit;
