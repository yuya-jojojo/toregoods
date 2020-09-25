<?php

require('functions.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('商品登録画面');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

//=================================
//画面処理
//=================================

//画面表示用データ取得
//=================================
//GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '' ;

//DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'],$p_id) : '';
//GETパラメータに不正な値がないかチェック
if(!empty($p_id) && empty($dbFormData)){
  debug('不正な値が入りました');
  header('Location:mypage.php');
  exit();
}
//新規登録画面が編集画面か判別用フラグ　
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
debug('商品ID：'.$p_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));
debug('カテゴリーデータ：'.print_r($dbCategoryData,true));

//POST送信があった場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $comment = $_POST['comment'];
  $price = (!empty($_POST['price'])) ? $_POST['price'] : 0;
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  //DBデータの有無で変える
  if(empty($dbFormData)){

    validRequired($name,'name');

    validMaxLen($name,'name');

    validSelect($category,'category_id');

    validMaxLen($comment,'comment',500);

    validNumber($price,'price');

  }else{
      if($dbFormData['name'] !== $name){
        validRequired($name,'name');

        validMaxLen($name,'name');
      }
      if($dbFormData['category_id'] !== $category){
        validSelect($category,'category_id');
      }
      if($dbFormData['comment'] !== $comment){
        validMaxLen($comment,'comment',500);
      }
      if((int)$dbFormData['price'] !== $price){
        validNumber($price,'price');
      }
    }

    if(empty($err_msg)){
      debug('バリデーションOKです');

      //例外処理
      try{
        //DB接続
        $dbh = dbConnect();
        //SQL文作成
        if($edit_flg){
          debug('DB更新です');
          $sql = 'UPDATE product SET name = :name, category_id = :category, comment = :comment, price = :price, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
          $data = array(':name' => $name, ':category' => $category, ':comment' => $comment, ':price' => $price, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
        }else{
          debug('DB新規登録です');
          $sql = 'INSERT INTO product (name, category_id, comment, price, pic1, pic2, pic3, user_id, create_date) VALUES(:name, :category_id, :comment, :price, :pic1, :pic2, :pic3, :u_id, :date)';
          $data = array(':name' => $name, ':category_id' => $category, ':comment' => $comment, ':price' => $price, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
        }
        debug('sql:'.$sql);
        debug('流し込みデータ：'.print_r($data,true));

        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
          $_SESSION['success_msg'] = SUC02;
          debug('商品登録からマイページに遷移します');
          header("Location:mypage.php");
          exit();
        }

        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>
 <?php
 $siteTitle = '商品登録';
require('head.php');
?>
<body class="page-2colum page-logined">

  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">

      <div class="form-container">

        <form class="form" action="" method="post" enctype="multipart/form-data" style="width:100%; box-sizing:border-box;">
          <h2 class="title">商品を投稿する</h2>
          <div class="area-msg">
            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            商品名<span>必須</span>
            <input type="text" name="name" value="<?php echo getFormData('name') ?>">
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            カテゴリー<span>必須</span>
            <select class="" name="category_id">
              <option value="0" <?php if(getFormData('category_id') == 0){echo 'selected'; } ?>>選択してください</option>
              <?php foreach ($dbCategoryData as $key => $val) { ?>
                <option value="<?php echo $val['id']; ?>" <?php if(getFormData('category_id') == $val['id']){ echo 'selected'; } ?>><?php echo $val['name']; ?></option>
              <?php } ?>
            </select>
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['category'])) echo $err_msg['category']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            説明
            <textarea name="comment" rows="10" cols="30" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
          </div>
          <label class="<?php if(!empty($err_msg['price'])) echo 'err'; ?>">
            値段
            <div class="form-group">
              <input type="text" name="price" class="price-form" value="<?php echo getFormData('price') ?>">
              <span class="price">円</span>
            </div>
          </label>
          <div class="area-msg">
            <?php if(!empty($err_msg['price'])) echo $err_msg['price']; ?>
          </div>
          <div style="overflow:hidden;">
            <div class="img-drop-container">
              画像１
              <label class="area-drop" <?php if(!empty($err_msg['pic1'])){ echo 'err'; } ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file" value="">
                <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img">
                ドラッグ＆ドロップ
              </label>
            </div>
            <div class="img-drop-container">
              画像２
              <label class="area-drop" <?php if(!empty($err_msg['pic2'])){ echo 'err'; } ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file" value="">
                <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img">
                ドラッグ＆ドロップ
              </label>
            </div>
            <div class="img-drop-container">
              画像３
              <label class="area-drop" <?php if(!empty($err_msg['pic3'])){ echo 'err'; } ?>>
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic3" class="input-file" value="">
                <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img">
                ドラッグ＆ドロップ
              </label>
            </div>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn" name="" value="投稿する">
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
