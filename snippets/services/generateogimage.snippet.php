<?php
/**
 * Сниппет для генерации Open Graph изображения.
 * Используется для создания изображения, если нет заданного ogImage или imageIntro.
 */
$site_url = $modx->getOption('site_url');

$id = $modx->resource->get('id');
$title = $modx->resource->get('pagetitle');
$longtitle = $modx->resource->get('longtitle');

$token = $modx->runSnippet('ogToken'); // Получаем токен для проверки доступа к изображению

$page = $modx->getObject('modResource', $id);
$og = $page->getTVValue('ogImage');
$intro = $page->getTVValue('imageIntro');

echo $id . ' ' . $og . ' ' . $intro;

if ($og) {
    $pthumb = $modx->runSnippet('pthumb', [
        'input' => $og,
        'options' => 'w=1200&f=webp'
    ]);
    return $site_url . $pthumb;
}

if ($intro) {
    $pthumb = $modx->runSnippet('pthumb', [
        'input' => $intro,
        'options' => 'w=1200&f=webp'
    ]);
    return $site_url . $pthumb;
}

return $site_url . "ogimage.php?id=$id&title=" . urlencode($title) . "&longtitle=" . urlencode($longtitle) . "&token=$token";
