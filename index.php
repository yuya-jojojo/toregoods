<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('トップページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//========================
//画面処理
//========================

//画面表示用データ取得
//========================
//カレントページのGETパラメータを取得　
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;//デフォルトは１ページめ
//カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
//ソート
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//表示件数
$span = 20;
//現在の表示レコードの先頭 SQLでページネーションの時に使う
$currentMinNum = (($currentPageNum - 1) * $span);//1->0 2->20
//DBからとった商品データを変数に格納
$dbProductData = getProductList($currentMinNum,$category,$sort);
//DBからカテゴリーデータ取得
$dbCategoryData = getCategory();
//検索用GETパラメータ
$link = (!empty(appendGetParam())) ? appendGetParam().'&p=' : '?p=';//TODO　ページネーションうまく出来ない部分あり

debug('現在のページ：'.$currentPageNum);
debug('フォーム用DBデータ：'.print_r($dbProductData,true));

//不正なパラメータが入っていないかチェック
if(!is_int($currentMinNum) && empty($dbProductData['data'])){
  debug('不正な値が入りました');
  header('Location:index.php');
  exit();
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
 ?>
<?php
$siteTitle = 'HOME';
require('head.php');
 ?>
 <style>
  header{
    position: fixed;
  }
 </style>
  <body class="page-2colum">

    <!-- メニュー -->
    <?php
    require('header.php');
     ?>

     <!-- hero -->
     <section class="hero js-float-target">
       <h2 class="hero__title">TOREGOODS!!</h2>
     </section>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- サイドバー -->
      <section id="sidebar">

        <form class="" action="" method="get">
          <h1 class="title">カテゴリー</h1>
          <div class="selectbox">
            <select name="c_id">
              <option value="0"　<?php if(getFormData('c_id',true) == 0) echo 'selected'; ?>>選択してください</option>
              <?php foreach ($dbCategoryData as $key => $val) { ?>
              <option value="<?php echo $val['id']; ?>" <?php if(getFormData('c_id',true) == $val['id']) echo 'selected'; ?>><?php echo $val['name']; ?></option>
              <?php } ?>
            </select>
          </div>
          <h1 class="title">表示順</h1>
          <div class="selectbox">
            <select name="sort">
              <option value="0" <?php if(getFormData('sort',true) == 0){echo 'selected';} ?>>選択してください</option>
              <option value="1" <?php if(getFormData('sort',true) == 1){echo 'selected';} ?>>安い順</option>
              <option value="2" <?php if(getFormData('sort',true) == 2){echo 'selected';} ?>>高い順</option>
            </select>
          </div>
          <input type="submit" value="検索">
        </form>
      </section>

      <!--  Main -->
      <section id="main">

        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
          </div>
          <div class="search-right">
            <span class="num"><?php  echo (!empty($dbProductData) ? ($currentMinNum+1) : 0); ?></span>-<span class="num"><?php echo $currentMinNum + count($dbProductData['data']); ?></span>件/<span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
          </div>
        </div>

        <div class="panel-list">
          <?php foreach ($dbProductData['data'] as $key => $val) { ?>
          <a  href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
            <div class="panel-head">
              <img src="<?php echo sanitize($val['pic1']) ?>" alt="<?php echo sanitize($val['name']); ?>">
            </div>
            <div class="panel-body">
              <p class="panel-title"><?php echo sanitize($val['name']);?></p>
              <span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span>
            </div>
          </a>
          <?php } ?>
        </div>

        <?php
        pagination($currentPageNum,$dbProductData['total_page'],$link);
         ?>
      </section>
    </div>

    <!-- footer -->
    <?php
    require('footer.php');
     ?>
