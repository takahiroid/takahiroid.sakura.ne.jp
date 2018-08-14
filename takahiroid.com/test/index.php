<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<title>Aoi Yamagichi</title>
<link href="https://fonts.googleapis.com/css?family=Satisfy" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,700" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Playball|Quicksand|Satisfy" rel="stylesheet">
<!-- 共通CSS -->
<link href="css/style.css" rel="stylesheet" type="text/css">
<!-- ページCSS -->
<link href="css/index.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssreset/cssreset-min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<!-- 共通JS -->
<script src="js/common.js"></script>

<script src="js/jquery.bgswitcher.js"></script>
<script type="text/javascript" src="js/jquery.matchHeight.js"></script>
<script>
    //高さ揃え
    $(function() {
      $('.news_inner').matchHeight();
      $('.news_inner .ttl').matchHeight();
      $('.news_inner .txt').matchHeight();
    });

</script>
</head>
<body>
  <header>
        <div id="nav">
              <ul class="logo_nav">
              <li class="logo"><a href="#"><img src="img/logo.png" alt="Flora Voice Laboratory ロゴ"></a></li>
                <li class="scroll_logo"><a href="#"><img src="img/scroll_logo.png" alt="Flora Voice Laboratory ロゴ"></a></li>
              </ul>
            <ul class="main_nav">

                <li class="nav_title"><a href="news.php">NEWS</a></li>
                <li class="nav_title"><a href="profile.php">PROFILE</a></li>
                <li class="nav_title"><a href="schedule.php">SCHEDULE</a></li>
                <li class="nav_title"><a href="discography.php">DISCOGRAPHY</a></li>
                <li class="nav_title"><a href="contact.php">CONTACT</a></li>
             </ul>
             <ul class="sns_nav">
                <li class="icon_fb"><a href="#"><img src="img/facebook_head.png" alt="Facebook"></a></li>
                <li class="icon_yt"><a href="#"><img src="img/youtube_head.png" alt="YouTube"></a></li>
                <li class="scroll_icon_fb"><a href="#"><img src="img/top_scroll_fb.png" alt="Facebook"></a></li>
                <li class="scroll_icon_yt"><a href="#"><img src="img/top_scroll_yt.png" alt="YouTube"></a></li>
            </ul>
          </div>
      <!--スマホ用ナビゲーション-->
    <div id="nav-toggle">
          <span></span>
          <span></span>
          <span></span>
      </div>

    <div id="sp">
        <div id="sp_nav">
              <ul class="sp_logo_nav">
              <li class="sp_logo"><a href="#"><img src="img/logo.png" alt="Flora Voice Laboratory ロゴ"></a></li>
              </ul>

              <ul class="sp_sns_nav">
                <li class="sp_icon_fb"><a href="#"><img src="img/sp/sp_fb.jpg" alt="Facebook"></a></li>
                <li class="sp_icon_yt"><a href="#"><img src="img/sp/sp_yt.jpg" alt="YouTube"></a></li>
             </ul>

            <ul class="sp_main_nav">
                <a href="index.php"><li class="sp_nav_title">HOME</li></a>
                <a href="news.php"><li class="sp_nav_title">NEWS</li></a>
                <a href="profile.php" ><li class="sp_nav_title">PROFILE</li></a>
                <a href="schedule.php"><li class="sp_nav_title">SCHEDULE</li></a>
                <a href="discography.php"><li class="sp_nav_title">DISCOGRAPHY</li></a>
                <a href="contact.php"><li class="sp_nav_title">CONTACT</li></a>
             </ul>

          </div>
  </div><!--スマホ用ナビゲーション-->
  </header>

  <div id="main_visual">
    <img id="main_logo" src="img/index/main_logo.png" alt="メインイメージロゴ">
    <div id="scroll_mark" ><img src="img/scroll_mark.png" alt="スクロールマーク"></div>
  </div>

<script>
    //高さ揃え
    $(function() {

      //メイン画像のスライド
      $('#main_visual').bgSwitcher({
          interval: 4000, // 背景画像を切り替える間隔を指定 3000=3秒
          loop: true, // 切り替えを繰り返すか指定 true=繰り返す　false=繰り返さない
          effect: "fade", // エフェクトの種類をfade,blind,clip,slide,drop,hideから指定
          duration: 3000, // エフェクトの時間を指定します。
          easing: "swing" // エフェクトのイージングをlinear,swingから指定
      });

      $(window).on('load resize', function(){
        var w = $(window).width();
        console.log(w);
        var x = 768;
        if (w < x) {
        //画面サイズが768px未満のときの処理
        $('#main_visual').bgSwitcher({
            images: ['img/sp/top_01.jpg','img/sp/top_02.jpg','img/sp/top_03.jpg'],
        });
        } else {
        //それ以外のときの処理
        $('#main_visual').bgSwitcher({
            images: ['img/index/top_01.jpg','img/index/top_02.jpg','img/index/top_03.jpg'],
        });
        }
      });
    });

</script>


  <div id="news">
    <div id="news_head" class="head">
      <h2 id="news_title">News</h2>
    </div>
    <div class="news-contents clearfix">
        <div class="news_box">
          <p class="news_data">2018.3.11</p>
            <a href="news.php">
            <div class="news_inner">
              <div class="ttl"><p>2018.3.11 山口葵 リサイタル</p></div>
              <div class="txt">テキストテキストテキストテキストテキストテキスト</div>
              <div class="read_more">Read more</div>
            </div>
            </a>
        </div>
        <div class="news_box">
          <p class="news_data">2018.3.11</p>
            <a href="news.php">
              <div class="news_inner">
                <div class="ttl"><p>2018.3.11 山口葵 リサイタル<br>Swing in Strings vol.2</p></div>
                <div class="txt">テキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキストテキスト</div>
                <div class="read_more">Read more</div>
             </div>
            </a>
        </div>
        <div class="news_box">
          <p class="news_data">2018.3.11</p>
            <a href="news.php">
              <div class="news_inner">
                <div class="ttl"><p>2018.3.11 山口葵 リサイタル<br>Swing in Strings vol.2</p></div>
                <div class="txt">テキストテキストテキストテキストテキストテキスト</div>
                <div class="read_more">Read more</div>
              </div>
            </a>
        </div>
    </div>

    <div class="read_the_news">
      <a href="news.php"><img src="img/index/read_the_news.jpg" class="hoverImg" alt="ニュースページへのリンク"></a>
    </div>

  </div>
  <!--#news-->

  <div id="foot_visual">
    <img src="img/index/foot_visual.jpg" alt="コンサート写真">
  </div>

  <footer id="footer_top" style="position:relative;">
    <div id="foot_content">
      <div class="footer_link clearfix">
        <img class="line" src="img/line.jpg" alt="LineのQRコード">
        <div class="footer_sns_icon">
        <div class="footer_fb_link">
          <a href="#"><img class="footer_fb hoverImg" src="img/facebook_foot.png" alt="Facebook"></a>
        </div>
        <div class="footer_yt_link">
          <a href="#"><img class="footer_yt hoverImg" src="img/you_tube_foot.png" alt="YouTube"></a>
        </div>
          </div>
      </div>
      <div class="footer_text" >
         <p class="tel_fax_email">Tel/Fax 092-715-3114&nbsp;&nbsp;<span class="br">E-mail aoiyamaguchi@icloud.com</span></p>
              <p class="copyright">Since 2009&copy;Flora Voice Laboratory.<span class="br">All Right Reserved.&nbsp;</span><span class="br">無断転載を禁ず</span></p>
      </div>
      <div class="gotop"><a href="#"><img src="img/go_top.png" class="hoverImg" alt="ページ上部へ戻るボタン"></a></div>
    </div>
  </footer>

</body>
</html>