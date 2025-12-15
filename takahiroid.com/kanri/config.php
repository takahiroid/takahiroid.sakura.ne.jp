<?php
// セッション開始
session_start();

// 設定
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // 本番環境では変更してください
define('DATA_DIR', __DIR__ . '/data/');
define('NEWS_DATA_FILE', DATA_DIR . 'news.json');
define('UPLOAD_DIR', dirname(__DIR__) . '/news/img/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// データディレクトリが存在しない場合は作成
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// アップロードディレクトリが存在しない場合は作成
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ログイン状態をチェック
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// ログインが必要なページで使用
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /kanri/index.php');
        exit;
    }
}

// ログアウト
function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

// ニュースデータを読み込む
function loadNewsData() {
    if (file_exists(NEWS_DATA_FILE)) {
        $json = file_get_contents(NEWS_DATA_FILE);
        $data = json_decode($json, true);
        return $data ? $data : [];
    }
    return [];
}

// ニュースデータを保存する
function saveNewsData($data) {
    // 日付順にソート（新しい順）
    usort($data, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    file_put_contents(NEWS_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// XSS対策
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 画像アップロード処理
function uploadImage($file) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'ファイルがアップロードされていません'];
    }
    
    // ファイルサイズチェック
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'ファイルサイズが大きすぎます（最大5MB）'];
    }
    
    // ファイルタイプチェック
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => '許可されていないファイル形式です（JPEG, PNG, GIF, WebPのみ）'];
    }
    
    // ファイル名を生成
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'news_' . date('YmdHis') . '_' . uniqid() . '.' . $extension;
    $filepath = UPLOAD_DIR . $filename;
    
    // ファイルを移動
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => '/news/img/' . $filename];
    } else {
        return ['success' => false, 'error' => 'ファイルのアップロードに失敗しました'];
    }
}

// 画像削除
function deleteImage($filename) {
    if (empty($filename)) {
        return false;
    }
    $filepath = UPLOAD_DIR . basename($filename);
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// YouTube URLから埋め込み用のIDを抽出
function extractYouTubeId($url) {
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        return $matches[1];
    }
    return null;
}
?>
