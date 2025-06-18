<?php
if ($modx->event->name === 'OnWebPagePrerender') {
    $output = &$modx->resource->_output;

    // 1x1 прозрачная заглушка
    $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    // Регулярка для поиска <img ...>
    $pattern = '/<img([^>]*?)src=["\']([^"\']+)["\']([^>]*)>/i';

    // Замена
    $output = preg_replace_callback($pattern, function($matches) use ($placeholder) {
        $before = $matches[1];
        $src = $matches[2];
        $after = $matches[3];

        // Пропускаем изображения, уже помеченные как ленивые
        if (strpos($before.$after, 'lazy') !== false) {
            return $matches[0];
        }

        // Добавляем класс lazy
        if (preg_match('/class=["\']([^"\']*)["\']/', $before.$after, $classMatch)) {
            $newClass = trim($classMatch[1] . ' lazy');
            $before = preg_replace('/class=["\']([^"\']*)["\']/', 'class="'.$newClass.' image"', $before.$after, 1);
        } else {
            $before .= ' class="lazy"';
        }

        // Конструируем новый тег
        return '<img'.$before.' src="'.$placeholder.'" data-src="'.$src.'"'.$after.'>';
    }, $output);
}