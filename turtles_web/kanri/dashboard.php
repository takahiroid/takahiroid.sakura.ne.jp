<?php
require_once 'config.php';
requireLogin();

if (isset($_GET['logout'])) {
    logout();
    header('Location: /kanri/index.php');
    exit;
}

$newsData = loadNewsData();
$publishedCount = 0;
$draftCount = 0;
foreach ($newsData as $news) {
    if ($news['published'] ?? false) {
        $publishedCount++;
    } else {
        $draftCount++;
    }
}
$discographyData = loadDiscographyData();
$discPublishedCount = 0;
foreach ($discographyData as $disc) {
    if ($disc['published'] ?? false) {
        $discPublishedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>ダッシュボード - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1>ダッシュボード</h1>
        </div>
        <div class="container">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>ライブ・ニュース</h3>
                    <div class="stat"><?php echo count($newsData); ?></div>
                    <div class="description">総記事数</div>
                    <a href="/kanri/news/" class="action-link">記事一覧を見る →</a>
                </div>
                <div class="dashboard-card">
                    <h3>公開中</h3>
                    <div class="stat"><?php echo $publishedCount; ?></div>
                    <div class="description">公開中の記事</div>
                </div>
                <div class="dashboard-card">
                    <h3>下書き</h3>
                    <div class="stat"><?php echo $draftCount; ?></div>
                    <div class="description">下書きの記事</div>
                </div>
                <div class="dashboard-card">
                    <h3>ディスコグラフィー</h3>
                    <div class="stat"><?php echo count($discographyData); ?></div>
                    <div class="description">公開中 <?php echo $discPublishedCount; ?>件</div>
                    <a href="/kanri/discography/" class="action-link">作品一覧を見る →</a>
                </div>
            </div>
            <div style="margin-top: 30px;">
                <div class="dashboard-card">
                    <h3>クイックアクション</h3>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="/kanri/news/edit.php" class="btn btn-primary">新規記事作成</a>
                        <a href="/kanri/discography/edit.php" class="btn btn-primary">新規作品作成</a>
                        <a href="/disc.php" class="btn btn-secondary" target="_blank" rel="noopener noreferrer">ディスコグラフィーを見る</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
