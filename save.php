<?php
/**
 * save.php
 * -----------------------------------------------------------
 * Silently receives one finished session from index.html and
 * appends it to data/submissions.json on THIS server. Nothing
 * is ever downloaded or shown on the visitor's own device —
 * you check the results yourself later at view.php.
 * -----------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');

// only real submissions accepted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

// small helpers — keep this tolerant of odd/missing input rather than fatal-erroring
function s($v, $max = 300) {
    $v = is_string($v) ? $v : '';
    $v = trim(strip_tags($v));
    if (function_exists('mb_substr')) { return mb_substr($v, 0, $max); }
    return substr($v, 0, $max);
}
function n($v) {
    return is_numeric($v) ? (int)$v : 0;
}

$entry = [
    'receivedAt' => date('Y-m-d H:i:s'),
    'date'       => s($data['date']    ?? '', 60),
    'time'       => s($data['time']    ?? '', 20),
    'location'   => s($data['location'] ?? '', 60),
    'address'    => s($data['address'] ?? '', 500),
    'noCount1'   => n($data['noCount1'] ?? 0),
    'noCount2'   => n($data['noCount2'] ?? 0),
    'noCount3'   => n($data['noCount3'] ?? 0),
    'total'      => n($data['total']    ?? 0),
];

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0755, true);
}
$file = $dataDir . '/submissions.json';

// simple file lock so two near-simultaneous submits can't clobber each other
$fp = @fopen($file, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'cannot open storage file']);
    exit;
}

$ok = false;
if (flock($fp, LOCK_EX)) {
    $size = filesize($file);
    $contents = $size > 0 ? fread($fp, $size) : '';
    $entries = json_decode($contents, true);
    if (!is_array($entries)) { $entries = []; }
    $entries[] = $entry;

    ftruncate($fp, 0);
    rewind($fp);
    $ok = fwrite($fp, json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
    fflush($fp);
    flock($fp, LOCK_UN);
}
fclose($fp);

echo json_encode(['ok' => $ok]);
