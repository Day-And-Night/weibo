<?php

require_once '../functions.php';

// 判断用户是否登录
weibo_get_current_user();

// 获取界面所需要的数据
$posts_count = weibo_fetch_one('select count(1) as num from posts;')['num'];

$users_count = weibo_fetch_one('select count(1) as num from users;')['num'];

$comments_count = weibo_fetch_one('select count(1) as num from comments;')['num'];

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Dashboard &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
  <script>NProgress.start()</script>

  <div class="main">
    <?php include 'inc/navbar.php'; ?>

    <div class="container-fluid">
      <div class="jumbotron text-center">
        <h1>微博</h1>
        <p>随时随地发现新鲜事！</p>
        <p><a class="btn btn-primary btn-lg" href="post-add.php" role="button">写微博</a></p>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">网站内容统计：</h3>
            </div>
            <ul class="list-group">
              <li class="list-group-item"><strong><?php echo $posts_count; ?></strong>篇微博</li>
              <li class="list-group-item"><strong><?php echo $users_count; ?></strong>个用户</li>
              <li class="list-group-item"><strong><?php echo $comments_count; ?></strong>条评论</li>
            </ul>
          </div>
        </div>
        <div class="col-md-4"></div>
      </div>
    </div>
  </div>

  <?php $current_page = 'index'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script src="/static/assets/vendors/chart/Chart.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
