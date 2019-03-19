<?php

/**
 * 取消对指定用户的关注
 */

require_once '../functions.php';

$current_user = weibo_get_current_user();

if (empty($_GET['id'])) {
  exit('缺少必要参数');
}

// $id = (int)$_GET['id'];
$id = $_GET['id'];
// => '1 or 1 = 1'
// sql 注入
// 1,2,3,4

//查询关注用户
$follow_users = weibo_fetch_one("select follow_users from users where id = {$current_user['id']};");

$total_follow_users = $follow_users['follow_users'];
$original_users = explode(',', $total_follow_users);

$delete_users = explode(',', $id);

//更改用户关注
$new_users = array_merge(array_diff($original_users, $delete_users));
$new_users_str = implode(',', $new_users);

//UPDATE users SET follow_users = '2' WHERE id = 1;
$rows = weibo_execute("UPDATE users SET follow_users = '{$new_users_str}' WHERE id = {$current_user['id']};");

header('location: follow_users.php');