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
<script type="text/javascript" src="/common/js/back_top.js"></script>
<link rel="icon" href="/favicon.ico">
<title>DISCOGRAPHY - TAKAHIROID.COM</title>
<style>
/* Discography Styles */
.disc-page-title {
    text-align: center;
    margin: 20px 0 50px;
}
.disc-page-title h1 {
    font-family: 'Quicksand', sans-serif;
    font-size: 42px;
    font-weight: 400;
    letter-spacing: 8px;
    color: #333;
    position: relative;
    display: inline-block;
}
.disc-page-title h1::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 2px;
    background: #333;
}

.disc-filter {
    text-align: right;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 15px;
}
.disc-filter label {
    font-family: 'Quicksand', sans-serif;
    font-size: 12px;
    letter-spacing: 1px;
    color: #666;
}
.disc-filter select {
    padding: 8px 30px 8px 15px;
    font-family: 'Quicksand', sans-serif;
    font-size: 13px;
    border: none;
    background: #333;
    color: #fff;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

.disc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px 30px;
    margin-bottom: 60px;
}

.disc-item {
    text-decoration: none;
    display: block;
    transition: transform 0.3s ease;
}
.disc-item:hover {
    transform: translateY(-5px);
}
.disc-item:hover .disc-image img {
    opacity: 0.85;
}
.disc-item:hover .disc-arrow {
    transform: translateX(5px);
}

.disc-image {
    position: relative;
    width: 100%;
    padding-bottom: 100%;
    background: #f5f5f5;
    overflow: hidden;
    margin-bottom: 15px;
}
.disc-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}
.disc-image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8e8e8;
    color: #999;
    font-size: 14px;
}

.disc-arrow {
    display: inline-block;
    margin-bottom: 8px;
    transition: transform 0.3s ease;
}
.disc-arrow svg {
    width: 30px;
    height: 10px;
}

.disc-subtitle {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
    letter-spacing: 0.5px;
}

.disc-title {
    font-family: 'Quicksand', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .disc-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px 15px;
    }
    .disc-page-title h1 {
        font-size: 28px;
        letter-spacing: 4px;
    }
    .disc-page-title {
        margin: 40px 0 35px;
    }
    .disc-title {
        font-size: 13px;
    }
    .disc-subtitle {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .disc-grid {
        grid-template-columns: 1fr;
        gap: 35px;
        max-width: 300px;
        margin: 0 auto 60px;
    }
}

.disc-empty {
    text-align: center;
    padding: 80px 20px;
    color: #999;
}
.disc-empty h2 {
    font-family: 'Quicksand', sans-serif;
    font-size: 18px;
    font-weight: 400;
    margin-bottom: 10px;
}
</style>
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
        <div class="disc-page-title">
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
        
        // リリース形態でフィルター
        $filterType = isset($_GET['type']) ? $_GET['type'] : '';
        if (!empty($filterType)) {
            $publishedData = array_filter($publishedData, function($item) use ($filterType) {
                return ($item['release_type'] ?? '') === $filterType;
            });
        }
        
        // カテゴリの一覧を取得
        $categories = [];
        foreach ($discographyData as $item) {
            if (!empty($item['category']) && ($item['published'] ?? true)) {
                $categories[$item['category']] = ($categories[$item['category']] ?? 0) + 1;
            }
        }
        
        // リリース形態の一覧を取得
        $releaseTypes = [];
        foreach ($discographyData as $item) {
            if (!empty($item['release_type']) && ($item['published'] ?? true)) {
                $releaseTypes[$item['release_type']] = ($releaseTypes[$item['release_type']] ?? 0) + 1;
            }
        }
        ?>
        
        <?php if (count($categories) > 1 || count($releaseTypes) > 1): ?>
        <div class="disc-filter">
            <?php if (count($categories) > 1): ?>
                <label>CATEGORY</label>
                <select onchange="updateFilter(this.value, 'category')" style="margin-right: 15px;">
                    <option value="">ALL</option>
                    <?php foreach ($categories as $cat => $count): ?>
                        <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterCategory === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>(<?php echo $count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if (count($releaseTypes) > 1): ?>
                <label>SORT BY</label>
                <select onchange="updateFilter(this.value, 'type')">
                    <option value="">ALL</option>
                    <?php foreach ($releaseTypes as $type => $count): ?>
                        <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterType === $type ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>(<?php echo $count; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <script>
        function updateFilter(value, param) {
            const url = new URL(window.location);
            if (value) {
                url.searchParams.set(param, value);
            } else {
                url.searchParams.delete(param);
            }
            // 他のパラメータも保持
            const category = url.searchParams.get('category') || '';
            const type = url.searchParams.get('type') || '';
            
            let newUrl = '/discography/';
            const params = [];
            if (category) params.push('category=' + encodeURIComponent(category));
            if (type) params.push('type=' + encodeURIComponent(type));
            if (params.length > 0) {
                newUrl += '?' + params.join('&');
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

