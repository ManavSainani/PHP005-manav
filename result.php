<?php
// The form sends the selected painting and meme settings with a GET request.
require_once 'data.inc.php';

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('Choose a painting first.');
}

// Only allow filenames included in the supplied painting data.
$filename = basename((string) $_GET['file']);
$validFilenames = array_column($paintings, 'filename');
if (!in_array($filename, $validFilenames, true)) {
    http_response_code(400);
    exit('Invalid painting selection.');
}

$imagePath = __DIR__ . '/images/' . $filename . '.jpg';
$fontPath = __DIR__ . '/font/Lato-Medium.ttf';
if (!is_file($imagePath) || !is_file($fontPath)) {
    http_response_code(404);
    exit('The painting image or font could not be found.');
}

$image = imagecreatefromjpeg($imagePath);
if ($image === false) {
    http_response_code(500);
    exit('The painting could not be loaded.');
}

$white = imagecolorallocate($image, 255, 255, 255);
$text1 = trim((string) ($_GET['text1'] ?? ''));
$text2 = trim((string) ($_GET['text2'] ?? ''));
$size1 = max(12, min(64, (int) ($_GET['size1'] ?? 24)));
$size2 = max(12, min(48, (int) ($_GET['size2'] ?? 48)));

// Draw the two caption lines near the top and bottom of the original image.
if ($text1 !== '') {
    imagettftext($image, $size1, 0, 10, 100, $white, $fontPath, $text1);
}
if ($text2 !== '') {
    imagettftext($image, $size2, 0, 10, imagesy($image) - 100, $white, $fontPath, $text2);
}

// The thumbnail grid requests width=100; the form requests 500–1000 pixels.
$width = max(1, min(2000, (int) ($_GET['width'] ?? 100)));
$scaledImage = imagescale($image, $width, $width);
if ($scaledImage === false) {
    imagedestroy($image);
    http_response_code(500);
    exit('The painting could not be resized.');
}

header('Content-Type: image/jpeg');
imagejpeg($scaledImage);
imagedestroy($scaledImage);
imagedestroy($image);
