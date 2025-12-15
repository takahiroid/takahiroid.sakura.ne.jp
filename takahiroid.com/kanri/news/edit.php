<?php
require_once dirname(__DIR__) . '/config.php';
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
    $title_url = trim($_POST['title_url'] ?? '');
    $lead = $_POST['lead'] ?? '';
    $category = $_POST['category'] ?? '';
    $date_input = $_POST['date'] ?? date('Y-m-d');
    // YYYY-MM-DD形式をYYYY/MM/DD形式に変換
    $date = str_replace('-', '/', $date_input);
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? true : false;
    $id = $_POST['id'] ?? null;
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $existing_image = $_POST['existing_image'] ?? '';
    $delete_image = isset($_POST['delete_image']) ? true : false;
    
    // LIVE記事用のフィールド
    $live_performers = $_POST['live_performers'] ?? '';
    $live_time = $_POST['live_time'] ?? '';
    $live_price = $_POST['live_price'] ?? '';
    $live_ticket_sales = [];
    if (isset($_POST['ticket_name']) && is_array($_POST['ticket_name'])) {
        foreach ($_POST['ticket_name'] as $index => $name) {
            $url = $_POST['ticket_url'][$index] ?? '';
            if (!empty($name) || !empty($url)) {
                $live_ticket_sales[] = [
                    'name' => $name,
                    'url' => $url
                ];
            }
        }
    }
    $live_sale_date = $_POST['live_sale_date'] ?? '';
    $live_contact = $_POST['live_contact'] ?? '';
    $live_contact_url = trim($_POST['live_contact_url'] ?? '');
    $live_other = $_POST['live_other'] ?? '';
    
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
            'title_url' => $title_url,
            'lead' => $lead,
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
        // LIVE記事の場合のみ追加フィールドを保存
        if ($category === 'LIVE') {
            $newNews['live_performers'] = $live_performers;
            $newNews['live_time'] = $live_time;
            $newNews['live_price'] = $live_price;
            $newNews['live_ticket_sales'] = $live_ticket_sales;
            $newNews['live_sale_date'] = $live_sale_date;
            $newNews['live_contact'] = $live_contact;
            $newNews['live_contact_url'] = $live_contact_url;
            $newNews['live_other'] = $live_other;
        }
        $newsData[] = $newNews;
        $msg = 'created';
    } else {
        // 編集
        $id = (int)$id;
        if (isset($newsData[$id])) {
            $newsData[$id]['title'] = $title;
            $newsData[$id]['title_url'] = $title_url;
            $newsData[$id]['lead'] = $lead;
            $newsData[$id]['category'] = $category;
            $newsData[$id]['date'] = $date;
            $newsData[$id]['content'] = $content;
            $newsData[$id]['published'] = $published;
            $newsData[$id]['image'] = $image_path;
            $newsData[$id]['youtube_id'] = $youtube_id;
            $newsData[$id]['youtube_url'] = $youtube_url;
            $newsData[$id]['updated_at'] = date('Y-m-d H:i:s');
            // LIVE記事の場合のみ追加フィールドを保存
            if ($category === 'LIVE') {
                $newsData[$id]['live_performers'] = $live_performers;
                $newsData[$id]['live_time'] = $live_time;
                $newsData[$id]['live_price'] = $live_price;
                $newsData[$id]['live_ticket_sales'] = $live_ticket_sales;
                $newsData[$id]['live_sale_date'] = $live_sale_date;
                $newsData[$id]['live_contact'] = $live_contact;
                $newsData[$id]['live_contact_url'] = $live_contact_url;
                $newsData[$id]['live_other'] = $live_other;
            } else {
                // LIVE以外のカテゴリに変更した場合は削除
                unset($newsData[$id]['live_performers']);
                unset($newsData[$id]['live_time']);
                unset($newsData[$id]['live_price']);
                unset($newsData[$id]['live_ticket_sales']);
                unset($newsData[$id]['live_sale_date']);
                unset($newsData[$id]['live_contact']);
                unset($newsData[$id]['live_contact_url']);
                unset($newsData[$id]['live_other']);
            }
        }
        $msg = 'updated';
    }
    
    saveNewsData($newsData);
    header('Location: /kanri/news/?msg=' . $msg);
    exit;
}

// デフォルト値
if (!$news) {
    $news = [
        'title' => '',
        'title_url' => '',
        'lead' => '',
        'category' => 'LIVE',
        'date' => date('Y/m/d'),
        'content' => '',
        'published' => true,
        'image' => '',
        'youtube_url' => '',
        'youtube_id' => null,
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?> - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
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
                    <input type="hidden" name="id" value="<?php echo (int)$_GET['id']; ?>">
                <?php endif; ?>
                
                <div class="form-layout">
                    <div class="form-main">
                        <div class="form-container">
                            <?php if ($error): ?>
                                <div class="error"><?php echo h($error); ?></div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="title">タイトル *</label>
                                <input type="text" id="title" name="title" value="<?php echo h($news['title']); ?>" required>
                                <div class="url-field-group">
                                    <label for="title_url">リンク先URL（任意）</label>
                                    <input type="url" id="title_url" name="title_url" value="<?php echo h($news['title_url'] ?? ''); ?>" placeholder="https://...">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="lead">リード文</label>
                                <input type="text" id="lead" name="lead" value="<?php echo h($news['lead'] ?? ''); ?>" placeholder="記事の要約や導入文を入力してください">
                            </div>
                    
                    <div class="form-group">
                        <label for="content">本文</label>
                        <textarea id="content" name="content" rows="3"><?php echo h($news['content']); ?></textarea>
                        <div class="help-text">HTMLタグを使用できます。改行は&lt;br&gt;タグを使用してください。</div>
                    </div>
                    
                    <!-- LIVE記事用のフィールド -->
                    <div id="live-fields" class="live-fields" style="display: none;">
                        <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; color: #333;">LIVE情報</h3>
                        
                        <div class="form-group">
                            <label for="live_performers">出演</label>
                            <textarea id="live_performers" name="live_performers" rows="3"><?php echo h($news['live_performers'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="live_time">開場 / 開演</label>
                            <input type="text" id="live_time" name="live_time" value="<?php echo h($news['live_time'] ?? ''); ?>" placeholder="19:00 / 19:30">
                        </div>
                        
                        <div class="form-group">
                            <label for="live_price">料金</label>
                            <input type="text" id="live_price" name="live_price" value="<?php echo h($news['live_price'] ?? ''); ?>" placeholder="前売¥5000+1drink / 当日¥5500+1drink">
                        </div>
                        
                        <div class="form-group">
                            <label>チケット発売先</label>
                            <div id="ticket-sales-container">
                                <?php
                                $ticket_sales = $news['live_ticket_sales'] ?? [];
                                if (empty($ticket_sales)) {
                                    $ticket_sales = [['name' => '', 'url' => '']];
                                }
                                foreach ($ticket_sales as $index => $ticket): ?>
                                    <div class="ticket-sale-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: flex-end;">
                                        <div style="flex: 1;">
                                            <input type="text" name="ticket_name[]" value="<?php echo h($ticket['name'] ?? ''); ?>" placeholder="e+" style="margin-bottom: 5px;">
                                            <input type="url" name="ticket_url[]" value="<?php echo h($ticket['url'] ?? ''); ?>" placeholder="https://...">
                                        </div>
                                        <button type="button" class="btn btn-secondary btn-small remove-ticket" style="margin-bottom: 0;">削除</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-ticket" class="btn btn-secondary btn-small">チケット発売先を追加</button>
                        </div>
                        
                        <div class="form-group">
                            <label for="live_sale_date">チケット発売日</label>
                            <input type="text" id="live_sale_date" name="live_sale_date" value="<?php echo h($news['live_sale_date'] ?? ''); ?>" placeholder="11月1日(土)10:00〜">
                        </div>
                        
                        <div class="form-group">
                            <label for="live_contact">お問い合わせ</label>
                            <input type="text" id="live_contact" name="live_contact" value="<?php echo h($news['live_contact'] ?? ''); ?>" placeholder="スターパインズカフェ">
                            <div class="url-field-group">
                                <label for="live_contact_url">リンク先URL（任意）</label>
                                <input type="url" id="live_contact_url" name="live_contact_url" value="<?php echo h($news['live_contact_url'] ?? ''); ?>" placeholder="https://...">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="live_other">その他のテキスト</label>
                            <textarea id="live_other" name="live_other" rows="3"><?php echo h($news['live_other'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; color: #333;">MEDIA</h3>
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
                        <?php if (!empty($news['youtube_id'])): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 5px;">
                                <p style="margin-bottom: 10px;">現在の動画:</p>
                                <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo h($news['youtube_id']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="max-width: 100%;"></iframe>
                            </div>
                        <?php endif; ?>
                    </div>
                        </div>
                    </div>
                    
                    <div class="form-sidebar">
                        <div class="sidebar-panel">
                            <h3>公開設定</h3>
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
                                <input type="date" id="date" name="date" value="<?php echo h(str_replace('/', '-', $news['date'])); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="published" name="published" <?php echo ($news['published'] ?? true) ? 'checked' : ''; ?>>
                                    <label for="published" style="margin: 0; font-weight: normal;">公開する</label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary"><?php echo $isEdit ? '更新' : '作成'; ?></button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='/kanri/news/'">キャンセル</button>
                            </div>
                        </div>
                    </div>
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
        
        // LIVEフィールドの表示/非表示
        const categorySelect = document.getElementById('category');
        const liveFields = document.getElementById('live-fields');
        
        function toggleLiveFields() {
            if (categorySelect.value === 'LIVE') {
                liveFields.style.display = 'block';
            } else {
                liveFields.style.display = 'none';
            }
        }
        
        // 初期表示
        toggleLiveFields();
        
        // カテゴリ変更時の処理
        categorySelect.addEventListener('change', toggleLiveFields);
        
        // チケット発売先の追加
        document.getElementById('add-ticket').addEventListener('click', function() {
            const container = document.getElementById('ticket-sales-container');
            const newItem = document.createElement('div');
            newItem.className = 'ticket-sale-item';
            newItem.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: flex-end;';
            newItem.innerHTML = `
                <div style="flex: 1;">
                    <input type="text" name="ticket_name[]" value="" placeholder="e+" style="margin-bottom: 5px;">
                    <input type="url" name="ticket_url[]" value="" placeholder="https://...">
                </div>
                <button type="button" class="btn btn-secondary btn-small remove-ticket" style="margin-bottom: 0;">削除</button>
            `;
            container.appendChild(newItem);
            
            // 削除ボタンのイベントリスナーを追加
            newItem.querySelector('.remove-ticket').addEventListener('click', function() {
                newItem.remove();
            });
        });
        
        // 既存の削除ボタンのイベントリスナー
        document.querySelectorAll('.remove-ticket').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.ticket-sale-item').remove();
            });
        });
    </script>
</body>
</html>

