<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM posts WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$post) { header('Location: dashboard.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $removeImage = isset($_POST['remove_image']);
  $newImagePath = $post['image'];

  if ($removeImage && !empty($post['image'])) {
    $old = __DIR__ . '/' . $post['image'];
    if (is_file($old)) @unlink($old);
    $newImagePath = null;
  }

  if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $tmp  = $_FILES['image']['tmp_name'];
      $size = (int)$_FILES['image']['size'];
      if ($size > 2 * 1024 * 1024) {
        $errors[] = 'Image must be ≤ 2MB.';
      } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) {
          $errors[] = 'Only JPG, PNG, GIF, WEBP allowed.';
        } else {
          if (!empty($post['image'])) {
            $old = __DIR__ . '/' . $post['image'];
            if (is_file($old)) @unlink($old);
          }
          $ext  = $allowed[$mime];
          $name = bin2hex(random_bytes(16)).'.'.$ext;
          $dir  = __DIR__ . '/uploads';
          if (!is_dir($dir)) mkdir($dir, 0775, true);
          $dest = $dir . '/' . $name;
          if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = 'Failed to upload image.';
          } else {
            $newImagePath = 'uploads/' . $name;
          }
        }
      }
    } else {
      $errors[] = 'Image upload error.';
    }
  }

  if ($title === '' || $content === '') {
    $errors[] = 'Title and content are required.';
  }

  if (!$errors) {
    $stmt = $conn->prepare("UPDATE posts SET title=?, content=?, image=? WHERE id=? AND user_id=?");
    $stmt->bind_param('sssii', $title, $content, $newImagePath, $id, $_SESSION['user_id']);
    if ($stmt->execute()) {
      $_SESSION['flash_success'] = 'Post updated.';
      header('Location: dashboard.php'); exit;
    } else {
      $errors[] = 'Database error.';
    }
    $stmt->close();
  }
}

include 'partials/header.php';
?>
<h2>Edit Post</h2>
<?php if ($errors): ?>
  <div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars',$errors)); ?></div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="mt-3">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($post['title']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" rows="6" class="form-control" required><?php echo htmlspecialchars($post['content']); ?></textarea>
  </div>

  <?php if (!empty($post['image'])): ?>
    <div class="mb-2">
      <label class="form-label d-block">Current Image</label>
      <img src="<?php echo htmlspecialchars($post['image']); ?>" class="img-fluid rounded mb-2" style="max-height:240px; object-fit:cover;">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
        <label class="form-check-label" for="remove_image">Remove current image</label>
      </div>
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label">Replace Image (optional)</label>
    <input type="file" name="image" class="form-control" accept="image/*">
  </div>

  <button class="btn btn-primary">Update</button>
</form>
<?php include 'partials/footer.php'; ?>



<style>
    body{
        
background-color: rgba(104, 103, 103, 0.15);
    }
</style>