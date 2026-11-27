<?php

function discDataFile() {
    return dirname(__DIR__) . '/kanri/data/discography.json';
}

function loadPublicDiscography() {
    $file = discDataFile();
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function discSortKey($date) {
    $date = trim((string)$date);
    if (preg_match('/(\d{4})\D*(\d{1,2})?\D*(\d{1,2})?/', $date, $matches)) {
        $month = isset($matches[2]) && $matches[2] !== '' ? sprintf('%02d', (int)$matches[2]) : '00';
        $day = isset($matches[3]) && $matches[3] !== '' ? sprintf('%02d', (int)$matches[3]) : '00';
        return $matches[1] . $month . $day;
    }
    return '00000000';
}

function sortDiscography(array $items) {
    usort($items, function ($a, $b) {
        $byDate = strcmp(discSortKey($b['release_date'] ?? ''), discSortKey($a['release_date'] ?? ''));
        if ($byDate !== 0) {
            return $byDate;
        }
        return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
    });
    return $items;
}

function publishedDiscography() {
    $items = array_values(array_filter(loadPublicDiscography(), function ($item) {
        return !empty($item['published']);
    }));
    return sortDiscography($items);
}

function findPublicDiscography($id) {
    $id = (string)$id;
    if ($id === '') {
        return null;
    }
    foreach (publishedDiscography() as $item) {
        if ((string)($item['id'] ?? '') === $id) {
            return $item;
        }
    }
    return null;
}

function discH($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function discImageSrc($path) {
    $path = (string)$path;
    if (preg_match('#^/discography/img/[A-Za-z0-9._-]+$#', $path)) {
        return $path;
    }
    return '';
}

function discPlain($html) {
    $text = preg_replace('/<br\s*\/?>/i', ' ', (string)$html);
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

function discBody($html) {
    $html = (string)$html;
    $html = preg_replace('#<(script|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/\s(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2/i', '', $html);
    return $html;
}
