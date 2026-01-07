<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="Keywords" content="まつもと,マツモト,タカヒロ,松本タカヒロ,ディスコグラフィー,discography" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript" src="/common/js/common.js"></script>
<link rel="stylesheet" href="/common/css/style.css" type="text/css"/>
<link rel="stylesheet" href="/common/css/style_sp.css" type="text/css" />
<link rel="stylesheet" href="/discography/discography.css" type="text/css" />
<script type="text/javascript" src="/common/js/back_top.js"></script>
<link rel="icon" href="/favicon.ico">
<title>DISCOGRAPHY - TAKAHIROID.COM</title>
</head>

<body id="discography">
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-TCCM7V"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TCCM7V');</script>
<!-- End Google Tag Manager -->

<div class="wrapper">
    <?php include("../common/inc/header.php"); ?>
    
    <div class="contents">
        <div class="page-title">
            <h1>DISCOGRAPHY</h1>
        </div>
        
        <?php
        // ディスコグラフィーデータを読み込み（セッションなしの関数を使用）
        require_once __DIR__ . '/functions.php';
        $discographyData = loadDiscographyData();
        $discographyCategories = loadDiscographyCategories();
        
        // 公開中のもののみフィルター
        $publishedData = array_filter($discographyData, function($item) {
            return $item['published'] ?? true;
        });
        
        // カテゴリでフィルター
        $filterCategory = isset($_GET['category']) ? $_GET['category'] : '';
        if (!empty($filterCategory)) {
            $publishedData = array_filter($publishedData, function($item) use ($filterCategory) {
                return ($item['category'] ?? '') === $filterCategory;
            });
        }
        
        // 発売日の降順でソート
        usort($publishedData, function($a, $b) {
            $dateA = $a['release_date'] ?? '';
            $dateB = $b['release_date'] ?? '';
            
            // 日付を統一形式（YYYY-MM-DD）に変換
            $normalizeDate = function($date) {
                if (empty($date)) return '0000-00-00';
                // "YYYY.MM.DD" 形式を "YYYY-MM-DD" に変換
                $date = str_replace('.', '-', $date);
                // "YYYY/MM/DD" 形式を "YYYY-MM-DD" に変換
                $date = str_replace('/', '-', $date);
                return $date;
            };
            
            $normalizedA = $normalizeDate($dateA);
            $normalizedB = $normalizeDate($dateB);
            
            // 降順（新しい日付が先）
            return strcmp($normalizedB, $normalizedA);
        });
        
        // カテゴリの一覧を取得
        $categories = [];
        foreach ($discographyData as $item) {
            if (!empty($item['category']) && ($item['published'] ?? true)) {
                $categories[$item['category']] = ($categories[$item['category']] ?? 0) + 1;
            }
        }
        ?>
        
        <?php if (count($categories) > 1): ?>
        <div class="disc-filter">
            <label>CATEGORY</label>
            <select onchange="updateFilter(this.value, 'category')">
                <option value="">ALL</option>
                <?php foreach ($categories as $cat => $count): ?>
                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterCategory === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>(<?php echo $count; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <script>
        function updateFilter(value, param) {
            let newUrl = '/discography/';
            if (value) {
                newUrl += '?category=' + encodeURIComponent(value);
            }
            location.href = newUrl;
        }
        </script>
        <?php endif; ?>
        
        <?php if (empty($publishedData)): ?>
            <div class="disc-empty">
                <h2>ディスコグラフィーはまだありません</h2>
            </div>
        <?php else: ?>
            <div class="disc-grid">
                <?php foreach ($publishedData as $index => $disc): ?>
                    <a href="/discography/detail.php?id=<?php echo htmlspecialchars($disc['id'] ?? $index, ENT_QUOTES, 'UTF-8'); ?>" class="disc-item">
                        <div class="disc-image">
                            <?php if (!empty($disc['image'])): ?>
                                <img src="<?php echo htmlspecialchars($disc['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($disc['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <div class="disc-image-placeholder">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="disc-arrow">
                            <svg viewBox="0 0 30 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="0" y1="5" x2="28" y2="5" stroke="#333" stroke-width="1"/>
                                <path d="M23 1L28 5L23 9" stroke="#333" stroke-width="1" fill="none"/>
                            </svg>
                        </div>
                        <div class="disc-subtitle">
                            <?php 
                            if (!empty($disc['category'])): 
                                $categoryColor = getDiscographyCategoryColor($disc['category']);
                            ?>
                                <span style="display: inline-block; padding: 2px 8px; background: <?php echo htmlspecialchars($categoryColor, ENT_QUOTES, 'UTF-8'); ?>; color: #fff; font-size: 10px; border-radius: 3px; margin-right: 5px;">
                                    <?php echo htmlspecialchars($disc['category'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($disc['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="disc-title"><?php echo htmlspecialchars($disc['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("../common/inc/footer.php"); ?>
</div>
</body>
</html>

