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
$message = '';

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
            
            <div class="news-list">
                <?php if (empty($newsData)): ?>
                    <div class="empty-state">
                        <h2>記事がありません</h2>
                        <p>新しい記事を作成してください</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($newsData as $index => $news): ?>
                        <div class="news-item">
                            <div class="news-info">
                                <div class="news-title">
                                    <?php echo h($news['title']); ?>
                                    <span class="badge <?php echo ($news['published'] ?? true) ? 'badge-published' : 'badge-draft'; ?>">
                                        <?php echo ($news['published'] ?? true) ? '公開中' : '下書き'; ?>
                                    </span>
                                </div>
                                <div class="news-meta">
                                    公開日: <?php echo h($news['date']); ?> | 
                                    カテゴリ: <?php echo h($news['category'] ?? '未設定'); ?> |
                                    ID: <?php echo h($news['id'] ?? $index); ?>
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
</body>
</html>

