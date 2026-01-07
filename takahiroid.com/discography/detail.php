<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<?php
// ディスコグラフィーデータを読み込み（セッションなしの関数を使用）
require_once __DIR__ . '/functions.php';
$discographyData = loadDiscographyData();

// IDでアイテムを検索
$requestId = isset($_GET['id']) ? $_GET['id'] : '';
$disc = null;
$currentIndex = -1;
foreach ($discographyData as $index => $item) {
    if (($item['id'] ?? $index) == $requestId && ($item['published'] ?? true)) {
        $disc = $item;
        $currentIndex = $index;
        break;
    }
}

// 見つからない場合は一覧にリダイレクト
if (!$disc) {
    header('Location: /discography/');
    exit;
}

$title = htmlspecialchars($disc['title'] ?? 'DISCOGRAPHY', ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars($disc['subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<meta name="Keywords" content="まつもと,マツモト,タカヒロ,松本タカヒロ,<?php echo $title; ?>,ディスコグラフィー" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript" src="/common/js/common.js"></script>
<link rel="stylesheet" href="/common/css/style.css" type="text/css"/>
<link rel="stylesheet" href="/common/css/style_sp.css" type="text/css" />
<script type="text/javascript" src="/common/js/back_top.js"></script>
<link rel="icon" href="/favicon.ico">
<title><?php echo $title; ?> - DISCOGRAPHY - TAKAHIROID.COM</title>
<style>
/* Discography Detail Styles */
.disc-detail-header {
    text-align: center;
    margin: 50px 0 40px;
}
.disc-detail-header .disc-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
    letter-spacing: 1px;
}
.disc-detail-header h1 {
    font-family: 'Quicksand', sans-serif;
    font-size: 32px;
    font-weight: 600;
    color: #333;
    letter-spacing: 2px;
    line-height: 1.3;
}

.disc-detail-content {
    display: flex;
    gap: 50px;
    margin-bottom: 60px;
}

.disc-detail-left {
    flex: 0 0 45%;
    max-width: 500px;
}

.disc-detail-right {
    flex: 1;
    min-width: 0;
}

.disc-detail-image {
    position: relative;
    width: 100%;
    background: #f5f5f5;
}
.disc-detail-image img {
    width: 100%;
    height: auto;
    display: block;
}
.disc-detail-image-placeholder {
    width: 100%;
    padding-bottom: 100%;
    background: #e8e8e8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 16px;
    position: relative;
}
.disc-detail-image-placeholder span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* 画像ナビゲーション（将来的に複数画像対応用） */
.disc-image-nav {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 15px;
}
.disc-image-nav-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ddd;
    cursor: pointer;
    transition: background 0.3s;
}
.disc-image-nav-dot.active {
    background: #333;
}

.disc-detail-info {
    padding-top: 10px;
}

.disc-release-info {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.disc-tracklist {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}
.disc-tracklist-title {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.disc-body {
    line-height: 1.8;
    font-size: 14px;
    color: #333;
}
.disc-body p {
    margin-bottom: 1em;
    font-size: 14px;
    line-height: 1.8;
}
.disc-body a {
    color: #0066cc;
    text-decoration: underline;
}
.disc-body a:hover {
    text-decoration: none;
}
.disc-body strong {
    font-weight: 600;
}
.disc-body ul, .disc-body ol {
    margin: 1em 0;
    padding-left: 1.5em;
}
.disc-body li {
    margin-bottom: 0.5em;
    font-size: 14px;
    line-height: 1.6;
}

.disc-price {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.disc-back-link {
    margin-top: 50px;
    text-align: center;
}
.disc-back-link a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Quicksand', sans-serif;
    font-size: 14px;
    color: #333;
    text-decoration: none;
    padding: 12px 25px;
    border: 1px solid #333;
    transition: all 0.3s;
}
.disc-back-link a:hover {
    background: #333;
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .disc-detail-content {
        flex-direction: column;
        gap: 30px;
    }
    .disc-detail-left {
        flex: none;
        max-width: 100%;
    }
    .disc-detail-header h1 {
        font-size: 24px;
    }
    .disc-detail-header {
        margin: 30px 0 25px;
    }
}
</style>
</head>

<body id="discography-detail">
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TCCM7V"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TCCM7V');</script>
<!-- End Google Tag Manager -->

<div class="wrapper">
    <?php include("../common/inc/header.php"); ?>
    
    <div class="contents">
        <div class="disc-detail-header">
            <?php 
            // カテゴリを表示
            if (!empty($disc['category'])): 
                $categoryColor = getDiscographyCategoryColor($disc['category']);
            ?>
                <div class="disc-subtitle" style="margin-bottom: 5px;">
                    <span style="display: inline-block; padding: 4px 12px; background: <?php echo htmlspecialchars($categoryColor, ENT_QUOTES, 'UTF-8'); ?>; color: #fff; font-size: 12px; border-radius: 4px;">
                        <?php echo htmlspecialchars($disc['category'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
            <?php endif; ?>
            <?php if (!empty($subtitle)): ?>
                <div class="disc-subtitle"><?php echo $subtitle; ?></div>
            <?php endif; ?>
            <h1><?php echo $title; ?></h1>
        </div>
        
        <div class="disc-detail-content">
            <div class="disc-detail-left">
                <div class="disc-detail-image">
                    <?php if (!empty($disc['image'])): ?>
                        <img src="<?php echo htmlspecialchars($disc['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $title; ?>">
                    <?php else: ?>
                        <div class="disc-detail-image-placeholder">
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="disc-detail-right">
                <div class="disc-detail-info">
                    <?php if (!empty($disc['release_date'])): ?>
                        <div class="disc-release-info">
                            <?php echo htmlspecialchars($disc['release_date'], ENT_QUOTES, 'UTF-8'); ?>
                            <?php if (!empty($disc['release_type'])): ?>
                                <?php echo htmlspecialchars($disc['release_type'], ENT_QUOTES, 'UTF-8'); ?>リリース
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($disc['price'])): ?>
                        <div class="disc-price">
                            ¥<?php echo htmlspecialchars($disc['price'], ENT_QUOTES, 'UTF-8'); ?>(税込)
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($disc['content'])): ?>
                        <div class="disc-body">
                            <?php echo $disc['content']; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="disc-back-link">
            <a href="/discography/">
                <svg width="20" height="10" viewBox="0 0 20 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="20" y1="5" x2="2" y2="5" stroke="currentColor" stroke-width="1"/>
                    <path d="M7 1L2 5L7 9" stroke="currentColor" stroke-width="1" fill="none"/>
                </svg>
                DISCOGRAPHY一覧に戻る
            </a>
        </div>
    </div>
    
    <?php include("../common/inc/footer.php"); ?>
</div>
</body>
</html>

