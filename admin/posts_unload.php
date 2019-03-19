<?php

require_once '../functions.php';


//查询数据
$posts = weibo_fetch_all("select
  posts.id,
  categories.name as category_name,
  users.avatar as users_avatar,
  users.slug as users_slug,
  posts.created,
  posts.user_id,
  posts.content,
  posts.feature
from posts
inner join categories on posts.category_id = categories.id
inner join users on posts.user_id = users.id
order by posts.created desc
limit 0, 20;");

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <div class="">
      <a href="./login.php" class="btn btn-primary">返回</a>
    </div>
    <div class="container-fluid">
      <div class="page-title">
        <h2>微博</h2>
      </div>
      <ul class="list-group">
      <?php foreach ($posts as $item): ?>
          <li class="list-group-item contents">
            <img src="<?php echo $item['users_avatar']; ?>" class="post_avatar">
            <h1><?php echo $item['users_slug'] ?></h1>
            <h4 class="time"><?php echo $item['created']; ?></h4> 
            <hr> 
            <h3><?php echo $item['content']; ?></h3>
            <?php if (empty($item['feature']) != 1): ?>
            <div><img src="<?php echo $item['feature']; ?>" class="image"></div>
            <?php endif ?>          
          </li>
      <?php endforeach ?>
      </ul>
    </div>
  </div>

  <?php $current_page = 'posts_unload'; ?>

  <script>NProgress.done()</script>
</body>
</html>