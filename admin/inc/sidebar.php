<!-- 也可以使用 $_SERVER['PHP_SELF'] 取代 $current_page -->
<?php

// 因为这个 sidebar.php 是被 index.php等文件载入执行，所以这里的相对路径是相对于index.php等文件
require_once '../functions.php';
$current_page = isset($current_page) ? $current_page : '';
//查询当前用户
$current_user = weibo_get_current_user();
//确保不会因为修改用户个人信息而不能及时更新
$current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");

?>
<div class="aside">
  <div class="profile">
    <img class="avatar" src="<?php echo $current_user['avatar'] ? $current_user['avatar'] : '/static/assets/img/default.png'; ?>">
    <h3 class="name"><?php echo $current_user['slug']; ?></h3>
  </div>
  <ul class="nav">
    <li<?php echo $current_page === 'index' ? ' class="active"' : '' ?>>
      <a href="/admin/index.php"><i class="fa fa-dashboard"></i>首页</a>
    </li>
    <?php $menu_posts = array('posts', 'post-add', 'categories'); ?>
    <li<?php echo in_array($current_page, $menu_posts) ? ' class="active"' : '' ?>>
      <a href="#menu-posts"<?php echo in_array($current_page, $menu_posts) ? '' : ' class="collapsed"' ?> data-toggle="collapse">
        <i class="fa fa-thumb-tack"></i>微博<i class="fa fa-angle-right"></i>
      </a>
      <ul id="menu-posts" class="collapse<?php echo in_array($current_page, $menu_posts) ? ' in' : '' ?>">
        <li<?php echo $current_page === 'posts' ? ' class="active"' : '' ?>><a href="/admin/posts.php">浏览微博</a></li>
        <li<?php echo $current_page === 'post-add' ? ' class="active"' : '' ?>><a href="/admin/post-add.php">写微博</a></li>
      </ul>
    </li>
    <li<?php echo $current_page === 'users' ? ' class="active"' : '' ?>>
      <a href="/admin/follow_users.php"><i class="fa fa-users"></i>关注</a>
    </li>
  </ul>
</div>
