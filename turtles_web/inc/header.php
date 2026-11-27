<?php
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$script_dir = dirname($script_name);
$on_news = basename($script_dir) === 'news';
$root = $on_news ? '../' : '';
$current_page = $on_news ? 'news/index.php' : basename($script_name);
$on_top = in_array($current_page, ['index.php', 'index.html', ''], true);

if (!function_exists('turtles_href')) {
    function turtles_href(string $hash): string
    {
        global $on_top, $root;
        return ($on_top ? '' : $root . 'index.php') . $hash;
    }
}

if (!function_exists('turtles_current')) {
    function turtles_current(string $page): string
    {
        global $current_page;
        return $current_page === $page ? ' aria-current="page"' : '';
    }
}
?>
    <p id="site-name">ザ・タートルズ　オフィシャルサイト</p>
    <div id="header">
        <div id="header_wrapper">
            <a id="header-logo" href="<?= $root ?>index.php" aria-label="TOP"<?= turtles_current('index.php') ?>></a>
            <a id="logo-title" href="<?= $root ?>index.php"<?= turtles_current('index.php') ?>>Let's Do The Turtle!</a>
            <button type="button" class="nav-toggle" aria-label="メニュー" aria-expanded="false" aria-controls="menu">
                <span></span><span></span><span></span>
            </button>
            <div id="menu">
                <ul>
                    <li><a href="<?= $root ?>index.php"<?= turtles_current('index.php') ?>>TOP</a></li>
                    <li><a href="<?= $root ?>news/#live"<?= turtles_current('news/index.php') ?>>ライブ&ニュース</a></li>
                    <li><a href="<?= $root ?>profile.php"<?= turtles_current('profile.php') ?>>紹介</a></li>
                    <li><a href="<?= $root ?>disc.php"<?= turtles_current('disc.php') ?>>ディスコグラフィー</a></li>
                    <!-- <li><a href="<?= turtles_href('#sns') ?>">SNS</a></li> -->
                    <li><a class="nav-external" href="https://turtle-mania.stores.jp/" target="_blank" rel="noopener noreferrer" aria-label="通販（外部サイト）">通販<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3zM5 5h6v2H7v10h10v-4h2v6H5V5z"/></svg></a></li>
                    <li><a href="<?= $root ?>contact.php"<?= turtles_current('contact.php') ?>>お問い合わせ</a></li>
                </ul>
            </div>
        </div>
    </div>
