<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$newsData = loadNewsData();
$categories = loadCategories();
$news = null;
$isEdit = false;
$error = '';

if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $index = findNewsIndex($newsData, (string)$_GET['id']);
    if ($index === null) {
        header('Location: /kanri/news/');
        exit;
    }
    $news = $newsData[$index];
    $isEdit = true;
}

function hourFromDate($value) {
    if (is_string($value) && strpos($value, ' ') !== false) {
        $time = explode(' ', $value)[1];
        if (strpos($time, ':') !== false) {
            return (int)explode(':', $time)[0];
        }
    }
    return 0;
}

function dateInputValue($value) {
    if (!is_string($value) || $value === '') {
        return '';
    }
    $date = explode(' ', $value)[0];
    return str_replace('/', '-', $date);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
        $error = '送信データが大きすぎます。画像を小さくするか、サーバーの upload_max_filesize と post_max_size を確認してください。';
    } else {
        $title = $_POST['title'] ?? '';
        $titleUrl = trim($_POST['title_url'] ?? '');
        $lead = trim($_POST['lead'] ?? '');
        $category = $_POST['category'] ?? '';
        $dateInput = $_POST['date'] ?? date('Y-m-d');
        $dateTime = (int)($_POST['date_time'] ?? 0);
        $date = str_replace('-', '/', $dateInput) . ' ' . sprintf('%02d', $dateTime) . ':00';
        $endDate = '';
        if (!empty($_POST['end_date'])) {
            $endDate = str_replace('-', '/', $_POST['end_date']) . ' ' . sprintf('%02d', (int)($_POST['end_date_time'] ?? 0)) . ':00';
        }
        $content = $_POST['content'] ?? '';
        $published = isset($_POST['published']);
        $showOnTop = isset($_POST['show_on_top']);
        $postId = trim((string)($_POST['id'] ?? ''));
        $youtubeUrl = trim($_POST['youtube_url'] ?? '');
        $existingImage = $_POST['existing_image'] ?? '';
        $deleteImage = isset($_POST['delete_image']);
        $existingThumbnail = $_POST['existing_thumbnail'] ?? '';
        $deleteThumbnail = isset($_POST['delete_thumbnail']);

        $liveDate = !empty($_POST['live_date']) ? str_replace('-', '/', $_POST['live_date']) : '';
        $liveTime = $_POST['live_time'] ?? '';
        $liveVenue = $_POST['live_venue'] ?? '';
        $livePerformers = $_POST['live_performers'] ?? '';
        $livePrice = $_POST['live_price'] ?? '';
        $liveTicketSales = [];
        if (isset($_POST['ticket_name']) && is_array($_POST['ticket_name'])) {
            foreach ($_POST['ticket_name'] as $i => $name) {
                $url = $_POST['ticket_url'][$i] ?? '';
                if ($name !== '' || $url !== '') {
                    $liveTicketSales[] = ['name' => $name, 'url' => $url];
                }
            }
        }
        $liveSaleDate = $_POST['live_sale_date'] ?? '';
        $liveContact = $_POST['live_contact'] ?? '';
        $liveContactUrl = trim($_POST['live_contact_url'] ?? '');
        $liveOther = $_POST['live_other'] ?? '';

        if ($lead === '') {
            $error = 'リード文は必須です';
        } elseif ($category === 'LIVE' && $liveDate === '') {
            $error = 'ライブ日時は必須です';
        }

        $imagePath = $existingImage;
        $thumbnailPath = $existingThumbnail;
        if ($error === '') {
            [$imagePath, $imageError] = storeUploadedImage($_FILES['image'] ?? null, $existingImage, $deleteImage, '画像');
            if ($imageError !== '') {
                $error = $imageError;
            }
        }
        if ($error === '') {
            [$thumbnailPath, $thumbError] = storeUploadedImage($_FILES['thumbnail'] ?? null, $existingThumbnail, $deleteThumbnail, 'サムネイル画像');
            if ($thumbError !== '') {
                $error = $thumbError;
            }
        }

        $youtubeId = $youtubeUrl !== '' ? extractYouTubeId($youtubeUrl) : null;
        $record = [
            'title' => $title,
            'title_url' => $titleUrl,
            'lead' => $lead,
            'category' => $category,
            'date' => $date,
            'end_date' => $endDate,
            'content' => $content,
            'published' => $published,
            'show_on_top' => $showOnTop,
            'image' => $imagePath,
            'thumbnail' => $thumbnailPath,
            'youtube_id' => $youtubeId,
            'youtube_url' => $youtubeUrl,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if ($category === 'LIVE') {
            $record['live_date'] = $liveDate;
            $record['live_venue'] = $liveVenue;
            $record['live_performers'] = $livePerformers;
            $record['live_time'] = $liveTime;
            $record['live_price'] = $livePrice;
            $record['live_ticket_sales'] = $liveTicketSales;
            $record['live_sale_date'] = $liveSaleDate;
            $record['live_contact'] = $liveContact;
            $record['live_contact_url'] = $liveContactUrl;
            $record['live_other'] = $liveOther;
        }

        if ($error === '') {
            if ($postId === '') {
                $record['id'] = uniqid();
                $record['created_at'] = date('Y-m-d H:i:s');
                array_unshift($newsData, $record);
                saveNewsData($newsData);
                header('Location: /kanri/news/?msg=created');
                exit;
            }
            $index = findNewsIndex($newsData, $postId);
            if ($index === null) {
                $error = '記事が見つかりません';
            } else {
                $record['id'] = $newsData[$index]['id'];
                $record['created_at'] = $newsData[$index]['created_at'] ?? date('Y-m-d H:i:s');
                $newsData[$index] = $record;
                saveNewsData($newsData);
                header('Location: /kanri/news/?msg=updated');
                exit;
            }
        }

        $news = $record;
        if ($postId !== '') {
            $news['id'] = $postId;
            $isEdit = true;
        }
    }
}

if (!$news) {
    $news = [
        'title' => '',
        'title_url' => '',
        'lead' => '',
        'category' => 'LIVE',
        'date' => date('Y/m/d H:i'),
        'end_date' => '',
        'content' => '',
        'published' => false,
        'show_on_top' => false,
        'image' => '',
        'thumbnail' => '',
        'youtube_url' => '',
        'youtube_id' => null,
        'live_date' => '',
        'live_venue' => '',
        'live_performers' => '',
        'live_time' => '',
        'live_price' => '',
        'live_ticket_sales' => [],
        'live_sale_date' => '',
        'live_contact' => '',
        'live_contact_url' => '',
        'live_other' => ''
    ];
}

$ticketSales = $news['live_ticket_sales'] ?? [];
if ($ticketSales === []) {
    $ticketSales = [['name' => '', 'url' => '']];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?> - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        #content-editor { height: 320px; margin-bottom: 10px; }
        .ql-editor { min-height: 270px; }
        .editor-mode-toggle { display: flex; justify-content: flex-end; margin-bottom: 8px; }
        .editor-mode-toggle button {
            padding: 5px 14px; font-size: 12px; border: 1px solid #ccc; background: #f5f5f5;
            color: #555; cursor: pointer; border-radius: 4px;
        }
        .editor-mode-toggle button.active { background: #D95D67; color: #fff; border-color: #D95D67; }
        #content-source {
            width: 100%; min-height: 320px; padding: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 13px; border: 1px solid #ccc; border-radius: 4px; background: #1e1e1e; color: #d4d4d4;
        }
    </style>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?></h1>
            <div class="header-actions">
                <a href="/kanri/news/" class="btn btn-secondary">一覧に戻る</a>
            </div>
        </div>
        <div class="container">
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?php echo h($news['id'] ?? $_GET['id']); ?>">
                <?php endif; ?>
                <div class="form-layout">
                    <div class="form-main">
                        <div class="form-container">
                            <?php if ($error): ?><div class="error"><?php echo h($error); ?></div><?php endif; ?>
                            <div class="form-group">
                                <label for="lead">リード文 <span style="color:#e11d48;">*</span></label>
                                <input type="text" id="lead" name="lead" value="<?php echo h($news['lead'] ?? ''); ?>" required placeholder="トップの見出しや記事の要約">
                            </div>
                            <div class="form-group">
                                <label for="title">タイトル</label>
                                <textarea id="title" name="title" rows="3"><?php echo h(preg_replace('/<br\s*\/?>/i', "\n", $news['title'] ?? '')); ?></textarea>
                                <div class="help-text">改行はそのまま記事タイトルに反映されます。</div>
                                <div class="url-field-group">
                                    <label for="title_url">リンク先URL（任意）</label>
                                    <input type="text" id="title_url" name="title_url" value="<?php echo h($news['title_url'] ?? ''); ?>" placeholder="https://...">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="content">本文</label>
                                <div class="editor-mode-toggle">
                                    <button type="button" id="btn-mode-visual" class="active">ビジュアル</button>
                                    <button type="button" id="btn-mode-source">HTMLソース</button>
                                </div>
                                <div id="content-editor-wrap"><div id="content-editor"></div></div>
                                <textarea id="content-source" style="display:none;"></textarea>
                                <textarea id="content" name="content" style="display:none;"><?php echo h($news['content'] ?? ''); ?></textarea>
                            </div>

                            <div id="live-fields" style="display:none;">
                                <h3 style="margin: 10px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #D95D67;">LIVE情報</h3>
                                <div class="form-group">
                                    <label for="live_date">ライブ日時 <span style="color:#e11d48;">*</span></label>
                                    <input type="date" id="live_date" name="live_date" value="<?php echo h(dateInputValue($news['live_date'] ?? '')); ?>">
                                    <div id="live_date_weekday" class="help-text"></div>
                                </div>
                                <div class="form-group">
                                    <label for="live_venue">会場</label>
                                    <input type="text" id="live_venue" name="live_venue" value="<?php echo h($news['live_venue'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="live_performers">出演</label>
                                    <textarea id="live_performers" name="live_performers" rows="3"><?php echo h($news['live_performers'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="live_time">開場 / 開演</label>
                                    <input type="text" id="live_time" name="live_time" value="<?php echo h($news['live_time'] ?? ''); ?>" placeholder="開場 17:30 / 開演 18:00">
                                </div>
                                <div class="form-group">
                                    <label for="live_price">料金</label>
                                    <input type="text" id="live_price" name="live_price" value="<?php echo h($news['live_price'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>チケット発売先</label>
                                    <div id="ticket-sales-container">
                                        <?php foreach ($ticketSales as $ticket): ?>
                                            <div class="ticket-sale-item" style="display:flex; gap:10px; margin-bottom:10px; align-items:flex-end;">
                                                <div style="flex:1;">
                                                    <input type="text" name="ticket_name[]" value="<?php echo h($ticket['name'] ?? ''); ?>" placeholder="イープラス" style="margin-bottom:5px;">
                                                    <input type="url" name="ticket_url[]" value="<?php echo h($ticket['url'] ?? ''); ?>" placeholder="https://...">
                                                </div>
                                                <button type="button" class="btn btn-secondary btn-small remove-ticket">削除</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" id="add-ticket" class="btn btn-secondary btn-small">チケット発売先を追加</button>
                                </div>
                                <div class="form-group">
                                    <label for="live_sale_date">チケット発売日</label>
                                    <input type="text" id="live_sale_date" name="live_sale_date" value="<?php echo h($news['live_sale_date'] ?? ''); ?>" placeholder="2026/07/26(日) 10:00〜">
                                </div>
                                <div class="form-group">
                                    <label for="live_contact">お問い合わせ</label>
                                    <input type="text" id="live_contact" name="live_contact" value="<?php echo h($news['live_contact'] ?? ''); ?>">
                                    <div class="url-field-group">
                                        <label for="live_contact_url">リンク先URL（任意）</label>
                                        <input type="url" id="live_contact_url" name="live_contact_url" value="<?php echo h($news['live_contact_url'] ?? ''); ?>" placeholder="https://...">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="live_other">その他のテキスト</label>
                                    <textarea id="live_other" name="live_other" rows="4"><?php echo h($news['live_other'] ?? ''); ?></textarea>
                                    <div class="help-text">リンクは HTML の &lt;a&gt; で書けます。</div>
                                </div>
                            </div>

                            <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #D95D67;">画像・動画</h3>
                            <div class="form-group">
                                <label for="thumbnail">サムネイル画像</label>
                                <?php if (!empty($news['thumbnail'])): ?>
                                    <div class="current-image">
                                        <p>現在のサムネイル:</p>
                                        <img src="<?php echo h($news['thumbnail']); ?>" alt="" style="max-width:300px;">
                                        <input type="hidden" name="existing_thumbnail" value="<?php echo h($news['thumbnail']); ?>">
                                        <div class="delete-image-checkbox">
                                            <input type="checkbox" id="delete_thumbnail" name="delete_thumbnail">
                                            <label for="delete_thumbnail" style="display:inline; font-weight:normal; color:#dc3545;">このサムネイルを削除する</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="help-text">JPEG, PNG, GIF, WebP。最大20MB。</div>
                                <div id="thumbnail-preview" class="image-preview" style="display:none;">
                                    <img id="preview-thumbnail-img" src="" alt="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="image">本文画像</label>
                                <?php if (!empty($news['image'])): ?>
                                    <div class="current-image">
                                        <p>現在の画像:</p>
                                        <img src="<?php echo h($news['image']); ?>" alt="" style="max-width:300px;">
                                        <input type="hidden" name="existing_image" value="<?php echo h($news['image']); ?>">
                                        <div class="delete-image-checkbox">
                                            <input type="checkbox" id="delete_image" name="delete_image">
                                            <label for="delete_image" style="display:inline; font-weight:normal; color:#dc3545;">この画像を削除する</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="help-text">記事の下に表示されます。JPEG, PNG, GIF, WebP。最大20MB。</div>
                                <div id="image-preview" class="image-preview" style="display:none;">
                                    <img id="preview-img" src="" alt="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="youtube_url">YouTube URL</label>
                                <input type="text" id="youtube_url" name="youtube_url" value="<?php echo h($news['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
                                <?php if (!empty($news['youtube_id'])): ?>
                                    <div style="margin-top:10px;">
                                        <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo h($news['youtube_id']); ?>" style="max-width:100%;" allowfullscreen></iframe>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-sidebar">
                        <div class="sidebar-panel">
                            <h3>公開設定</h3>
                            <div class="form-group">
                                <label for="category">カテゴリ <span style="color:#e11d48;">*</span></label>
                                <select id="category" name="category" required>
                                    <?php foreach ($categories as $category):
                                        $catName = is_array($category) ? ($category['name'] ?? '') : $category;
                                    ?>
                                        <option value="<?php echo h($catName); ?>" <?php echo (($news['category'] ?? '') === $catName) ? 'selected' : ''; ?>><?php echo h($catName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="help-text"><a href="/kanri/categories.php">カテゴリを管理</a></div>
                            </div>
                            <div class="form-group">
                                <label for="date">公開日 <span style="color:#e11d48;">*</span></label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="date" id="date" name="date" required value="<?php echo h(dateInputValue($news['date'] ?? date('Y/m/d'))); ?>" style="flex:1;">
                                    <input type="number" id="date_time" name="date_time" min="0" max="23" value="<?php echo (int)hourFromDate($news['date'] ?? ''); ?>" style="width:70px;">
                                    <span>時</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="end_date">公開終了日（任意）</label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="date" id="end_date" name="end_date" value="<?php echo h(dateInputValue($news['end_date'] ?? '')); ?>" style="flex:1;">
                                    <input type="number" id="end_date_time" name="end_date_time" min="0" max="23" value="<?php echo (int)hourFromDate($news['end_date'] ?? ''); ?>" style="width:70px;">
                                    <span>時</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="published" name="published" <?php echo !empty($news['published']) ? 'checked' : ''; ?>>
                                    <label for="published" style="margin:0; font-weight:normal;">公開する</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="show_on_top" name="show_on_top" <?php echo !empty($news['show_on_top']) ? 'checked' : ''; ?>>
                                    <label for="show_on_top" style="margin:0; font-weight:normal;">トップに見出しを出す</label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '更新' : '作成'; ?></button>
                                <a href="/kanri/news/" class="btn btn-secondary">キャンセル</a>
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
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
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

        function previewFile(input, imgId, boxId) {
            input.addEventListener('change', function () {
                var file = input.files[0];
                if (!file) {
                    document.getElementById(boxId).style.display = 'none';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById(imgId).src = event.target.result;
                    document.getElementById(boxId).style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }
        previewFile(document.getElementById('thumbnail'), 'preview-thumbnail-img', 'thumbnail-preview');
        previewFile(document.getElementById('image'), 'preview-img', 'image-preview');

        [['delete_thumbnail', 'thumbnail'], ['delete_image', 'image']].forEach(function (pair) {
            var checkbox = document.getElementById(pair[0]);
            if (!checkbox) return;
            checkbox.addEventListener('change', function () {
                document.getElementById(pair[1]).disabled = checkbox.checked;
            });
        });

        var categorySelect = document.getElementById('category');
        var liveFields = document.getElementById('live-fields');
        var liveDateInput = document.getElementById('live_date');
        function toggleLiveFields() {
            var isLive = categorySelect.value === 'LIVE';
            liveFields.style.display = isLive ? 'block' : 'none';
            if (isLive) liveDateInput.setAttribute('required', 'required');
            else liveDateInput.removeAttribute('required');
        }
        categorySelect.addEventListener('change', toggleLiveFields);
        toggleLiveFields();

        var weekday = document.getElementById('live_date_weekday');
        function updateWeekday() {
            if (!liveDateInput.value) {
                weekday.textContent = '';
                return;
            }
            var date = new Date(liveDateInput.value + 'T00:00:00');
            var names = ['日', '月', '火', '水', '木', '金', '土'];
            weekday.textContent = date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日(' + names[date.getDay()] + ')';
        }
        liveDateInput.addEventListener('change', updateWeekday);
        updateWeekday();

        document.getElementById('add-ticket').addEventListener('click', function () {
            var item = document.createElement('div');
            item.className = 'ticket-sale-item';
            item.style.cssText = 'display:flex; gap:10px; margin-bottom:10px; align-items:flex-end;';
            item.innerHTML = '<div style="flex:1;"><input type="text" name="ticket_name[]" placeholder="イープラス" style="margin-bottom:5px;"><input type="url" name="ticket_url[]" placeholder="https://..."></div><button type="button" class="btn btn-secondary btn-small remove-ticket">削除</button>';
            document.getElementById('ticket-sales-container').appendChild(item);
        });
        document.getElementById('ticket-sales-container').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-ticket')) {
                event.target.closest('.ticket-sale-item').remove();
            }
        });
    </script>
</body>
</html>
