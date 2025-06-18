<?php
/**
 * Плагин для автоматической замены src изображений на [[!pthumb]].
 * Не добавляет width/height в style, только как атрибуты.
 * Срабатывает на событии OnBeforeDocFormSave.
 */

$eventName = $modx->event->name;

if ($eventName === 'OnBeforeDocFormSave') {
    // Получаем содержимое ресурса
    $content = $resource->get('content');

    // Регулярное выражение для поиска тега <img>
    $pattern = '/<img([^>]*)src=["\']([^"\']+)["\']([^>]*)>/i';

    // Замена для каждого <img>
    $content = preg_replace_callback($pattern, function ($matches) use ($modx) {
        $attributesBefore = $matches[1]; // Атрибуты перед src
        $src = $matches[2]; // Значение src
        $attributesAfter = $matches[3]; // Атрибуты после src

        // Удаляем лишние символы "/" и пробелы
        $attributesBefore = preg_replace('/\s+\/\s*/', ' ', $attributesBefore);
        $attributesAfter = preg_replace('/\s+\/\s*/', ' ', $attributesAfter);

        // Удаляем все существующие атрибуты width и height
        $attributesBefore = preg_replace('/\s*width=["\']\d+["\']/', '', $attributesBefore);
        $attributesAfter = preg_replace('/\s*width=["\']\d+["\']/', '', $attributesAfter);
        $attributesBefore = preg_replace('/\s*height=["\']\d+["\']/', '', $attributesBefore);
        $attributesAfter = preg_replace('/\s*height=["\']\d+["\']/', '', $attributesAfter);

        // Удаляем width и height из style (оставляем остальные style свойства)
        $removeSizesFromStyle = function($str) {
            return preg_replace_callback('/style=["\']([^"\']*)["\']/', function($styleMatch) {
                // Удаляем width: ...; и height: ...; из style
                $style = $styleMatch[1];
                $style = preg_replace('/\bwidth\s*:\s*\d+px;?/i', '', $style);
                $style = preg_replace('/\bheight\s*:\s*\d+px;?/i', '', $style);
                $style = trim($style);
                // Если после очистки остались стили, возвращаем style, иначе убираем style атрибут
                return $style ? 'style="' . $style . '"' : '';
            }, $str);
        };
        $attributesBefore = $removeSizesFromStyle($attributesBefore);
        $attributesAfter = $removeSizesFromStyle($attributesAfter);

        // Ищем ширину и высоту в атрибутах
        preg_match('/width=["\'](\d+)["\']/', $attributesBefore . $attributesAfter, $widthMatch);
        preg_match('/height=["\'](\d+)["\']/', $attributesBefore . $attributesAfter, $heightMatch);

        // Также ищем ширину и высоту в style
        preg_match('/style=["\'][^"\']*width\s*:\s*(\d+)px;?[^"\']*["\']/', $matches[0], $styleWidthMatch);
        preg_match('/style=["\'][^"\']*height\s*:\s*(\d+)px;?[^"\']*["\']/', $matches[0], $styleHeightMatch);

        // Определяем итоговые значения ширины и высоты
        $width = isset($widthMatch[1]) ? $widthMatch[1] : (isset($styleWidthMatch[1]) ? $styleWidthMatch[1] : '');
        $height = isset($heightMatch[1]) ? $heightMatch[1] : (isset($styleHeightMatch[1]) ? $styleHeightMatch[1] : '');

        // Если размеры не указаны, не заменяем src
        if (empty($width) || empty($height)) {
            return $matches[0];
        }

        // Формируем сниппет [[!pthumb]]
        $pthumbSnippet = '[[!pthumb? &input=`' . $src . '` &options=`w=' . $width . '&h=' . $height . '&f=webp`]]';

        // Возвращаем измененный тег <img> с корректными атрибутами width и height, без width/height в style
        return '<img' . $attributesBefore . 'src="' . $pthumbSnippet . '"' . $attributesAfter . ' width="' . $width . '" height="' . $height . '">';
    }, $content);

    // Сохраняем измененное содержимое
    $resource->set('content', $content);
    $resource->save();

    // Очистка кеша
    $modx->cacheManager->refresh();
}