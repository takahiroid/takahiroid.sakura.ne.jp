
      //スムーススクロール
      $(function(){　
        // #で始まるリンクをクリックしたら実行されます
        $('a[href^="#"]').click(function() {
          // スクロールの速度
          var speed = 400; // ミリ秒で記述
          var href= $(this).attr("href");
          var target = $(href == "#" || href == "" ? 'html' : href);
          var position = target.offset().top;
          $('body,html').animate({scrollTop:position}, speed, 'swing');
          return false;
        });
      });

      //ヘッダーの切り替え
      $(function() {
         var headNav = $("header");
            //scrollだけだと読み込み時困るのでloadも追加
            $(window).on('load scroll', function () {
            //現在の位置が500px以上かつ、クラスfixedが付与されていない時
            if($(this).scrollTop() > 500 && headNav.hasClass('fixed') == false) {
            //headerの高さ分上に設定
            headNav.css({"top": '-100px'});
            //クラスfixedを付与
            headNav.addClass('fixed');
            //位置を0に設定し、アニメーションのスピードを指定
            headNav.animate({"top": 0},600);
            }
            //現在の位置が300px以下かつ、クラスfixedが付与されている時にfixedを外す
            else if($(this).scrollTop() < 300 && headNav.hasClass('fixed') == true){
              headNav.removeClass('fixed');
            }
       });
     });

    //ハンバーガーメニュー
     $(function(){
         $("#nav-toggle").click(function(){
         $(this).next().slideToggle();
         $(this).toggleClass("active");
         })
         });