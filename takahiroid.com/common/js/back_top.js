$(function() {
  //TOPに戻る
  // var $("#back-top") = $("#back-top");
  $(window).scroll(function() {
    if ($(this).scrollTop() > 100) {
      $("#back-top").stop().fadeIn(10);
      $("#back-top").addClass("scroll");
      scrollHeight = $(document).height();
      scrollPosition = $(window).height() + $(window).scrollTop();

      var windowWidth = $(window).width();
      var windowSm = 897;

      if (windowWidth < windowSm) {
        //スマホ時

        if ((scrollHeight - scrollPosition) / scrollHeight <= 0.01) {
          $("#back-top").addClass("fixed");
          $("#back-top").removeClass("scroll");
        } else {
          //それ以外のスクロールの位置の場合
          $("#back-top").addClass("scroll");
          $("#back-top").removeClass("fixed");
        }
      } else {
        //非スマホ時//

        if ((scrollHeight - scrollPosition) / scrollHeight <= 0.01) {
          //スクロールの位置が下部5%の範囲に来た場合
          $("#back-top").addClass("fixed");
          $("#back-top").removeClass("scroll");
        } else {
          //それ以外のスクロールの位置の場合
          $("#back-top").addClass("scroll");
          $("#back-top").removeClass("fixed");
        }
      }
    } else {
      $("#back-top").stop().fadeOut();
    }
  });
});
