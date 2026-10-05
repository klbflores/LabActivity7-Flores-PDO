<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

    if ($post_id) {
        try {
            // Begin the atomic transaction (Slide 12)
            $pdo->beginTransaction();

            // 1. Delete associated comments first manually
            $delComments = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
            $delComments->execute([$post_id]);

            // 2. Delete the post ensuring ownership
            $delPost = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
            $delPost->execute([$post_id, $_SESSION['user_id']]);

            // Commit the multi-step write
            $pdo->commit();
        } catch (Throwable $e) {
            // Roll back if any step fails
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die("Transaction failed: " . $e->getMessage());
        }
    }
}

header("Location: index.php");
exit;