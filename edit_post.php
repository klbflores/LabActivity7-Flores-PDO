<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch post ensuring the authenticated user is the owner
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    die("Access denied or post does not exist.");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title)) $errors[] = "Title cannot be empty.";
    if (empty($content)) $errors[] = "Content cannot be empty.";

    if (empty($errors)) {
        $update = $pdo->prepare("UPDATE posts SET title = ?, content = ?, is_edited = 1 WHERE id = ? AND user_id = ?");
        $update->execute([$title, $content, $id, $_SESSION['user_id']]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post - Blog Site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-small">
        <div class="card">
            <h2>Edit Post</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($post['title']) ?>">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>