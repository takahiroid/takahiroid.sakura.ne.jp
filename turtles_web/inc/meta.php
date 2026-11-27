<?php
$turtles_site_name = 'ザ・タートルズ公式サイト';
$turtles_origin = 'https://turtles-web.com';

$page_heading = trim((string) ($page_heading ?? ''));
$page_description = trim((string) ($page_description ?? 'ザ・タートルズ（The Turtles）の公式サイトです。1994年に結成し、1997年にデビューした4人組。ライブ情報、ニュース、メンバー紹介を掲載しています。'));
$page_path = ltrim((string) ($page_path ?? ''), '/');
$page_robots = trim((string) ($page_robots ?? 'index, follow, max-image-preview:large'));
$page_type = trim((string) ($page_type ?? 'website'));

$document_title = $page_heading === '' ? $turtles_site_name : $page_heading . ' | ' . $turtles_site_name;
$canonical = $turtles_origin . '/' . $page_path;
$og_image = $turtles_origin . '/images/mainv_1.png';
$indexable = stripos($page_robots, 'noindex') === false;

$h = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
};

$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebSite',
            '@id' => $turtles_origin . '/#website',
            'url' => $turtles_origin . '/',
            'name' => $turtles_site_name,
            'inLanguage' => 'ja',
            'publisher' => ['@id' => $turtles_origin . '/#band'],
        ],
        [
            '@type' => 'MusicGroup',
            '@id' => $turtles_origin . '/#band',
            'name' => 'ザ・タートルズ',
            'alternateName' => ['The Turtles', 'タートルズ'],
            'url' => $turtles_origin . '/',
            'image' => $og_image,
            'foundingDate' => '1994',
            'genre' => 'ロック',
            'sameAs' => ['http://theturtles.exblog.jp/'],
            'member' => [
                ['@type' => 'OrganizationRole', 'member' => ['@type' => 'Person', 'name' => '松本タカヒロ'], 'roleName' => 'Vocal & Guitar'],
                ['@type' => 'OrganizationRole', 'member' => ['@type' => 'Person', 'name' => '道太郎'], 'roleName' => 'Bass'],
                ['@type' => 'OrganizationRole', 'member' => ['@type' => 'Person', 'name' => '岩見直明'], 'roleName' => 'Keyboards'],
                ['@type' => 'OrganizationRole', 'member' => ['@type' => 'Person', 'name' => '山本昇'], 'roleName' => 'Drums'],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonical . '#webpage',
            'url' => $canonical,
            'name' => $document_title,
            'description' => $page_description,
            'inLanguage' => 'ja',
            'isPartOf' => ['@id' => $turtles_origin . '/#website'],
            'about' => ['@id' => $turtles_origin . '/#band'],
        ],
    ],
];
$schema_json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
    <title><?php echo $h($document_title); ?></title>
    <meta name="description" content="<?php echo $h($page_description); ?>">
    <meta name="robots" content="<?php echo $h($page_robots); ?>">
    <link rel="canonical" href="<?php echo $h($canonical); ?>">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:site_name" content="<?php echo $h($turtles_site_name); ?>">
    <meta property="og:type" content="<?php echo $h($page_type); ?>">
    <meta property="og:title" content="<?php echo $h($document_title); ?>">
    <meta property="og:description" content="<?php echo $h($page_description); ?>">
    <meta property="og:url" content="<?php echo $h($canonical); ?>">
    <meta property="og:image" content="<?php echo $h($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="610">
    <meta property="og:image:alt" content="ザ・タートルズ">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $h($document_title); ?>">
    <meta name="twitter:description" content="<?php echo $h($page_description); ?>">
    <meta name="twitter:image" content="<?php echo $h($og_image); ?>">
    <meta name="theme-color" content="#232122">
<?php if ($indexable && $schema_json !== false): ?>
    <script type="application/ld+json"><?php echo $schema_json; ?></script>
<?php endif; ?>
