<?php
$site_url = $modx->getOption('site_url');
$og = $modx->resource->get('ogImage');
$intro = $modx->resource->get('imageIntro');
if ($og) {
    return $site_url . $modx->runSnippet('pthumb', [
        'input' => $og,
        'options' => 'w=1200&f=webp'
    ]);
}
if ($intro) {
    return $site_url . $modx->runSnippet('pthumb', [
        'input' => $intro,
        'options' => 'w=1200&f=webp'
    ]);
}
$id = $modx->resource->get('id');
$title = $modx->resource->get('pagetitle');
$longtitle = $modx->resource->get('longtitle');
$token = $modx->runSnippet('ogToken');
return $site_url . "ogimage.php?id=$id&title=" . urlencode($title) . "&longtitle=" . urlencode($longtitle) . "&token=$token";