<?php
/**
 * Плагин для замены [[!pthumb]] на реальный src и style при редактировании ресурса.
 * Срабатывает на событии OnDocFormRender.
 */

$eventName = $modx->event->name;

// Логируем вызов плагина
//$modx->log(modX::LOG_LEVEL_ERROR, 'Плагин PThumbReverseProcessor вызван на событии: ' . $eventName);

if ($eventName === 'OnDocFormRender') {
    // Получаем содержимое ресурса
    $content = $resource->get('content');

    // Логируем содержимое до обработки
 //   $modx->log(modX::LOG_LEVEL_ERROR, 'Содержимое ресурса до обработки: ' . $content);

    // Регулярное выражение для поиска [[!pthumb]]
    $pattern = '/\[\[!pthumb\?\s*&input=`([^`]+)`\s*&options=`([^`]+)`\]\]/i';

    // Замена для каждого [[!pthumb]]
    $content = preg_replace_callback($pattern, function ($matches) use ($modx) {
        $src = $matches[1]; // Путь к изображению из &input=
        $options = $matches[2]; // Параметры из &options=

        // Ищем параметры w (ширина) и h (высота) в options
        preg_match('/w=(\d+)/', $options, $widthMatch);
        preg_match('/h=(\d+)/', $options, $heightMatch);

        $width = isset($widthMatch[1]) ? $widthMatch[1] : '';
        $height = isset($heightMatch[1]) ? $heightMatch[1] : '';

        // Формируем style с размерами, если они указаны
        //$style = '';
        //if (!empty($width)) {
        //    $style .= 'width:' . $width . 'px;';
        //}
        //if (!empty($height)) {
        //    $style .= 'height:' . $height . 'px;';
        //}

        // Логируем замену
 //       $modx->log(modX::LOG_LEVEL_ERROR, 'Заменён [[!pthumb]] на src: ' . $src . ' и style: ' . $style);

        // Возвращаем корректный тег <img> с реальным src и style
       // return $src . '" style="' . $style ;
		return $src;
    }, $content);

    // Логируем содержимое после обработки
  //  $modx->log(modX::LOG_LEVEL_ERROR, 'Содержимое ресурса после обработки: ' . $content);

    // Сохраняем изменённое содержимое в ресурс
    $resource->set('content', $content);
}