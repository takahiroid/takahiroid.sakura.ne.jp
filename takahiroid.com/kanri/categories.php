<?php
require_once __DIR__ . '/config.php';
requireLogin();

$categories = loadCategories();
$message = '';
$error = '';

// カテゴリ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newCategory = trim($_POST['new_category'] ?? '');
    if (empty($newCategory)) {
        $error = 'カテゴリ名を入力してください';
    } elseif (in_array($newCategory, $categories)) {
        $error = 'このカテゴリは既に存在します';
    } else {
        $categories[] = $newCategory;
        saveCategories($categories);
        $message = 'カテゴリを追加しました';
        $categories = loadCategories(); // 再読み込み
    }
}

// カテゴリ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $categoryToDelete = $_POST['category'] ?? '';
    if (!empty($categoryToDelete)) {
        $key = array_search($categoryToDelete, $categories);
        if ($key !== false) {
            // このカテゴリを使用している記事があるかチェック
            $newsData = loadNewsData();
            $isUsed = false;
            foreach ($newsData as $news) {
                if (($news['category'] ?? '') === $categoryToDelete) {
                    $isUsed = true;
                    break;
                }
            }
            
            if ($isUsed) {
                $error = 'このカテゴリは記事で使用されているため削除できません';
            } else {
                unset($categories[$key]);
                $categories = array_values($categories); // インデックスを再構築
                saveCategories($categories);
                $message = 'カテゴリを削除しました';
                $categories = loadCategories(); // 再読み込み
            }
        }
    }
}

// カテゴリ編集処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $oldCategory = $_POST['old_category'] ?? '';
    $newCategory = trim($_POST['new_category'] ?? '');
    
    if (empty($newCategory)) {
        $error = 'カテゴリ名を入力してください';
    } elseif ($oldCategory === $newCategory) {
        $error = '変更がありません';
    } elseif (in_array($newCategory, $categories)) {
        $error = 'このカテゴリは既に存在します';
    } else {
        $key = array_search($oldCategory, $categories);
        if ($key !== false) {
            $categories[$key] = $newCategory;
            saveCategories($categories);
            
            // 記事データのカテゴリも更新
            $newsData = loadNewsData();
            foreach ($newsData as $index => $news) {
                if (($news['category'] ?? '') === $oldCategory) {
                    $newsData[$index]['category'] = $newCategory;
                }
            }
            saveNewsData($newsData);
            
            $message = 'カテゴリを更新しました';
            $categories = loadCategories(); // 再読み込み
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カテゴリ管理 - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>カテゴリ管理</h1>
            <div class="header-actions">
                <a href="/kanri/news/" class="btn btn-secondary">ニュース記事に戻る</a>
            </div>
        </div>
        
        <div class="container">
            <?php if ($message): ?>
                <div class="success"><?php echo h($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo h($error); ?></div>
            <?php endif; ?>
            
            <!-- カテゴリ追加フォーム -->
            <div class="form-container" style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 20px;">カテゴリを追加</h2>
                <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="new_category">カテゴリ名</label>
                        <input type="text" id="new_category" name="new_category" required placeholder="例: イベント">
                    </div>
                    <button type="submit" class="btn btn-primary">追加</button>
                </form>
            </div>
            
            <!-- カテゴリ一覧 -->
            <div class="form-container">
                <h2 style="margin-bottom: 20px;">カテゴリ一覧</h2>
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <p>カテゴリがありません</p>
                    </div>
                <?php else: ?>
                    <div class="category-list">
                        <?php foreach ($categories as $index => $category): ?>
                            <div class="category-item" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px; background: #fff;">
                                <div style="flex: 1;">
                                    <form method="POST" action="" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="old_category" value="<?php echo h($category); ?>">
                                        <input type="text" name="new_category" value="<?php echo h($category); ?>" required style="flex: 1; padding: 8px; border: 2px solid #e0e0e0; border-radius: 5px;">
                                        <button type="submit" class="btn btn-edit btn-small">更新</button>
                                    </form>
                                </div>
                                <form method="POST" action="" style="margin-left: 10px;" onsubmit="return confirm('本当に削除しますか？\nこのカテゴリを使用している記事がある場合は削除できません。');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category" value="<?php echo h($category); ?>">
                                    <button type="submit" class="btn btn-delete btn-small">削除</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

