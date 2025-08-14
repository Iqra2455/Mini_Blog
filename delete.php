<?php 
session_start();
if(empty($_SESSION['user_id'])){
    header('Location: login.php');
    exit;
}
require __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
// fetech post to verify 

$stmt = $conn->prepare("SELECT * FROM posts WHERE id =? AND user_id=? LIMIT 1 ") ;
$stmt->bind_param('li' , $id, $_SESSION['user_id']);
$stmt->execute();
$post= $stmt->get_result()->fetch_assoc();
$stmt->close();

if($post){
    if(!empty($post['image'])){
        $file = __DIR__ . '/' . $post['image'];
    if (is_file($file)) @unlink($file);
    }
     $del = $conn->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
  $del->bind_param('ii', $id, $_SESSION['user_id']);
  $del->execute();
  $del->close();
  $_SESSION['flash_success'] = 'Post deleted.';
}
header('Location: dashboard.php');
exit;
