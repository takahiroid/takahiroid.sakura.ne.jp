<?php
$page_heading = 'お問い合わせ';
$page_path = 'contact.php';
$page_description = 'ザ・タートルズへのお問い合わせ窓口です。タートルマニア事務局のメールアドレスと、お手紙の送付先をご案内します。';
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
</head>
<body>
<?php include __DIR__ . '/inc/header.php'; ?>

    <div id="wrapper">
        <div id="contents">
            <div id="Main">
                <div id="contact">
                    <p class="sectionHeading">お問い合わせ</p>
                    <p class="txtMargin1">
                        タートルマニア事務局：
                        <a href="mailto:office.turtlemania@gmail.com">office.turtlemania@gmail.com</a>
                    </p>
                    <p class="sectionHeading">お手紙</p>
                    <p class="txtMargin1">
                        〒154-0004<br>
                        東京都世田谷区太子堂1-12-39 三軒茶屋ビル6F<br>
                        株式会社ヒツジスタジオ内 タートルマニア事務局
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
