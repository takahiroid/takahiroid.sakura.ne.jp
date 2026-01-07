<?php
// セッション開始
session_start();

// 設定
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '#0821Rock'); // 本番環境では変更してください
define('DATA_DIR', __DIR__ . '/data/');
define('NEWS_DATA_FILE', DATA_DIR . 'news.json');
define('CATEGORIES_DATA_FILE', DATA_DIR . 'categories.json');
define('SUBCATEGORIES_DATA_FILE', DATA_DIR . 'subcategories.json');
define('DISCOGRAPHY_DATA_FILE', DATA_DIR . 'discography.json');
define('DISCOGRAPHY_CATEGORIES_DATA_FILE', DATA_DIR . 'discography_categories.json');
define('UPLOAD_DIR', dirname(__DIR__) . '/news/img/');
define('DISCOGRAPHY_UPLOAD_DIR', dirname(__DIR__) . '/discography/img/');
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
        if (!$data) {
            return [];
        }
        // データ構造が["news": {...}, "index": 0]形式の場合は変換
        if (isset($data[0]['news'])) {
            $normalizedData = [];
            foreach ($data as $item) {
                if (isset($item['news'])) {
                    $normalizedData[] = $item['news'];
                } else {
                    $normalizedData[] = $item;
                }
            }
            return $normalizedData;
        }
        return $data;
    }
    return [];
}

// ニュースデータを保存する
function saveNewsData($data) {
    // 手動で並び替えが設定されていない場合のみ、日付順にソート（新しい順）
    // 並び替え機能で順序が保持されるため、ここではソートしない
    file_put_contents(NEWS_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// XSS対策
function h($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
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

// カテゴリデータを読み込む（互換性のため、文字列配列とオブジェクト配列の両方に対応）
function loadCategories() {
    if (file_exists(CATEGORIES_DATA_FILE)) {
        $json = file_get_contents(CATEGORIES_DATA_FILE);
        $data = json_decode($json, true);
        if (!$data) {
            return [];
        }
        
        // 既存の文字列配列の場合は、オブジェクト配列に変換
        if (isset($data[0]) && is_string($data[0])) {
            $defaultColors = [
                'LIVE' => '#e74c3c',
                'RELEASE' => '#3498db',
                'You Tube' => '#e74c3c',
                'TV・GUITAR' => '#9b59b6',
                'ラジオ出演' => '#f39c12',
                'SHOP・GOODS' => '#1abc9c',
                'その他' => '#95a5a6'
            ];
            $converted = [];
            foreach ($data as $category) {
                $converted[] = [
                    'name' => $category,
                    'color' => $defaultColors[$category] ?? '#70b539'
                ];
            }
            // 変換したデータを保存
            saveCategories($converted);
            return $converted;
        }
        
        return $data;
    }
    // デフォルトカテゴリを返す
    return [
        ['name' => 'LIVE', 'color' => '#e74c3c'],
        ['name' => 'RELEASE', 'color' => '#3498db'],
        ['name' => 'You Tube', 'color' => '#e74c3c'],
        ['name' => 'TV・GUITAR', 'color' => '#9b59b6'],
        ['name' => 'ラジオ出演', 'color' => '#f39c12'],
        ['name' => 'SHOP・GOODS', 'color' => '#1abc9c'],
        ['name' => 'その他', 'color' => '#95a5a6']
    ];
}

// カテゴリデータを保存する
function saveCategories($categories) {
    file_put_contents(CATEGORIES_DATA_FILE, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// カテゴリ名から色を取得する
function getCategoryColor($categoryName) {
    $categories = loadCategories();
    foreach ($categories as $category) {
        if (is_array($category) && isset($category['name']) && $category['name'] === $categoryName) {
            return $category['color'] ?? '#70b539';
        }
    }
    return '#70b539'; // デフォルト色
}

// サブカテゴリデータを読み込む
function loadSubcategories() {
    if (file_exists(SUBCATEGORIES_DATA_FILE)) {
        $json = file_get_contents(SUBCATEGORIES_DATA_FILE);
        $data = json_decode($json, true);
        return $data ? $data : [];
    }
    // デフォルトサブカテゴリを返す
    return [
        'ザ・タートルズ',
        'TAKAHIROID',
        'SPARKY',
        '世良公則'
    ];
}

// サブカテゴリデータを保存する
function saveSubcategories($subcategories) {
    file_put_contents(SUBCATEGORIES_DATA_FILE, json_encode($subcategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ディスコグラフィーデータを読み込む
function loadDiscographyData() {
    if (file_exists(DISCOGRAPHY_DATA_FILE)) {
        $json = file_get_contents(DISCOGRAPHY_DATA_FILE);
        $data = json_decode($json, true);
        return $data ? $data : [];
    }
    return [];
}

// ディスコグラフィーデータを保存する
function saveDiscographyData($data) {
    file_put_contents(DISCOGRAPHY_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ディスコグラフィー画像アップロード処理
function uploadDiscographyImage($file) {
    // アップロードディレクトリが存在しない場合は作成
    if (!file_exists(DISCOGRAPHY_UPLOAD_DIR)) {
        mkdir(DISCOGRAPHY_UPLOAD_DIR, 0755, true);
    }
    
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
    $filename = 'disc_' . date('YmdHis') . '_' . uniqid() . '.' . $extension;
    $filepath = DISCOGRAPHY_UPLOAD_DIR . $filename;
    
    // ファイルを移動
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => '/discography/img/' . $filename];
    } else {
        return ['success' => false, 'error' => 'ファイルのアップロードに失敗しました'];
    }
}

// ディスコグラフィー画像削除
function deleteDiscographyImage($filename) {
    if (empty($filename)) {
        return false;
    }
    $filepath = DISCOGRAPHY_UPLOAD_DIR . basename($filename);
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

// 次のディスコグラフィーIDを取得
function getNextDiscographyId() {
    $data = loadDiscographyData();
    $maxId = 0;
    foreach ($data as $item) {
        if (isset($item['id']) && $item['id'] > $maxId) {
            $maxId = $item['id'];
        }
    }
    return $maxId + 1;
}

// ディスコグラフィーカテゴリデータを読み込む
function loadDiscographyCategories() {
    if (file_exists(DISCOGRAPHY_CATEGORIES_DATA_FILE)) {
        $json = file_get_contents(DISCOGRAPHY_CATEGORIES_DATA_FILE);
        $data = json_decode($json, true);
        return $data ? $data : [];
    }
    // デフォルトカテゴリを返す
    return [
        ['name' => '松本タカヒロ', 'color' => '#667eea'],
        ['name' => 'ザ・タートルズ', 'color' => '#48bb78'],
        ['name' => 'SPARKY', 'color' => '#f56565']
    ];
}

// ディスコグラフィーカテゴリデータを保存する
function saveDiscographyCategories($categories) {
    file_put_contents(DISCOGRAPHY_CATEGORIES_DATA_FILE, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ディスコグラフィーカテゴリ名から色を取得する
function getDiscographyCategoryColor($categoryName) {
    $categories = loadDiscographyCategories();
    foreach ($categories as $category) {
        if (is_array($category) && isset($category['name']) && $category['name'] === $categoryName) {
            return $category['color'] ?? '#667eea';
        }
    }
    return '#667eea'; // デフォルト色
}
?>
