<?php
require_once 'auth_check.php';
require_auth();
require 'db.php';

// Handle adding a new text comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $post_id      = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $comment_text = trim($_POST['comment'] ?? '');

    if ($post_id && !empty($comment_text)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $comment_text]);
    }
    header("Location: index.php");
    exit;
}

// Fetch all posts ordered newest-first with author usernames
$postStmt = $pdo->query("
    SELECT p.*, u.username 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$posts = $postStmt->fetchAll();

// Fetch all comments with author usernames
$commentStmt = $pdo->query("
    SELECT c.*, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    ORDER BY c.created_at ASC
");
$allComments = $commentStmt->fetchAll();

// Group comments by their parent post_id
$commentsByPost = [];
foreach ($allComments as $c) {
    $commentsByPost[$c['post_id']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsfeed - Blog Site</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <span class="nav-brand">Blog Site</span>
            <div class="nav-items">
                <span>Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <a href="create_post.php" class="btn btn-sm btn-primary">+ New Post</a>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </header>

    <main class="feed-container">
        <?php if (empty($posts)): ?>
            <div class="card"><p>No posts yet. <a href="create_post.php" style="color: var(--primary-color);">Be the first to post!</a></p></div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <article class="card post-card">
                <div class="post-header">
                    <div>
                        <h2><?= htmlspecialchars($post['title']) ?></h2>
                        <span class="meta">By <?= htmlspecialchars($post['username']) ?> on <?= date("M d, Y h:i A", strtotime($post['created_at'])) ?></span>
                        <?php if ($post['is_edited']): ?>
                            <span class="badge-edited">(Edited)</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                        <div class="post-action-group">
                            <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-secondary">Edit Post</a>
                            <form method="POST" action="delete_post.php" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="post-content">
                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                </div>

                <hr>

                <!-- Comments Section -->
                <div class="comments-section">
                    <h4>Comments</h4>
                    <?php $postComments = $commentsByPost[$post['id']] ?? []; ?>
                    <?php if (empty($postComments)): ?>
                        <p class="meta">No comments yet.</p>
                    <?php else: ?>
                        <?php foreach ($postComments as $comment): ?>
                            <div class="comment-box">
                                <div class="comment-header">
                                    <strong><?= htmlspecialchars($comment['username']) ?></strong>
                                    <span class="meta"><?= date("M d, h:i A", strtotime($comment['created_at'])) ?></span>
                                    <?php if ($comment['is_edited']): ?>
                                        <span class="badge-edited">(Edited)</span>
                                    <?php endif; ?>

                                    <?php if ($comment['user_id'] === $_SESSION['user_id']): ?>
                                        <div class="comment-actions">
                                            <a href="edit_comment.php?id=<?= $comment['id'] ?>" class="edit-link">Edit</a>
                                            <form method="POST" action="delete_comment.php" onsubmit="return confirm('Delete this comment?');">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <button type="submit" class="delete-link-btn">Delete</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Add Comment Form -->
                    <form method="POST" action="index.php" class="comment-form">
                        <input type="hidden" name="action" value="add_comment">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <input type="text" name="comment" required placeholder="Write a comment..." class="comment-input">
                        <button type="submit" class="btn btn-sm btn-primary">Comment</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </main>
</body>
</html>