<?php

function newsDataFile() {
    return dirname(__DIR__) . '/kanri/data/news.json';
}

function newsCategoriesFile() {
    return dirname(__DIR__) . '/kanri/data/categories.json';
}

function publicH($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function allowBr($str) {
    $escaped = publicH($str);
    $escaped = preg_replace('/&lt;br\s*\/?&gt;/i', '<br>', $escaped);
    return nl2br($escaped, false);
}

function plainText($str) {
    $text = preg_replace('/<br\s*\/?>/i', ' ', (string)$str);
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
}

function safeUrl($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $url) || strpos($url, '/') === 0) {
        return $url;
    }
    return '';
}

function loadPublicNews() {
    $file = newsDataFile();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function loadPublicCategories() {
    $file = newsCategoriesFile();
    if (!is_file($file)) {
        return [
            ['name' => 'LIVE', 'color' => '#D95D67'],
            ['name' => 'ニュース', 'color' => '#4ECDC4']
        ];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function categoryColor(array $categories, $name) {
    foreach ($categories as $category) {
        $categoryName = is_array($category) ? ($category['name'] ?? '') : $category;
        if ($categoryName === $name) {
            return is_array($category) ? ($category['color'] ?? '#D95D67') : '#D95D67';
        }
    }
    return '#D95D67';
}

function newsIsVisible(array $news, $now = null) {
    $now = $now ?? time();
    if (empty($news['published'])) {
        return false;
    }
    if (!empty($news['date'])) {
        $publishedAt = strtotime(str_replace('/', '-', $news['date']));
        if ($publishedAt !== false && $now < $publishedAt) {
            return false;
        }
    }
    if (!empty($news['end_date'])) {
        $endsAt = strtotime(str_replace('/', '-', $news['end_date']));
        if ($endsAt !== false && $now > $endsAt) {
            return false;
        }
    }
    return true;
}

function formatLiveDate($liveDate) {
    $timestamp = strtotime(str_replace('/', '-', (string)$liveDate));
    if ($timestamp === false) {
        return (string)$liveDate;
    }
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    return date('Y/n/j', $timestamp) . '(' . $weekdays[date('w', $timestamp)] . ')';
}

function newsHeadlineParts(array $news) {
    $lead = plainText($news['lead'] ?? '');
    if ($lead === '') {
        $lead = plainText($news['title'] ?? '');
    }
    $schedule = '';
    if (($news['category'] ?? '') === 'LIVE' && !empty($news['live_date'])) {
        $schedule = formatLiveDate($news['live_date']);
        if (!empty($news['live_venue'])) {
            $schedule .= ' ' . $news['live_venue'];
        }
    }
    return ['schedule' => $schedule, 'lead' => $lead];
}

function newsHeadlineText(array $news) {
    $parts = newsHeadlineParts($news);
    if ($parts['schedule'] === '') {
        return $parts['lead'];
    }
    if ($parts['lead'] === '') {
        return $parts['schedule'];
    }
    return $parts['schedule'] . ' ' . $parts['lead'];
}

function newsHeadlines($limit = 10) {
    $items = [];
    foreach (loadPublicNews() as $news) {
        if (!newsIsVisible($news) || empty($news['show_on_top']) || empty($news['id'])) {
            continue;
        }
        $items[] = $news;
        if (count($items) >= $limit) {
            break;
        }
    }
    return $items;
}

function splitPublicNews() {
    $live = [];
    $info = [];
    foreach (loadPublicNews() as $news) {
        if (!newsIsVisible($news) || empty($news['id'])) {
            continue;
        }
        if (($news['category'] ?? '') === 'LIVE') {
            $live[] = $news;
        } else {
            $info[] = $news;
        }
    }
    return [$live, $info];
}

function renderNewsArticle(array $news) {
    $category = $news['category'] ?? '';
    $isLive = $category === 'LIVE';
    $titleUrl = safeUrl($news['title_url'] ?? '');
    $title = allowBr($news['title'] ?? '');
    if ($title === '') {
        $title = publicH($news['lead'] ?? '');
    }
    $sideImage = '';
    if (!empty($news['thumbnail'])) {
        $sideImage = $news['thumbnail'];
    } elseif (!empty($news['image'])) {
        $sideImage = $news['image'];
    }
    $bodyImage = (!empty($news['image']) && $news['image'] !== $sideImage) ? $news['image'] : '';
    $youtubeId = $news['youtube_id'] ?? '';
    if (!is_string($youtubeId) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeId)) {
        $youtubeId = '';
    }
    ?>
    <article class="news-article<?php echo $sideImage !== '' ? ' has-thumb' : ''; ?>" id="<?php echo publicH($news['id']); ?>">
        <?php if ($sideImage !== ''): ?>
            <div class="news-thumb">
                <?php $popupImage = !empty($news['image']) ? $news['image'] : $sideImage; ?>
                <button type="button" class="news-thumb-button" data-full="<?php echo publicH($popupImage); ?>" aria-label="画像を拡大">
                <img src="<?php echo publicH($sideImage); ?>" alt="<?php echo publicH(plainText($news['title'] ?? $news['lead'] ?? '')); ?>">
                </button>
            </div>
        <?php endif; ?>
        <div class="news-copy">
        <p class="txtNewsTitle"><?php echo $title; ?></p>
        <div class="txtMargin1 news-body">
            <?php if ($isLive): ?>
                <?php
                $when = '';
                if (!empty($news['live_date'])) {
                    $when = formatLiveDate($news['live_date']);
                }
                if (!empty($news['live_venue'])) {
                    $when = trim($when . ' ' . $news['live_venue']);
                }
                ?>
                <?php if ($when !== ''): ?>
                    <p class="news-content-date">
                        <?php if ($titleUrl !== ''): ?>
                            <a href="<?php echo publicH($titleUrl); ?>"<?php echo strpos($titleUrl, '/') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>><?php echo publicH($when); ?></a>
                        <?php else: ?>
                            <?php echo publicH($when); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($news['live_performers'])): ?>
                    <p>■ 出演：<?php echo nl2br(publicH($news['live_performers'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($news['live_time'])): ?>
                    <p>■ <?php echo publicH($news['live_time']); ?></p>
                <?php endif; ?>
                <?php if (!empty($news['live_price'])): ?>
                    <p>■ 料金：<?php echo publicH($news['live_price']); ?></p>
                <?php endif; ?>
                <?php
                $ticketLinks = [];
                foreach ($news['live_ticket_sales'] ?? [] as $ticket) {
                    $ticketUrl = safeUrl($ticket['url'] ?? '');
                    $ticketName = trim($ticket['name'] ?? '');
                    if ($ticketName !== '' && $ticketUrl !== '') {
                        $ticketLinks[] = '<a href="' . publicH($ticketUrl) . '" target="_blank" rel="noopener noreferrer">' . publicH($ticketName) . '</a>';
                    } elseif ($ticketName !== '') {
                        $ticketLinks[] = publicH($ticketName);
                    }
                }
                ?>
                <?php if ($ticketLinks !== []): ?>
                    <p>■ チケット：<?php echo implode(' / ', $ticketLinks); ?><?php echo !empty($news['live_sale_date']) ? ' ' . publicH($news['live_sale_date']) : ''; ?></p>
                <?php endif; ?>
                <?php if (!empty($news['live_contact']) || !empty($news['live_contact_url'])): ?>
                    <p>■ お問い合わせ：
                        <?php $contactUrl = safeUrl($news['live_contact_url'] ?? ''); ?>
                        <?php if ($contactUrl !== ''): ?>
                            <a href="<?php echo publicH($contactUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo publicH($news['live_contact'] !== '' ? $news['live_contact'] : $contactUrl); ?></a>
                        <?php else: ?>
                            <?php echo publicH($news['live_contact']); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($news['live_other'])): ?>
                    <p><?php echo $news['live_other']; ?></p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($news['content']) && trim(strip_tags($news['content'])) !== ''): ?>
                <div class="news-content-html"><?php echo $news['content']; ?></div>
            <?php endif; ?>
            <?php if ($bodyImage !== ''): ?>
                <p><img class="news-image" src="<?php echo publicH($bodyImage); ?>" alt=""></p>
            <?php endif; ?>
            <?php if ($youtubeId !== ''): ?>
                <div class="video-container">
                    <iframe src="https://www.youtube.com/embed/<?php echo publicH($youtubeId); ?>" title="<?php echo publicH(plainText($news['title'] ?? '')); ?>" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </article>
    <?php
}
