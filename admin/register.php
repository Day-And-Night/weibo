<?php 

require_once '../functions.php';

function register () {
  $GLOBALS['success'] = 0;
  if (empty($_POST['email'])) {
    $GLOBALS['message'] = '请填写邮箱';
    return;
  }
  if (empty($_POST['password'])) {
    $GLOBALS['message'] = '请填写密码';
    return;
  }
  if (empty($_POST['checkbox1'])) {
    $GLOBALS['message'] = '请确认';
    return;
  }
  if (empty($_POST['slug'])) {
    $GLOBALS['message'] = '请输入用户名';
    return;
  }

  $email = $_POST['email'];
  $password = $_POST['password'];
  $slug = $_POST['slug'];

  $rows = weibo_execute("insert into users values (null, '{$slug}', '{$email}', '{$password}', null, null, 'activated', null, null);");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '注册失败！' : '注册成功！';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   register();
}

?>

<!DOCTYPE html>
<html lang="zh-CN">
<html>
<head>
	<meta charset="utf-8">
  	<title>Sign in &laquo; Register</title>
  	<link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  	<link rel="stylesheet" href="/static/assets/css/admin.css">
</head>
<body>
	<div class="register">
		<form class="register-wrap" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" novalidate>
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
		  <div class="form-group">
		    <label for="email">电子邮件</label>
		    <input class="form-control" id="email" aria-describedby="emailHelp" placeholder="请输入邮件" name="email" autocomplete="off">
		    <div id="email_warn" class="alert alert-danger" style="display: none;" >邮件已被占用</div>
		    <div id="email_warn2" class="alert alert-success" style="display: none;">邮件未被占用</div>
        <div id="email_warn3" class="alert alert-danger" style="display: none;">请确认邮箱格式是否正确</div>
		  </div>
		  <div class="form-group">
		  	<label for="slug">用户名</label>
		  	<input class="form-control" id="slug" placeholder="请输入用户名" name="slug" autocomplete="off">
		  </div>
		  <div class="form-group">
		    <label for="password">密码</label>
		    <input name="password" type="password" class="form-control" id="password" placeholder="请输入密码" autocomplete="off">
		  </div>
		  <div class="form-check">
		    <input type="checkbox" class="form-check-input" id="check" value="yes" name="checkbox1">
		    <label class="form-check-label" for="check">确认</label>
		  </div>
		  <button type="submit" class="btn btn-primary btn-block">注册</button>
		  <a href="login.php" class="btn btn-primary btn-block">返回登陆</a>
		</form>
	</div>


    <script src="/static/assets/vendors/jquery/jquery.js"></script>
    <script>
    //在用户输入自己的邮箱过后，判断邮件是否已经被注册
    $(function ($) {

      //邮箱正则表达式
      var emailFormat = /^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/
      $('#email').on('blur', function () {
        var value = $(this).val()
        // 忽略掉文本框为空或者不是一个邮箱
        if (!value || !emailFormat.test(value)) {
          $('#email_warn2').css('display', 'none');
          $('#email_warn').css('display', 'none');
          $('#email_warn3').css('display', 'block');
          return;
        }
        //用户输入了一个合理的邮箱地址，获取数据库的邮箱地址
        //通过JS发送AJAX请求告诉服务端的某个接口，
        //根据情况显示不同的结果
        $.get('/admin/api/email.php', { email: value }, function (res) {
          if (res) {
          	$('#email_warn').css('display', 'block');
            $('#email_warn2').css('display', 'none');
            $('#email_warn3').css('display', 'none');
          }
          if (!res) {
          	$('#email_warn2').css('display', 'block');
            $('#email_warn').css('display', 'none');
            $('#email_warn3').css('display', 'none');
          }
        })
      })
    })
    </script>
</body>
</html>