<?php
require_once dirname(__DIR__) . '/inc/news_lib.php';
[$liveItems, $infoItems] = splitPublicNews();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ライブ・ニュース - タートルズ公式サイト</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@900&amp;family=Kaisei+Opti:wght@700&amp;family=Montserrat:wght@700&amp;display=swap" rel="stylesheet">
    <link href="../css/basic.css" rel="stylesheet">
    <meta name="keywords" content="まつもと,マツモト,タカヒロ,松本タカヒロ,タートルズ,ザ・タートルズ,turtles,餃子大王,sparky,まっちゃん">
    <meta name="description" content="ザ・タートルズのライブとニュース">
</head>
<body>
<?php include dirname(__DIR__) . '/inc/header.php'; ?>

    <div id="wrapper">
        <div id="contents">
            <div id="Main" class="news-list-page">
                <p class="sectionHeading" id="live">ライブ</p>
                <?php if ($liveItems === []): ?>
                    <p class="news-empty">現在、ライブ情報はありません。</p>
                <?php else: ?>
                    <?php foreach ($liveItems as $news) {
                        renderNewsArticle($news);
                    } ?>
                <?php endif; ?>

                <p class="sectionHeading" id="news">ニュース</p>
                <?php if ($infoItems === []): ?>
                    <p class="news-empty">現在、ニュースはありません。</p>
                <?php else: ?>
                    <?php foreach ($infoItems as $news) {
                        renderNewsArticle($news);
                    } ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php $page_scripts = ['js/news-lightbox.js']; ?>
<?php include dirname(__DIR__) . '/inc/footer.php'; ?>
</body>
</html>
