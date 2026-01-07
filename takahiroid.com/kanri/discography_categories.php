<?php
require_once __DIR__ . '/config.php';
requireLogin();

$categories = loadDiscographyCategories();
$message = '';
$error = '';

// ヘルパー関数：カテゴリ名を取得
function getCategoryName($category) {
    return is_array($category) ? ($category['name'] ?? '') : $category;
}

// ヘルパー関数：カテゴリ名で検索
function findCategoryIndex($categories, $categoryName) {
    foreach ($categories as $index => $category) {
        if (getCategoryName($category) === $categoryName) {
            return $index;
        }
    }
    return false;
}

// カテゴリ追加処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newCategory = trim($_POST['new_category'] ?? '');
    $newColor = trim($_POST['new_color'] ?? '#667eea');
    
    if (empty($newCategory)) {
        $error = 'カテゴリ名を入力してください';
    } else {
        // 既存のカテゴリ名と重複チェック
        $exists = false;
        foreach ($categories as $category) {
            if (getCategoryName($category) === $newCategory) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            $error = 'このカテゴリは既に存在します';
        } else {
            $categories[] = [
                'name' => $newCategory,
                'color' => $newColor
            ];
            saveDiscographyCategories($categories);
            $message = 'カテゴリを追加しました';
            $categories = loadDiscographyCategories(); // 再読み込み
        }
    }
}

// カテゴリ削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $categoryToDelete = $_POST['category'] ?? '';
    if (!empty($categoryToDelete)) {
        $key = findCategoryIndex($categories, $categoryToDelete);
        if ($key !== false) {
            // このカテゴリを使用しているディスコグラフィーがあるかチェック
            $discographyData = loadDiscographyData();
            $isUsed = false;
            foreach ($discographyData as $disc) {
                if (($disc['category'] ?? '') === $categoryToDelete) {
                    $isUsed = true;
                    break;
                }
            }
            
            if ($isUsed) {
                $error = 'このカテゴリはディスコグラフィーで使用されているため削除できません';
            } else {
                unset($categories[$key]);
                $categories = array_values($categories); // インデックスを再構築
                saveDiscographyCategories($categories);
                $message = 'カテゴリを削除しました';
                $categories = loadDiscographyCategories(); // 再読み込み
            }
        }
    }
}

// カテゴリ編集処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $oldCategory = $_POST['old_category'] ?? '';
    $newCategory = trim($_POST['new_category'] ?? '');
    $newColor = trim($_POST['new_color'] ?? '#667eea');
    
    if (empty($newCategory)) {
        $error = 'カテゴリ名を入力してください';
    } else {
        $key = findCategoryIndex($categories, $oldCategory);
        if ($key !== false) {
            // 名前が変更された場合、重複チェック
            if ($oldCategory !== $newCategory) {
                $exists = false;
                foreach ($categories as $index => $category) {
                    if ($index !== $key && getCategoryName($category) === $newCategory) {
                        $exists = true;
                        break;
                    }
                }
                if ($exists) {
                    $error = 'このカテゴリ名は既に存在します';
                } else {
                    // ディスコグラフィーデータのカテゴリも更新
                    $discographyData = loadDiscographyData();
                    foreach ($discographyData as $index => $disc) {
                        if (($disc['category'] ?? '') === $oldCategory) {
                            $discographyData[$index]['category'] = $newCategory;
                        }
                    }
                    saveDiscographyData($discographyData);
                }
            }
            
            if (empty($error)) {
                $categories[$key] = [
                    'name' => $newCategory,
                    'color' => $newColor
                ];
                saveDiscographyCategories($categories);
                $message = 'カテゴリを更新しました';
                $categories = loadDiscographyCategories(); // 再読み込み
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ディスコグラフィーカテゴリ管理 - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <style>
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .color-preview {
            width: 40px;
            height: 30px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
        }
        .category-form-row {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .category-form-row .form-group {
            flex: 1;
            margin: 0;
        }
        .category-form-row .form-group.color-group {
            flex: 0 0 120px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>ディスコグラフィーカテゴリ管理</h1>
            <div class="header-actions">
                <a href="/kanri/discography/" class="btn btn-secondary">ディスコグラフィーに戻る</a>
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
                <h2 style="margin-bottom: 20px;">カテゴリ（アーティスト名）を追加</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="category-form-row">
                        <div class="form-group">
                            <label for="new_category">カテゴリ名（アーティスト名）</label>
                            <input type="text" id="new_category" name="new_category" required placeholder="例: 松本タカヒロ">
                        </div>
                        <div class="form-group color-group">
                            <label for="new_color">背景色</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="new_color" name="new_color" value="#667eea" style="width: 60px; height: 30px; border: 2px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                                <span class="color-preview" id="new_color_preview" style="background-color: #667eea;"></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: fit-content; margin-top: 24px;">追加</button>
                    </div>
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
                        <?php foreach ($categories as $index => $category): 
                            $categoryName = getCategoryName($category);
                            $categoryColor = is_array($category) ? ($category['color'] ?? '#667eea') : '#667eea';
                        ?>
                            <div class="category-item" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px; background: #fff;">
                                <div style="flex: 1;">
                                    <form method="POST" action="" class="category-form-row">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="old_category" value="<?php echo h($categoryName); ?>">
                                        <div class="form-group">
                                            <label>カテゴリ名</label>
                                            <input type="text" name="new_category" value="<?php echo h($categoryName); ?>" required style="padding: 8px; border: 2px solid #e0e0e0; border-radius: 5px; width: 100%;">
                                        </div>
                                        <div class="form-group color-group">
                                            <label>背景色</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" name="new_color" value="<?php echo h($categoryColor); ?>" class="category-color-input" data-preview="color_preview_<?php echo $index; ?>" style="width: 60px; height: 30px; border: 2px solid #e0e0e0; border-radius: 4px; cursor: pointer;">
                                                <span class="color-preview" id="color_preview_<?php echo $index; ?>" style="background-color: <?php echo h($categoryColor); ?>;"></span>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-edit btn-small" style="height: fit-content; margin-top: 24px;">更新</button>
                                    </form>
                                </div>
                                <form method="POST" action="" style="margin-left: 10px; align-self: flex-end;" onsubmit="return confirm('本当に削除しますか？\nこのカテゴリを使用しているディスコグラフィーがある場合は削除できません。');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category" value="<?php echo h($categoryName); ?>">
                                    <button type="submit" class="btn btn-delete btn-small">削除</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // カラー picker の変更をプレビューに反映
        document.addEventListener('DOMContentLoaded', function() {
            // 追加フォームのカラーピッカー
            const newColorInput = document.getElementById('new_color');
            const newColorPreview = document.getElementById('new_color_preview');
            if (newColorInput && newColorPreview) {
                newColorInput.addEventListener('input', function() {
                    newColorPreview.style.backgroundColor = this.value;
                });
            }
            
            // 編集フォームのカラーピッカー
            const colorInputs = document.querySelectorAll('.category-color-input');
            colorInputs.forEach(function(input) {
                const previewId = input.getAttribute('data-preview');
                const preview = document.getElementById(previewId);
                if (preview) {
                    input.addEventListener('input', function() {
                        preview.style.backgroundColor = this.value;
                    });
                }
            });
        });
    </script>
</body>
</html>

