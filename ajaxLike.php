<?php

require('functions.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('Ajax');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


if(isset($_POST['productId']) && isset($_SESSION['user_id']) && isLogin()){
  debug('post送信があります');
  $p_id = $_POST['productId'];
  debug('商品ID:'.$p_id);

  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->rowCount();
    debug($result);
    //レコードがある場合
    if(!empty($result)){
      //レコード削除
      $sql = 'DELETE FROM favorites WHERE product_id = :p_id AND user_id = :u_id';
      $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id']);
      $stmt = queryPost($dbh,$sql,$data);
    }else{
      //レコード挿入
      $sql = 'INSERT INTO favorites (product_id, user_id, create_date) VALUES(:p_id, :u_id, :date)';
      $data = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh,$sql,$data);
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
debug('Ajax終わり');

?>
