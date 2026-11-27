<?php
session_start();

// タートルズ専用。TAKAHIROID の認証情報は使わない。本番前に変更すること。
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'YA4jemBOaJ');
define('DATA_DIR', __DIR__ . '/data/');
define('NEWS_DATA_FILE', DATA_DIR . 'news.json');
define('CATEGORIES_DATA_FILE', DATA_DIR . 'categories.json');
define('DISCOGRAPHY_DATA_FILE', DATA_DIR . 'discography.json');
define('UPLOAD_DIR', dirname(__DIR__) . '/news/img/');
define('UPLOAD_URL_PREFIX', '/news/img/');
define('DISCOGRAPHY_UPLOAD_DIR', dirname(__DIR__) . '/discography/img/');
define('DISCOGRAPHY_UPLOAD_URL', '/discography/img/');
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20MB

if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
if (!file_exists(DISCOGRAPHY_UPLOAD_DIR)) {
    mkdir(DISCOGRAPHY_UPLOAD_DIR, 0755, true);
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /kanri/index.php');
        exit;
    }
}

function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

function h($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function loadNewsData() {
    if (!file_exists(NEWS_DATA_FILE)) {
        return [];
    }
    $json = file_get_contents(NEWS_DATA_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveNewsData($data) {
    $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents(NEWS_DATA_FILE, $json . "\n", LOCK_EX) !== false;
}

function findNewsIndex($newsData, $id) {
    foreach ($newsData as $index => $news) {
        if ((string)($news['id'] ?? '') === (string)$id) {
            return $index;
        }
    }
    return null;
}

function defaultCategories() {
    return [
        ['name' => 'LIVE', 'color' => '#D95D67'],
        ['name' => 'ニュース', 'color' => '#4ECDC4']
    ];
}

function loadCategories() {
    if (!file_exists(CATEGORIES_DATA_FILE)) {
        return defaultCategories();
    }
    $data = json_decode(file_get_contents(CATEGORIES_DATA_FILE), true);
    if (!is_array($data) || $data === []) {
        return defaultCategories();
    }
    return $data;
}

function saveCategories($categories) {
    $json = json_encode(array_values($categories), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents(CATEGORIES_DATA_FILE, $json . "\n", LOCK_EX) !== false;
}

function getCategoryColor($categoryName) {
    foreach (loadCategories() as $category) {
        $name = is_array($category) ? ($category['name'] ?? '') : $category;
        if ($name === $categoryName) {
            return is_array($category) ? ($category['color'] ?? '#D95D67') : '#D95D67';
        }
    }
    return '#D95D67';
}

function uploadImage($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'ファイルがアップロードされていません'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'ファイルサイズが大きすぎます（最大20MB）'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mimeType])) {
        return ['success' => false, 'error' => '許可されていないファイル形式です（JPEG, PNG, GIF, WebPのみ）'];
    }

    $filename = 'news_' . date('YmdHis') . '_' . uniqid() . '.' . $allowed[$mimeType];
    $filepath = UPLOAD_DIR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'ファイルのアップロードに失敗しました'];
    }
    return ['success' => true, 'filename' => $filename, 'path' => UPLOAD_URL_PREFIX . $filename];
}

function deleteUploadedImage($path) {
    if (!is_string($path) || $path === '' || strpos($path, UPLOAD_URL_PREFIX) !== 0) {
        return false;
    }
    $filepath = UPLOAD_DIR . basename($path);
    if (is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function uploadErrorMessage($errorCode, $label = '画像') {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return $label . 'のファイルサイズが大きすぎます';
        case UPLOAD_ERR_PARTIAL:
            return $label . 'のアップロードが完了しませんでした';
        default:
            return $label . 'のアップロードに失敗しました';
    }
}

function storeUploadedImage($file, $existing, $deleteExisting, $label) {
    $path = $existing;
    $error = '';
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($file);
        if ($result['success']) {
            if ($existing !== '' && !$deleteExisting) {
                deleteUploadedImage($existing);
            }
            $path = $result['path'];
        } else {
            $error = $result['error'] ?? ($label . 'のアップロードに失敗しました');
        }
    } elseif (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = uploadErrorMessage($file['error'], $label);
    } elseif ($deleteExisting && $existing !== '') {
        deleteUploadedImage($existing);
        $path = '';
    }
    return [$path, $error];
}

function extractYouTubeId($url) {
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}

function loadDiscographyData() {
    if (!file_exists(DISCOGRAPHY_DATA_FILE)) {
        return [];
    }
    $data = json_decode(file_get_contents(DISCOGRAPHY_DATA_FILE), true);
    return is_array($data) ? $data : [];
}

function saveDiscographyData($data) {
    $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }
    return file_put_contents(DISCOGRAPHY_DATA_FILE, $json . "\n", LOCK_EX) !== false;
}

function findDiscographyIndex($data, $id) {
    foreach ($data as $index => $item) {
        if ((string)($item['id'] ?? '') === (string)$id) {
            return $index;
        }
    }
    return null;
}

function nextDiscographyId($data) {
    $maxId = 0;
    foreach ($data as $item) {
        $maxId = max($maxId, (int)($item['id'] ?? 0));
    }
    return $maxId + 1;
}

function discographyReleaseTypes() {
    return ['CD', 'デジタル配信', 'Download', 'DVD'];
}

function uploadDiscographyImage($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'ファイルがアップロードされていません'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'ファイルサイズが大きすぎます（最大20MB）'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mimeType])) {
        return ['success' => false, 'error' => '許可されていないファイル形式です（JPEG, PNG, GIF, WebPのみ）'];
    }

    $filename = 'disc_' . date('YmdHis') . '_' . uniqid() . '.' . $allowed[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], DISCOGRAPHY_UPLOAD_DIR . $filename)) {
        return ['success' => false, 'error' => 'ファイルのアップロードに失敗しました'];
    }
    return ['success' => true, 'path' => DISCOGRAPHY_UPLOAD_URL . $filename];
}

function deleteDiscographyImage($path) {
    if (!is_string($path) || $path === '' || strpos($path, DISCOGRAPHY_UPLOAD_URL) !== 0) {
        return false;
    }
    $filepath = DISCOGRAPHY_UPLOAD_DIR . basename($path);
    if (is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

function storeDiscographyImage($file, $existing, $deleteExisting) {
    $path = $existing;
    $error = '';
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $result = uploadDiscographyImage($file);
        if ($result['success']) {
            if ($existing !== '' && $existing !== $result['path']) {
                deleteDiscographyImage($existing);
            }
            $path = $result['path'];
        } else {
            $error = $result['error'] ?? 'ジャケット画像のアップロードに失敗しました';
        }
    } elseif (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = uploadErrorMessage($file['error'], 'ジャケット画像');
    } elseif ($deleteExisting && $existing !== '') {
        deleteDiscographyImage($existing);
        $path = '';
    }
    return [$path, $error];
}
