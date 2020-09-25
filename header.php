<header class="js-float-menu">
  <div class="site-width">
    <h1 class="title"><a href="index.php">TOREGOODS!!</a></h1>
    <nav class="top-nav">
      <ul>
      <?php if(!empty($_SESSION['user_id'])){?>
        <li><a href="mypage.php">マイページ</a></li>
        <li><a href="logout.php">ログアウト</a></li>
      <?php }else{ ?>
        <li><a href="signup.php" class="btn btn-primary">ユーザー登録</a></li>
        <li><a href="login.php">ログイン</a></li>
      <?php } ?>
      </ul>
    </nav>
  </div>
</header>
