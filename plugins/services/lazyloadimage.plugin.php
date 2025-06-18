<?php
/**
 * Плагин для добавления lazyload и очистки style от width/height у <img>.
 * Срабатывает на событии OnWebPagePrerender.
 */

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
        if (strpos($before . $after, 'lazy') !== false) {
            // Костыль: чистим width/height из style и возвращаем оригинал
            $before = preg_replace_callback('/style=["\']([^"\']*)["\']/', function($styleMatch) {
                $style = $styleMatch[1];
                // Удаляем width: ...; и height: ...; из style
                $style = preg_replace('/\bwidth\s*:\s*\d+px;?/i', '', $style);
                $style = preg_replace('/\bheight\s*:\s*\d+px;?/i', '', $style);
                $style = trim($style);
                // Если после очистки остались стили, возвращаем style, иначе убираем style атрибут
                return $style ? 'style="' . $style . '"' : '';
            }, $before);
            $after = preg_replace_callback('/style=["\']([^"\']*)["\']/', function($styleMatch) {
                $style = $styleMatch[1];
                $style = preg_replace('/\bwidth\s*:\s*\d+px;?/i', '', $style);
                $style = preg_replace('/\bheight\s*:\s*\d+px;?/i', '', $style);
                $style = trim($style);
                return $style ? 'style="' . $style . '"' : '';
            }, $after);
            return '<img' . $before . 'src="' . $src . '"' . $after . '>';
        }

        // Добавляем класс lazy и image
        if (preg_match('/class=["\']([^"\']*)["\']/', $before . $after, $classMatch)) {
            $newClass = trim($classMatch[1] . ' lazy');
            $before = preg_replace('/class=["\']([^"\']*)["\']/', 'class="' . $newClass . ' image"', $before . $after, 1);
        } else {
            $before .= ' class="lazy image"';
        }

        // Костыль: чистим width/height из style
        $before = preg_replace_callback('/style=["\']([^"\']*)["\']/', function($styleMatch) {
            $style = $styleMatch[1];
            $style = preg_replace('/\bwidth\s*:\s*\d+px;?/i', '', $style);
            $style = preg_replace('/\bheight\s*:\s*\d+px;?/i', '', $style);
            $style = trim($style);
            return $style ? 'style="' . $style . '"' : '';
        }, $before);
        $after = preg_replace_callback('/style=["\']([^"\']*)["\']/', function($styleMatch) {
            $style = $styleMatch[1];
            $style = preg_replace('/\bwidth\s*:\s*\d+px;?/i', '', $style);
            $style = preg_replace('/\bheight\s*:\s*\d+px;?/i', '', $style);
            $style = trim($style);
            return $style ? 'style="' . $style . '"' : '';
        }, $after);

        // Конструируем новый тег
        return '<img' . $before . 'src="' . $placeholder . '" data-src="' . $src . '"' . $after . '>';
    }, $output);
}