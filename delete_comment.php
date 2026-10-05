<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

    if ($comment_id) {
        // Enforce ownership: only delete if the comment belongs to the logged-in user
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $_SESSION['user_id']]);
    }
}

header("Location: index.php");
exit;