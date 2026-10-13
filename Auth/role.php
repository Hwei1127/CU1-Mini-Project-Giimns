<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentRole()
{
    return $_SESSION['user']['role'] ?? null;
}

function isGuest()
{
    return !isset($_SESSION['user']);
}

function isAdmin()
{
    return currentRole() === 'admin';
}

function isEditor()
{
    return in_array(currentRole(), ['editor', 'admin'], true);
}

function isUser()
{
    return in_array(currentRole(), ['user', 'editor', 'admin'], true);
}

/**
 * Guests go to Sign In; logged-in users with the wrong role get a 403 page.
 */
function requireRole(array $roles)
{
    if (isGuest()) {
        header('Location: Sign_In.php');
        exit;
    }
    if (!in_array(currentRole(), $roles, true)) {
        header('Location: No-access.php');
        exit;
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/* ---------- CSRF + flash messages ---------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function csrfOk(): bool
{
    return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '');
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="gx-alert gx-alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
}

function redirectTo(string $page): void
{
    header('Location: ' . $page);
    exit;
}

/* ---------- images ---------- */

function gameImageUrl(?string $file): string
{
    $file = basename((string) $file);
    if ($file === '') {
        return '';
    }
    foreach (['Assets/Images/games/', 'Assets/Images/'] as $dir) {
        if (is_file(__DIR__ . '/../' . $dir . $file)) {
            return $dir . rawurlencode($file);
        }
    }
    return '';
}

/** Saves an uploaded image into Assets/Images/games/ and returns its file name (or $old when nothing was uploaded). */
function saveUpload(string $field, ?string $old): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $old;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The ' . $field . ' upload failed. Please try again.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('The ' . $field . ' must be 5 MB or smaller.');
    }
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif', IMAGETYPE_WEBP => 'webp'];
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !isset($allowed[$info[2]])) {
        throw new RuntimeException('The ' . $field . ' must be a JPG, PNG, GIF or WEBP image.');
    }
    $dir = __DIR__ . '/../Assets/Images/games/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        throw new RuntimeException('Could not save the ' . $field . ' file.');
    }
    return $name;
}

// Users can delete their own review, admins can delete any review
// Returns ['error' => '...'] when something is wrong, otherwise ['ok' => true]
function deleteReview($pdo, $reviewId, $userId, $isAdmin)
{
    $stmt = $pdo->prepare('SELECT user_id FROM reviews WHERE id = ?');
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch();

    if (!$review) {
        return ['error' => 'That review no longer exists.'];
    }
    if ($review['user_id'] != $userId && !$isAdmin) {
        return ['error' => 'You can only delete your own reviews.'];
    }

    $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt->execute([$reviewId]);

    return ['ok' => true];
}
    ?>

