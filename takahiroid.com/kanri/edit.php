<?php
require_once 'config.php';
requireLogin();

$newsData = loadNewsData();
$news = null;
$isEdit = false;
$error = '';

// 編集モード
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if (isset($newsData[$id])) {
        $news = $newsData[$id];
        $isEdit = true;
    }
}

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $date = $_POST['date'] ?? date('Y/m/d');
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? true : false;
    $id = $_POST['id'] ?? null;
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $existing_image = $_POST['existing_image'] ?? '';
    $delete_image = isset($_POST['delete_image']) ? true : false;
    
    // 画像アップロード処理
    $image_path = $existing_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if ($uploadResult['success']) {
            // 既存の画像を削除
            if (!empty($existing_image) && !$delete_image) {
                deleteImage(basename($existing_image));
            }
            $image_path = $uploadResult['path'];
        } else {
            $error = $uploadResult['error'] ?? '画像のアップロードに失敗しました';
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // アップロードエラー
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'ファイルサイズが大きすぎます';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'ファイルのアップロードが完了しませんでした';
                break;
            default:
                $error = '画像のアップロードに失敗しました';
        }
    } elseif ($delete_image && !empty($existing_image)) {
        // 画像削除がリクエストされた場合
        deleteImage(basename($existing_image));
        $image_path = '';
    }
    
    // エラーがある場合は処理を中断しない（エラーメッセージを表示するため）
    
    // YouTube URLからIDを抽出
    $youtube_id = null;
    if (!empty($youtube_url)) {
        $youtube_id = extractYouTubeId($youtube_url);
    }
    
    // 新規作成
    if ($id === null || $id === '') {
        $newNews = [
            'id' => uniqid(),
            'title' => $title,
            'category' => $category,
            'date' => $date,
            'content' => $content,
            'published' => $published,
            'image' => $image_path,
            'youtube_id' => $youtube_id,
            'youtube_url' => $youtube_url,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $newsData[] = $newNews;
        $msg = 'created';
    } else {
        // 編集
        $id = (int)$id;
        if (isset($newsData[$id])) {
            $newsData[$id]['title'] = $title;
            $newsData[$id]['category'] = $category;
            $newsData[$id]['date'] = $date;
            $newsData[$id]['content'] = $content;
            $newsData[$id]['published'] = $published;
            $newsData[$id]['image'] = $image_path;
            $newsData[$id]['youtube_id'] = $youtube_id;
            $newsData[$id]['youtube_url'] = $youtube_url;
            $newsData[$id]['updated_at'] = date('Y-m-d H:i:s');
        }
        $msg = 'updated';
    }
    
    saveNewsData($newsData);
    header('Location: /kanri/dashboard.php?msg=' . $msg);
    exit;
}

// デフォルト値
if (!$news) {
    $news = [
        'title' => '',
        'category' => 'LIVE',
        'date' => date('Y/m/d'),
        'content' => '',
        'published' => true,
        'image' => '',
        'youtube_url' => '',
        'youtube_id' => null
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?> - TAKAHIROID.COM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 {
            font-size: 24px;
            color: #333;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        textarea {
            min-height: 300px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input[type="checkbox"] {
            width: auto;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .image-preview {
            margin-top: 10px;
            max-width: 300px;
        }
        .image-preview img {
            max-width: 100%;
            height: auto;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 5px;
        }
        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .current-image img {
            max-width: 300px;
            height: auto;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            margin-top: 10px;
        }
        .delete-image-checkbox {
            margin-top: 10px;
        }
        input[type="file"] {
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?></h1>
            <a href="/kanri/dashboard.php" class="btn btn-secondary">一覧に戻る</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <?php if ($error): ?>
                <div class="error" style="background: #fee; color: #c33; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #fcc;">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">タイトル *</label>
                    <input type="text" id="title" name="title" value="<?php echo h($news['title']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">カテゴリ *</label>
                        <select id="category" name="category" required>
                            <option value="LIVE" <?php echo ($news['category'] === 'LIVE') ? 'selected' : ''; ?>>LIVE</option>
                            <option value="RELEASE" <?php echo ($news['category'] === 'RELEASE') ? 'selected' : ''; ?>>RELEASE</option>
                            <option value="You Tube" <?php echo ($news['category'] === 'You Tube') ? 'selected' : ''; ?>>You Tube</option>
                            <option value="TV・GUITAR" <?php echo ($news['category'] === 'TV・GUITAR') ? 'selected' : ''; ?>>TV・GUITAR</option>
                            <option value="ラジオ出演" <?php echo ($news['category'] === 'ラジオ出演') ? 'selected' : ''; ?>>ラジオ出演</option>
                            <option value="SHOP・GOODS" <?php echo ($news['category'] === 'SHOP・GOODS') ? 'selected' : ''; ?>>SHOP・GOODS</option>
                            <option value="その他" <?php echo ($news['category'] === 'その他') ? 'selected' : ''; ?>>その他</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">公開日 *</label>
                        <input type="text" id="date" name="date" value="<?php echo h($news['date']); ?>" required placeholder="2025/12/08">
                        <div class="help-text">形式: YYYY/MM/DD</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="content">本文 *</label>
                    <textarea id="content" name="content" required><?php echo h($news['content']); ?></textarea>
                    <div class="help-text">HTMLタグを使用できます。改行は&lt;br&gt;タグを使用してください。</div>
                </div>
                
                <div class="form-group">
                    <label for="image">画像（記事の下に表示されます）</label>
                    <?php if (!empty($news['image'])): ?>
                        <div class="current-image">
                            <p>現在の画像:</p>
                            <img src="<?php echo h($news['image']); ?>" alt="現在の画像">
                            <input type="hidden" name="existing_image" value="<?php echo h($news['image']); ?>">
                            <div class="delete-image-checkbox">
                                <input type="checkbox" id="delete_image" name="delete_image">
                                <label for="delete_image" style="margin: 0; font-weight: normal; color: #dc3545;">この画像を削除する</label>
                            </div>
                        </div>
                        <p style="margin-top: 10px; color: #666;">新しい画像をアップロードすると、現在の画像が置き換えられます。</p>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <div class="help-text">JPEG, PNG, GIF, WebP形式、最大5MB</div>
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <p>プレビュー:</p>
                        <img id="preview-img" src="" alt="プレビュー">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="youtube_url">YouTube URL（記事の下に埋め込み表示されます）</label>
                    <input type="text" id="youtube_url" name="youtube_url" value="<?php echo h($news['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=... または https://youtu.be/...">
                    <div class="help-text">YouTubeのURLを入力してください。記事の下に動画が埋め込み表示されます。</div>
                    <?php if (!empty($news['youtube_id'])): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 5px;">
                            <p style="margin-bottom: 10px;">現在の動画:</p>
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo h($news['youtube_id']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="max-width: 100%;"></iframe>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="published" name="published" <?php echo ($news['published'] ?? true) ? 'checked' : ''; ?>>
                        <label for="published" style="margin: 0; font-weight: normal;">公開する</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '更新' : '作成'; ?></button>
                    <a href="/kanri/dashboard.php" class="btn btn-secondary">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // 画像プレビュー機能
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('image-preview').style.display = 'none';
            }
        });
        
        // 画像削除チェックボックスの処理
        const deleteCheckbox = document.getElementById('delete_image');
        if (deleteCheckbox) {
            deleteCheckbox.addEventListener('change', function() {
                const fileInput = document.getElementById('image');
                if (this.checked) {
                    fileInput.disabled = true;
                } else {
                    fileInput.disabled = false;
                }
            });
        }
    </script>
</body>
</html>

