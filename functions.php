<?php

//ログを取るか
ini_set("log_errors","on");
//ログの出力先
ini_set("error_log","php.log");

//デバッグ
function debug($str){
  error_log('デバッグ：'.$str);
}

//1、セッションファイルの置き場を変更する
session_save_path("/var/tmp/");

// 2、ガーベージコレクションが削除するセッションの有効期限を設定
ini_set('session.gc_maxlifetime',60*60*24*30);

// 3、ブラウザを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime',60*60*24*30);

// 4、セッションを使う
session_start();

// 現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();

function debugLogStart(){
  debug('<<<<<<<<<<<<<<<<<<<<<<<<<<画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数の中身：'.print_r($_SESSION,true));

}

const MSG01 = '入力必須です。';
const MSG02 = 'Emailの形式で入力してください。';
const MSG03 = 'パスワードとパスワード（再入力）があっていません。';
const MSG04 = '半角英数字で入力してください。';
const MSG05 = '6文字以上で入力してください。';
const MSG06 = '255文字以内で入力してください。';
const MSG07 = 'エラーが発生しました。しばらく経ってからやり直してください。';
const MSG08 = 'そのEmailはダメです';
const MSG09 = 'パスワードかメルアドが違います';
const MSG10 = '電話番号の形式で入力してください';
const MSG11 = '郵便番号の形式で入力してください';
const MSG12 = '半角数字で入力してください';
const MSG13 = '正しくありません';
const SUC01 = 'プロフィールを変更しました。';
const SUC02 = '商品を投稿しました。';
const SUC03 = '商品を購入しました。やりとりしましょう！';

//エラーメッセージ格納用の変数
$err_msg = array();

//========================
//バリデーション関数
//========================
//未入力チェック
function validRequired($str,$key){
  global $err_msg;
  if($str === ''){
    $err_msg[$key] = MSG01;
  }
}

//Email形式チェック
function validEmail($str,$key){
  global $err_msg;
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    $err_msg[$key] = MSG02;
  }
}

//パスワードとパスワード（再入力）があってるかチェック
function validMatch($str1,$str2,$key){
  global $err_msg;
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

//半角英数字チェック
function validHalf($str,$key){
  global $err_msg;
  if(!preg_match("/^[0-9a-zA-Z]*$/",$str)){
    $err_msg[$key] = MSG04;
  }
}

//最小文字数チェック
function validMinLen($str,$key, $min = 6){
  global $err_msg;
  if(mb_strlen($str) < $min){
    $err_msg[$key] = MSG05;
  }
}

//最大文字数チェック
function validMaxLen($str,$key,$max = 255){
  global $err_msg;
  if(mb_strlen($str) > $max){
    $err_msg = MSG06;
  }
}

//電話番号形式チェック
function validTel($str,$key){
  global $err_msg;
  if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/",$str)){
    $err_msg[$key] = MSG10;
  }
}

//郵便番号形式チェック
function validZip($str,$key){
  global $err_msg;
  if(!preg_match("/^\d{7}$/",$str)){
    $err_msg[$key] = MSG11;
  }
}

//半角数字チェック
function validNumber($str,$key){
  global $err_msg;
  if(!preg_match("/^[0-9]+$/",$str)){
    $err_msg[$key] = MSG12;
  }
}

//セレクトボックスチェック
function validSelect($str,$key){
  global $err_msg;
  if(!preg_match("/^[0-9]+$/",$str)){
    $err_msg[$key] = MSG13;
  }
}

//パスワードバリデーション集め
function validPass($pass,$key){

  validMaxLen($pass,$key);

  validMinLen($pass,$key);

  validHalf($pass,$key);
}

//Email重複チェック
function validEmailDup($email){
  global $err_msg;
  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);

    $stmt = queryPost($dbh,$sql,$data);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    debug('emialDupでとったやつ：'.print_r($result,true));

    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//DB接続関数
function dbConnect(){
  $dsn = 'mysql:dbname=output_webservice;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => true, //trueにしたら複数のプレースホルダー使えた
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );

  //PDOオブジェクト作成
  $dbh = new PDO($dsn,$user,$password,$options);

  return $dbh;
}

//SQL実行関数
function queryPost($dbh,$sql,$data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);//PDOStatementオブジェクトを返す
  //プレースホルダに値をセットし、SQL文を実行 executeは実行ついでにセットしてくれてる
  if(!$stmt->execute($data)){
    debug('クエリに失敗ました！');
    debug('sqlエラー'.print_r($stmt->errorinfo(),true));
    $err_msg['common'] = MSG07;
    return false;
  }
  debug('クエリ成功');
  return $stmt;
}

//ユーザー情報取得関数
function getUser($user){
  debug('ユーザー情報を取得します');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM users WHERE id = :id';
    $data = array(':id' => $user);

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    global $err_msg;
    $err_msg['common'] = MSG07;
  }
}

//商品情報を取得
function getProduct($u_id,$p_id){
  debug('商品情報を取得します');
  debug('ユーザーid：'.$u_id);
  debug('商品id:'.$p_id);

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL文作成　
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :p_id';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//ページングとかに使う用
function getProductList($currentMinNum,$category,$sort,$span = 20){//謎〜
  debug('商品情報を取得します');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //件数用のSQL文作成
    $sql = 'SELECT id FROM product';
    if(!empty($category)) $sql .= ' WHERE category_id = :category';//:categoryのバインドがなぜかできない->できた　エミュレーションが肝でした
    if(!empty($sort)){
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $data = array(':category' => $category);
    $stmt = queryPost($dbh,$sql,$data);
    $result['total'] = $stmt->rowCount();//総レコード数
    $result['total_page'] = ceil($result['total']/$span);//総ページ数

    if(!$stmt){
      return false;
    }

    //ページングようのSQL作成
    $sql = 'SELECT * FROM product';
    if(!empty($category)) $sql .= ' WHERE category_id = :category';
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }

    $sql .= ' LIMIT :span OFFSET :currentMinNum';
    $stmt = $dbh->prepare($sql);
    if(!empty($category)) $stmt->bindValue(':category',$category,PDO::PARAM_INT);
    $stmt->bindValue(':span',$span,PDO::PARAM_INT);
    $stmt->bindValue(':currentMinNum',$currentMinNum,PDO::PARAM_INT);
    debug('sql:'.$sql);
    //クエリ実行
    $data = $stmt->execute();
    //debug('sql:'.$data);
    if($data){
      //クエリ結果のデータを全レコード格納
      $result['data'] = $stmt->fetchAll();
      //debug('クエリ：'.print_r($result,true));
      return $result;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('getProductList()のエラー発生だど：'.$e->getMessage());
  }
}
// 商品詳細画面での商品情報取得関数
function getProductOne($p_id){
  debug('商品情報を取得します');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL分作成
    $sql = 'SELECT p.id, p.name, p.price, p.comment, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, p.update_date, c.name AS category FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id';
    $data = array(':p_id' => $p_id);

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return fasle;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//カテゴリー情報を取得
function getCategory(){
  debug('カテゴリー情報を取得します');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM category';
    $data = array();

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//お気に入り情報取得
function isLike($u_id,$p_id){
  debug('お気に入り情報確認');
  debug('ユーザーID：'.$u_id);
  debug('商品情報：'.$p_id);

  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(':p_id' => $p_id, ':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    debug($stmt->rowCount());

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('お気に入りじゃないです');
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//ログイン認証
function isLogin(){

  if(!empty($_SESSION['login_date'])){
    debug('ログイン済みユーザーです');

    if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです');

      //セッション削除
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限内です');
      return true;
    }

  }else{
    debug('未ログインユーザーだ！');
    return false;
  }
}

//掲示板情報を取得
function getMsgsAndBoard($m_id){
  debug('掲示板情報を取得します');

  //例外処理
  try{
    //DB接続
    $dbh = dbConnect();
    //SQL分作成
    $sql = 'SELECT m.id, m.bord_id, m.send_date, m.to_user, m.from_user, m.msg, m.created_date, b.sale_user, b.buy_user, b.product_id, b.created_date FROM message AS m RIGHT JOIN board AS b ON b.id = m.bord_id WHERE b.id = :m_id ORDER BY send_date ASC';
    $data = array(':m_id' => $m_id);

    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    //debug('掲示板sql：'.print_r($stmt,true));

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//自分の商品データ取得
function getMyProducts($u_id){
  debug('自分の商品情報を取得します');

  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0 ORDER BY create_date ASC LIMIT 8';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getMyMsgsAndBoard($u_id){
  debug('自分のメッセージ情報を取得します');

  try{
    //DBへ接続
    $dbh = dbConnect();

    //まず掲示板情報を取得します
    //SQL文作成
    $sql = 'SELECT * FROM board AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh,$sql,$data);
    $result = $stmt->fetchAll();
    debug('自分の掲示板情報：'.print_r($result,true));

    if(!empty($result)){
      foreach($result as $key => $val){
        //sql文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :b_id ORDER BY send_date DESC';
        $data = array(':b_id' => $val['id']);
        $stmt = queryPost($dbh,$sql,$data);
        //debug('fetchAllのやつ:'.print_r($stmt->fetchAll(),true));
        $result[$key]['msg'] = $stmt->fetchAll();
        //debug('jibun:'.print_r($result[$key]['msg'],true));
        //debug('jibunyo2:'.print_r($result,true));
      }
    }
    if($stmt){
      return $result;
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }

}

function getMyLike($u_id){
  debug('自分のお気にり情報を取得します');

  try{
    //DB接続
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites AS f LEFT JOIN product AS p ON f.product_id = p.id WHERE f.user_id = :id';
    $data = array(':id' => $u_id);

    $stmt = queryPost($dbh,$sql,$data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

//=======================
//その他
//=======================
//サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_COMPAT);
}
//フォーム入力保持
function getFormData($str, $flg = false){

  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }

  global $dbFormData;
  //global $err_msg;

  //ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（フォームにエラーがある＝POSTされてるハズなので、まずありえないが）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
       //POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームでひっかかっている状態）
       if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
         return sanitize($method[$str]);
       }else{
         //そもそも変更していない場合
        return  sanitize($dbFormData[$str]);
       }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}
//画像アップロード
function uploadImg($file,$key){
  debug('画像アップロード開始');
  debug('FILE情報：'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])) {
    //例外処理
    try{
      //バリデーション
      switch($file['error']){
        case UPLOAD_ERR_OK://ok
          break;
        case UPLOAD_ERR_INI_SIZE://php.iniのディレクティブの値を超えています。
          throw new RuntimeException('ファイスサイズが大きすぎます');
          break;
        case UPLOAD_ERR_FORM_SIZE:////フォーム定義の最大サイズが超過した場合
          throw new RuntimeException('HTML フォームで指定された MAX_FILE_SIZE を超えています。');
          break;
        case UPLOAD_ERR_NO_FILE://ファイル未洗濯の場合
          throw new RuntimeException('ファイルはアップロードされませんでした。');
          break;
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = @exif_imagetype($file['tmp_name']);
      debug('画像のやつ：'.print_r($type,true));
      if(!in_array($type, [IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG],true)){
        throw new RuntimeException('画像形式が未対応です');
      }

      $path = 'img/'.sha1_file($file['tmp_name']).image_type_to_extension($type);

      if(!move_uploaded_file($file['tmp_name'],$path)){
        throw new RuntimeException('ファイルの保存時にエラーが発生しました');
      }

      //chmod

      debug('ファイルは正常にアップされました');
      debug('ファイルパス：'.$path);
      return $path;


    }catch(RuntimeException $e){
      error_log($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }

}

//ページング
//$currentPageNum :現在のページ数
//$totalPageNum : 総ページ数
//$link : 検索用GETパラメータ
//$pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link, $pageColNum = 5){//TODO GETパラメータないときうまくページネーション出来ない
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif($currentPageNum == ($totalPageNum - 1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif($currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif($currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $currentPageNum + 4;
    // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="'.$link.$i.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i){echo 'active';}
        echo '"><a href ="'.$link.$i.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="'.$link.$maxPageNum.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//セッション一回だけとる
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key=array()){//引数のやつだけ取り除く
  if(!empty($_GET)){
    $str = '?';
    foreach ($_GET as $key => $val) {
      if(!in_array($key,$arr_del_key,true)){//取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    return mb_substr($str,0,-1,'utf-8');
  }
}

//画像表示
function showImg($path){
  if(empty($path)){
    $img = 'img/sample-img.png';
    return $img;
  }else{
    return $path;
  }
}
