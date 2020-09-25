<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('退会画面');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//POST送信があった場合
if(!empty($_POST)){

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :id AND delete_flg = 0';
    $sql2 = 'UPDATE product SET delete_flg = 1 WHERE user_id = :id AND delete_flg = 0';
    $sql3 = 'UPDATE favorites SET delete_flg = 1 WHERE user_id = :id AND delete_flg = 0';
    //データ流し込み
    $data = array(':id' => $_SESSION['user_id']);

    //クエリ実行
    $stmt1 = queryPost($dbh,$sql1,$data);
    $stmt2 = queryPost($dbh,$sql2,$data);
    $stmt3 = queryPost($dbh,$sql3,$data);

    if($stmt1 && $stmt2 && $stmt3){
      // セッション削除
      session_destroy();

      debug('退会します。ホームページに遷移');
      header('Location:index.php');
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
?>
<?php
$siteTitle = '退会';
require('head.php');
 ?>
 <body class="page-1colum">
   <style>
     .form .btn{
       float:none;
     }
     .form{
       text-align: center;
     }
   </style>

   <!-- メニュー -->
   <?php
   require('header.php');
    ?>

   <div id="contents" class="site-width">

     <!-- Main -->
     <section id="main">
       <div class="form-container">
         <form class="form" action="" method="post">
           <h2 class="title">退会</h2>
           <div class="area-msg">
             <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
           </div>
           <div class="btn-container">
             <input type="submit" class="btn btn-mid" name="submit" value="退会する">
           </div>
         </form>
       </div>
       <a href="mypage.php">マイページへ戻る</a>
     </section>
   </div>

   <!-- footer -->
   <?php
   require('footer.php');
    ?>
