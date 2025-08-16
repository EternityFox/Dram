<?php declare(strict_types=1);

ob_start();
header('Content-Type: text/plain');

define('LOG_FILE', __DIR__ . '/storage/cron_fonts.log');

function saveLog()
{
    $lastSize = file_exists(LOG_FILE) ? filesize(LOG_FILE) : 0;
    $data = ob_get_contents();
    $size = strlen($data);
    if ($lastSize > $size)
        $data .= str_repeat(' ', ($lastSize - $size));

    $fh = fopen(LOG_FILE, 'w');
    fwrite($fh, $data);
    fclose($fh);
}

set_exception_handler(function(Throwable $e) {
    $error = get_class($e) . ': ' . $e->getMessage();
    error_log($error, 0);

    echo $error . "\r\n";
    var_dump($e);

    saveLog();
    exit;
});

$apiKey = 'AIzaSyB5GL1fbBYENrfJn5dIUKf7kQIjW-yWlTk';
$fontDir = __DIR__ . '/../fonts/'; // Адаптируйте путь к директории шрифтов
$indexFile = $fontDir . 'fonts.json'; // Файл для трекинга

if (!is_dir($fontDir)) {
    mkdir($fontDir, 0755, true);
}

// Функция для запроса API
function fetchGoogleFonts(string $apiKey): array
{
    $url = "https://www.googleapis.com/webfonts/v1/webfonts?key={$apiKey}&capability=WOFF2&sort=date";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if (isset($data['error'])) {
        throw new Exception('API error: ' . json_encode($data['error']));
    }
    return $data['items'] ?? [];
}

// Загружаем существующий индекс
$existingFonts = file_exists($indexFile) ? json_decode(file_get_contents($indexFile), true) : [];

// Получаем список шрифтов
$fonts = fetchGoogleFonts($apiKey);
$newDownloads = 0;

foreach ($fonts as $font) {
    $family = $font['family'];
    $subsets = $font['subsets'];

    // Фильтр: должен поддерживать все три subsets
    if (in_array('armenian', $subsets) && in_array('cyrillic', $subsets) && in_array('latin', $subsets)) {
        // Проверяем, скачан ли уже (по версии)
        if (!isset($existingFonts[$family]) || $existingFonts[$family]['version'] !== $font['version']) {
            // Скачиваем regular вариант в WOFF2 (если доступен)
            $fileUrl = $font['files']['regular'] ?? null;
            if ($fileUrl && str_ends_with($fileUrl, '.woff2')) {
                $fileName = strtolower(str_replace(' ', '-', $family)) . '.woff2';
                $filePath = $fontDir . $fileName;

                // Скачиваем
                $content = file_get_contents($fileUrl);
                if ($content === false) {
                    echo "Ошибка скачивания: {$family}\n";
                    continue;
                }
                file_put_contents($filePath, $content);

                // Обновляем индекс
                $existingFonts[$family] = [
                    'version' => $font['version'],
                    'lastModified' => $font['lastModified'],
                    'downloaded' => date('Y-m-d H:i:s')
                ];
                $newDownloads++;
                echo "Скачан новый/обновленный шрифт: {$family}\n";
            } else {
                echo "Нет regular WOFF2 для: {$family}\n";
            }
        }
    }
}

// Сохраняем обновленный индекс
file_put_contents($indexFile, json_encode($existingFonts, JSON_PRETTY_PRINT));

echo "Скачано новых/обновленных шрифтов: {$newDownloads}\n";

saveLog();