<?php
session_start();
if(empty($_SESSION['user_id'])){
    header("Location: login.php");
exit;
}
require __DIR__ . '/config/db.php';

include 'partials/header.php';

$uid = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt -> bind_param('i', $uid);
$stmt -> execute();
$posts = $stmt->get_result();
$stmt->close();
?>


<h2>MY Posts </h2>
<a href="create.php" style="width:20%; height: 50px; font-size: 25px; font-weight: 100;"   class="btn btn-primary btn-sm mb-3">+Create New</a>

<?php if($posts->num_rows===0):?>
    <div class="alert alert-info ">No Post yet.</div>
    <?php endif;?>


    <?php  while($p = $posts->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body d-flex shadow-sm border rounded-3">
                <?php if(!empty($p['image'])): ?>
                     <img src="<?php echo htmlspecialchars($p['image']); ?>" style="height:120px;width:190px;object-fit:cover;" class="rounded me-3" alt="">

                     <?php endif; ?>
                       <div class="flex-grow-1">
        <h5 class="card-title mb-1"><?php echo htmlspecialchars($p['title']); ?></h5>
        <div class="text-muted small mb-2"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></div>
        <div>
          <a class="btn btn-outline-secondary btn-sm" href="edit.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
          <a class="btn btn-outline-danger btn-sm" href="delete.php?id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete this post?');">Delete</a>
        </div>
            </div>
        </div>
        </div>

        <?php endwhile; ?>

        <?php include 'partials/footer.php'; ?>




    <style>
        body{
            
background-color: rgba(104, 103, 103, 0.15);
        }
        .card-body {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card-body:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    background-color: #f8f9fa; /* halka background change */
}

    </style>