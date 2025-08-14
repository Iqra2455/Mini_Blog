<?php
session_start();
require __DIR__ . '/config/db.php';

if(!empty($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}
$email='';
$password='';
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password']?? '');
}

if($email ==='' || $password === ''){
    $errors[] ='Email and Password are required.'; 
}
else{
     $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    
    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = (int)$user['id'];
      $_SESSION['user_name'] = $user['name'];
      $_SESSION['flash_success'] = 'Welcome back!';
      header('Location: dashboard.php'); exit;
    } else {
      $errors[] = 'Invalid credentials.';
    }
  }

include 'partials/header.php';
?>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
<h2>Login</h2>
<?php if ($errors): ?>
    <div class="alert alert-danger"><?php echo implode('<br>', array_map('htmlspecialchars',$errors)); ?></div>
    <?php endif; ?>
    <form method="post" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="text" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary ">Login</button>
        
    </form>

                </div>
            </div>
        </div>
    </div>
</div>

    <?php include 'partials/footer.php';