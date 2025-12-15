<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

// ログアウト処理
if (isset($_GET['logout'])) {
    logout();
    header('Location: /kanri/index.php');
    exit;
}

$newsData = loadNewsData();
$categories = loadCategories();
$subcategories = loadSubcategories();
$message = '';

// フィルター処理
$filterCategory = $_GET['category'] ?? '';
$filterSubcategory = $_GET['subcategory'] ?? '';

// フィルター適用
if (!empty($filterCategory) || !empty($filterSubcategory)) {
    $filteredData = [];
    foreach ($newsData as $index => $news) {
        $matchCategory = empty($filterCategory) || ($news['category'] ?? '') === $filterCategory;
        $matchSubcategory = empty($filterSubcategory) || ($news['subcategory'] ?? '') === $filterSubcategory;
        
        if ($matchCategory && $matchSubcategory) {
            $filteredData[] = $news;
        }
    }
    $newsData = $filteredData;
}

// 並び替え処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    $order = json_decode($_POST['order'], true);
    if ($order && is_array($order)) {
        // 順序に基づいて記事を並び替え
        $reorderedData = [];
        foreach ($order as $id) {
            foreach ($newsData as $index => $news) {
                if (($news['id'] ?? $index) == $id) {
                    $reorderedData[] = $news;
                    break;
                }
            }
        }
        // 残りの記事を追加（順序に含まれていない場合）
        foreach ($newsData as $index => $news) {
            $id = $news['id'] ?? $index;
            if (!in_array($id, $order)) {
                $reorderedData[] = $news;
            }
        }
        saveNewsData($reorderedData);
        echo json_encode(['success' => true]);
        exit;
    }
}

// メッセージ表示
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    switch ($msg) {
        case 'created':
            $message = '<div class="success">記事を作成しました</div>';
            break;
        case 'updated':
            $message = '<div class="success">記事を更新しました</div>';
            break;
        case 'deleted':
            $message = '<div class="success">記事を削除しました</div>';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ニュース記事管理 - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>ニュース記事管理</h1>
            <div class="header-actions">
                <a href="/kanri/news/edit.php" class="btn btn-primary">新規記事作成</a>
            </div>
        </div>
        
        <div class="container">
            <?php echo $message; ?>
            
            <!-- フィルター -->
            <div class="filter-container" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                        <label for="category" style="display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; color: #333;">カテゴリ</label>
                        <select id="category" name="category" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                            <option value="">すべて</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo h($category); ?>" <?php echo $filterCategory === $category ? 'selected' : ''; ?>>
                                    <?php echo h($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                        <label for="subcategory" style="display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; color: #333;">サブカテゴリ</label>
                        <select id="subcategory" name="subcategory" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                            <option value="">すべて</option>
                            <?php foreach ($subcategories as $subcategory): ?>
                                <option value="<?php echo h($subcategory); ?>" <?php echo $filterSubcategory === $subcategory ? 'selected' : ''; ?>>
                                    <?php echo h($subcategory); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">絞り込み</button>
                        <?php if (!empty($filterCategory) || !empty($filterSubcategory)): ?>
                            <a href="/kanri/news/" class="btn btn-secondary" style="white-space: nowrap;">クリア</a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php if (!empty($filterCategory) || !empty($filterSubcategory)): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span style="font-size: 14px; color: #666;">フィルター:</span>
                            <?php if (!empty($filterCategory)): ?>
                                <span class="category-badge"><?php echo h($filterCategory); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($filterSubcategory)): ?>
                                <span class="subcategory-badge"><?php echo h($filterSubcategory); ?></span>
                            <?php endif; ?>
                            <span style="font-size: 14px; color: #666; margin-left: auto;">
                                表示件数: <strong><?php echo count($newsData); ?></strong>件
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="news-list" id="news-list">
                <?php if (empty($newsData)): ?>
                    <div class="empty-state">
                        <h2><?php echo (!empty($filterCategory) || !empty($filterSubcategory)) ? '該当する記事がありません' : '記事がありません'; ?></h2>
                        <p>
                            <?php if (!empty($filterCategory) || !empty($filterSubcategory)): ?>
                                フィルター条件を変更するか、<a href="/kanri/news/" style="color: #667eea;">フィルターをクリア</a>してください。
                            <?php else: ?>
                                新しい記事を作成してください
                            <?php endif; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($newsData as $index => $news): ?>
                        <?php
                        // 公開終了日の判定
                        $isExpired = false;
                        if (!empty($news['end_date'] ?? '')) {
                            $endDateStr = $news['end_date'];
                            // YYYY/MM/DD HH:MM形式をタイムスタンプに変換
                            $endDateStr = str_replace('/', '-', $endDateStr);
                            $endTimestamp = strtotime($endDateStr);
                            $currentTimestamp = time();
                            if ($endTimestamp !== false && $currentTimestamp > $endTimestamp) {
                                $isExpired = true;
                            }
                        }
                        ?>
                        <div class="news-item <?php echo $isExpired ? 'news-expired' : ''; ?>" data-id="<?php echo h($news['id'] ?? $index); ?>">
                            <div class="news-drag-handle" style="cursor: move; color: #999; user-select: none;">☰</div>
                            <div class="news-info">
                                <div class="news-title">
                                    <?php 
                                    // ライブ日時の表示（曜日付き）
                                    if (!empty($news['live_date'] ?? '')) {
                                        $liveDateStr = $news['live_date'];
                                        // YYYY/MM/DD形式から曜日を取得
                                        $liveDateFormatted = str_replace('/', '-', $liveDateStr);
                                        $timestamp = strtotime($liveDateFormatted);
                                        if ($timestamp !== false) {
                                            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                                            $weekday = $weekdays[date('w', $timestamp)];
                                            echo '<span style="color: #667eea; font-weight: 600; margin-right: 8px;">' . h($liveDateStr) . '(' . $weekday . ')</span>';
                                        } else {
                                            echo '<span style="color: #667eea; font-weight: 600; margin-right: 8px;">' . h($liveDateStr) . '</span>';
                                        }
                                    }
                                    ?>
                                    <?php echo h($news['title'] ?? ''); ?>
                                    <span class="badge <?php echo ($news['published'] ?? true) ? 'badge-published' : 'badge-draft'; ?>">
                                        <?php echo ($news['published'] ?? true) ? '公開中' : '下書き'; ?>
                                    </span>
                                    <?php if ($isExpired): ?>
                                        <span class="badge badge-expired" title="公開終了日を過ぎています">
                                            非公開
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="news-meta">
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 3px;">
                                        <span class="category-badge" title="<?php echo h($news['category'] ?? '未設定'); ?>">
                                            <?php echo h($news['category'] ?? '未設定'); ?>
                                        </span>
                                        <?php if (!empty($news['subcategory'] ?? '')): ?>
                                            <span class="subcategory-badge" title="<?php echo h($news['subcategory']); ?>">
                                                <?php echo h($news['subcategory']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12px; color: #888;">
                                        公開日: <?php echo h($news['date']); ?>
                                        <?php if (($news['category'] ?? '') === 'LIVE' && !empty($news['live_date'] ?? '')): ?>
                                            | ライブ日時: <?php echo h($news['live_date']); ?>
                                        <?php endif; ?>
                                        | ID: <?php echo h($news['id'] ?? $index); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="news-actions">
                                <a href="/kanri/news/edit.php?id=<?php echo $index; ?>" class="btn btn-edit btn-small">編集</a>
                                <a href="/kanri/news/delete.php?id=<?php echo $index; ?>" class="btn btn-delete btn-small" onclick="return confirm('本当に削除しますか？');">削除</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // 並び替え機能
        const newsList = document.getElementById('news-list');
        if (newsList) {
            const sortable = new Sortable(newsList, {
                handle: '.news-drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    // 並び替え後の順序を取得
                    const items = newsList.querySelectorAll('.news-item');
                    const order = Array.from(items).map(item => item.getAttribute('data-id'));
                    
                    // サーバーに送信
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=reorder&order=' + encodeURIComponent(JSON.stringify(order))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 成功メッセージを表示（オプション）
                            const message = document.createElement('div');
                            message.className = 'success';
                            message.textContent = '並び順を更新しました';
                            message.style.marginTop = '10px';
                            const container = document.querySelector('.container');
                            if (container) {
                                container.insertBefore(message, container.firstChild);
                                setTimeout(() => message.remove(), 3000);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
    </script>
</body>
</html>

