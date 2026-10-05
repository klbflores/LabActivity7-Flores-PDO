<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$comment = $stmt->fetch();

if (!$comment) {
    die("Access denied or comment does not exist.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_text = trim($_POST['comment'] ?? '');

    if (empty($comment_text)) {
        $errors[] = "Comment cannot be empty.";
    } else {
        $update = $pdo->prepare("UPDATE comments SET comment = ?, is_edited = 1 WHERE id = ? AND user_id = ?");
        $update->execute([$comment_text, $id, $_SESSION['user_id']]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Comment - Blog Site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-small">
        <div class="card">
            <h2>Edit Comment</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Comment</label>
                    <textarea name="comment" rows="4" required><?= htmlspecialchars($comment['comment']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Comment</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>