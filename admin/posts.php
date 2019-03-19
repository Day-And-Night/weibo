<?php

require_once '../functions.php';

weibo_get_current_user();

// 接收筛选参数
// ==================================

$where = '1 = 1';  //防止sql注入
$search = '';

// 分类筛选
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
  $where .= ' and posts.category_id = ' . $_GET['category'];
  $search .= '&category=' . $_GET['category'];
}

// 处理分页参数
// =========================================

$size = 20;
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
// 必须 >= 1 && <= 总页数

// $page = $page < 1 ? 1 : $page;
if ($page < 1) {
  // 跳转到第一页
  header('Location: /admin/posts.php?page=1' . $search);
}

// 只要是处理分页功能一定会用到最大的页码数
$total_count = (int)weibo_fetch_one("select count(1) as count from posts
inner join categories on posts.category_id = categories.id
inner join users on posts.user_id = users.id
where {$where};")['count'];

$total_pages = (int)ceil($total_count / $size);

// $page = $page > $total_pages ? $total_pages : $page;
if ($page > $total_pages && $total_pages != 0) {
  // 跳转到第最后页
  header('Location: /admin/posts.php?page=' . $total_pages . $search);
}

// 获取全部数据
// ===================================

// 计算出越过多少条
$offset = ($page - 1) * $size;

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
where {$where}
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

//var_dump($comments);

// 查询全部的分类数据
$categories = weibo_fetch_all('select * from categories;');

// 处理分页页码

$visiables = 5;

// 计算最大和最小展示的页码
$begin = $page - ($visiables - 1) / 2;
$end = $begin + $visiables - 1;

// begin > 0  end <= total_pages
$begin = $begin < 1 ? 1 : $begin; // 确保了begin不会小于1
$end = $begin + $visiables - 1; // 因为50行可能导致begin变化这里同步两者关系
$end = $end > $total_pages ? $total_pages : $end; // 确保了end不会大于total_pages
$begin = $end - $visiables + 1; // 因为52可能改变了end也就有可能打破begin和end的关系
$begin = $begin < 1 ? 1 : $begin; // 确保不能小于1

function add_comments () {
  //查询当前用户
  $current_user = weibo_get_current_user();
  //确保不会因为修改用户个人信息而不能及时更新
  $current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");

  if (empty($_POST['comments'])) {
    $GLOBALS['message'] = '请输入评论';
    $GLOBALS['success'] = false;
    return;
  }

  $add_comments = $_POST['comments'];
  $created = date("Y-m-d H:i:s");
  $post_id = $_POST['post_id'];
  $current_user_slug = $current_user['slug'];
  $current_user_email = $current_user['email'];

  //insert into comments values (null, '{$author}', '{$email}', '{created}', '{content}', '{post_id}');
  $rows = weibo_execute("insert into comments values (null, '{$current_user_slug}', '{$current_user_email}', '{$created}', '{$add_comments}', '{$post_id}');");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '注册失败！' : '注册成功！';

  if ($GLOBALS['success'] == true) {
      header('location: posts.php');
    }  
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  add_comments();
}

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
    <?php include 'inc/navbar.php'; ?>

    <div class="container-fluid">
      <div class="page-title">
        <h1>浏览微博</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写微博</a>
      </div>
      <div class="page-action">
        <?php if (isset($message)): ?>
        <?php if ($success): ?>
        <div class="alert alert-success">
          <?php echo $message; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
          <?php echo $message; ?>
        </div>
        <?php endif ?>
        <?php endif ?>        
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item): ?>
            <option value="<?php echo $item['id']; ?>"<?php echo isset($_GET['category']) && $_GET['category'] == $item['id'] ? ' selected' : '' ?>>
              <?php echo $item['name']; ?>
            </option>
            <?php endforeach ?>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="?page=<?php echo ($page-1); ?>">上一页</a></li>
          <?php for ($i = $begin; $i <= $end; $i++): ?>
          <li<?php echo $i === $page ? ' class="active"' : '' ?>><a href="?page=<?php echo $i . $search; ?>"><?php echo $i; ?></a></li>
          <?php endfor ?>
          <li><a href="?page=<?php echo ($page+1); ?>">下一页</a></li>
        </ul>
      </div>
      <ul class="list-group">
      <?php foreach ($posts as $item): ?>
        <li class="list-group-item contents">
          <a href="profile.php?user=<?php echo $item['user_id'] ?>"><img src="<?php echo $item['users_avatar']; ?>" class="post_avatar"></a>
          <h1><?php echo $item['users_slug'] ?></h1>
          <h4 class="time"><?php echo $item['created']; ?></h4> 
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
          <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
            <textarea id="comments" name="comments" class="form-control" cols="30" rows="6"></textarea>
            <input name="post_id" style="display: none" value="<?php echo $item['id'] ?>">
            <br>            
            <button type="submit" class="btn btn-primary">发表评论</button>
          </form>  
        </li>
        <br>
        <br>
      <?php endforeach ?>
      </ul>
    </div>
  </div>

  <?php $current_page = 'posts'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
