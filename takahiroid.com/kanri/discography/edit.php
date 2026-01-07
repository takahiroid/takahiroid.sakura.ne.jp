<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$discographyData = loadDiscographyData();
$isEdit = isset($_GET['id']);
$editIndex = $isEdit ? (int)$_GET['id'] : -1;
$disc = $isEdit && isset($discographyData[$editIndex]) ? $discographyData[$editIndex] : null;

// 新規作成モードの場合、デフォルト値を設定
if (!$disc) {
    $disc = [
        'id' => getNextDiscographyId(),
        'title' => '',
        'subtitle' => '',
        'release_date' => '',
        'release_type' => 'CD',
        'price' => '',
        'image' => '',
        'content' => '',
        'published' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $isEdit = false;
}

$errors = [];
$success = '';

// フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // データ取得
    $disc['title'] = trim($_POST['title'] ?? '');
    $disc['subtitle'] = trim($_POST['subtitle'] ?? '');
    $disc['category'] = trim($_POST['category'] ?? '');
    $disc['release_date'] = trim($_POST['release_date'] ?? '');
    $disc['release_type'] = trim($_POST['release_type'] ?? 'CD');
    $disc['price'] = trim($_POST['price'] ?? '');
    $disc['content'] = $_POST['content'] ?? '';
    $disc['published'] = isset($_POST['published']);
    $disc['updated_at'] = date('Y-m-d H:i:s');
    
    // バリデーション
    if (empty($disc['title'])) {
        $errors[] = 'タイトルを入力してください';
    }
    
    // 画像アップロード処理
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = uploadDiscographyImage($_FILES['image']);
        if ($uploadResult['success']) {
            // 古い画像があれば削除
            if (!empty($disc['image'])) {
                deleteDiscographyImage(basename($disc['image']));
            }
            $disc['image'] = $uploadResult['path'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }
    
    // 画像削除チェック
    if (isset($_POST['delete_image']) && !empty($disc['image'])) {
        deleteDiscographyImage(basename($disc['image']));
        $disc['image'] = '';
    }
    
    // エラーがなければ保存
    if (empty($errors)) {
        if ($editIndex >= 0 && isset($discographyData[$editIndex])) {
            $discographyData[$editIndex] = $disc;
        } else {
            $disc['created_at'] = date('Y-m-d H:i:s');
            array_unshift($discographyData, $disc);
        }
        saveDiscographyData($discographyData);
        
        $redirectMsg = $isEdit ? 'updated' : 'created';
        header('Location: /kanri/discography/?msg=' . $redirectMsg);
        exit;
    }
}

// リリース形態の選択肢を取得
$releaseTypes = getReleaseTypes();

// カテゴリ一覧を読み込み
$discographyCategories = loadDiscographyCategories();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'ディスコグラフィー編集' : '新規ディスコグラフィー作成'; ?> - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        .image-preview-container {
            margin-top: 15px;
        }
        .image-preview-container img {
            max-width: 300px;
            max-height: 300px;
            object-fit: contain;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 5px;
            background: #fff;
        }
        .current-image-info {
            margin-top: 10px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .delete-image-option {
            margin-top: 10px;
            padding: 10px;
            background: #fff5f5;
            border-radius: 5px;
            border: 1px solid #fed7d7;
        }
        /* Quill Editor Styles */
        #content-editor {
            height: 400px;
            margin-bottom: 10px;
        }
        .ql-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .ql-editor {
            min-height: 350px;
        }
    </style>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1><?php echo $isEdit ? 'ディスコグラフィー編集' : '新規ディスコグラフィー作成'; ?></h1>
            <div class="header-actions">
                <a href="/kanri/discography/" class="btn btn-secondary">← 一覧に戻る</a>
            </div>
        </div>
        
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo h($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-layout">
                    <div class="form-main">
                        <div class="form-container">
                            <!-- タイトル -->
                            <div class="form-group">
                                <label for="title">タイトル <span style="color: #e74c3c;">*</span></label>
                                <input type="text" id="title" name="title" value="<?php echo h($disc['title']); ?>" required placeholder="例：SAMPLE ALBUM">
                            </div>
                            
                            <!-- サブタイトル -->
                            <div class="form-group">
                                <label for="subtitle">サブタイトル</label>
                                <input type="text" id="subtitle" name="subtitle" value="<?php echo h($disc['subtitle']); ?>" placeholder="例：1stアルバム">
                                <p class="help-text">リリース形態などを入力</p>
                            </div>
                            
                            <!-- カテゴリ（アーティスト名） -->
                            <div class="form-group">
                                <label for="category">カテゴリ（アーティスト名）</label>
                                <select id="category" name="category">
                                    <option value="">選択してください</option>
                                    <?php foreach ($discographyCategories as $cat): 
                                        $catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
                                    ?>
                                        <option value="<?php echo h($catName); ?>" <?php echo ($disc['category'] ?? '') === $catName ? 'selected' : ''; ?>>
                                            <?php echo h($catName); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="help-text">
                                    <a href="/kanri/discography_categories.php" target="_blank">カテゴリ管理</a>で追加・編集できます
                                </p>
                            </div>
                            
                            <!-- ジャケット画像 -->
                            <div class="form-group">
                                <label for="image">ジャケット画像</label>
                                <input type="file" id="image" name="image" accept="image/*">
                                <p class="help-text">JPEG, PNG, GIF, WebP形式（最大5MB）</p>
                                
                                <?php if (!empty($disc['image'])): ?>
                                    <div class="current-image-info">
                                        <p><strong>現在の画像:</strong></p>
                                        <div class="image-preview-container">
                                            <img src="<?php echo h($disc['image']); ?>" alt="現在のジャケット画像">
                                        </div>
                                        <div class="delete-image-option">
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                                <input type="checkbox" name="delete_image" value="1">
                                                <span style="color: #c53030;">この画像を削除する</span>
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- コメント（WYSIWYG） -->
                            <div class="form-group">
                                <label for="content">コメント</label>
                                <div id="content-editor"></div>
                                <textarea id="content" name="content" style="display: none;"><?php echo h($disc['content']); ?></textarea>
                                <p class="help-text">詳細な説明、トラックリスト、購入リンクなどを記載</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-sidebar">
                        <div class="sidebar-panel">
                            <h3>公開設定</h3>
                            
                            <!-- 公開状態 -->
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="published" value="1" <?php echo ($disc['published'] ?? true) ? 'checked' : ''; ?>>
                                    <span>公開する</span>
                                </label>
                            </div>
                            
                            <!-- 発売日 -->
                            <div class="form-group">
                                <label for="release_date">発売日</label>
                                <input type="text" id="release_date" name="release_date" value="<?php echo h($disc['release_date']); ?>" placeholder="例：2024/01/01">
                                <p class="help-text">YYYY/MM/DD形式</p>
                            </div>
                            
                            <!-- リリース形態 -->
                            <div class="form-group">
                                <label for="release_type">リリース形態</label>
                                <select id="release_type" name="release_type">
                                    <?php foreach ($releaseTypes as $type): ?>
                                        <option value="<?php echo h($type); ?>" <?php echo ($disc['release_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                            <?php echo h($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- 値段 -->
                            <div class="form-group">
                                <label for="price">値段（税込）</label>
                                <input type="text" id="price" name="price" value="<?php echo h($disc['price']); ?>" placeholder="例：3,300">
                                <p class="help-text">数字のみ入力（カンマ可）</p>
                            </div>
                            
                            <!-- 送信ボタン -->
                            <div class="form-actions" style="border-top: none; margin-top: 20px; padding-top: 0;">
                                <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '更新する' : '作成する'; ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Quill Editor初期化
        var quill = new Quill('#content-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: '内容を入力してください...'
        });
        
        // 既存の内容をエディタに設定
        var contentTextarea = document.getElementById('content');
        if (contentTextarea.value) {
            quill.root.innerHTML = contentTextarea.value;
        }
        
        // フォーム送信時にエディタの内容をtextareaにコピー
        document.querySelector('form').addEventListener('submit', function() {
            contentTextarea.value = quill.root.innerHTML;
        });
        
        // エディタの変更を監視してtextareaに反映（リアルタイム保存）
        quill.on('text-change', function() {
            contentTextarea.value = quill.root.innerHTML;
        });
    </script>
</body>
</html>

