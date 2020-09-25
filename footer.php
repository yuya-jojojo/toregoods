<footer id="footer">
  Copyrights
</footer>

<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script>
  $(function(){
    var $ftr = $('#footer');
    if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
      $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
    }

    //フロートヘッダーメニュー
    var targetHeight = $('.js-float-target').height();
    $(window).on('scroll',function(){
      $('.js-float-menu').toggleClass('float-active',$(this).scrollTop() > targetHeight);
    });

    //メッセージ表示
    var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g,"").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){$jsShowMsg.slideToggle('slow');},3000);
    }

    //画像ライブプレビュー
    var $areaDrop = $('.area-drop');
    var $inputFile = $('.input-file');

    $areaDrop.on('dragover',function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border','3px #ccc dotted');
    });
    $areaDrop.on('dragleave',function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border','none');
    });
    $inputFile.on('change',function(e){
      $areaDrop.css('border','none');

      var file = this.files[0];//FileListオブジェクト
      var $img = $(this).siblings('.prev-img');
      var fileReader = new FileReader();
      console.log(file);
      fileReader.onload = function (e){
        $img.attr('src',e.target.result).show();
      };

      fileReader.readAsDataURL(file);
    });

    //商品画像切り替え
    $(".js__img__sub__blk").first().addClass("active");
    $(".js-img-sub").on("click", function(){
      $imgUrl = $(this).attr("src");
      $(".js-img-main").attr("src",$imgUrl).hide().fadeIn();
      $(".js__img__sub__blk.active").removeClass("active");
      $(this).parent().addClass("active");
    });

    //お気に入り登録、削除
    var $like = $('.js-click-like') || null;
    var likeProductId = $like.data('productid') ||null;

    if(likeProductId !== undefined && likeProductId !== null){

        $like.on('click',function(){
          var $this = $(this);
          $.ajax({
            type: "POST",
            url: "ajaxLike.php",
            data: {productId: likeProductId}
          }).done(function(data){
            $this.toggleClass('active');
          }).fail(function(msg){
            console.log('AjaxError');
          });
        });
      }

  });
</script>
</body>
</html>
