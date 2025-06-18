<?php
use Tagger\Model\TaggerTag;
use Tagger\Model\TaggerTagResource;

$tag = $modx->stripTags(htmlentities($tag));
//$modx->log(1, 'tag: ' . $tag);
// Получаем id по алиасу
$tag = $modx->getObject(TaggerTag::class, ['alias' => $tag]);
if (!$tag) return '';

$q = $modx->newQuery(TaggerTagResource::class);
$q->where(['tag' => $tag->get('id')]);
$q->select('resource');
$ids = [];
if ($q->prepare() && $q->stmt->execute()) {
    while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = $row['resource'];
    }
}
return implode(',', $ids);