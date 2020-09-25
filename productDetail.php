<?php

require('functions.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('商品詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
//GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品データを取得
$dbFormData = getProductOne($p_id);
//パラメータに不正な値が入っていないチェック
if(!empty($p_id) && empty($dbFormData)){
  error_log('エラー発生：指定ページに不正な値が入りました。');
  header("Location:index.php");
  eixt();
}
debug('取得したDBデータ：'.print_r($dbFormData,true));

//POST送信されていた場合
if(!empty($_POST['submit'])){
  debug('POST送信があります。');

  //ログイン認証
  require('auth.php');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL分作成
    $sql = 'INSERT INTO board(sale_user,buy_user,product_id,created_date,update_date) VALUES(:sale_user,:buy_user,:product_id,:created_date,:update_date)';
    $data = array(':sale_user' => $dbFormData['user_id'], ':buy_user' => $_SESSION['user_id'], ':product_id' => $dbFormData['id'], ':created_date' => date("Y/m/d H:i:s"), ':update_date' => date("Y/m/d H:i:s"));

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      $_SESSION['success_msg'] = SUC03;
      debug('商品詳細から掲示板に移動します');
      header("Location:msg.php?m_id=".$dbh->lastInsertId());
      exit();
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
debug('画面表示処理終了＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');
 ?>
<?php
 $siteTitle = '商品詳細ページ';
 require('head.php');
?>
<body class="page-1colum">

  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <section id="main">

      <div class="title">
        <span class="badge"><?php echo $dbFormData['category']; ?></span>
        <?php echo $dbFormData['name']; ?>
        <i class="fa fa-heart icn-like js-click-like <?php if(isLike($_SESSION['user_id'],$dbFormData['id'])){echo 'active';} ?>" aria-hidden="true" data-productid="<?php echo sanitize($dbFormData['id']); ?>"></i>
      </div>

      <div class="product-img-container">

        <div class="img-main">
          <img class="js-img-main" src="<?php echo showImg($dbFormData['pic1']); ?>" alt="">
        </div>
        <div class="img-sub">
          <div class="js__img__sub__blk">
            <img class="js-img-sub" src="<?php echo showImg($dbFormData['pic1']); ?>" alt="">
          </div>
          <div class="js__img__sub__blk">
            <img class="js-img-sub" src="<?php echo showImg($dbFormData['pic2']); ?>" alt="">
          </div>
          <div class="js__img__sub__blk">
            <img class="js-img-sub" src="<?php echo showImg($dbFormData['pic3']); ?>" alt="">
          </div>
        </div>

      </div>

      <div class="product-detail">
        <p><?php echo $dbFormData['comment']; ?></p>
      </div>

      <div class="product-buy">

        <div class="item-left">
          <a href="index.php<?php echo appendGetParam(array('p_id')); ?>">商品一覧に戻る</a>
        </div>
        <form action="" method="post">
          <div class="item-right">
            <input type="submit" name="submit" class="btn" value="購入する">
          </div>
        </form>
        <div class="item-right">
          <p class="price"><?php echo number_format($dbFormData['price']); ?></p>
        </div>

      </div>
    </section>


  </div>

  <!-- footer -->
  <?php
  require('footer.php');
   ?>
