<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('プロフィール編集画面');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//ユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザーデータ：'.print_r($dbFormData,true));
//var_dump($dbFormData);
//POST送信があった場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  //変数にユーザー情報を代入
  $username = $_POST['username'];
  $tel = (!empty($_POST['tel'])) ? $_POST['tel'] : 0;
  $zip = (!empty($_POST['zip'])) ? $_POST['zip'] : 0;
  $addr = $_POST['addr'];
  $age = (!empty($_POST['age'])) ? $_POST['age'] : 0;
  $email = $_POST['email'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;


  //DBの情報と入力した情報が異なる場合にバリデーションを行う
  if($dbFormData['username'] !== $username){
    validMaxLen($username,'username');
  }

  if((int)$dbFormData['tel'] !== $tel){
    validTel($tel,'tel');
  }

  if((int)$dbFormData['zip'] !== $zip){
    validZip($zip,'zip');
  }

  if($dbFormData['addr'] !== $addr){
    validMaxLen($addr,'addr');
  }

  if($dbFormData['age'] !== $age){
    validNumber($age,'age');
  }

  if($dbFormData['email'] !== $email){
    validMaxLen($email,'email');

    if(!empty($err_msg)){
      validEmailDup($emial);
    }
    validEmail($email,'email');
  }

  if(empty($err_msg)){
    debug('バリデーションOKだい');

    //例外処理
    try{
      //DB接続
      $dbh = dbConnect();
      //SQL文作成
      $sql = 'UPDATE users SET username = :u_name, tel = :tel, zip = :zip, addr = :addr, age = :age, email = :email, pic = :pic WHERE id = :id';
      $data = array(':u_name' => $username, ':tel' => $tel, ':zip' =>$zip, ':addr' =>$addr, ':age' =>$age, ':email' =>$email, ':pic' => $pic, ':id' => $dbFormData['id']);

      //クエリ実行
      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){

        //サクセスメッセージをセッションに詰める
        $_SESSION['success_msg'] = SUC01;

        debug('プロフィール編集からマイページに遷移します');
        header('Location:mypage.php');
        exit();
      }
    }catch(Exception $e){
      error_log('エラーメッセージ：'.$e->getMessage());

    }
  }
}
debug('画面表示終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
?>
<?php
$siteTitle = 'プロフィール編集';
require('head.php');
 ?>
  <body class="page-2colum page-logined">

    <!-- メニュー -->
    <?php
    require('header.php');
     ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <h1 class="page-title">プロフィール編集</h1>

      <!-- Main -->
      <section id="main">
        <div class="form-container">

          <form class="form" action="" method="post" enctype="multipart/form-data">
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
              名前
              <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['tel'])) echo 'err'; ?>">
              電話番号<span style="font-size:14px;">※ハイフンなしご入力ください</span>
              <input type="text" name="tel" value="<?php if(!empty(getFormData('tel'))) echo getFormData('tel'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['tel'])) echo $err_msg['tel']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['zip'])) echo 'err'; ?>">
              郵便番号<span style="font-size:14px;">※ハイフンなしご入力ください</span>
              <input type="text" name="zip" value="<?php if(!empty(getFormData('zip'))) echo getFormData('zip'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['zip'])) echo $err_msg['zip']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['addr'])) echo 'err'; ?>">
              住所
              <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['addr'])) echo $err_msg['addr']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['age'])) echo 'err'; ?>">
              年齢
              <input type="number" name="age" value="<?php if(!empty(getFormData('age'))) echo getFormData('age'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
              <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
            </div>
            <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
              プロフィール写真
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file" value="">
              <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img">
            </label>

            <div class="btn-container">
              <input type="submit" value="変更する" class="btn btn-mid">
            </div>

          </form>
        </div>

      </section>

      <!-- サイドバー -->
      <?php
      require('sidebar.php');
       ?>
    </div>

    <!-- footer -->
    <?php
    require('footer.php');
     ?>
