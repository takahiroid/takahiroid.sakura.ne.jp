<?php
// 現在のページを判定
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>管理画面</h2>
        <a href="/" class="site-name" target="_blank" rel="noopener noreferrer">takahiroid.com</a>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-section">メニュー</li>
        <li><a href="/kanri/dashboard.php" class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">ダッシュボード</a></li>
        <li><a href="/kanri/news/" class="<?php echo (strpos($request_uri, '/kanri/news/') !== false) ? 'active' : ''; ?>">ニュース記事</a></li>
        <li><a href="/kanri/categories.php" class="<?php echo ($current_page === 'categories.php') ? 'active' : ''; ?>">カテゴリ管理</a></li>
        <li><a href="/kanri/subcategories.php" class="<?php echo ($current_page === 'subcategories.php') ? 'active' : ''; ?>">サブカテゴリ管理</a></li>
        <!-- 将来的に他のメニューを追加可能 -->
        <!-- <li><a href="/kanri/works/">作品管理</a></li> -->
        <!-- <li><a href="/kanri/bio/">プロフィール管理</a></li> -->
    </ul>
    <div class="sidebar-footer">
        <a href="/kanri/dashboard.php?logout=1" class="btn btn-secondary" style="width: 100%; text-align: center;">ログアウト</a>
    </div>
</div>

