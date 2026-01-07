<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$discographyData = loadDiscographyData();
$discographyCategories = loadDiscographyCategories();
$message = '';

// フィルター処理
$filterCategory = $_GET['category'] ?? '';

// フィルター適用
if (!empty($filterCategory)) {
    $filteredData = [];
    foreach ($discographyData as $index => $disc) {
        if (($disc['category'] ?? '') === $filterCategory) {
            $filteredData[] = $disc;
        }
    }
    $discographyData = $filteredData;
}

// 並び替え処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reorder') {
    $order = json_decode($_POST['order'], true);
    if ($order && is_array($order)) {
        $reorderedData = [];
        foreach ($order as $id) {
            foreach ($discographyData as $item) {
                if (($item['id'] ?? 0) == $id) {
                    $reorderedData[] = $item;
                    break;
                }
            }
        }
        // 残りのアイテムを追加
        foreach ($discographyData as $item) {
            $id = $item['id'] ?? 0;
            if (!in_array($id, $order)) {
                $reorderedData[] = $item;
            }
        }
        saveDiscographyData($reorderedData);
        echo json_encode(['success' => true]);
        exit;
    }
}

// メッセージ表示
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    switch ($msg) {
        case 'created':
            $message = '<div class="success">ディスコグラフィーを作成しました</div>';
            break;
        case 'updated':
            $message = '<div class="success">ディスコグラフィーを更新しました</div>';
            break;
        case 'deleted':
            $message = '<div class="success">ディスコグラフィーを削除しました</div>';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ディスコグラフィー管理 - TAKAHIROID.COM</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .disc-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            gap: 15px;
            background: white;
        }
        .disc-item:last-child {
            border-bottom: none;
        }
        .disc-item:hover {
            background: #f9f9f9;
        }
        .disc-item.sortable-ghost {
            opacity: 0.4;
        }
        .disc-drag-handle {
            cursor: move;
            color: #999;
            user-select: none;
            font-size: 16px;
            padding: 6px;
        }
        .disc-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            background: #f0f0f0;
            flex-shrink: 0;
        }
        .disc-image-placeholder {
            width: 80px;
            height: 80px;
            background: #e0e0e0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 11px;
            flex-shrink: 0;
        }
        .disc-info {
            flex: 1;
            min-width: 0;
        }
        .disc-title {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .disc-subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }
        .disc-meta {
            font-size: 12px;
            color: #888;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .disc-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        .disc-item.disc-draft {
            background: #f9f9f9;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>ディスコグラフィー管理</h1>
            <div class="header-actions">
                <a href="/kanri/discography/edit.php" class="btn btn-primary">新規作成</a>
                <a href="/discography/" class="btn btn-secondary" target="_blank">公開ページを見る</a>
            </div>
        </div>
        
        <div class="container">
            <?php echo $message; ?>
            
            <!-- フィルター -->
            <?php if (count($discographyCategories) > 0): ?>
                <div class="filter-container" style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                            <label for="category" style="display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; color: #333;">カテゴリ（アーティスト名）</label>
                            <select id="category" name="category" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                                <option value="">すべて</option>
                                <?php foreach ($discographyCategories as $category): 
                                    $catName = is_array($category) ? ($category['name'] ?? '') : $category;
                                ?>
                                    <option value="<?php echo h($catName); ?>" <?php echo $filterCategory === $catName ? 'selected' : ''; ?>>
                                        <?php echo h($catName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">絞り込み</button>
                            <?php if (!empty($filterCategory)): ?>
                                <a href="/kanri/discography/" class="btn btn-secondary" style="white-space: nowrap;">クリア</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="news-list" id="disc-list">
                <?php if (empty($discographyData)): ?>
                    <div class="empty-state">
                        <h2>ディスコグラフィーがありません</h2>
                        <p>新しいディスコグラフィーを作成してください</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($discographyData as $index => $disc): ?>
                        <?php
                        $isDraft = !($disc['published'] ?? true);
                        ?>
                        <div class="disc-item <?php echo $isDraft ? 'disc-draft' : ''; ?>" data-id="<?php echo h($disc['id'] ?? $index); ?>">
                            <div class="disc-drag-handle">☰</div>
                            <?php if (!empty($disc['image'])): ?>
                                <img src="<?php echo h($disc['image']); ?>" alt="" class="disc-image">
                            <?php else: ?>
                                <div class="disc-image-placeholder">No Image</div>
                            <?php endif; ?>
                            <div class="disc-info">
                                <div class="disc-title">
                                    <?php echo h($disc['title'] ?? '無題'); ?>
                                    <span class="badge <?php echo ($disc['published'] ?? true) ? 'badge-published' : 'badge-draft'; ?>">
                                        <?php echo ($disc['published'] ?? true) ? '公開中' : '下書き'; ?>
                                    </span>
                                </div>
                                <div class="disc-subtitle"><?php echo h($disc['subtitle'] ?? ''); ?></div>
                                <div class="disc-meta">
                                    <?php if (!empty($disc['category'])): 
                                        $categoryColor = getDiscographyCategoryColor($disc['category']);
                                    ?>
                                        <span class="category-badge" style="background: <?php echo h($categoryColor); ?>; border-color: <?php echo h($categoryColor); ?>; color: #fff;" title="<?php echo h($disc['category']); ?>">
                                            <?php echo h($disc['category']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span>📅 <?php echo h($disc['release_date'] ?? '-'); ?></span>
                                    <span>💿 <?php echo h($disc['release_type'] ?? '-'); ?></span>
                                    <?php if (!empty($disc['price'])): ?>
                                        <span>¥<?php echo h($disc['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="disc-actions">
                                <a href="/kanri/discography/edit.php?id=<?php echo $index; ?>" class="btn btn-edit btn-small">編集</a>
                                <a href="/kanri/discography/delete.php?id=<?php echo $index; ?>" class="btn btn-delete btn-small" onclick="return confirm('本当に削除しますか？');">削除</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        const discList = document.getElementById('disc-list');
        if (discList && discList.querySelectorAll('.disc-item').length > 0) {
            const sortable = new Sortable(discList, {
                handle: '.disc-drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    const items = discList.querySelectorAll('.disc-item');
                    const order = Array.from(items).map(item => item.getAttribute('data-id'));
                    
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
                    .catch(error => console.error('Error:', error));
                }
            });
        }
    </script>
</body>
</html>

