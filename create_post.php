<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title)) $errors[] = "Title is required.";
    if (empty($content)) $errors[] = "Content is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $content]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post - Blog Site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container-small">
        <div class="card">
            <h2>Create New Post</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="create_post.php">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="6" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Publish Post</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>