<?php //tech.php
$dbh = new PDO('mysql:host=mysql;dbname=techc', 'root', '');

  if (isset($_POST['body'])) {
    // POSTで送られてくるフォームパラメータ body がある場合
  
    $image_filename = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
      // アップロードされた画像がある場合
      if (preg_match('/^image\//', $_FILES['image']['type']) !== 1) {
        // アップロードされたものが画像ではなかった場合
        header("HTTP/1.1 302 Found");
        header("Location: ./tech.php");
      }
  
      // 元のファイル名から拡張子を取得
      $pathinfo = pathinfo($_FILES['image']['name']);
      $extension = $pathinfo['extension'];
      // 新しいファイル名を決める。他の投稿の画像ファイルと重複しないように時間+乱数で決める。
      $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;
      $filepath =  '/var/www/public/image/' . $image_filename;
      move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    }
  
    // insertする
    $insert_sth = $dbh->prepare("INSERT INTO test (message, img_name) VALUES (:message, :img_name)");
    $insert_sth->execute([
      ':message' => $_POST['body'],
      ':img_name' => $image_filename,
    ]);

  // 処理が終わったらリダイレクトする
  // リダイレクトしないと，リロード時にまた同じ内容でPOSTすることになる
  header("HTTP/1.1 302 Found");
  header("Location: ./tech.php");
  return;
}

// いままで保存してきたものを取得(time降順)
$select_sth = $dbh->prepare('SELECT * FROM test ORDER BY time  DESC');
$select_sth->execute();
?>

<!-- フォームのPOST先はこのファイル自身にする -->
<form method="POST" action="./tech.php" enctype="multipart/form-data">
  <textarea name="body"></textarea>
  <div style="margin: 1em 0;">
    <input type="file" accept="image/*" name="image" id="imageInput">
  </div>
  <button type="submit">送信</button>
</form>

<hr>

<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <h3><?= $entry['id'] ?></h3> 
    <dt>日時:<?= $entry['time'] ?></dt>
    <dt>内容</dt>
    <dd>
      <?= nl2br(htmlspecialchars($entry['message']))?>
      <?php if(!empty($entry['img_name'])): ?>
      <div>
        <img src="/image/<?= $entry['img_name'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const imageInput = document.getElementById("imageInput");
  imageInput.addEventListener("change", () => {
    if (imageInput.files.length < 1) {
      // 未選択の場合
      return;
    }
    if (imageInput.files[0].size > 5 * 1024 * 1024) {
      // ファイルが5MBより多い場合
      alert("5MB以下のファイルを選択してください。");
      imageInput.value = "";
    }
  });
});
</script>

