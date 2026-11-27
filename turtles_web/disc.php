<?php
require_once __DIR__ . '/inc/disc_lib.php';

$discId = isset($_GET['id']) ? (string)$_GET['id'] : '';
$disc = $discId !== '' ? findPublicDiscography($discId) : null;
$isFragment = isset($_GET['fragment']);

if ($isFragment) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex');
    if (!$disc) {
        http_response_code(404);
        echo '<p class="disc-missing">作品が見つかりません。</p>';
        exit;
    }
    include __DIR__ . '/inc/disc_detail.php';
    exit;
}

$discMissing = false;
if ($disc) {
    $plain = discPlain($disc['content'] ?? '');
    $page_heading = (string)($disc['title'] ?? 'ディスコグラフィー');
    $page_path = 'disc.php?id=' . rawurlencode((string)$disc['id']);
    $page_description = $plain !== '' ? mb_substr($plain, 0, 120) : $page_heading . 'の収録情報です。';
} elseif ($discId !== '') {
    http_response_code(404);
    $discMissing = true;
    $page_heading = 'ディスコグラフィー';
    $page_path = 'disc.php';
    $page_robots = 'noindex, follow';
    $page_description = '指定された作品は見つかりません。';
} else {
    $page_heading = 'ディスコグラフィー';
    $page_path = 'disc.php';
    $page_description = 'ザ・タートルズのディスコグラフィーです。アルバム、シングルのジャケットと収録曲を掲載しています。';
    $page_scripts = ['js/disc.js'];
}

$discList = $disc || $discMissing ? [] : publishedDiscography();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php include __DIR__ . '/inc/meta.php'; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@900&amp;family=Kaisei+Opti:wght@700&amp;family=Montserrat:wght@700&amp;display=swap" rel="stylesheet">
<link href="css/basic.css" rel="stylesheet">
<link href="css/disc.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/inc/header.php'; ?>

<div id="wrapper">
    <div id="contents">
        <div id="Main" class="disc-page">
            <?php if ($disc): ?>
                <p class="sectionHeading">ディスコグラフィー</p>
                <a class="disc-back" href="/disc.php"><span class="disc-back-arrow" aria-hidden="true"></span>BACK</a>
                <?php include __DIR__ . '/inc/disc_detail.php'; ?>
            <?php elseif ($discMissing): ?>
                <h1 class="sectionHeading">ディスコグラフィー</h1>
                <p class="disc-missing">作品が見つかりません。</p>
                <p><a class="disc-back" href="/disc.php"><span class="disc-back-arrow" aria-hidden="true"></span>BACK</a></p>
            <?php else: ?>
                <h1 class="sectionHeading">ディスコグラフィー</h1>
                <?php if ($discList === []): ?>
                    <p class="disc-empty">ディスコグラフィーはまだありません。</p>
                <?php else: ?>
                    <div class="disc-grid">
                        <?php foreach ($discList as $item):
                            $itemId = (string)($item['id'] ?? '');
                            $itemTitle = (string)($item['title'] ?? '');
                            $itemImage = discImageSrc($item['image'] ?? '');
                        ?>
                            <a class="disc-item" href="/disc.php?id=<?php echo rawurlencode($itemId); ?>" data-id="<?php echo discH($itemId); ?>">
                                <span class="disc-image">
                                    <?php if ($itemImage !== ''): ?>
                                        <img src="<?php echo discH($itemImage); ?>" alt="<?php echo discH($itemTitle); ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="disc-image-placeholder">No Image</span>
                                    <?php endif; ?>
                                </span>
                                <span class="disc-arrow" aria-hidden="true">
                                    <svg viewBox="0 0 34 12" fill="none">
                                        <line x1="0" y1="6" x2="31" y2="6" stroke="currentColor" stroke-width="1.4"/>
                                        <path d="M25 1.5 31 6 25 10.5" stroke="currentColor" stroke-width="1.4" fill="none"/>
                                    </svg>
                                </span>
                                <?php if (trim((string)($item['subtitle'] ?? '')) !== ''): ?>
                                    <span class="disc-subtitle"><?php echo discH($item['subtitle']); ?></span>
                                <?php endif; ?>
                                <span class="disc-title"><?php echo discH($itemTitle); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$disc && !$discMissing): ?>
<div id="disc-modal" class="disc-modal" hidden>
    <div class="disc-modal-panel" role="dialog" aria-modal="true" aria-label="作品詳細">
        <button type="button" class="disc-modal-close" aria-label="閉じる">&times;</button>
        <div id="disc-modal-body"></div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
