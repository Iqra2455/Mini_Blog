<?php
require __DIR__ . '/config/db.php';
include 'partials/header.php';

// Search + Pagination
$q = trim($_GET['q'] ?? '');
$perPage = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Count total
if ($q !== '') {
  $like = "%{$q}%";
  $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM posts WHERE title LIKE ?");
  $stmt->bind_param('s', $like);
  $stmt->execute();
  $total = (int)$stmt->get_result()->fetch_assoc()['cnt'];
  $stmt->close();
} else {
  $res = $conn->query("SELECT COUNT(*) AS cnt FROM posts");
  $total = (int)$res->fetch_assoc()['cnt'];
}
$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch posts
if ($q !== '') {
  $stmt = $conn->prepare("
    SELECT p.*, u.name AS author
    FROM posts p JOIN users u ON p.user_id=u.id
    WHERE p.title LIKE ?
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param('sii', $like, $perPage, $offset);
} else {
  $stmt = $conn->prepare("
    SELECT p.*, u.name AS author
    FROM posts p JOIN users u ON p.user_id=u.id
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$posts = $stmt->get_result();
$stmt->close();
?><div class="d-flex align-items-center justify-content-between mb-3">
  <h2 class="mb-0">Latest Posts</h2>
 

  <form class="d-flex" method="get">
    <input type="text" name="q" class="form-control me-2" placeholder="Search title..." value="<?php echo htmlspecialchars($q); ?>">
    <button class="btn btn-outline-secondary">Search</button>
  </form>
</div>
 
  <br>
  <br>
  <br>

<div class="row">
<?php while ($post = $posts->fetch_assoc()): ?>
  <div class="col-md-4 mb-3"> <!-- 3 cards per row -->
    <div class="card h-100"  style="min-height: 300px;">
      <div class="card-body">
        <h5 class="card-title mb-1"><?php echo htmlspecialchars($post['title']); ?></h5>
        <div class="text-muted small mb-2">
          by <?php echo htmlspecialchars($post['author']); ?> • <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
        </div>
        <?php if (!empty($post['image'])): ?>
          <img src="<?php echo htmlspecialchars($post['image']); ?>" 
               alt="Post image" 
               class="img-fluid rounded mb-2" 
               style="max-height:150px; object-fit:cover; width:100%;">
        <?php endif; ?>
        <p class="card-text">
          <?php
            $excerpt = function_exists('mb_substr')
                ? mb_substr(strip_tags($post['content']), 0, 100)
                : substr(strip_tags($post['content']), 0, 100);
            echo nl2br(htmlspecialchars($excerpt)) . (strlen($post['content']) > 100 ? '…' : '');
          ?>
        </p>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation">
  <ul class="pagination">
    <?php $queryBase = $q !== '' ? 'q='.urlencode($q).'&' : ''; ?>
    <?php for ($i=1; $i<=$totalPages; $i++): ?>
      <li class="page-item <?php echo $i===$page ? 'active' : ''; ?>">
        <a class="page-link" href="?<?php echo $queryBase; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>



<?php include 'partials/footer.php'; ?>


<style>
 body{
background: linear-gradient(to right, #e3e3e3, #f7f7f7);
 }
  .card {
    max-width: 500px; /* width control */
    border-radius: 10px;
    
}

.card-body {
    padding: 8px; /* less padding */

}
.card-title {
    font-size: 14px; /* smaller title text */
}
.text-muted {
    font-size: 12px; /* smaller date text */
}
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card img {
    transition: transform 0.3s ease;
}
.card:hover img {
    transform: scale(1.05);
}


</style>