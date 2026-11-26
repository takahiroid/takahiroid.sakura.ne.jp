<?php
require_once dirname(__DIR__) . '/config.php';
requireLogin();

if (isset($_GET['logout'])) {
    logout();
    header('Location: /kanri/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder') {
    $order = json_decode($_POST['order'] ?? '', true);
    $newsData = loadNewsData();
    if (is_array($order)) {
        $byId = [];
        foreach ($newsData as $news) {
            $byId[(string)($news['id'] ?? '')] = $news;
        }
        $reordered = [];
        foreach ($order as $id) {
            $id = (string)$id;
            if (isset($byId[$id])) {
                $reordered[] = $byId[$id];
                unset($byId[$id]);
            }
        }
        foreach ($byId as $news) {
            $reordered[] = $news;
        }
        saveNewsData($reordered);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true]);
        exit;
    }
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => false]);
    exit;
}

$newsData = loadNewsData();
$categories = loadCategories();
$message = '';
if (isset($_GET['msg'])) {
    $messages = [
        'created' => '記事を作成しました',
        'updated' => '記事を更新しました',
        'deleted' => '記事を削除しました'
    ];
    if (isset($messages[$_GET['msg']])) {
        $message = $messages[$_GET['msg']];
    }
}

function adminTitle(array $news) {
    if (($news['category'] ?? '') === 'LIVE') {
        $parts = [];
        if (!empty($news['live_date'])) {
            $ts = strtotime(str_replace('/', '-', $news['live_date']));
            if ($ts !== false) {
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                $parts[] = date('Y/n/j', $ts) . '(' . $weekdays[date('w', $ts)] . ')';
            } else {
                $parts[] = $news['live_date'];
            }
        }
        if (!empty($news['live_venue'])) {
            $parts[] = $news['live_venue'];
        }
        $title = $news['title'] !== '' ? $news['title'] : ($news['lead'] ?? '');
        $title = trim(preg_replace('/\s+/u', ' ', str_replace(['<br>', '<br/>', '<br />'], ' ', $title)));
        if ($title !== '') {
            $parts[] = '「' . $title . '」';
        }
        return implode(' ', $parts);
    }
    $text = $news['lead'] ?? '';
    if ($text === '') {
        $text = $news['title'] ?? '';
    }
    return trim(str_replace(['<br>', '<br/>', '<br />'], ' ', $text));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>ライブ・ニュース - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1>ライブ・ニュース</h1>
            <div class="header-actions">
                <a href="/kanri/news/edit.php" class="btn btn-primary">新規記事作成</a>
            </div>
        </div>
        <div class="container">
            <?php if ($message): ?><div class="success"><?php echo h($message); ?></div><?php endif; ?>
            <div class="news-list" id="news-list">
                <?php if ($newsData === []): ?>
                    <div class="empty-state">
                        <h2>記事がありません</h2>
                        <p>新しい記事を作成してください</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($newsData as $news):
                        $id = (string)($news['id'] ?? '');
                        $isExpired = false;
                        if (!empty($news['end_date'])) {
                            $endTs = strtotime(str_replace('/', '-', $news['end_date']));
                            $isExpired = $endTs !== false && time() > $endTs;
                        }
                        $isDraft = !($news['published'] ?? false);
                        $classes = ['news-item'];
                        if ($isExpired) $classes[] = 'news-expired';
                        if ($isDraft) $classes[] = 'news-draft';
                        $categoryName = $news['category'] ?? '未設定';
                        $categoryColor = '#D95D67';
                        foreach ($categories as $category) {
                            $name = is_array($category) ? ($category['name'] ?? '') : $category;
                            if ($name === $categoryName && is_array($category)) {
                                $categoryColor = $category['color'] ?? $categoryColor;
                            }
                        }
                    ?>
                        <div class="<?php echo h(implode(' ', $classes)); ?>" data-id="<?php echo h($id); ?>">
                            <div class="news-drag-handle">☰</div>
                            <div class="news-info">
                                <div class="news-title">
                                    <?php echo h(adminTitle($news)); ?>
                                    <?php if (!$isExpired): ?>
                                        <span class="badge <?php echo $isDraft ? 'badge-draft' : 'badge-published'; ?>">
                                            <?php echo $isDraft ? '下書き' : '公開中'; ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($isExpired): ?><span class="badge badge-expired">公開終了</span><?php endif; ?>
                                    <?php if (!empty($news['show_on_top'])): ?><span class="badge badge-top">TOP</span><?php endif; ?>
                                </div>
                                <div class="news-meta">
                                    <span class="category-badge" style="background: <?php echo h($categoryColor); ?>;"><?php echo h($categoryName); ?></span>
                                </div>
                            </div>
                            <div class="news-actions">
                                <a href="/kanri/news/edit.php?id=<?php echo rawurlencode($id); ?>" class="btn btn-edit btn-small">編集</a>
                                <a href="/kanri/news/delete.php?id=<?php echo rawurlencode($id); ?>" class="btn btn-delete btn-small" onclick="return confirm('本当に削除しますか？');">削除</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        var newsList = document.getElementById('news-list');
        if (newsList && newsList.querySelector('.news-item')) {
            new Sortable(newsList, {
                handle: '.news-drag-handle',
                animation: 150,
                onEnd: function () {
                    var order = Array.from(newsList.querySelectorAll('.news-item')).map(function (item) {
                        return item.getAttribute('data-id');
                    });
                    var body = new URLSearchParams();
                    body.set('action', 'reorder');
                    body.set('order', JSON.stringify(order));
                    fetch(window.location.pathname, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    }).then(function (response) { return response.json(); }).then(function (data) {
                        if (!data.success) return;
                        var message = document.createElement('div');
                        message.className = 'success';
                        message.textContent = '並び順を更新しました';
                        var container = document.querySelector('.container');
                        container.insertBefore(message, container.firstChild);
                        setTimeout(function () { message.remove(); }, 3000);
                    });
                }
            });
        }
    </script>
</body>
</html>
