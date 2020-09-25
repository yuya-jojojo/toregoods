<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ログイン画面');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//POST送信がある場合
if(!empty($_POST)){
  debug('POST送信があります');

  //ユーザー情報を変数に代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $login_save = (!empty($_POST['login_save'])) ? true : false;

  //未入力チェック
  validRequired($pass,'pass');
  validRequired($email,'email');

  if(empty($err_msg)){

    validEmail($email,'email');

    validMaxLen($email,'email');

    validPass($pass,'pass');

    if(empty($err_msg)){
      debug('バリデーションOKです');

      //例外処理
      try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);

        //SQL実行
        $stmt = queryPost($dbh,$sql,$data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        debug('ログイン画面でとってきたやつ：'.print_r($result,true));

        if(!empty($result) && password_verify($pass,array_shift($result))){
          debug('パスワードマッチしました。');

          //ログイン有効期限デフォルト
          $sessLimit = 60*60;
          //最終ログイン日時を現在に
          $_SESSION['login_date'] = time();//現在の Unix タイムスタンプを返す

          if($login_save){
            debug('ログイン保持にチェックあります');

            //ログイン期限伸ばす
            $_SESSION['login_limit'] = $sessLimit * 24 * 30;
          }else{
            debug('ログイン保持にチェックなしです');

            $_SESSION['login_limit'] = $sessLimit;
          }

          //ユーザーIDを格納
          $_SESSION['user_id'] = $result['id'];

          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('ログインページからマイページに遷移します');
          header("Location:mypage.php");
          exit();//処理終了

        }else{
          debug('パスワード違かった！');
          $err_msg['common'] = MSG09;
        }

      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>
<?php
$siteTitle = 'ログイン';
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
            <h2 class="title">ログイン</h2>
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
              パスワード<span style="font-size:14px;">※半角英数字6文字以上</span>
              <input type="password" name="pass" value="">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
            </div>
            <label>
              <input type="checkbox" name="login_save" value="">ログイン保持にチェックはこちら
            </label>
            <div class="btn-container">
              <input type="submit" value="送信" class="btn btn-mid">
            </div>
          </form>
        </div>
      </section>
    </div>

    <!-- footer -->
    <?php
    require('footer.php');
     ?>
