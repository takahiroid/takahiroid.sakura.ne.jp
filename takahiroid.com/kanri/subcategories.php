<?php
require_once __DIR__ . '/config.php';
requireLogin();

$subcategories = loadSubcategories();
$message = '';
$error = '';

// サブカテゴリ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newSubcategory = trim($_POST['new_subcategory'] ?? '');
    if (empty($newSubcategory)) {
        $error = 'サブカテゴリ名を入力してください';
    } elseif (in_array($newSubcategory, $subcategories)) {
        $error = 'このサブカテゴリは既に存在します';
    } else {
        $subcategories[] = $newSubcategory;
        saveSubcategories($subcategories);
        $message = 'サブカテゴリを追加しました';
        $subcategories = loadSubcategories(); // 再読み込み
    }
}

// サブカテゴリ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $subcategoryToDelete = $_POST['subcategory'] ?? '';
    if (!empty($subcategoryToDelete)) {
        $key = array_search($subcategoryToDelete, $subcategories);
        if ($key !== false) {
            // このサブカテゴリを使用している記事があるかチェック
            $newsData = loadNewsData();
            $isUsed = false;
            $usedInArticles = [];
            foreach ($newsData as $index => $news) {
                if (($news['subcategory'] ?? '') === $subcategoryToDelete) {
                    $isUsed = true;
                    $usedInArticles[] = $news['title'] ?? 'タイトルなし';
                }
            }
            
            if ($isUsed) {
                $error = 'このサブカテゴリは記事で使用されているため削除できません。使用中の記事: ' . implode(', ', array_slice($usedInArticles, 0, 3)) . (count($usedInArticles) > 3 ? '...' : '');
            } else {
                unset($subcategories[$key]);
                $subcategories = array_values($subcategories); // インデックスを再構築
                saveSubcategories($subcategories);
                $message = 'サブカテゴリを削除しました';
                $subcategories = loadSubcategories(); // 再読み込み
            }
        }
    }
}

// サブカテゴリ編集処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $oldSubcategory = $_POST['old_subcategory'] ?? '';
    $newSubcategory = trim($_POST['new_subcategory'] ?? '');
    
    if (empty($newSubcategory)) {
        $error = 'サブカテゴリ名を入力してください';
    } elseif ($oldSubcategory === $newSubcategory) {
        $error = '変更がありません';
    } elseif (in_array($newSubcategory, $subcategories)) {
        $error = 'このサブカテゴリは既に存在します';
    } else {
        $key = array_search($oldSubcategory, $subcategories);
        if ($key !== false) {
            $subcategories[$key] = $newSubcategory;
            saveSubcategories($subcategories);
            
            // 記事データのサブカテゴリも更新
            $newsData = loadNewsData();
            $updatedCount = 0;
            foreach ($newsData as $index => $news) {
                if (($news['subcategory'] ?? '') === $oldSubcategory) {
                    $newsData[$index]['subcategory'] = $newSubcategory;
                    $updatedCount++;
                }
            }
            saveNewsData($newsData);
            
            $message = 'サブカテゴリを更新しました' . ($updatedCount > 0 ? '（' . $updatedCount . '件の記事も更新されました）' : '');
            $subcategories = loadSubcategories(); // 再読み込み
        }
    }
}

// 使用状況を取得
$usageStats = [];
$newsData = loadNewsData();
foreach ($subcategories as $subcategory) {
    $count = 0;
    foreach ($newsData as $news) {
        if (($news['subcategory'] ?? '') === $subcategory) {
            $count++;
        }
    }
    $usageStats[$subcategory] = $count;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>サブカテゴリ管理 - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>サブカテゴリ管理</h1>
            <div class="header-actions">
                <a href="/kanri/news/" class="btn btn-secondary">ニュース記事に戻る</a>
                <a href="/kanri/categories.php" class="btn btn-secondary">カテゴリ管理</a>
            </div>
        </div>
        
        <div class="container">
            <?php if ($message): ?>
                <div class="success"><?php echo h($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="error"><?php echo h($error); ?></div>
            <?php endif; ?>
            
            <!-- サブカテゴリ追加フォーム -->
            <div class="form-container" style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 20px;">サブカテゴリを追加</h2>
                <form method="POST" action="" style="display: flex; gap: 10px; align-items: flex-end;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <label for="new_subcategory">サブカテゴリ名</label>
                        <input type="text" id="new_subcategory" name="new_subcategory" required placeholder="例: ザ・タートルズ" autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary">追加</button>
                </form>
            </div>
            
            <!-- サブカテゴリ一覧 -->
            <div class="form-container">
                <h2 style="margin-bottom: 20px;">サブカテゴリ一覧</h2>
                <?php if (empty($subcategories)): ?>
                    <div class="empty-state">
                        <p>サブカテゴリがありません</p>
                    </div>
                <?php else: ?>
                    <div class="category-list">
                        <?php foreach ($subcategories as $index => $subcategory): ?>
                            <div class="category-item" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px; background: #fff;">
                                <div style="flex: 1; display: flex; align-items: center; gap: 15px;">
                                    <form method="POST" action="" style="display: flex; gap: 10px; align-items: center; flex: 1;">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="old_subcategory" value="<?php echo h($subcategory); ?>">
                                        <input type="text" name="new_subcategory" value="<?php echo h($subcategory); ?>" required style="flex: 1; padding: 8px; border: 2px solid #e0e0e0; border-radius: 5px;">
                                        <button type="submit" class="btn btn-edit btn-small">更新</button>
                                    </form>
                                    <div style="min-width: 80px; text-align: right; color: #666; font-size: 14px;">
                                        <?php if (isset($usageStats[$subcategory]) && $usageStats[$subcategory] > 0): ?>
                                            <span style="color: #667eea; font-weight: bold;"><?php echo $usageStats[$subcategory]; ?>件</span>
                                        <?php else: ?>
                                            <span style="color: #999;">未使用</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form method="POST" action="" style="margin-left: 10px;" onsubmit="return confirm('本当に削除しますか？\nこのサブカテゴリを使用している記事がある場合は削除できません。');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subcategory" value="<?php echo h($subcategory); ?>">
                                    <button type="submit" class="btn btn-delete btn-small" <?php echo (isset($usageStats[$subcategory]) && $usageStats[$subcategory] > 0) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>削除</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 5px; font-size: 14px; color: #666;">
                        <strong>合計:</strong> <?php echo count($subcategories); ?>件のサブカテゴリ
                        <?php 
                        $totalUsed = array_sum($usageStats);
                        if ($totalUsed > 0): 
                        ?>
                            | <strong>使用中:</strong> <?php echo $totalUsed; ?>件の記事
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

