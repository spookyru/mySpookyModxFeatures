<?php
$id = $modx->resource->get('id');
$secret = 'TEXT_KEY_FOR_OG_TOKEN'; // Замените на ваш секретный ключ
return md5($id . $secret);