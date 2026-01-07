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
$newsData = [];
foreach ($allNewsData as $news) {
    // publishedがtrueでない場合はスキップ
    if (!($news['published'] ?? false)) {
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
  <script src="../common/js/rollover.js" type="text/javascript"></script>


  <!-- PAGE TOPに戻るボタン -->
  <script type="text/javascript" src="../common/js/back_top.js"></script>

  <title>TAKAHIROID.COM -松本タカヒロ- Home Page</title>
  <style type="text/css">
    .alert {
      font-size: 16px;
      border: 2px solid #ff0000;
      border-radius: 4px;
      padding: 1rem;
      width: 98%;
      margin: 0 auto;
      box-sizing: border-box;
    }
    
    .button-link {
      display: inline-block;
      background-color: #70b539;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      margin: 10px 0;
      transition: background-color 0.3s;
    }
    
    .button-link:hover {
      background-color: #5a9a2e;
      text-decoration: none;
      color: white;
    }
    
    .streaming-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 15px 0;
    }
    
    .streaming-button {
      display: inline-block;
      background-color: #333;
      color: white;
      padding: 6px 12px;
      border-radius: 3px;
      text-decoration: none;
      font-size: 12px;
      font-weight: bold;
      transition: all 0.3s ease;
      border: 1px solid #555;
      box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    
    .streaming-button:hover {
      background-color: #555;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.3);
      text-decoration: none;
      color: white;
    }
  </style>
</head>

<body id="news">
  <div class="wrapper">
    <?php include("../common/inc/header.php"); ?>

    <div class="sp_title sp">
      <p>Live&Media</p>
    </div>

    <div class="contents">
      <div class="wrap_area">
        <div class="news_left">

          <table border="0" class="news_table">
					<?php foreach ($newsData as $news): ?>
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
							<td id="<?php echo h($news['id'] ?? ''); ?>">
								<p class="cdtitle">
									<span style="background-color:<?php echo $categoryBgColor; ?>"><?php echo h($category); ?></span>
									<?php if (!empty($news['lead'] ?? '')): ?>
										<?php echo h($news['lead']); ?>
									<?php endif; ?>
									<?php if (!empty($dateDisplay)): ?>
										<span style="color: #666; font-size: 12px; margin: 5px 0;background-color: #fff;"><?php echo h($dateDisplay); ?></span>
									<?php endif; ?>
								</p>
								<div class="txtBloc">
									<?php if (!empty($news['title'] ?? '')): ?>
										<p class="ttl">
											<?php 
											// LIVEカテゴリの場合、ライブ日時をタイトルの前に表示（曜日付き）
											if ($category === 'LIVE' && !empty($news['live_date'] ?? '')) {
												$liveDateStr = $news['live_date'];
												// YYYY/MM/DD形式から曜日を取得
												$liveDateFormatted = str_replace('/', '-', $liveDateStr);
												$timestamp = strtotime($liveDateFormatted);
												if ($timestamp !== false) {
													$weekdays = ['日', '月', '火', '水', '木', '金', '土'];
													$weekday = $weekdays[date('w', $timestamp)];
													echo '<span style="color: #667eea; font-weight: 600; margin-right: 8px;">' . h($liveDateStr) . '(' . $weekday . ')</span>';
												} else {
													echo '<span style="color: #667eea; font-weight: 600; margin-right: 8px;">' . h($liveDateStr) . '</span>';
												}
											}
											?>
											<?php if (!empty($news['title_url'] ?? '')): ?>
												<a href="<?php echo h($news['title_url']); ?>" target="_blank" class="bold"><?php echo str_replace('<br>', '<br>', $news['live_venue']); ?></a>
											<?php else: ?>
												<span class="bold"><?php echo str_replace('<br>', '<br>', $news['live_venue']); ?></span>
											<?php endif; ?>
										</p>
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
											<?php if (!empty($news['title_url'] ?? '')): ?>
												<a href="<?php echo h($news['title_url']); ?>" target="_blank">
											<?php endif; ?>
											<img src="<?php echo h($news['image']); ?>" class="newsimg">
											<?php if (!empty($news['title_url'] ?? '')): ?>
												</a>
											<?php endif; ?>
										<?php endif; ?>
									<?php elseif ($category === 'You Tube'): ?>
										<?php if (!empty($news['youtube_id'] ?? '')): ?>
											<div style="margin: 15px 0;">
												<iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo h($news['youtube_id']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="max-width: 100%;"></iframe>
											</div>
										<?php elseif (!empty($news['youtube_url'] ?? '')): ?>
											<p class="txt">
												▶️ <a href="<?php echo h($news['youtube_url']); ?>" target="_blank" class="bold">YouTubeで視聴</a><br><br>
											</p>
										<?php endif; ?>
										<?php if (!empty($news['content'] ?? '')): ?>
											<p class="txt"><?php echo str_replace('<br>', '<br>', $news['content']); ?></p>
										<?php endif; ?>
										<?php if (!empty($news['image'] ?? '')): ?>
											<br>
											<?php if (!empty($news['youtube_url'] ?? '')): ?>
												<a href="<?php echo h($news['youtube_url']); ?>" target="_blank">
											<?php endif; ?>
											<img src="<?php echo h($news['image']); ?>" class="newsimg">
											<?php if (!empty($news['youtube_url'] ?? '')): ?>
												</a>
											<?php endif; ?>
										<?php endif; ?>
									<?php else: ?>
										<?php if (!empty($news['content'] ?? '')): ?>
											<p class="txt"><?php echo str_replace('<br>', '<br>', $news['content']); ?></p>
										<?php endif; ?>
										<?php if (!empty($news['image'] ?? '')): ?>
											<br>
											<img src="<?php echo h($news['image']); ?>" class="newsimg">
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>

					<!-- 


						<tr>
              <td>
                <p class="cdtitle">
                  <span style="background-color:#70b539">TV・GUITAR</span>
                  <br class="sp">NHK「うたコン」世良公則バンドのギターで生演奏で出演します。
                </p>
                <div class="txtBloc">
                  <p class="ttl"><a href="https://www.nhk.jp/p/utacon/ts/1J9MXY5QX2/episode/te/92PXLRWQN9/" target="_blank" class="bold">2024年7月9日（火）午後7:57〜午後8:42</a></p>
                  <p class="ttl bold" style="margin-top: 0.5rem">NHK「うたコン」<br>コラボ祭り!野口五郎×世良公則▽INI×新妻×クリスでBTS</p>
                  <p class="txt">

									野口五郎×世良公則が代表曲「私鉄沿線」「銃爪」でコラボ▽細川たかし×武田真治がチェッカーズ「ジュリアに傷心」▽渡辺美里×ＩＮＩ「Ｍｙ　Ｒｅｖｏｌｕｔｉｏｎ」▽丘みどり×すぎもとまさと・ちあきなおみ名曲「かもめの街」▽新妻聖子×クリス・ハート×ＩＮＩがＢＴＳ「Ｄｙｎａｍｉｔｅ」▽ＭＩＮＭＩ代表曲「シャナナ☆」▽細川たかし「男船」▽真田ナオキ「２４６」▽ＩＮＩ「ＬＯＵＤ」▽ＮＨＫホールから生放送
                  </p>
                </div>
              </td>
            </tr> -->

            <!-- <tr>
              <td>
                <p class="cdtitle"><span style="background-color:#70b539">SHOP・GOODS</span><br>ヴィレッジヴァンガードの新型ショッピングプラットホーム「voon」にて「松本タカヒロ」書き下ろしキャラのセレクトショップがスタートしました</p>
                <div class="txtBloc">
                  <p class="ttl"><a href="https://voon.shop/users/12896fc9-3ed3-44b8-85db-71d6c5e267df" target="_blank" class="bold">Village Vangurd 「voon」</a></p>
                  <p class="ttl bold" style="margin-top: 0.5rem"></p>
                  <p class="txt">
                    以前にSNSで公開した落書きキャラ。なんと！<br>
                    ヴィレッジヴァンガードさんから、商品化のオファーを頂きました。<br>
                    <br>
                    ヴィレヴァンの新型ショッピングプラットホーム「voon」にて<br>
                    松本タカヒロオリジナルキャラクターのセレクトショップがスタートしました<br>
                    今回の為に描き下ろしました、是非ヴィレヴァンでチェックしてねー
                    <br><br>
                    サイトはこちら<br>
                    <a href="https://voon.shop/users/12896fc9-3ed3-44b8-85db-71d6c5e267df" target="_blank">Village Vangurd 「voon」</a>
                    <br>

                  </p>
                  <div style="width: 100%;"><img src="img/voon.jpeg" style="width: 100%;"></div>
                </div>
              </td>
            </tr> -->



          </table>

					<div class="sp">
					<?php include("inc_sidebar_list.php"); ?>
					</div>
        </div>

				<?php include("inc_sidebar.php"); ?>
      </div>

    </div>
    <?php include("../common/inc/footer.php"); ?>

  </div>
  </div>
</body>

</html>