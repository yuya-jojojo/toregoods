<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ユーザー登録画面');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//POST送信があった場合
if(!empty($_POST)){
  debug('POST送信があります');

  //変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //未入力チェック
  validRequired($email,'email');
  validRequired($pass,'pass');
  validRequired($pass_re,'pass_re');

  if(empty($err_msg)){

    validEmail($email,'email');

    validEmailDup($email);

    validMaxLen($email,'email');

    validMaxLen($pass,'pass');

    validMinLen($pass,'pass');

    validHalf($pass,'pass');

    if(empty($err_msg)){

      validMatch($pass,$pass_re,'pass');

      if(empty($err_msg)){

        debug('バリデーションOKです。');

        //例外処理
        try{
          //DB接続
          $dbh = dbConnect();
          //SQL文作成
          $sql = 'INSERT INTO users(email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass,PASSWORD_DEFAULT), ':login_time' => date("Y/m/d H:i:s"), ':create_date' => date("Y/m/d H:i:s"));

          //クエリ実行
          $stmt = queryPost($dbh,$sql,$data);

          if($stmt){
            //ログイン有効期限デフォルト１時間
            $sessLimit = 60*60;
            //最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sessLimit;

            //ユーザーIDをセッション入れる
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション情報：'.print_r($_SESSION,true));

            //マイページへ遷移
            debug('ユーザー登録からマイページへ遷移するよ！');
            header("Location:mypage.php");
            exit();
          }
        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>
<?php
$siteTitle = 'ユーザー登録';
require('head.php');
 ?>
  <body class="page-1colum">

    <!-- メニュー -->
    <?php
    require('header.php');
     ?>

    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main">
        <div class="form-container">
          <form class="form" action="" method="post">
            <h2 class="title">ユーザー登録</h2>
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
              パスワード<span style="font-size:14px;">※半角英数字6文字以上</span>
              <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
              パスワード（再入力）
              <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
            </div>
            <div class="btn-container">
              <input type="submit" value="入会" class="btn btn-mid">
            </div>
          </form>
        </div>
      </section>
    </div>
    <!-- footer -->
    <?php
    require('footer.php');
     ?>
