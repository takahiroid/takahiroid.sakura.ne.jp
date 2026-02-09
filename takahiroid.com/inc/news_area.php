      <div class="news_area">
<?php
// 管理画面のニュースデータを読み込み
$newsJsonPath = __DIR__ . '/../kanri/data/news.json';
if (file_exists($newsJsonPath)) {
    $newsData = json_decode(file_get_contents($newsJsonPath), true);
    if ($newsData) {
        $today = date('Y/m/d');
        $displayCount = 0;
        $maxDisplay = 5; // 表示するニュース数

        foreach ($newsData as $news) {
            // 公開されていないものはスキップ
            if (!isset($news['published']) || $news['published'] !== true) {
                continue;
            }

            // 公開日が未来のものはスキップ
            if (!empty($news['date'])) {
                $publishDate = strtotime($news['date']);
                if ($publishDate !== false && $publishDate > time()) {
                    continue;
                }
            }

            // TOPページに表示する設定になっていないものはスキップ
            if (!isset($news['show_on_top']) || $news['show_on_top'] !== true) {
                continue;
            }

            // 終了日が過ぎているものはスキップ
            if (!empty($news['end_date'])) {
                $endDate = date('Y/m/d', strtotime($news['end_date']));
                if ($endDate < $today) {
                    continue;
                }
            }

            if ($displayCount >= $maxDisplay) {
                break;
            }

            // 表示するタイトルを決定
            $displayTitle = '';
            if (!empty($news['live_date'])) {
                // ライブ情報の場合
                $liveDateFormatted = date('Y/n/j（' . ['日','月','火','水','木','金','土'][date('w', strtotime($news['live_date']))] . '）', strtotime($news['live_date']));
                $displayTitle = $liveDateFormatted;
                if (!empty($news['lead'])) {
                    $displayTitle .= ' ' . htmlspecialchars($news['lead'], ENT_QUOTES, 'UTF-8');
                } elseif (!empty($news['title'])) {
                    $displayTitle .= ' ' . htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8');
                }
            } else {
                // その他のニュース
                if (!empty($news['lead'])) {
                    $displayTitle = htmlspecialchars($news['lead'], ENT_QUOTES, 'UTF-8');
                } elseif (!empty($news['title'])) {
                    $displayTitle = htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8');
                }
            }

            // リンク先を決定
            // LIVEカテゴリの場合は/live/、それ以外は/news/にリンク
            $category = $news['category'] ?? '';
            $baseUrl = ($category === 'LIVE') ? '/live/' : '/news/';
            $linkUrl = $baseUrl . '#' . htmlspecialchars($news['id'], ENT_QUOTES, 'UTF-8');
            $linkTarget = '';
            if (!empty($news['youtube_url'])) {
                $linkUrl = htmlspecialchars($news['youtube_url'], ENT_QUOTES, 'UTF-8');
                $linkTarget = ' target="_blank"';
            }

            // 更新日
            $updateDate = '';
            if (!empty($news['date'])) {
                $updateDate = date('Y/n/j', strtotime($news['date']));
            }

            // カテゴリアイコンのスタイルを決定
            $category = $news['category'] ?? '';
            $categoryIcon = '';
            if (!empty($category)) {
                // 管理画面で設定されたカテゴリの色を取得
                $categoryColor = '#70b539'; // デフォルト色
                $categoriesJsonPath = __DIR__ . '/../kanri/data/categories.json';
                if (file_exists($categoriesJsonPath)) {
                    $categoriesData = json_decode(file_get_contents($categoriesJsonPath), true);
                    if ($categoriesData) {
                        foreach ($categoriesData as $cat) {
                            $catName = is_array($cat) ? ($cat['name'] ?? '') : $cat;
                            if ($catName === $category) {
                                $categoryColor = is_array($cat) ? ($cat['color'] ?? '#70b539') : '#70b539';
                                break;
                            }
                        }
                    }
                }
                $categoryIcon = '<span class="category-icon" style="display: inline-block; padding: 2px 8px; background-color: ' . htmlspecialchars($categoryColor, ENT_QUOTES, 'UTF-8') . '; color: white; font-size: 11px; font-weight: bold; border-radius: 3px; margin-right: 6px; vertical-align: middle;">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            echo '<p class="ttl" style="margin-top: 10px;">' . $categoryIcon . '<a href="' . $linkUrl . '"' . $linkTarget . '>' . $displayTitle . '</a><span>【' . $updateDate . ' update】</span></p>' . "\n";

            $displayCount++;
        }
    }
}
?>
			</div>

