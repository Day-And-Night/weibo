<?php

require_once '../functions.php';

weibo_get_current_user();

$current_user = weibo_get_current_user();
$current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");

//查询关注用户有哪些
$follow_users = weibo_fetch_one("select follow_users from users where id = {$current_user['id']};");

//得到用户所关注的用户
$total_follow_users = $follow_users['follow_users'];


// 处理分页参数
// =========================================

$size = 20;
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
// 必须 >= 1 && <= 总页数

// $page = $page < 1 ? 1 : $page;
if ($page < 1) {
  // 跳转到第一页
  header('Location: /admin/follow_users.php?page=1');
}

// 只要是处理分页功能一定会用到最大的页码数
$total_count = (int)weibo_fetch_one('select count(1) as count from posts
inner join categories on posts.category_id = categories.id
inner join users on posts.user_id = users.id
where posts.user_id in (' . $total_follow_users . ');')['count'];

$total_pages = (int)ceil($total_count / $size);


// $page = $page > $total_pages ? $total_pages : $page;
if ($page > $total_pages && $total_pages != 0) {
  // 跳转到第最后页
  header('Location: /admin/follow_users.php?page=' . $total_pages);
}

$offset = ($page - 1) * $size;

//查询关注用户的信息
//select * from users where id in (1,2,3);
$users = weibo_fetch_all('select * from users where id in (' . $total_follow_users . ');');

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
where posts.user_id in (" . $total_follow_users . ")
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

$begin = $begin < 1 ? 1 : $begin; // 确保了 begin 不会小于 1
$end = $begin + $visiables - 1; // 因为 50 行可能导致 begin 变化，这里同步两者关系
$end = $end > $total_pages ? $total_pages : $end; // 确保了 end 不会大于 total_pages
$begin = $end - $visiables + 1; // 因为 52 可能改变了 end，也就有可能打破 begin 和 end 的关系
$begin = $begin < 1 ? 1 : $begin; // 确保不能小于 1


//实现添加功能
function add_user () {
  $current_user = weibo_get_current_user();
  $current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");
  
  if (empty($_POST['slug'])) {
    $GLOBALS['message'] = '请填写用户名！';
    $GLOBALS['success'] = false;
    return;
  }

  $slug = $_POST['slug'];

  $increase_users = weibo_fetch_one("select * from users where slug = '{$slug}';");

  if (empty($increase_users)) {
    $GLOBALS['message'] = '未找到该用户！';
    $GLOBALS['success'] = false;
    return;    
  }

  $increase_users_id = $increase_users['id'];

  //查询关注用户
  $follow_users = weibo_fetch_one("select follow_users from users where id = {$current_user['id']};");

  
  //更改新关注的用户
  $total_follow_users = $follow_users['follow_users'];
  $original_users = explode(',', $total_follow_users);

  array_push($original_users, $increase_users_id);

  //除去重复
  $original_users = array_unique($original_users);
  //升序排列
  sort($original_users);
  $new_users = $original_users;

  $new_users_str = implode(',', $new_users);

  //数据库修改
  //UPDATE users SET follow_users = '2' WHERE id = 1;
  $rows = weibo_execute("UPDATE users SET follow_users = '{$new_users_str}' WHERE id = {$current_user['id']};");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '添加失败！可能是因为已经存在该用户' : '添加成功！';

  if ($rows == 1) {
    header('location: follow_users.php');
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  add_user();
}


?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Users &laquo; Admin</title>
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
        <h1>关注</h1>
      </div>
      <hr >
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
      <div class="row">
        <div class="col-md-4">
          <form class="row" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype='multipart/form-data'>
            <h2>添加新用户</h2>
            <div class="form-group">
              <label for="slug">用户名</label>
              <input id="slug" class="form-control" name="slug" type="text" autocomplete="off">
            </div>
            <div class="form-group">
              <button class="btn btn-primary" type="submit">添加关注</button>
            </div>
          </form>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <h5>已关注的用户</h5>
            <a id="btn_delete" class="btn btn-danger btn-sm" href="/admin/post-delete.php" style="display: none">批量取消</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
               <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th class="text-center" width="80">头像</th>
                <th>邮箱</th>
                <th>用户名</th>
                <th>兴趣</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <?php if ($current_user['follow_users'] != null): ?>
              <tbody>
                <?php foreach ($users as $item): ?>
                <tr>
                  <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id']; ?>"></td>
                  <td class="text-center"><img class="follow_user_avatar" src="<?php echo $item['avatar'] ?>"></td>
                  <td><?php echo $item['email'] ?></td>
                  <td><?php echo $item['slug'] ?></td>
                  <td><?php echo $item['interest'] ?></td>
                  <td class="text-center">
                    <a href="/admin/users-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">取消关注</a>
                  </td>
                </tr>
                <?php endforeach ?>
              </tbody>
            <?php endif ?>
          </table> 
        </div>
      </div>
      <hr >
      <div class="page-action">
        <h4 class="form-inline">关注用户的微博</h4>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="?page=<?php echo ($page-1); ?>">上一页</a></li>
          <?php for ($i = $begin; $i <= $end; $i++): ?>
          <li<?php echo $i === $page ? ' class="active"' : '' ?>><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
          <?php endfor ?>
          <li><a href="?page=<?php echo ($page+1); ?>">下一页</a></li>
        </ul>
      </div>
      <ul class="list-group">
        <?php if ($posts != null): ?>
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
          </li>
          <br>
          <br>
        <?php endforeach ?>
        <?php endif ?>
      </ul>    
    </div>
  </div>

  <?php $current_page = 'users'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
  <script>
    //实现批量删除功能
    $(function ($) {
      // 在表格中的任意一个 checkbox 选中状态变化时
      var $tbodyCheckboxs = $('tbody input')
      var $btnDelete = $('#btn_delete')

      // 定义一个数组记录被选中的
      var allCheckeds = []
      $tbodyCheckboxs.on('change', function () {
        var id = $(this).data('id')

        // 根据有没有选中当前这个 checkbox 决定是添加还是移除
        if ($(this).prop('checked')) {
          allCheckeds.includes(id) || allCheckeds.push(id)
        } else {
          allCheckeds.splice(allCheckeds.indexOf(id), 1)
        }

        // 根据剩下多少选中的 checkbox 决定是否显示删除
        allCheckeds.length ? $btnDelete.fadeIn() : $btnDelete.fadeOut()
        $btnDelete.prop('search', '?id=' + allCheckeds)
      })

      // 全选和全不选
      $('thead input').on('change', function () {
        //获取当前选中状态
        var checked = $(this).prop('checked')
        $tbodyCheckboxs.prop('checked', checked).trigger('change')
      })
    })
  </script>
</body>
</html>
