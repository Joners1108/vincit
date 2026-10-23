<?php
/**
 * Fotos de produto: capa + galeria em products.image / products.gallery (máx. 3 ficheiros no total).
 */
const VINCIT_MAX_PRODUCT_PHOTOS = 3;

if (!function_exists('getProductPhotosOrdered')) {
    /**
     * @param array $product Registo de products (image, gallery)
     * @return list<string> nomes de ficheiro em assets/img/products/, sem duplicados
     */
    function getProductPhotosOrdered(array $product): array {
        $main = isset($product['image']) ? trim((string) $product['image']) : '';
        $photos = [];
        if ($main !== '') {
            $photos[] = $main;
        }
        if (!empty($product['gallery'])) {
            foreach (explode(',', (string) $product['gallery']) as $fn) {
                $fn = trim($fn);
                if ($fn === '' || $fn === $main) {
                    continue;
                }
                $photos[] = $fn;
            }
        }
        return array_slice($photos, 0, VINCIT_MAX_PRODUCT_PHOTOS);
    }
}

/**
 * Grava uploads do campo photos[] para o diretório indicado (ex.: assets/img/products/) e devolve nomes seguros.
 *
 * @return list<string>
 */
function collectUploadedProductPhotos(string $uploadDir, string $field = 'photos', int $maxFiles = 10): array {
    $out = [];
    $maxFiles = max(1, $maxFiles);
    $uploadDir = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR;
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
        return $out;
    }
    $names = $_FILES[$field]['name'];
    $tmpNames = $_FILES[$field]['tmp_name'];
    $errors = $_FILES[$field]['error'];
    if (!is_array($names)) {
        return $out;
    }
    foreach ($names as $key => $origName) {
        if (!isset($errors[$key]) || $errors[$key] !== UPLOAD_ERR_OK) {
            continue;
        }
        $tmpName = $tmpNames[$key] ?? '';
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            continue;
        }
        $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $origName));
        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
        if (move_uploaded_file($tmpName, $targetPath)) {
            $out[] = $fileName;
            if (count($out) >= $maxFiles) {
                break;
            }
        }
    }
    return $out;
}

/**
 * @param list<string> $ordered
 * @return array{image: ?string, gallery: ?string}
 */
function splitMainAndGallery(array $ordered): array {
    $ordered = array_values(array_filter($ordered, static function ($f) {
        return is_string($f) && $f !== '';
    }));
    $ordered = array_slice($ordered, 0, VINCIT_MAX_PRODUCT_PHOTOS);
    if (count($ordered) === 0) {
        return ['image' => null, 'gallery' => null];
    }
    $image = $ordered[0];
    $rest = array_slice($ordered, 1);
    $gallery = count($rest) > 0 ? implode(',', $rest) : null;
    return ['image' => $image, 'gallery' => $gallery];
}
