<?php
/**
 * フロントエンド用のディスコグラフィー関数
 * セッションを開始しないため、フロントエンドページで安全に使用できます
 */

define('DATA_DIR', dirname(__DIR__) . '/kanri/data/');
define('DISCOGRAPHY_DATA_FILE', DATA_DIR . 'discography.json');
define('DISCOGRAPHY_CATEGORIES_DATA_FILE', DATA_DIR . 'discography_categories.json');

// ディスコグラフィーデータを読み込む
function loadDiscographyData() {
    if (file_exists(DISCOGRAPHY_DATA_FILE)) {
        $json = file_get_contents(DISCOGRAPHY_DATA_FILE);
        $data = json_decode($json, true);
        return $data ? $data : [];
    }
    return [];
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

