<?php
require_once __DIR__ . '/news_lib.php';
$headlines = newsHeadlines(10);
?>
<div class="news-headlines">
    <?php if ($headlines === []): ?>
        <p class="news-empty">現在、ニュースはありません。</p>
    <?php else: ?>
        <?php foreach ($headlines as $news): ?>
            <?php
            $href = 'news/#' . rawurlencode($news['id']);
            $target = '';
            if (($news['category'] ?? '') === 'You Tube' && safeUrl($news['youtube_url'] ?? '') !== '') {
                $href = safeUrl($news['youtube_url']);
                $target = ' target="_blank" rel="noopener noreferrer"';
            }
            ?>
            <?php $parts = newsHeadlineParts($news); ?>
            <p class="news-headline">
                <?php if (!empty($news['category'])): ?>
                    <span class="category-icon"><?php echo publicH($news['category']); ?></span>
                <?php endif; ?>
                <a href="<?php echo publicH($href); ?>"<?php echo $target; ?>>
                    <?php if ($parts['schedule'] !== ''): ?>
                        <span class="news-schedule"><?php echo publicH($parts['schedule']); ?></span>
                    <?php endif; ?>
                    <?php if ($parts['lead'] !== ''): ?>
                        <span class="news-lead"><?php echo publicH($parts['lead']); ?></span>
                    <?php endif; ?>
                </a>
            </p>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<p class="news-more"><a href="news/#live">すべてのライブ・ニュース →</a></p>
