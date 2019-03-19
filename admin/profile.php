<?php

require_once '../functions.php';

weibo_get_current_user();

//查询当前用户
$current_user = weibo_get_current_user();
//确保不会因为修改用户个人信息而不能及时更新
$current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");

$real_user = $current_user;

$current_user_id = $current_user['id'];

$isOtherUser = false;

//判断是否查看他人中心
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['user'])) {
  $current_user_id = $_GET['user'];

  if ($current_user_id != $real_user['id']) {
    $isOtherUser = true;
  }

  //查找他人数据
  $current_user = weibo_fetch_one("select * from users where id = {$current_user_id};");
}

// 处理分页参数
// =========================================

$size = 20;
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
// 必须 >= 1 && <= 总页数

// $page = $page < 1 ? 1 : $page;
if ($page < 1) {
  // 跳转到第一页
  header('Location: /admin/profile.php?page=1');
}

// 只要是处理分页功能一定会用到最大的页码数
$total_count = (int)weibo_fetch_one("select count(1) as count from posts
where user_id = {$current_user_id};")['count'];

$total_pages = (int)ceil($total_count / $size);

if ($page > $total_pages && $total_pages != 0) {
  // 跳转到第最后页
  header('Location: /admin/follow_users.php?page=' . $total_pages);
}

$offset = ($page - 1) * $size;

//查询关注用户的微博
//select * from posts where user_id in (1,2,3);
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
where user_id = {$current_user_id}
order by posts.created desc
limit {$offset}, {$size};");

$comments = weibo_fetch_all("select 
  comments.id,
  comments.author,
  comments.email,
  comments.created,
  comments.content,
  comments.post_id,
  users.avatar as users_avatar
from comments 
inner join users on comments.email = users.email
order by created desc;");

// 处理分页页码
// ===============================

$visiables = 5;

// 计算最大和最小展示的页码
$begin = $page - ($visiables - 1) / 2;
$end = $begin + $visiables - 1;

$begin = $begin < 1 ? 1 : $begin; // 确保了begin不会小于1
$end = $begin + $visiables - 1; // 因为50行可能导致begin变化，这里同步两者关系
$end = $end > $total_pages ? $total_pages : $end; // 确保了end不会大于total_pages
$begin = $end - $visiables + 1; // 因为52可能改变了end，也就有可能打破begin和end的关系
$begin = $begin < 1 ? 1 : $begin; // 确保不能小于 1

function update_user () {
  $avatar = $_FILES['avatar'];
  //查询当前用户
  $current_user = weibo_get_current_user();
  //确保不会因为修改用户个人信息而不能及时更新
  $current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");

  $current_user_id = $current_user['id'];

  // 接收并保存
  $bio = $_POST['bio'];
  $interest = $_POST['interest'];

  if ($avatar['error'] == UPLOAD_ERR_OK) {
    $targetAvatar = '../static/uploads/' . uniqid() . '-' . $avatar['name'];
    if (!move_uploaded_file($avatar['tmp_name'], $targetAvatar)) {
      $GLOBALS['message'] = '上传图片失败';
    }

    $targetAvatar = substr($targetAvatar, 2);   
  }

  $new_avatar = $avatar['error'] == UPLOAD_ERR_OK ? $targetAvatar :  $current_user['avatar'];

  //update users set slug = 'admin', email = 'aaa', nickname = 'bbb' where slug = 'admin';

  $rows = weibo_execute("update users set bio = '{$bio}', interest = '{$interest}', avatar = '{$new_avatar}' where id = '{$current_user_id}';");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '更改失败！' : '更改成功！';

  if ($GLOBALS['success'] == true) {
    header('location: profile.php');
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  update_user();
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Profile</title>
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
      <div class="page-title">
        <h1>我的个人资料</h1>
      </div>
      <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <?php if (isset($message)): ?>
          <?php if ($success): ?>
          <div class="alert alert-success">
            <strong>成功！</strong> <?php echo $message; ?>
          </div>
          <?php else: ?>
          <div class="alert alert-danger">
            <strong>错误！</strong> <?php echo $message; ?>
          </div>
          <?php endif ?>
          <?php endif ?>
          <label class="col-sm-3 control-label" for="avatar">头像</label>
          <div class="col-sm-6">
            <img src="<?php echo $current_user['avatar'] ? $current_user['avatar'] : '/static/assets/img/default.png'; ?>" class='profile_avatar'>
            <?php if ($isOtherUser == false): ?>
              <input id="avatar" class="form-control" name="avatar" type="file">
            <?php endif ?>
          </div>
        </div>
        <div class="form-group">
          <label for="bio" class="col-sm-3 control-label">简介</label>
          <div class="col-sm-6">
            <textarea id="bio" class="form-control" name="bio" cols="30" rows="6" <?php echo ($isOtherUser == true) ? "readonly" : ""; ?>><?php echo $current_user['bio'] ?></textarea>
          </div>
        </div>
        <div class="form-group">
          <label for="email" class="col-sm-3 control-label">邮箱</label>
          <div class="col-sm-6">
            <input id="email" class="form-control" name="email" type="type" value="<?php echo $current_user['email'] ?>" readonly>
            <p class="help-block">登录邮箱不允许修改</p>
          </div>
        </div>
        <div class="form-group">
          <label for="interest" class="col-sm-3 control-label">兴趣爱好</label>
          <div class="col-sm-6">
            <input id="interest" class="form-control" name="interest" type="type" value="<?php echo $current_user['interest'] ?>" <?php echo ($isOtherUser == true) ? "readonly" : ""; ?>>
          </div>
        </div>
        <?php if ($isOtherUser == false): ?>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-6">
            <button type="submit" class="btn btn-primary">更新</button>
            <a class="btn btn-primary" href="password-reset.php">修改密码</a>
          </div>
        </div>
        <?php endif ?>
      </form>
      <div class="page-action">
        <h2 class="form-inline">已发布的微博</h2>
        <ul class="pagination pagination-sm pull-right" id="page_position">
          <li><a href="?user=<?php echo $current_user_id ?>&page=<?php echo ($page-1); ?>">上一页</a></li>
          <?php for ($i = $begin; $i <= $end; $i++): ?>
          <li<?php echo $i === $page ? ' class="active"' : '' ?>><a href="?user=<?php echo $current_user_id ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
          <?php endfor ?>
          <li><a href="?user=<?php echo $current_user_id ?>&page=<?php echo ($page+1); ?>">下一页</a></li>
        </ul>
      </div>  
      <ul class="list-group">
      <?php if ($posts != null):?>
        <?php foreach ($posts as $item): ?>
          <li class="list-group-item contents">
            <a href="profile.php?user=<?php echo $item['user_id'] ?>"><img src="<?php echo $item['users_avatar']; ?>" class="post_avatar"></a>
            <h1><?php echo $item['users_slug'] ?></h1>
            <h4 class="time"><?php echo $item['created']; ?></h4> 
            <?php if ($isOtherUser == false): ?>
              <a href="post-delete.php?id=<?php echo $item['id'] ?>" class="btn btn-danger delete_btn">删除微博</a>
            <?php endif ?>
            <hr> 
            <h3><?php echo $item['content']; ?></h3>
            <?php if (empty($item['feature']) != 1): ?>
            <div><img src="<?php echo $item['feature']; ?>" class="image"></div>
            <?php endif ?>
            <hr>
            <p>
              <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="<?php echo "#" . $item['id'] ?>" aria-expanded="false" aria-controls="<?php echo $item['id'] ?>">
                查看评论
              </button>
            </p>
            <div class="collapse" id="<?php echo $item['id'] ?>">
              <div class="card card-body">
                <?php foreach ($comments as $items): ?>
                  <?php if ($items['post_id'] == $item['id']): ?>
                    <img src="<?php echo $items['users_avatar']; ?>" class="comments_avatar">
                    <div class="comments_font"><?php echo $items['author'] ?></div>
                    <div class="comments_font"><?php echo $items['content']; ?></div>
                    <hr>
                  <?php endif ?>
                <?php endforeach ?>
              </div>
            </div>
          </li>
          <br>
          <br>
        <?php endforeach ?>
      <?php endif ?>
      </ul>            
    </div>
  </div>

  <?php $current_page = 'profile'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>