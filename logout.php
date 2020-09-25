<?php

//========================
//ログアウト
//========================

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ログアウトページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

debug('ログアウトします');

session_destroy();
debug('ログインページへ遷移します');
header("Location:login.php");
