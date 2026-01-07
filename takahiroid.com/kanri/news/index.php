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
                            <?php foreach ($categories as $category): 
                                $catName = is_array($category) ? ($category['name'] ?? '') : $category;
                            ?>
                                <option value="<?php echo h($catName); ?>" <?php echo $filterCategory === $catName ? 'selected' : ''; ?>>
                                    <?php echo h($catName); ?>
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
                        // 下書きの判定
                        $isDraft = !($news['published'] ?? true);
                        // クラスの組み立て
                        $itemClasses = [];
                        if ($isExpired) {
                            $itemClasses[] = 'news-expired';
                        }
                        if ($isDraft) {
                            $itemClasses[] = 'news-draft';
                        }
                        ?>
                        <div class="news-item <?php echo implode(' ', $itemClasses); ?>" data-id="<?php echo h($news['id'] ?? $index); ?>">
                            <div class="news-drag-handle" style="cursor: move; color: #999; user-select: none;">☰</div>
                            <div class="news-info">
                                <div class="news-title">
                                    <?php
                                        $isLive = ($news['category'] ?? '') === 'LIVE';
                                        if ($isLive) {
                                            // ライブ用タイトル: ライブ日時 + 会場 + 「タイトル」
                                            $liveParts = [];
                                            if (!empty($news['live_date'] ?? '')) {
                                                $liveDateStr = $news['live_date'];
                                                $liveDateFormatted = str_replace('/', '-', $liveDateStr);
                                                $timestamp = strtotime($liveDateFormatted);
                                                if ($timestamp !== false) {
                                                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                                                    $weekday = $weekdays[date('w', $timestamp)];
                                                    $liveParts[] = '<span style="color: #667eea;">' . h($liveDateStr . '(' . $weekday . ')') . '</span>';
                                                } else {
                                                    $liveParts[] = '<span style="color: #667eea;">' . h($liveDateStr) . '</span>';
                                                }
                                            }
                                            if (!empty($news['live_venue'] ?? '')) {
                                                // 日付の直後はスラッシュを入れずスペースで接続
                                                if (!empty($liveParts)) {
                                                    $liveParts[] = h($news['live_venue']);
                                                } else {
                                                    $liveParts[] = h($news['live_venue']);
                                                }
                                            }
                                            $liveMainTitle = $news['live_title'] ?? '';
                                            if (empty($liveMainTitle)) {
                                                $liveMainTitle = $news['title'] ?? '';
                                            }
                                            $titlePart = '「' . h($liveMainTitle) . '」';
                                            
                                            // 日付後はスラッシュを入れないで連結し、その後タイトルはスラッシュで区切る
                                            $lineHead = '';
                                            if (!empty($liveParts)) {
                                                // 最初の要素は日付（青字）になる想定
                                                $lineHead = $liveParts[0];
                                                if (isset($liveParts[1])) {
                                                    $lineHead .= ' ' . $liveParts[1];
                                                }
                                            }
                                            
                                            if ($lineHead !== '') {
                                                // 会場の後もスラッシュなしでタイトルを続ける
                                                echo $lineHead . ' ' . $titlePart;
                                            } else {
                                                // 日付・会場が無ければタイトルのみ
                                                echo $titlePart;
                                            }
                                        } else {
                                            // その他: リード文のみ表示（なければタイトル）
                                            $displayText = $news['lead'] ?? '';
                                            if ($displayText === '') {
                                                $displayText = $news['title'] ?? '';
                                            }
                                            echo h($displayText);
                                        }
                                    ?>
                                    <?php if (!($isExpired && ($news['published'] ?? true))): ?>
                                        <span class="badge <?php echo ($news['published'] ?? true) ? 'badge-published' : 'badge-draft'; ?>">
                                            <?php echo ($news['published'] ?? true) ? '公開中' : '下書き'; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($isExpired): ?>
                                        <span class="badge badge-expired" title="公開終了日を過ぎています">
                                        公開終了
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="news-meta">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 12px; color: #888;">
                                        <?php
                                            // カテゴリ管理で設定したカラーを反映
                                            $categoryNameForBadge = $news['category'] ?? '未設定';
                                            $categoryColorForBadge = '#fb923c'; // デフォルト（既存色を踏襲）
                                            foreach ($categories as $cat) {
                                                $catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
                                                if ($catName === $categoryNameForBadge && is_array($cat)) {
                                                    $categoryColorForBadge = $cat['color'] ?? $categoryColorForBadge;
                                                    break;
                                                }
                                            }
                                        ?>
                                        <span class="category-badge" style="background: <?php echo h($categoryColorForBadge); ?>; border-color: <?php echo h($categoryColorForBadge); ?>; color: #fff;" title="<?php echo h($news['category'] ?? '未設定'); ?>">
                                            <?php echo h($news['category'] ?? '未設定'); ?>
                                        </span>
                                        <?php if (!empty($news['subcategory'] ?? '')): ?>
                                            <span class="subcategory-badge" style="background: #facc15; border-color: #eab308; color: #7c2d12;" title="<?php echo h($news['subcategory']); ?>">
                                                <?php echo h($news['subcategory']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span>ID: <?php echo h($news['id'] ?? $index); ?></span>
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

