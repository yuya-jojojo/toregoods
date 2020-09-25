<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

//========================
//画面表示用データ
//========================
$u_id = $_SESSION['user_id'];
$productData = getMyProducts($u_id);
$boardData = getMyMsgsAndBoard($u_id);
$favoriteData = getMyLike($u_id);

debug('取得した商品データ：'.print_r($productData,true));
debug('取得した掲示板データ：'.print_r($boardData,true));
debug('取得したお気に入りデータ：'.print_r($favoriteData,true));

debug('画面表示終了 ＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜＜');

 ?>
<?php
$siteTitle = 'マイページ';
require('head.php');
 ?>
  <body class="page-2colum">

    <!-- メニュー -->
    <?php
    require('header.php');
     ?>

    <!-- メッセージ表示 -->
    <p id="js-show-msg" class="msg-slide" style="display:none;"><?php echo getSessionFlash('success_msg'); ?></p>
    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <h1 class="page-title">マイページ</h1>

      <!-- Main -->
      <section id="main">
        <section class="list panel-list">
          <h2 class="title">登録商品一覧</h2>
          <?php
            if(!empty($productData)):
              foreach ($productData as $key => $val):
           ?>
              <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                <div>
                  <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                </div>
                <div>
                  <p><?php echo sanitize($val['name']); ?> <span class="price"><?php echo sanitize(number_format($val['price'])); ?></span></p>
                </div>

              </a>
          <?php endforeach;
          endif;
          ?>
        </section>

        <section class=" list list-table">
          <h2 class="title">
            連絡掲示板一覧
          </h2>
          <table class="table">
            <thead>
              <tr>
                <th>最新送信日時</th>
                <th>取引相手</th>
                <th>メッセージ</th>
              </tr>
            </thead>

            <tbody>
              <?php
              if(!empty($boardData)):
                foreach ($boardData as $key => $val):
                  if(!empty($val['msg'])):
                    $msg = array_shift($val['msg']);
              ?>
                 <tr>
                   <td><?php echo sanitize(date('Y/m/d H:i:s',strtotime($msg['send_date'])));?></td>
                   <td>◯◯ ◯◯</td>
                   <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?></a></td>
                 </tr>
              <?php
                  else:
               ?>
                <tr>
                  <td>--</td>
                  <td>◯◯ ◯◯</td>
                  <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
                </tr>
              <?php
                  endif;
                endforeach;
              endif;
               ?>
            </tbody>
          </table>
        </section>

        <section>
          <h2 class="title">お気に入り一覧</h2>
          <?php
          if(!empty($favoriteData)):
            foreach ($favoriteData as $key => $val):
          ?>

            <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id'];?>"class="panel">
              <div>
                <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
              </div>
              <div>
                <p><?php echo sanitize($val['name']); ?><span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
              </div>
            </a>
          <?php
            endforeach;
          endif;
           ?>
        </section>

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
