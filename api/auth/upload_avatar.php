<?php
/**
 * FinanceTrack API — Upload Avatar
 */

require_once __DIR__ . '/../../auth/middleware.php';

$userId = requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    sendJsonResponse(['success' => false, 'message' => 'Gagal mengunggah file.'], 400);
}

$file = $_FILES['avatar'];

// 1. Validasi ukuran (Maks 5MB)
if ($file['size'] > 5242880) {
    sendJsonResponse(['success' => false, 'message' => 'Ukuran file melebihi 5MB.'], 400);
}

// 2. Validasi MIME type
$allowedMimeTypes = ['image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedMimeTypes, true)) {
    sendJsonResponse(['success' => false, 'message' => 'Format file tidak didukung. Harap gunakan JPG atau PNG.'], 400);
}

// 3. Konversi / Simpan sebagai JPG
$uploadDir = __DIR__ . '/../../uploads/avatars';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$targetPath = $uploadDir . '/user_' . $userId . '.jpg';

// Gunakan GD untuk memproses gambar agar aman dari kode berbahaya yang menumpang dan ukurannya konsisten
if ($mime === 'image/png') {
    $image = @imagecreatefrompng($file['tmp_name']);
} else {
    $image = @imagecreatefromjpeg($file['tmp_name']);
}

if (!$image) {
    sendJsonResponse(['success' => false, 'message' => 'File gambar rusak atau tidak valid.'], 400);
}

// Crop to square if needed, or just save
$width = imagesx($image);
$height = imagesy($image);
$size = min($width, $height);
$squareImage = imagecreatetruecolor($size, $size);

// Fill white background for transparent PNGs
$white = imagecolorallocate($squareImage, 255, 255, 255);
imagefill($squareImage, 0, 0, $white);

imagecopyresampled($squareImage, $image, 0, 0, ($width - $size)/2, ($height - $size)/2, $size, $size, $size, $size);

if (!imagejpeg($squareImage, $targetPath, 85)) {
    sendJsonResponse(['success' => false, 'message' => 'Gagal menyimpan gambar.'], 500);
}

imagedestroy($image);
imagedestroy($squareImage);

sendJsonResponse([
    'success' => true,
    'message' => 'Foto profil berhasil diperbarui.',
    'avatar_url' => '../uploads/avatars/user_' . $userId . '.jpg?t=' . time()
]);
