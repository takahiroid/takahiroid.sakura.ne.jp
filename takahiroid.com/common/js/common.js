$(function() {
  //スマホ用ナビ
  $('.menu-trigger').on('click', function() {
    $(this).toggleClass("active");
    $('.gnavi_sp .menu').toggleClass("open");
  });

  $('.gnavi_sp .menu a').on('click', function() {
    $('.menu-trigger').removeClass("active");
    $('.gnavi_sp .menu').removeClass("open");
  });
});
