<?php
require_once 'config.php';
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
    <title>管理画面 - ニュース記事管理 - TAKAHIROID.COM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        h1 {
            font-size: 24px;
            color: #333;
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .news-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .news-item {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .news-item:last-child {
            border-bottom: none;
        }
        .news-item:hover {
            background: #f9f9f9;
        }
        .news-info {
            flex: 1;
        }
        .news-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .news-meta {
            font-size: 14px;
            color: #666;
        }
        .news-actions {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .badge-published {
            background: #d4edda;
            color: #155724;
        }
        .badge-draft {
            background: #fff3cd;
            color: #856404;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>ニュース記事管理</h1>
            <div class="header-actions">
                <a href="/kanri/edit.php" class="btn btn-primary">新規記事作成</a>
                <a href="/kanri/dashboard.php?logout=1" class="btn btn-secondary">ログアウト</a>
            </div>
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
                            <a href="/kanri/edit.php?id=<?php echo $index; ?>" class="btn btn-edit btn-small">編集</a>
                            <a href="/kanri/delete.php?id=<?php echo $index; ?>" class="btn btn-delete btn-small" onclick="return confirm('本当に削除しますか？');">削除</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

