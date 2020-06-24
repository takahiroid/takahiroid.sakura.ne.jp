$(function() {
  //スマホ用ナビ
  $('.menu-trigger').on('click', function() {
    $(this).toggleClass("active");
    if($('.gnavi_sp .menu').css('display') === 'block') {
      $('.gnavi_sp .menu').slideUp('1500');
    }else {
      $('.gnavi_sp .menu').slideDown('1500');
    }
  });

  $('.menu-item a').on('click', function() {
    $('.menu-trigger').removeClass("active");
    $('.gnavi_sp .menu').slideUp('1500');
  });
});