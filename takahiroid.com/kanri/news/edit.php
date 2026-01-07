<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$newsData = loadNewsData();
$categories = loadCategories();
$subcategories = loadSubcategories();
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
    $lead = trim($_POST['lead'] ?? '');
    $category = $_POST['category'] ?? '';
    $subcategory = trim($_POST['subcategory'] ?? '');
    $date_input = $_POST['date'] ?? date('Y-m-d');
    $date_time = $_POST['date_time'] ?? '00';
    // YYYY-MM-DD形式をYYYY/MM/DD形式に変換し、時間を追加
    $date = str_replace('-', '/', $date_input) . ' ' . sprintf('%02d', (int)$date_time) . ':00';
    $end_date_input = $_POST['end_date'] ?? '';
    $end_date_time = $_POST['end_date_time'] ?? '';
    if ($end_date_input) {
        $end_date = str_replace('-', '/', $end_date_input) . ' ' . sprintf('%02d', (int)$end_date_time) . ':00';
    } else {
        $end_date = '';
    }
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? true : false;
    $show_on_top = isset($_POST['show_on_top']) ? true : false;
    $id = $_POST['id'] ?? null;
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $existing_image = $_POST['existing_image'] ?? '';
    $delete_image = isset($_POST['delete_image']) ? true : false;
    
    // LIVE記事用のフィールド
    $live_date_input = $_POST['live_date'] ?? '';
    $live_date = $live_date_input ? str_replace('-', '/', $live_date_input) : '';
    $live_time = $_POST['live_time'] ?? '';
    $live_venue = $_POST['live_venue'] ?? '';
    $live_performers = $_POST['live_performers'] ?? '';
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
    
    // バリデーション
    if ($lead === '') {
        $error = 'リード文は必須です';
    }
    
    if (empty($error)) {
        // 新規作成
        if ($id === null || $id === '') {
            $newNews = [
                'id' => uniqid(),
                'title' => $title,
                'title_url' => $title_url,
                'lead' => $lead,
                'category' => $category,
                'subcategory' => $subcategory,
                'date' => $date,
                'end_date' => $end_date,
                'content' => $content,
                'published' => $published,
                'show_on_top' => $show_on_top,
                'image' => $image_path,
                'youtube_id' => $youtube_id,
                'youtube_url' => $youtube_url,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            // LIVE記事の場合のみ追加フィールドを保存
            if ($category === 'LIVE') {
                $newNews['live_date'] = $live_date;
                $newNews['live_venue'] = $live_venue;
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
                $newsData[$id]['subcategory'] = $subcategory;
                $newsData[$id]['date'] = $date;
                $newsData[$id]['end_date'] = $end_date;
                $newsData[$id]['content'] = $content;
                $newsData[$id]['published'] = $published;
                $newsData[$id]['show_on_top'] = $show_on_top;
                $newsData[$id]['image'] = $image_path;
                $newsData[$id]['youtube_id'] = $youtube_id;
                $newsData[$id]['youtube_url'] = $youtube_url;
                $newsData[$id]['updated_at'] = date('Y-m-d H:i:s');
                // LIVE記事の場合のみ追加フィールドを保存
                if ($category === 'LIVE') {
                    $newsData[$id]['live_date'] = $live_date;
                    $newsData[$id]['live_venue'] = $live_venue;
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
                    unset($newsData[$id]['live_date']);
                    unset($newsData[$id]['live_venue']);
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
    } else {
        // 入力値を維持して再表示
        $news = [
            'title' => $title,
            'title_url' => $title_url,
            'lead' => $lead,
            'category' => $category,
            'subcategory' => $subcategory,
            'date' => $date,
            'end_date' => $end_date,
            'content' => $content,
            'published' => $published,
            'show_on_top' => $show_on_top,
            'image' => $image_path,
            'youtube_id' => $youtube_id,
            'youtube_url' => $youtube_url,
            'live_date' => $live_date,
            'live_venue' => $live_venue,
            'live_performers' => $live_performers,
            'live_time' => $live_time,
            'live_price' => $live_price,
            'live_ticket_sales' => $live_ticket_sales,
            'live_sale_date' => $live_sale_date,
            'live_contact' => $live_contact,
            'live_contact_url' => $live_contact_url,
            'live_other' => $live_other
        ];
    }
}

// デフォルト値
if (!$news) {
    $news = [
        'title' => '',
        'title_url' => '',
        'lead' => '',
        'category' => 'LIVE',
        'subcategory' => '',
        'date' => date('Y/m/d H:i'),
        'end_date' => '',
        'content' => '',
        'published' => false,
        'show_on_top' => false,
        'image' => '',
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? '記事編集' : '新規記事作成'; ?> - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <script src="https://cdn.tiny.cloud/1/x532xptb4sf4peadechzdffgx3n0y9uvfdt7tlu72pg98vw6/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
                                <label for="lead">リード文 <span style="color: #e11d48;">*</span></label>
                                <input type="text" id="lead" name="lead" value="<?php echo h($news['lead'] ?? ''); ?>" placeholder="記事の要約や導入文を入力してください" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">イベントタイトル</label>
                                <input type="text" id="title" name="title" value="<?php echo h($news['title']); ?>">
                                <div class="url-field-group">
                                    <label for="title_url">リンク先URL（任意）</label>
                                    <input type="url" id="title_url" name="title_url" value="<?php echo h($news['title_url'] ?? ''); ?>" placeholder="https://...">
                                </div>
                            </div>
                    
                    <div class="form-group">
                        <label for="content">本文</label>
                        <textarea id="content" name="content"><?php echo h($news['content']); ?></textarea>
                    </div>
                    
                    <!-- LIVE記事用のフィールド -->
                    <div id="live-fields" class="live-fields" style="display: none;">
                        <h3 style="margin: 30px 0 20px; padding-bottom: 10px; border-bottom: 2px solid #667eea; color: #333;">LIVE情報</h3>
                        
                        <div class="form-group">
                            <label for="live_date">ライブ日時 <span style="color: #e11d48;">*</span></label>
                            <input type="date" id="live_date" name="live_date" value="<?php echo h(!empty($news['live_date'] ?? '') ? str_replace('/', '-', $news['live_date']) : ''); ?>">
                            <div id="live_date_weekday" style="margin-top: 5px; font-size: 13px; color: #666;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="live_venue">会場</label>
                            <input type="text" id="live_venue" name="live_venue" value="<?php echo h($news['live_venue'] ?? ''); ?>" placeholder="会場名を入力してください">
                        </div>
                        
                        <div class="form-group">
                            <label for="live_performers">出演</label>
                            <textarea id="live_performers" name="live_performers" rows="3"><?php echo h($news['live_performers'] ?? ''); ?></textarea>
                            <div class="help-text">（例）■ 出演：</div>
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
                                <label for="category">カテゴリ <span style="color: #e11d48;">*</span></label>
                                <select id="category" name="category" required>
                                    <?php foreach ($categories as $category): 
                                        $catName = is_array($category) ? ($category['name'] ?? '') : $category;
                                    ?>
                                        <option value="<?php echo h($catName); ?>" <?php echo (isset($news['category']) && $news['category'] === $catName) ? 'selected' : ''; ?>><?php echo h($catName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div style="margin-top: 5px;">
                                    <a href="/kanri/categories.php" target="_blank" style="font-size: 12px; color: #667eea; text-decoration: none;">カテゴリを管理</a>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subcategory">サブカテゴリ</label>
                                <select id="subcategory" name="subcategory">
                                    <option value="">選択なし</option>
                                    <?php foreach ($subcategories as $subcategory): ?>
                                        <option value="<?php echo h($subcategory); ?>" <?php echo (isset($news['subcategory']) && $news['subcategory'] === $subcategory) ? 'selected' : ''; ?>><?php echo h($subcategory); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="date">公開日 <span style="color: #e11d48;">*</span></label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="date" id="date" name="date" value="<?php 
                                        $dateValue = $news['date'] ?? date('Y/m/d H:i');
                                        if (strpos($dateValue, ' ') !== false) {
                                            list($date_part, $time_part) = explode(' ', $dateValue);
                                            echo h(str_replace('/', '-', $date_part));
                                        } else {
                                            echo h(str_replace('/', '-', $dateValue));
                                        }
                                    ?>" required style="flex: 1;">
                                    <span style="color: #666;">時</span>
                                    <input type="number" id="date_time" name="date_time" min="0" max="23" value="<?php 
                                        $dateValue = $news['date'] ?? date('Y/m/d H:i');
                                        if (strpos($dateValue, ' ') !== false) {
                                            list($date_part, $time_part) = explode(' ', $dateValue);
                                            if (strpos($time_part, ':') !== false) {
                                                list($hour, $min) = explode(':', $time_part);
                                                echo h((int)$hour);
                                            } else {
                                                echo '0';
                                            }
                                        } else {
                                            echo '0';
                                        }
                                    ?>" style="width: 60px; padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 14px; background-color: #f0f8ff;">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">公開終了日（任意）</label>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="date" id="end_date" name="end_date" value="<?php 
                                        if (!empty($news['end_date'])) {
                                            $endDateValue = $news['end_date'];
                                            if (strpos($endDateValue, ' ') !== false) {
                                                list($date_part, $time_part) = explode(' ', $endDateValue);
                                                echo h(str_replace('/', '-', $date_part));
                                            } else {
                                                echo h(str_replace('/', '-', $endDateValue));
                                            }
                                        }
                                    ?>" style="flex: 1;">
                                    <span style="color: #666;">時</span>
                                    <input type="number" id="end_date_time" name="end_date_time" min="0" max="23" value="<?php 
                                        if (!empty($news['end_date'])) {
                                            $endDateValue = $news['end_date'];
                                            if (strpos($endDateValue, ' ') !== false) {
                                                list($date_part, $time_part) = explode(' ', $endDateValue);
                                                if (strpos($time_part, ':') !== false) {
                                                    list($hour, $min) = explode(':', $time_part);
                                                    echo h((int)$hour);
                                                } else {
                                                    echo '0';
                                                }
                                            } else {
                                                echo '0';
                                            }
                                        }
                                    ?>" style="width: 60px; padding: 10px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 14px; background-color: #f0f8ff;">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="published" name="published" <?php echo ($news['published'] ?? false) ? 'checked' : ''; ?>>
                                    <label for="published" style="margin: 0; font-weight: normal;">公開する</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="show_on_top" name="show_on_top" <?php echo ($news['show_on_top'] ?? false) ? 'checked' : ''; ?>>
                                    <label for="show_on_top" style="margin: 0; font-weight: normal;">TOPページに表示する</label>
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
        // TinyMCE初期化
        tinymce.init({
            selector: '#content',
            language: 'ja',
            height: 400,
            menubar: false,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic forecolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'link | removeformat | help',
            link_assume_external_targets: true,
            link_default_target: '_blank',
            link_title: false,
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }'
        });
        
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
            const liveDateInput = document.getElementById('live_date');
            if (categorySelect.value === 'LIVE') {
                liveFields.style.display = 'block';
                if (liveDateInput) {
                    liveDateInput.setAttribute('required', 'required');
                }
            } else {
                liveFields.style.display = 'none';
                if (liveDateInput) {
                    liveDateInput.removeAttribute('required');
                }
            }
        }
        
        // 初期表示
        toggleLiveFields();
        
        // カテゴリ変更時の処理
        categorySelect.addEventListener('change', toggleLiveFields);
        
        // ライブ日時の曜日表示
        const liveDateInput = document.getElementById('live_date');
        const liveDateWeekday = document.getElementById('live_date_weekday');
        
        function updateLiveDateWeekday() {
            if (liveDateInput && liveDateWeekday && liveDateInput.value) {
                const date = new Date(liveDateInput.value + 'T00:00:00');
                const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                const weekday = weekdays[date.getDay()];
                const year = date.getFullYear();
                const month = date.getMonth() + 1;
                const day = date.getDate();
                liveDateWeekday.textContent = year + '年' + month + '月' + day + '日(' + weekday + ')';
                liveDateWeekday.style.fontWeight = '500';
                liveDateWeekday.style.color = '#333';
            } else if (liveDateWeekday) {
                liveDateWeekday.textContent = '';
            }
        }
        
        if (liveDateInput) {
            liveDateInput.addEventListener('change', updateLiveDateWeekday);
            // 初期表示時にも曜日を表示
            updateLiveDateWeekday();
        }
        
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

