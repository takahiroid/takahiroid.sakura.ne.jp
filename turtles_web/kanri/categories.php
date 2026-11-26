<?php
require_once __DIR__ . '/config.php';
requireLogin();

$categories = loadCategories();
$message = '';
$error = '';

function categoryName($category) {
    return is_array($category) ? ($category['name'] ?? '') : (string)$category;
}

function categoryIndex($categories, $name) {
    foreach ($categories as $index => $category) {
        if (categoryName($category) === $name) {
            return $index;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $newCategory = trim($_POST['new_category'] ?? '');
        $newColor = trim($_POST['new_color'] ?? '#D95D67');
        if ($newCategory === '') {
            $error = 'カテゴリ名を入力してください';
        } elseif (categoryIndex($categories, $newCategory) !== false) {
            $error = 'このカテゴリは既に存在します';
        } else {
            $categories[] = ['name' => $newCategory, 'color' => $newColor];
            saveCategories($categories);
            $message = 'カテゴリを追加しました';
            $categories = loadCategories();
        }
    } elseif ($action === 'delete') {
        $categoryToDelete = $_POST['category'] ?? '';
        $key = categoryIndex($categories, $categoryToDelete);
        if ($key !== false) {
            $isUsed = false;
            foreach (loadNewsData() as $news) {
                if (($news['category'] ?? '') === $categoryToDelete) {
                    $isUsed = true;
                    break;
                }
            }
            if ($isUsed) {
                $error = 'このカテゴリは記事で使用されているため削除できません';
            } else {
                unset($categories[$key]);
                saveCategories(array_values($categories));
                $message = 'カテゴリを削除しました';
                $categories = loadCategories();
            }
        }
    } elseif ($action === 'edit') {
        $oldCategory = $_POST['old_category'] ?? '';
        $newCategory = trim($_POST['new_category'] ?? '');
        $newColor = trim($_POST['new_color'] ?? '#D95D67');
        $key = categoryIndex($categories, $oldCategory);
        if ($newCategory === '') {
            $error = 'カテゴリ名を入力してください';
        } elseif ($key === false) {
            $error = 'カテゴリが見つかりません';
        } else {
            if ($oldCategory !== $newCategory && categoryIndex($categories, $newCategory) !== false) {
                $error = 'このカテゴリ名は既に存在します';
            } else {
                if ($oldCategory !== $newCategory) {
                    $newsData = loadNewsData();
                    foreach ($newsData as $index => $news) {
                        if (($news['category'] ?? '') === $oldCategory) {
                            $newsData[$index]['category'] = $newCategory;
                        }
                    }
                    saveNewsData($newsData);
                }
                $categories[$key] = ['name' => $newCategory, 'color' => $newColor];
                saveCategories($categories);
                $message = 'カテゴリを更新しました';
                $categories = loadCategories();
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
    <meta name="robots" content="noindex">
    <title>カテゴリ管理 - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <style>
        .color-picker-wrapper { display: flex; align-items: center; gap: 10px; }
        .color-preview { width: 40px; height: 30px; border: 2px solid #e0e0e0; border-radius: 4px; display: inline-block; }
        .category-form-row { display: flex; gap: 10px; align-items: flex-end; }
        .category-form-row .form-group { flex: 1; margin: 0; }
        .category-form-row .form-group.color-group { flex: 0 0 150px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1>カテゴリ管理</h1>
            <div class="header-actions">
                <a href="/kanri/news/" class="btn btn-secondary">記事一覧に戻る</a>
            </div>
        </div>
        <div class="container">
            <?php if ($message): ?><div class="success"><?php echo h($message); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="error"><?php echo h($error); ?></div><?php endif; ?>

            <div class="form-container" style="margin-bottom: 30px;">
                <h2 style="margin-bottom: 20px;">カテゴリを追加</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="category-form-row">
                        <div class="form-group">
                            <label for="new_category">カテゴリ名</label>
                            <input type="text" id="new_category" name="new_category" required placeholder="例: RELEASE">
                        </div>
                        <div class="form-group color-group">
                            <label for="new_color">背景色</label>
                            <div class="color-picker-wrapper">
                                <input type="color" id="new_color" name="new_color" value="#D95D67">
                                <span class="color-preview" id="new_color_preview" style="background-color: #D95D67;"></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">追加</button>
                    </div>
                </form>
            </div>

            <div class="form-container">
                <h2 style="margin-bottom: 20px;">カテゴリ一覧</h2>
                <?php foreach ($categories as $index => $category):
                    $name = categoryName($category);
                    $color = is_array($category) ? ($category['color'] ?? '#D95D67') : '#D95D67';
                ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e0e0e0; border-radius: 5px; margin-bottom: 10px;">
                        <form method="POST" action="" class="category-form-row" style="flex: 1;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="old_category" value="<?php echo h($name); ?>">
                            <div class="form-group">
                                <label>カテゴリ名</label>
                                <input type="text" name="new_category" value="<?php echo h($name); ?>" required>
                            </div>
                            <div class="form-group color-group">
                                <label>背景色</label>
                                <div class="color-picker-wrapper">
                                    <input type="color" name="new_color" value="<?php echo h($color); ?>" class="category-color-input" data-preview="color_preview_<?php echo (int)$index; ?>">
                                    <span class="color-preview" id="color_preview_<?php echo (int)$index; ?>" style="background-color: <?php echo h($color); ?>;"></span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-edit btn-small">更新</button>
                        </form>
                        <form method="POST" action="" style="margin-left: 10px;" onsubmit="return confirm('本当に削除しますか？');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category" value="<?php echo h($name); ?>">
                            <button type="submit" class="btn btn-delete btn-small">削除</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('new_color').addEventListener('input', function () {
            document.getElementById('new_color_preview').style.backgroundColor = this.value;
        });
        document.querySelectorAll('.category-color-input').forEach(function (input) {
            input.addEventListener('input', function () {
                var preview = document.getElementById(input.getAttribute('data-preview'));
                if (preview) preview.style.backgroundColor = input.value;
            });
        });
    </script>
</body>
</html>
