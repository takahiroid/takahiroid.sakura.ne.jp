<?php
$page_scripts = ['js/mainv.js'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>タートルズ公式サイト</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@900&amp;family=Kaisei+Opti:wght@700&amp;family=Montserrat:wght@700&amp;display=swap" rel="stylesheet">
    <link href="css/basic.css" rel="stylesheet">
    <meta name="keywords" content="まつもと,マツモト,タカヒロ,松本タカヒロ,タートルズ,ザ・タートルズ,turtles,餃子大王,sparky,まっちゃん">
    <meta name="description" content="タートルズ公式サイト">
</head>
<body>
<?php include __DIR__ . '/inc/header.php'; ?>

    <div id="wrapper">
        <div id="contents">
            <div id="mainImg">
                <img src="images/mainv_1.png" alt="ザ・タートルズ">
                <img src="images/mainv_2.png" alt="">
            </div>

            <div id="Main">
                <p class="sectionHeading" id="live">ライブ・ニュース</p>
                <?php include __DIR__ . '/inc/news_headlines.php'; ?>

                <div class="txtBoxHalf">
                    <p class="sectionHeading" id="sns">SNS</p>
                    <div class="sns-members">
                        <div class="sns-member">
                            <p class="sns-name">タートルズのBLOG（休眠中）</p>
                            <p class="sns-icons">
                                <a class="sns-icon-blog" href="http://theturtles.exblog.jp/" target="_blank" aria-label="タートルズのBLOG" title="BLOG"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9.2L20 7.8V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm8 1.6V9h4.4L14 4.6zM8 12h8v1.6H8V12zm0 3.4h8V17H8v-1.6z"/></svg><span>BLOG</span></a>
                            </p>
                        </div>
                        <div class="sns-member">
                            <p class="sns-name">松本タカヒロ</p>
                            <p class="sns-icons">
                                <a class="sns-icon-hp" href="http://takahiroid.com/" target="_blank" aria-label="松本タカヒロ公式HP" title="公式HP"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.2 3 11h2v9h6v-6h2v6h6v-9h2L12 3.2z"/></svg><span>Official HP</span></a>
                                <a class="sns-icon-x" href="https://x.com/takahiroid" target="_blank" aria-label="松本タカヒロのX" title="X"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.835L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg><span>X (Twitter)</span></a>
                                <a class="sns-icon-ig" href="https://www.instagram.com/takahiroid/" target="_blank" aria-label="松本タカヒロのInstagram" title="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm0 2a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7zm5 2.8a4.2 4.2 0 1 1 0 8.4 4.2 4.2 0 0 1 0-8.4zm0 2a2.2 2.2 0 1 0 0 4.4 2.2 2.2 0 0 0 0-4.4zM17.4 6.2a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2z"/></svg><span>Instagram</span></a>
                            </p>
                        </div>
                        <div class="sns-member">
                            <p class="sns-name">道太郎</p>
                            <p class="sns-icons">
                                <a class="sns-icon-hp" href="http://michitaro.sakuraweb.com/" target="_blank" aria-label="道太郎公式HP" title="公式HP"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.2 3 11h2v9h6v-6h2v6h6v-9h2L12 3.2z"/></svg><span>Official HP</span></a>
                                <a class="sns-icon-x" href="https://x.com/MichitaroS" target="_blank" aria-label="道太郎のX" title="X"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.835L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg><span>X (Twitter)</span></a>
                                <a class="sns-icon-ig" href="https://www.instagram.com/michitaro_shimamoto/" target="_blank" aria-label="道太郎のInstagram" title="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm0 2a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7zm5 2.8a4.2 4.2 0 1 1 0 8.4 4.2 4.2 0 0 1 0-8.4zm0 2a2.2 2.2 0 1 0 0 4.4 2.2 2.2 0 0 0 0-4.4zM17.4 6.2a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2z"/></svg><span>Instagram</span></a>
                            </p>
                        </div>
                        <div class="sns-member">
                            <p class="sns-name">岩見直明</p>
                            <p class="sns-icons">
                                <a class="sns-icon-hp" href="https://ourmusic-2016.com/iwami.html" target="_blank" aria-label="岩見直明公式HP" title="公式HP"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.2 3 11h2v9h6v-6h2v6h6v-9h2L12 3.2z"/></svg><span>Official HP</span></a>
                            </p>
                        </div>
                        <div class="sns-member">
                            <p class="sns-name">山本昇</p>
                            <p class="sns-icons">
                                <a class="sns-icon-x" href="https://x.com/Yamamotonobotan" target="_blank" aria-label="山本昇のX" title="X"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.835L1.254 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg><span>X (Twitter)</span></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>
