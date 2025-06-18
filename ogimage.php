<?php
  $date = date('Y-m-d H:i:s');
$secret = 'SECRET_KEY';
function ogimage_log($message) {
    $logDir = __DIR__ . '/assets/PATH_TO_OGIMAGE';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logfile = $logDir . '/deb.log';
    $date = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$date] $message\n", FILE_APPEND);
}
//ogimage_log("ogimage.php started");
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

$isTelegram = stripos($userAgent, 'Telegram') !== false;
$isVK = stripos($userAgent, 'vkShare') !== false;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$title = isset($_GET['title']) ? trim($_GET['title']) : 'ЗАГОЛОВОК ЛЮБОЙ';
$longtitle = isset($_GET['longtitle']) ? trim($_GET['longtitle']) : 'ПОДПИСЬ ЛЮБАЯ';
$token = isset($_GET['token']) ? $_GET['token'] : '';

$expected_token = md5($id . $secret);

ogimage_log("Кто-то поделился материалом в $userAgent, title=$title с ID id=$id ");
//ogimage_log("expected_token=$expected_token");
if($isVK){
  ogimage_log("Это ВК-шаринг, id=$id, title=$title, longtitle=$longtitle");
}
if (!$id || $token !== $expected_token) {
//    ogimage_log("Access denied: id=$id, token=$token, expected=$expected_token");
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied!!!! Invalid parameters or token.');
}

$targetDir = __DIR__ . '/assets/imagesogdino/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}
$filename = $id . '_' . $token . '.png';
$filepath = $targetDir . $filename;
    mkdir($targetDir, 0755, true);

$filename = $id . '_' . $token . ($isTelegram ? '_tg' : '') . '.png';
$filepath = $targetDir . $filename;

if (file_exists($filepath)) {
    header('Location: /assets/imagesogdino/' . $filename);
    exit;
}
// --- ПАРАМЕТРЫ ИЗОБРАЖЕНИЯ ---
if ($isTelegram) {
    // Квадрат 800x800 с чёрным кругом и белым spooky.ru
    $size = 800;
    $im = imagecreatetruecolor($size, $size);
    $black = imagecolorallocate($im, 0, 0, 0);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefill($im, 0, 0, $black);
    imagefilledellipse($im, $size/2, $size/2, $size-40, $size-40, $black);

    $font = __DIR__ . '/Roboto.ttf';
    $text = "ЕЩЕ \n КАКОЙ-ТО ТЕКСТ";
    $fontSize = 62;

    // Центрируем spooky.ru по центру круга
    $bbox = imagettfbbox($fontSize, 0, $font, $text);
    $textWidth = $bbox[2] - $bbox[0];
    $textX = ($size - $textWidth) / 2;
    $textY = $size/2 + $fontSize / 2;
    imagettftext($im, $fontSize, 0, $textX, $textY, $white, $font, $text);

    imagepng($im, $filepath);
    imagedestroy($im);
    header('Location: /assets/imagesogdino/' . $filename);
    exit;
}
if($isVK) 
{
$width = 550;
$height = round($width / 2.25);
$footer = 'будет осуществлен переход из вк на сайт';
} else {
$width = 1200;
$height = 630;
$footer = 'другая подпись';
}
$im = imagecreatetruecolor($width, $height);

$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$gray = imagecolorallocate($im, 180, 180, 180);

imagefilledrectangle($im, 0, 0, $width, $height, $black);

// --- ШРИФТЫ ---
$font =  __DIR__ .'/Roboto.ttf';

// --- ТЕКСТ ---
// Основной заголовок
$titleSize = $isVK ? 18 : 42;
$titleBox = imagettfbbox($titleSize, 0, $font, $title);
$titleWidth = $titleBox[2] - $titleBox[0];
$titleX = ($width - $titleWidth) / 2;
$titleY = $height / 2 - 30;
imagettftext($im, $titleSize, 0, $titleX, $titleY, $white, $font, $title);

// Подзаголовок (longtitle)
if ($longtitle) {
  $longtitleSize = $isVK ? 10 : 31;    
    $longtitleBox = imagettfbbox($longtitleSize, 0, $font, $longtitle);
    $longtitleWidth = $longtitleBox[2] - $longtitleBox[0];
    $longtitleX = ($width - $longtitleWidth) / 2;
    $longtitleY = $titleY + 60;
    imagettftext($im, $longtitleSize, 0, $longtitleX, $longtitleY, $gray, $font, $longtitle);
}

// Подпись внизу spooky.ru

$footerSize = $isVK ? 12 : 24;
$footerBox = imagettfbbox($footerSize, 0, $font, $footer);
$footerWidth = $footerBox[2] - $footerBox[0];
$footerX = ($width - $footerWidth) / 2;
$footerY = $height - 30;
imagettftext($im, $footerSize, 0, $footerX, $footerY, $gray, $font, $footer);

// Сохраняем файл
imagepng($im, $filepath);
imagedestroy($im);

// Отдаём ссылку на изображение
header('Location: /assets/imagesogdino/' . $filename);
exit;