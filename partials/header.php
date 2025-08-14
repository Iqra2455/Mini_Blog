<!-- header + Navbar + Alerts -->

<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
$loggedIn = !empty($_SESSION['user_id']);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    if(!empty($_SESSION['flash_success'])){
        echo '<div  class="alert alert-success m-0 rounded-0 text-center" >'. htmlspecialchars($_SESSION['flash_success']).'</div>';
        unset($_SESSION['flash_success']);
    }

    if(!empty($_SESSION['flash_error'])){
        echo '<div class="alert alert-danger m-0 rounded-0 text-center ">'. htmlspecialchars($_SESSION['flash_error']).'</div>';
        unset($_SESSION['flash_error']);
    }
    ?>
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark">
        <div class="container">
            <a  class="navbar-brand fw-bold "    href="index.php">Mini Blog</a>
            <button class="navbar-toggler " type="button" data-bs-togle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon" ></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul  class="navbar-nav me-auto mb-2 mb-lg-0" >
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                     <?php if($loggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">My Posts</a></li>
                        <li class="nav-item"><a class="nav-link" href="create.php">Create Post</a></li>
                     <?php endif; ?>
                </ul> 
                 <ul class="navbar-nav ms-auto">
              <?php if ($loggedIn): ?>
            <li class="nav-item"><span class="navbar-text me-3">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span></li>
         <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a></li>
            <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>

            </div>
        </div>
    </nav>

    <div class="container py-4">
