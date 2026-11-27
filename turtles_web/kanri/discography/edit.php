<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$discographyData = loadDiscographyData();
$releaseTypes = discographyReleaseTypes();
$isEdit = false;
$error = '';
$disc = [
    'id' => '',
    'title' => '',
    'subtitle' => '',
    'release_date' => '',
    'release_type' => 'CD',
    'price' => '',
    'image' => '',
    'content' => '',
    'published' => true,
    'created_at' => ''
];

if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $index = findDiscographyIndex($discographyData, (string)$_GET['id']);
    if ($index === null) {
        header('Location: /kanri/discography/');
        exit;
    }
    $disc = array_merge($disc, $discographyData[$index]);
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
        $error = '送信データが大きすぎます。画像を小さくするか、サーバーの upload_max_filesize と post_max_size を確認してください。';
    } else {
        $postId = trim((string)($_POST['id'] ?? ''));
        $index = $postId === '' ? null : findDiscographyIndex($discographyData, $postId);
        $isEdit = $index !== null;
        $current = $isEdit ? $discographyData[$index] : [];

        $disc['id'] = $isEdit ? ($current['id'] ?? $postId) : nextDiscographyId($discographyData);
        $disc['title'] = trim($_POST['title'] ?? '');
        $disc['subtitle'] = trim($_POST['subtitle'] ?? '');
        $disc['release_date'] = trim($_POST['release_date'] ?? '');
        $disc['release_type'] = trim($_POST['release_type'] ?? 'CD');
        if (!in_array($disc['release_type'], $releaseTypes, true)) {
            $disc['release_type'] = 'CD';
        }
        $disc['price'] = trim($_POST['price'] ?? '');
        $disc['content'] = $_POST['content'] ?? '';
        $disc['published'] = isset($_POST['published']);
        $disc['created_at'] = $current['created_at'] ?? date('Y-m-d H:i:s');
        $disc['updated_at'] = date('Y-m-d H:i:s');

        if ($disc['title'] === '') {
            $error = 'タイトルを入力してください';
        }

        $existingImage = $isEdit ? (string)($current['image'] ?? '') : '';
        $postedImage = (string)($_POST['existing_image'] ?? '');
        if ($postedImage !== $existingImage) {
            $postedImage = $existingImage;
        }
        $disc['image'] = $postedImage;

        if ($error === '') {
            [$imagePath, $imageError] = storeDiscographyImage($_FILES['image'] ?? null, $postedImage, isset($_POST['delete_image']));
            if ($imageError !== '') {
                $error = $imageError;
            } else {
                $disc['image'] = $imagePath;
            }
        }

        if ($error === '') {
            if ($isEdit) {
                $discographyData[$index] = $disc;
            } else {
                array_unshift($discographyData, $disc);
            }
            saveDiscographyData($discographyData);
            header('Location: /kanri/discography/?msg=' . ($isEdit ? 'updated' : 'created'));
            exit;
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
    <title><?php echo $isEdit ? '作品編集' : '新規作品作成'; ?> - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        #content-editor { height: 360px; margin-bottom: 10px; }
        .ql-editor { min-height: 300px; }
        .editor-mode-toggle { display: flex; justify-content: flex-end; margin-bottom: 8px; }
        .editor-mode-toggle button {
            padding: 5px 14px; font-size: 12px; border: 1px solid #ccc; background: #f5f5f5;
            color: #555; cursor: pointer; border-radius: 4px;
        }
        .editor-mode-toggle button.active { background: #D95D67; color: #fff; border-color: #D95D67; }
        #content-source {
            width: 100%; min-height: 360px; padding: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 13px; border: 1px solid #ccc; border-radius: 4px; background: #1e1e1e; color: #d4d4d4;
        }
    </style>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1><?php echo $isEdit ? '作品編集' : '新規作品作成'; ?></h1>
            <div class="header-actions">
                <a href="/kanri/discography/" class="btn btn-secondary">一覧に戻る</a>
            </div>
        </div>
        <div class="container">
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo h($disc['id']); ?>">
                <?php endif; ?>
                <div class="form-layout">
                    <div class="form-main">
                        <div class="form-container">
                            <?php if ($error): ?><div class="error"><?php echo h($error); ?></div><?php endif; ?>
                            <div class="form-group">
                                <label for="title">タイトル <span style="color:#e11d48;">*</span></label>
                                <input type="text" id="title" name="title" value="<?php echo h($disc['title']); ?>" required placeholder="例：タートルマニア">
                            </div>
                            <div class="form-group">
                                <label for="subtitle">サブタイトル</label>
                                <input type="text" id="subtitle" name="subtitle" value="<?php echo h($disc['subtitle']); ?>" placeholder="例：BEST ALBUM">
                                <div class="help-text">一覧のタイトル上に表示されます。</div>
                            </div>
                            <div class="form-group">
                                <label for="image">ジャケット画像</label>
                                <?php if (!empty($disc['image'])): ?>
                                    <div class="current-image">
                                        <p>現在の画像:</p>
                                        <img src="<?php echo h($disc['image']); ?>" alt="" style="max-width:240px;">
                                        <input type="hidden" name="existing_image" value="<?php echo h($disc['image']); ?>">
                                        <div class="delete-image-checkbox">
                                            <input type="checkbox" id="delete_image" name="delete_image">
                                            <label for="delete_image" style="display:inline; font-weight:normal; color:#dc3545;">この画像を削除する</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="help-text">JPEG, PNG, GIF, WebP。最大20MB。</div>
                                <div id="image-preview" class="image-preview" style="display:none;">
                                    <img id="preview-img" src="" alt="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="content">解説・収録曲</label>
                                <div class="editor-mode-toggle">
                                    <button type="button" id="btn-mode-visual" class="active">ビジュアル</button>
                                    <button type="button" id="btn-mode-source">HTMLソース</button>
                                </div>
                                <div id="content-editor-wrap"><div id="content-editor"></div></div>
                                <textarea id="content-source" style="display:none;"></textarea>
                                <textarea id="content" name="content" style="display:none;"><?php echo h($disc['content']); ?></textarea>
                                <div class="help-text">収録曲や解説を入力します。</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-sidebar">
                        <div class="sidebar-panel">
                            <h3>公開設定</h3>
                            <div class="form-group">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                    <input type="checkbox" name="published" value="1" <?php echo !empty($disc['published']) ? 'checked' : ''; ?>>
                                    <span>公開する</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="release_date">発売日</label>
                                <input type="text" id="release_date" name="release_date" value="<?php echo h($disc['release_date']); ?>" placeholder="例：2011.9.23">
                                <div class="help-text">公開ページは発売日の新しい順に並びます。</div>
                            </div>
                            <div class="form-group">
                                <label for="release_type">リリース形態</label>
                                <select id="release_type" name="release_type">
                                    <?php foreach ($releaseTypes as $type): ?>
                                        <option value="<?php echo h($type); ?>" <?php echo ($disc['release_type'] ?? '') === $type ? 'selected' : ''; ?>><?php echo h($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="price">価格（税込）</label>
                                <input type="text" id="price" name="price" value="<?php echo h($disc['price']); ?>" placeholder="例：4715">
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '更新する' : '作成する'; ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        var quill = new Quill('#content-editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: '収録曲や解説を入力してください'
        });
        var contentTextarea = document.getElementById('content');
        var contentSource = document.getElementById('content-source');
        var editorWrap = document.getElementById('content-editor-wrap');
        var isSourceMode = false;
        if (contentTextarea.value) {
            quill.root.innerHTML = contentTextarea.value;
        }
        document.querySelector('form').addEventListener('submit', function () {
            contentTextarea.value = isSourceMode ? contentSource.value : quill.root.innerHTML;
        });
        document.getElementById('btn-mode-source').addEventListener('click', function () {
            contentSource.value = quill.root.innerHTML;
            editorWrap.style.display = 'none';
            contentSource.style.display = 'block';
            this.classList.add('active');
            document.getElementById('btn-mode-visual').classList.remove('active');
            isSourceMode = true;
        });
        document.getElementById('btn-mode-visual').addEventListener('click', function () {
            quill.root.innerHTML = contentSource.value;
            contentSource.style.display = 'none';
            editorWrap.style.display = 'block';
            this.classList.add('active');
            document.getElementById('btn-mode-source').classList.remove('active');
            isSourceMode = false;
        });

        var imageInput = document.getElementById('image');
        imageInput.addEventListener('change', function () {
            var file = imageInput.files[0];
            var box = document.getElementById('image-preview');
            if (!file) {
                box.style.display = 'none';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (event) {
                document.getElementById('preview-img').src = event.target.result;
                box.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
        var deleteImage = document.getElementById('delete_image');
        if (deleteImage) {
            deleteImage.addEventListener('change', function () {
                imageInput.disabled = deleteImage.checked;
            });
        }
    </script>
</body>
</html>
