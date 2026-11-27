<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__, 2) . '/inc/disc_lib.php';
requireLogin();

$discographyData = sortDiscography(loadDiscographyData());
$message = '';
if (isset($_GET['msg'])) {
    $messages = [
        'created' => '作品を作成しました',
        'updated' => '作品を更新しました',
        'deleted' => '作品を削除しました'
    ];
    if (isset($messages[$_GET['msg']])) {
        $message = $messages[$_GET['msg']];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>ディスコグラフィー - タートルズ</title>
    <link rel="stylesheet" href="/kanri/common.css">
    <style>
        .disc-thumb,
        .disc-thumb-empty {
            width: 64px;
            height: 64px;
            flex: none;
            border-radius: 4px;
            background: #f3f3f3;
            object-fit: contain;
        }
        .disc-thumb-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 10px;
        }
        .news-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include dirname(__DIR__) . '/sidebar.php'; ?>
    <div class="main-content">
        <div class="header">
            <h1>ディスコグラフィー</h1>
            <div class="header-actions">
                <a href="/kanri/discography/edit.php" class="btn btn-primary">新規作成</a>
                <a href="/disc.php" class="btn btn-secondary" target="_blank" rel="noopener noreferrer">公開ページを見る</a>
            </div>
        </div>
        <div class="container">
            <?php if ($message): ?><div class="success"><?php echo h($message); ?></div><?php endif; ?>
            <div class="news-list">
                <?php if ($discographyData === []): ?>
                    <div class="empty-state">
                        <h2>作品がありません</h2>
                        <p>新しい作品を作成してください</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($discographyData as $disc):
                        $id = (string)($disc['id'] ?? '');
                        $isDraft = empty($disc['published']);
                        $image = discImageSrc($disc['image'] ?? '');
                    ?>
                        <div class="news-item<?php echo $isDraft ? ' news-draft' : ''; ?>">
                            <?php if ($image !== ''): ?>
                                <img class="disc-thumb" src="<?php echo h($image); ?>" alt="">
                            <?php else: ?>
                                <div class="disc-thumb-empty">No Image</div>
                            <?php endif; ?>
                            <div class="news-info">
                                <div class="news-title">
                                    <?php echo h($disc['title'] ?? '無題'); ?>
                                    <span class="badge <?php echo $isDraft ? 'badge-draft' : 'badge-published'; ?>">
                                        <?php echo $isDraft ? '下書き' : '公開中'; ?>
                                    </span>
                                </div>
                                <div class="news-meta">
                                    <?php if (!empty($disc['subtitle'])): ?><span><?php echo h($disc['subtitle']); ?></span><?php endif; ?>
                                    <span><?php echo h($disc['release_date'] ?? '-'); ?></span>
                                    <span><?php echo h($disc['release_type'] ?? '-'); ?></span>
                                    <?php if (!empty($disc['price'])): ?><span>¥<?php echo h($disc['price']); ?></span><?php endif; ?>
                                </div>
                            </div>
                            <div class="news-actions">
                                <a href="/kanri/discography/edit.php?id=<?php echo rawurlencode($id); ?>" class="btn btn-edit btn-small">編集</a>
                                <a href="/kanri/discography/delete.php?id=<?php echo rawurlencode($id); ?>" class="btn btn-delete btn-small" onclick="return confirm('本当に削除しますか？');">削除</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
