<?php
  //根据用户注册用的邮箱判断是否已经被占用

require_once '../../config.php';

// 1. 接收传递过来的邮箱
if (empty($_GET['email'])) {
  exit('缺少必要参数');
}
$email = $_GET['email'];

$conn = mysqli_connect(WEIBO_DB_HOST, WEIBO_DB_USER, WEIBO_DB_PASS, WEIBO_DB_NAME);
if (!$conn) {
  exit('连接数据库失败');
}

$res = mysqli_query($conn, "select email from users where email = '{$email}';");
if (!$res) {
  exit('查询失败');
}

$isexist = mysqli_fetch_assoc($res);

echo $isexist['email'];

