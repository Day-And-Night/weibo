<?php

/**
 * 根据用户邮箱获取用户头像
 * email => image
 */

require_once '../../config.php';

//接收传递过来的邮箱
if (empty($_GET['email'])) {
  exit('缺少必要参数');
}
$email = $_GET['email'];

//查询对应的头像地址q
$conn = mysqli_connect(WEIBO_DB_HOST, WEIBO_DB_USER, WEIBO_DB_PASS, WEIBO_DB_NAME);
if (!$conn) {
  exit('连接数据库失败');
}

$res = mysqli_query($conn, "select avatar from users where email = '{$email}' limit 1;");
if (!$res) {
  exit('查询失败');
}

$row = mysqli_fetch_assoc($res);
//echo

echo $row['avatar'];
