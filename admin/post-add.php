<?php

require_once '../functions.php';

//查询用户数据库
weibo_get_current_user();

function add_post () {
  $user_id = $_SESSION['current_login_user']['id'];
  $user_slug = $_SESSION['current_login_user']['slug'];
  if (empty($_POST['content']) || empty($_POST['category'])) {
    $GLOBALS['message'] = '请完整填写表单！';
    $GLOBALS['success'] = false;
    return;
  }

  // 接收并保存
  $content = $_POST['content'];
  $category_id = $_POST['category'];
  $created = date("Y-m-d H:i:s");

  //接受图片
  $feature = $_FILES['feature'];

  var_dump($feature);

  if ($feature['error'] == 0) { 
    $feature_des = '../static/uploads/' . $feature['name'];
    $ismove = move_uploaded_file($feature['tmp_name'], $feature_des);  //只能移动到已经存在的目录上
    if (!$ismove) {
      echo '上传文件失败3';
    }

    $feature_des = substr($feature_des, 2);
  } else {
    $feature_des = '';
  }



  // insert into posts values (null, '321323123', '111', '2017-07-01 14:00:00', '1', '1', '1');
  $rows = weibo_execute("insert into posts values (null, '{$user_slug}', '{$feature_des}', '{$created}', '{$content}', '{$user_id}', '{$category_id}');");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '添加失败！' : '添加成功！';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    add_post();
  }

// 查询全部的分类数据
$categories = weibo_fetch_all('select * from categories;');

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Add new post &laquo; Admin</title>
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
        <h1>写微博</h1>
      </div>
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
      <form class="row" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype='multipart/form-data'>
          <div class="form-group">
            <label for="content">内容</label>
            <textarea id="content" class="form-control input-lg" name="content" cols="30" rows="10" placeholder="不超过139个字" maxlength="139"></textarea>
          </div>
          <div class="form-group col-sm-3">
            <label for="feature">上传图片</label>
            <img class="help-block thumbnail" style="display: none">
            <input id="feature" class="form-control" name="feature" type="file">
          </div>
          <div class="form-group col-sm-3">
            <label for="category">所属分类</label>
            <select id="category" class="form-control" name="category">
              <?php foreach ($categories as $item):?>
                <option value="<?php echo $item['id'] ?>"><?php echo $item['name']; ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="form-group col-sm-7">
            <button class="btn btn-primary" type="submit">发布</button>
          </div>
      </form>
    </div>
  </div>

  <?php $current_page = 'post-add'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
