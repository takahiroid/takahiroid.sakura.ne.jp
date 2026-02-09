<?php
// CMSデータを読み込む
function loadNewsDataForFront() {
    $newsDataFile = __DIR__ . '/../kanri/data/news.json';
    if (file_exists($newsDataFile)) {
        $json = file_get_contents($newsDataFile);
        $data = json_decode($json, true);
        if (!$data) {
            return [];
        }
        // データ構造が["news": {...}, "index": 0]形式の場合は変換
        if (isset($data[0]['news'])) {
            $normalizedData = [];
            foreach ($data as $item) {
                if (isset($item['news'])) {
                    $normalizedData[] = $item['news'];
                } else {
                    $normalizedData[] = $item;
                }
            }
            return $normalizedData;
        }
        return $data;
    }
    return [];
}

// XSS対策
function h($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// ニュースデータを読み込む
$allNewsData = loadNewsDataForFront();
$currentTime = time();

// 公開されている記事のみフィルタリング（元の順序を保持）
// LIVEページではLIVEカテゴリのみ表示
$newsData = [];
foreach ($allNewsData as $news) {
    // publishedがtrueでない場合はスキップ
    if (!($news['published'] ?? false)) {
        continue;
    }
    
    // LIVEカテゴリのみ表示
    $category = $news['category'] ?? '';
    if ($category !== 'LIVE') {
        continue;
    }
    
    // 公開終了日が過ぎている場合はスキップ
    if (!empty($news['end_date'] ?? '')) {
        $endDateStr = $news['end_date'];
        $endDateStr = str_replace('/', '-', $endDateStr);
        $endTimestamp = strtotime($endDateStr);
        if ($endTimestamp !== false && $currentTime > $endTimestamp) {
            continue;
        }
    }
    
    $newsData[] = $news;
}

// 管理画面の並び順（JSONファイルの順序）を保持するため、ソートしない
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="Keywords" content="まつもと,マツモト,タカヒロ,松本タカヒロ,タートルズ,ザ・タートルズ,turtles,餃子大王,sparky,まっちゃん" />
  <script src="//ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script type="text/javascript" src="/common/js/common.js"></script>
  <link rel="stylesheet" href="/common/css/style.css" type="text/css" />
  <link rel="stylesheet" href="/common/css/style_sp.css" type="text/css" />
  <link rel="stylesheet" href="/news/news.css" type="text/css" />
  <script src="../common/js/rollover.js" type="text/javascript"></script>


  <!-- PAGE TOPに戻るボタン -->
  <script type="text/javascript" src="../common/js/back_top.js"></script>

  <title>TAKAHIROID.COM -松本タカヒロ- LIVE</title>
</head>

<body id="live">
  <div class="wrapper">
    <?php include("../common/inc/header.php"); ?>

    <div class="contents">
      <div class="page-title">
        <h1>LIVE</h1>
      </div>
      
      <div class="wrap_area">
        <div class="news_left" style="width: 100%;">

          <table border="0" class="news_table">
					<?php 
					$totalNews = count($newsData);
					foreach ($newsData as $index => $news): ?>
						<?php
						$category = $news['category'] ?? '';
						// 管理画面で設定されたカテゴリの色を取得
						$categoryBgColor = '#70b539'; // デフォルト色
						if (!empty($category)) {
							$categoriesJsonPath = __DIR__ . '/../kanri/data/categories.json';
							if (file_exists($categoriesJsonPath)) {
								$categoriesData = json_decode(file_get_contents($categoriesJsonPath), true);
								if ($categoriesData) {
									foreach ($categoriesData as $cat) {
										$catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
										if ($catName === $category) {
											$categoryBgColor = is_array($cat) ? ($cat['color'] ?? '#70b539') : '#70b539';
											break;
										}
									}
								}
							}
						}
						$dateStr = $news['date'] ?? '';
						$dateDisplay = '';
						if (!empty($dateStr)) {
							$dateParts = explode(' ', $dateStr);
							$dateDisplay = str_replace('/', '/', $dateParts[0]) . ' update';
						}
						?>
						<tr>
							<td id="<?php echo h($news['id'] ?? ''); ?>" class="news-item">
								<hr class="news-divider">
								<div class="news-body">
									<?php if (!empty($news['thumbnail'] ?? '')): ?>
										<div class="news-thumb">
											<img src="<?php echo h($news['thumbnail']); ?>" class="newsimg thumbnail-clickable" alt="" data-full-image="<?php echo h($news['thumbnail']); ?>">
										</div>
									<?php endif; ?>
									<div class="txtBloc">
										<!-- カテゴリアイコン・サブカテゴリアイコン・更新日（同じ行） -->
										<p class="cdtitle">
											<span class="category-badge" style="background-color: <?php echo h($categoryBgColor); ?>;"><?php echo h($category); ?></span>
											<?php if (!empty($news['subcategory'] ?? '')): ?>
												<span class="subcategory-badge"><?php echo h($news['subcategory']); ?></span>
											<?php endif; ?>
											<?php if (!empty($dateDisplay)): ?>
												<span class="date-display"><?php echo h($dateDisplay); ?></span>
											<?php endif; ?>
										</p>
										<!-- イベントタイトル（改行） -->
										<?php if (!empty($news['title'] ?? '')): ?>
											<p class="ttl event-title">
												<?php echo str_replace('<br>', '<br>', $news['title']); ?>
											</p>
										<?php endif; ?>
										<!-- 日付と会場（1行） -->
										<?php if ($category === 'LIVE'): ?>
											<?php
											$liveDateStr = $news['live_date'] ?? '';
											$liveDateDisplay = '';
											if (!empty($liveDateStr)) {
												// YYYY/MM/DD形式から曜日を取得
												$liveDateFormatted = str_replace('/', '-', $liveDateStr);
												$timestamp = strtotime($liveDateFormatted);
												if ($timestamp !== false) {
													$weekdays = ['日', '月', '火', '水', '木', '金', '土'];
													$weekday = $weekdays[date('w', $timestamp)];
													$liveDateDisplay = h($liveDateStr) . '(' . $weekday . ')';
												} else {
													$liveDateDisplay = h($liveDateStr);
												}
											}
											?>
											<?php if (!empty($liveDateDisplay) || !empty($news['live_venue'] ?? '')): ?>
												<p class="live-date-venue">
													<?php if (!empty($liveDateDisplay)): ?>
														<span class="live-date-text"><?php echo $liveDateDisplay; ?></span>
													<?php endif; ?>
													<?php if (!empty($news['live_venue'] ?? '')): ?>
														<span class="venue-text">
															<?php if (!empty($news['title_url'] ?? '')): ?>
																<a href="<?php echo h($news['title_url']); ?>" target="_blank" class="bold"><?php echo str_replace('<br>', '<br>', $news['live_venue']); ?></a>
															<?php else: ?>
																<span class="bold"><?php echo str_replace('<br>', '<br>', $news['live_venue']); ?></span>
															<?php endif; ?>
														</span>
													<?php endif; ?>
												</p>
											<?php endif; ?>
										<?php endif; ?>
										
										<?php if ($category === 'LIVE'): ?>
											<?php if (!empty($news['live_performers'] ?? '')): ?>
												<p class="txt"><?php echo str_replace('<br>', '<br>', $news['live_performers']); ?><br></p>
											<?php endif; ?>
											<?php if (!empty($news['live_time'] ?? '')): ?>
												<p class="txt">■ 時間：<?php echo h($news['live_time']); ?><br></p>
											<?php endif; ?>
											<?php if (!empty($news['live_price'] ?? '')): ?>
												<p class="txt">■ 料金：<?php echo h($news['live_price']); ?><br></p>
											<?php endif; ?>
											<?php if (!empty($news['live_ticket_sales'] ?? []) && is_array($news['live_ticket_sales'])): ?>
												<p class="txt">■ チケット発売：
												<?php 
												$ticketLinks = [];
												foreach ($news['live_ticket_sales'] as $ticket) {
													if (!empty($ticket['name']) && !empty($ticket['url'])) {
														$ticketLinks[] = '<a href="' . h($ticket['url']) . '" target="_blank">' . h($ticket['name']) . '</a>';
													}
												}
												echo implode(' / ', $ticketLinks);
												?>
												<?php if (!empty($news['live_sale_date'] ?? '')): ?>
													<?php echo ' ' . h($news['live_sale_date']); ?>
												<?php endif; ?>
												<br></p>
											<?php endif; ?>
											<?php if (!empty($news['live_contact'] ?? '') || !empty($news['live_contact_url'] ?? '')): ?>
												<p class="txt">■ 問い合わせ：
												<?php if (!empty($news['live_contact_url'] ?? '')): ?>
													<a href="<?php echo h($news['live_contact_url']); ?>" target="_blank"><?php echo h($news['live_contact'] ?? $news['live_contact_url']); ?></a>
												<?php else: ?>
													<?php echo h($news['live_contact']); ?>
												<?php endif; ?>
												<br></p>
											<?php endif; ?>
											<?php if (!empty($news['live_other'] ?? '')): ?>
												<p class="txt"><?php echo str_replace('<br>', '<br>', $news['live_other']); ?></p>
											<?php endif; ?>
											<?php if (!empty($news['image'] ?? '')): ?>
												<br>
												<img src="<?php echo h($news['image']); ?>" class="newsimg article-image-clickable" alt="" data-full-image="<?php echo h($news['image']); ?>" style="cursor: pointer;">
											<?php endif; ?>
										<?php endif; ?>
									</div>
								</div>
								<?php if ($index === $totalNews - 1): ?>
									<hr class="news-divider">
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>

          </table>

        </div>
      </div>

    </div>
    <?php include("../common/inc/footer.php"); ?>

  </div>
  </div>

  <!-- 画像ポップアップモーダル -->
  <div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <img class="image-modal-content" id="modalImage">
  </div>

  <script>
    $(document).ready(function() {
      // サムネイル画像をクリックしたとき
      $('.thumbnail-clickable').on('click', function() {
        var fullImageSrc = $(this).data('full-image');
        $('#modalImage').attr('src', fullImageSrc);
        $('#imageModal').fadeIn(200);
        $('body').css('overflow', 'hidden'); // 背景のスクロールを無効化
      });

      // 記事下の画像をクリックしたとき
      $('.article-image-clickable').on('click', function() {
        var fullImageSrc = $(this).data('full-image');
        $('#modalImage').attr('src', fullImageSrc);
        $('#imageModal').fadeIn(200);
        $('body').css('overflow', 'hidden'); // 背景のスクロールを無効化
      });

      // 閉じるボタンをクリックしたとき
      $('.image-modal-close').on('click', function() {
        $('#imageModal').fadeOut(200);
        $('body').css('overflow', 'auto'); // 背景のスクロールを有効化
      });

      // モーダル背景をクリックしたとき
      $('#imageModal').on('click', function(e) {
        if ($(e.target).is('#imageModal')) {
          $('#imageModal').fadeOut(200);
          $('body').css('overflow', 'auto');
        }
      });

      // ESCキーで閉じる
      $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#imageModal').is(':visible')) {
          $('#imageModal').fadeOut(200);
          $('body').css('overflow', 'auto');
        }
      });
    });
  </script>
</body>

</html>

