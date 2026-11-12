<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/app/inc/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Seul l’administrateur peut exporter l’historique des tâches.');
}

$sql = "SELECT t.id, t.title, t.description, t.due_date, t.status, t.created_at,
               creator.username AS creator_name, assigned.username AS assigned_name
        FROM tasks t
        LEFT JOIN users creator ON creator.id = COALESCE(t.created_by, t.user_id)
        LEFT JOIN users assigned ON assigned.id = t.assigned_to
        ORDER BY t.created_at DESC, t.id DESC";
$tasks = $pdo->query($sql)->fetchAll();

$statusLabels = [
    'pending' => 'En attente',
    'in_progress' => 'En cours',
    'completed' => 'Terminee'
];

function pdfText(string $text): string
{
    $text = str_replace(["\r", "\n"], ' ', trim($text));
    if (function_exists('iconv')) {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        if ($converted !== false) $text = $converted;
    }
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function pdfStream(array $lines): string
{
    $stream = "BT\n/F1 10 Tf\n50 790 Td\n14 TL\n";
    foreach ($lines as $index => $line) {
        if ($index > 0) $stream .= "T*\n";
        $stream .= '(' . pdfText($line) . ") Tj\n";
    }
    $stream .= "ET";
    return $stream;
}

$records = [];
foreach ($tasks as $task) {
    $createdAt = $task['created_at']
        ? date('d/m/Y H:i:s', strtotime($task['created_at']))
        : 'Non disponible';
    $dueDate = $task['due_date']
        ? date('d/m/Y', strtotime($task['due_date']))
        : 'Sans echeance';
    $recipient = $task['assigned_name'] ?: 'Tous les utilisateurs';
    $status = $statusLabels[$task['status']] ?? $task['status'];

    $records[] = "Tache #" . $task['id'] . " - " . ($task['title'] ?: 'Sans titre');
    $records[] = "Creee par : " . ($task['creator_name'] ?: 'Utilisateur inconnu');
    $records[] = "Destinataire : " . $recipient;
    $records[] = "Statut : " . $status;
    $records[] = "Echeance : " . $dueDate;
    $records[] = "Date de creation : " . $createdAt;
    $records[] = "Timestamp : " . ($task['created_at'] ?: 'Non disponible');
    $records[] = "Description : " . ($task['description'] ?: 'Aucune description.');
    $records[] = str_repeat('-', 90);
}

if (empty($records)) {
    $records = ['Aucune tache disponible dans l\'historique.'];
}

$pages = array_chunk($records, 38);
$objects = [];
$objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
$objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

$pageRefs = [];
$objectId = 4;
foreach ($pages as $pageNumber => $lines) {
    $pageObjectId = $objectId++;
    $contentObjectId = $objectId++;
    $pageRefs[] = $pageObjectId . ' 0 R';
    $stream = pdfStream(array_merge(
        ["Historique des taches - " . date('d/m/Y H:i:s')],
        ["Page " . ($pageNumber + 1) . "/" . count($pages)],
        $lines
    ));
    $objects[$contentObjectId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
    $objects[$pageObjectId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents $contentObjectId 0 R >>";
}
$objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($pageRefs) . " >>";
ksort($objects);

$pdf = "%PDF-1.4\n";
$offsets = [0];
foreach ($objects as $id => $object) {
    $offsets[$id] = strlen($pdf);
    $pdf .= $id . " 0 obj\n" . $object . "\nendobj\n";
}
$xrefOffset = strlen($pdf);
$maxId = max(array_keys($objects));
$pdf .= "xref\n0 " . ($maxId + 1) . "\n";
$pdf .= "0000000000 65535 f \n";
for ($id = 1; $id <= $maxId; $id++) {
    $pdf .= isset($offsets[$id])
        ? sprintf("%010d 00000 n \n", $offsets[$id])
        : "0000000000 00000 f \n";
}
$pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\n";
$pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

$filename = 'historique_taches_' . date('Y-m-d_His') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, max-age=0, must-revalidate');
echo $pdf;
exit;
