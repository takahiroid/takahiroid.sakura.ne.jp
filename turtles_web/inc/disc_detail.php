<?php
$discTitle = (string)($disc['title'] ?? '');
$discSubtitle = trim((string)($disc['subtitle'] ?? ''));
$discDate = trim((string)($disc['release_date'] ?? ''));
$discPrice = trim((string)($disc['price'] ?? ''));
$discImage = discImageSrc($disc['image'] ?? '');
$discHtml = trim(discBody($disc['content'] ?? ''));
?>
<article class="disc-detail">
    <?php if ($discSubtitle !== ''): ?>
        <p class="disc-kicker"><?php echo discH($discSubtitle); ?></p>
    <?php endif; ?>
    <h1 class="disc-detail-title" id="disc-detail-title"><?php echo discH($discTitle); ?></h1>
    <div class="disc-detail-layout">
        <div class="disc-detail-image">
            <?php if ($discImage !== ''): ?>
                <img src="<?php echo discH($discImage); ?>" alt="<?php echo discH($discTitle); ?>">
            <?php else: ?>
                <div class="disc-image-placeholder">No Image</div>
            <?php endif; ?>
        </div>
        <div class="disc-detail-info">
            <?php if ($discDate !== '' || $discPrice !== ''): ?>
                <p class="disc-release">
                    <?php if ($discDate !== ''): ?><span>発売日：<?php echo discH($discDate); ?></span><?php endif; ?>
                    <?php if ($discPrice !== ''): ?><span>価格：¥<?php echo discH($discPrice); ?>(税込)</span><?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if ($discHtml !== ''): ?>
                <div class="disc-body"><?php echo $discHtml; ?></div>
            <?php endif; ?>
        </div>
    </div>
</article>
