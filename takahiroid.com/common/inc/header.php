<?php
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$firstSegment = explode('/', trim($uriPath, '/'))[0] ?? '';
$navIsCurrent = function ($target) use ($firstSegment) {
	return $firstSegment === trim($target, '/');
};
?>

<!-- スムーススクロール -->
<script type="text/javascript" src="../common/js/smooth-scroll.js"></script>

<link href='https://fonts.googleapis.com/css?family=Quicksand:400,700|Roboto:400,400italic,700,700italic|Dosis:500' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Droid+Serif:400,400italic' rel='stylesheet' type='text/css'>
<div class="header">

	<div class="header_title">
		<img src="/common/img/mv_red.png" class="header_icon" alt="">
		<h1>
			<a href="/" target="_self" class="ttl_main">TAKAHIROID.COM</a>
			<p class="ttl_sub">TAKAHIRO MATSUMOTO OFFICIAL WEB</p>
		</h1>
	</div>

	<ul class="gnavi_pc pc">
	<li class="<?= $navIsCurrent('') ? 'current' : '' ?>"><a href="/">HOME</a></li>
	  <li class="<?= $navIsCurrent('live') ? 'current' : '' ?>"><a href="/live/">LIVE</a></li>
	  <li class="<?= $navIsCurrent('news') ? 'current' : '' ?>"><a href="/news/">NEWS</a></li>
	  <li class="<?= $navIsCurrent('discography') ? 'current' : '' ?>"><a href="/discography/">Discography</a></li>

	<li class="<?= $navIsCurrent('bio') ? 'current' : '' ?>"><a href="/bio/">Profile</a></li>
	  <li class="<?= $navIsCurrent('works') ? 'current' : '' ?>"><a href="/works/">Works</a></li>
	  <li class=""><a href="https://turtle-mania.stores.jp/" target="_blank">Goods</a></li>
	  <li class=""><a href="https://tunecore.co.jp/artists?id=1096989" target="_blank">MUSIC</a></li>
	  <li class="twitter_btn"><a href="http://twitter.com/takahiroid" target="_blank"><img src="/common/img/x.svg" alt="X" width="16"></a></li>
		<li class="insta_btn"><a href="https://www.instagram.com/takahiroid/" Target="_blank"><img src="/common/img/instagram.png" alt="Instagram" width="16"></a></li>
		<li class="youtube_btn"><a href="https://www.youtube.com/@takahiroid" target="_blank"><img src="/common/img/youtube.svg" alt="YouTube" width="16"></a></li>
	</ul>

	<div class="menu-trigger-wrap sp">
		<div class="menu-trigger sp">
	    <span></span>
	    <span></span>
	    <span></span>
		</div>
	</div>
	<nav class="gnavi_sp sp">
		<ul class="menu">
		<li class="<?= $navIsCurrent('') ? 'current' : '' ?>"><a href="/">HOME</a></li>
	  <li class="<?= $navIsCurrent('live') ? 'current' : '' ?>"><a href="/live/">LIVE</a></li>
	  <li class="<?= $navIsCurrent('news') ? 'current' : '' ?>"><a href="/news/">NEWS</a></li>
	  <li class="<?= $navIsCurrent('discography') ? 'current' : '' ?>"><a href="/discography/">Discography</a></li>
	  <li class="<?= $navIsCurrent('bio') ? 'current' : '' ?>"><a href="/bio/">Profile</a></li>
		<li class="<?= $navIsCurrent('works') ? 'current' : '' ?>"><a href="/works/">Works</a></li>

	  <li class=""><a href="https://turtle-mania.stores.jp/" target="_blank">Goods</a></li>
	  <li class=""><a href="https://tunecore.co.jp/artists?id=1096989" target="_blank">MUSIC</a></li>
	  <li class="sns_icons_wrap">
		<ul class="sns_icons">
		  <li class="twitter_btn"><a href="http://twitter.com/takahiroid" target="_blank"><img src="/common/img/x.svg" alt="X" width="16"></a></li>
		  <li class="insta_btn"><a href="https://www.instagram.com/takahiroid/" Target="_blank"><img src="/common/img/instagram.svg" alt="Instagram" width="16"></a></li>
		  <li class="youtube_btn"><a href="https://www.youtube.com/@takahiroid" target="_blank"><img src="/common/img/youtube.svg" alt="YouTube" width="16"></a></li>
		</ul>
	  </li>


		</ul>
	</nav>


</div>
