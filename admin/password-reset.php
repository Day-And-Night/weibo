<?php

require_once '../functions.php';

weibo_get_current_user();

function update_password () {
  $current_user = weibo_get_current_user();
  $current_user = weibo_fetch_one("select * from users where id = '{$current_user['id']}';");
  if (empty($_POST['old']) || empty($_POST['password']) || empty($_POST['confirm'])) {
    $GLOBALS['message'] = '请完整填写！';
    $GLOBALS['success'] = false;
    return;
  }
  if ($_POST['old'] != $current_user['password']) {
    $GLOBALS['message'] = '请确保旧密码正确';
    $GLOBALS['success'] = false;
    return;
  }
  if ($_POST['password'] != $_POST['confirm']) {
    $GLOBALS['message'] = '请确认两次密码是否一致';
    $GLOBALS['success'] = false;
    return;
  }


  // 接收并保存
  $old = $_POST['old'];
  $password = $_POST['password'];  

  $rows = weibo_execute("update users set password = '$password' where id = {$current_user['id']};");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '修改失败！' : '修改成功！';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  update_password();
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Password reset &laquo; Admin</title>
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
        <h1>修改密码</h1>
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
      <form class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
        <div class="form-group">
          <label for="old" class="col-sm-3 control-label">旧密码</label>
          <div class="col-sm-7">
            <input id="old" class="form-control" type="password" placeholder="旧密码" name="old">
          </div>
        </div>
        <div class="form-group">
          <label for="password" class="col-sm-3 control-label">新密码</label>
          <div class="col-sm-7">
            <input id="password" class="form-control" type="password" placeholder="新密码" name="password">
          </div>
        </div>
        <div class="form-group">
          <label for="confirm" class="col-sm-3 control-label">确认新密码</label>
          <div class="col-sm-7">
            <input id="confirm" class="form-control" type="password" placeholder="确认新密码" name="confirm">
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-3 col-sm-7">
            <button type="submit" class="btn btn-primary">修改密码</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php $current_page = 'password-reset'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
