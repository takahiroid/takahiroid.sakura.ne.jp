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
<link rel="stylesheet" href="/discography/discography.css" type="text/css" />
<script type="text/javascript" src="/common/js/back_top.js"></script>
<link rel="icon" href="/favicon.ico">
<title><?php echo $title; ?> - DISCOGRAPHY - TAKAHIROID.COM</title>
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
                    <span class="category-badge" style="background: <?php echo htmlspecialchars($categoryColor, ENT_QUOTES, 'UTF-8'); ?>;">
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
                            <?php echo htmlspecialchars($disc['release_date'], ENT_QUOTES, 'UTF-8'); ?> 発売
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

