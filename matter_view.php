<?php
// Legacy entry point: preserve old links while keeping one canonical workspace URL.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) $id = filter_input(INPUT_GET, 'matter_id', FILTER_VALIDATE_INT);
$reference = trim($_GET['reference'] ?? '');
if ($id) {
    header('Location: matters.php?id=' . $id, true, 302);
} elseif ($reference !== '') {
    header('Location: matters.php?reference=' . rawurlencode($reference), true, 302);
} else {
    header('Location: matters.php', true, 302);
}
exit;
