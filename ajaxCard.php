<?php
ini_set('log_errors','on');  //ログを取るか
ini_set('error_log','php.log');  //ログの出力ファイルを指定
session_start(); //セッション使う

    if (isset($_POST['attack'])) {
        var_dump('ajaxhkj成功');
        var_dump($_POST['attack'].'asfdafas');
        error_log('success');
        error_log($_POST['attack']);
    } else {
        var_dump('ajax失敗');
        error_log('fault');
    }
?>
<h1>sjfdlasfl</h1>