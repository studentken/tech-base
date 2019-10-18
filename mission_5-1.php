<?php
//フォームとの変数名対応注意！

//DBの準備をする
	//DB接続
	$dsn = 'mysql:dbname=db_name;host=localhost';//<--半角空白入れるとerror
	$user = 'name';		//ユーザ名
	$password = 'password';	//パスワード
	error_reporting(E_ALL & ~E_NOTICE);	// PHPのエラーを表示するように設定
	//DBへの接続
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	//echo "接続OK";

	//テーブルを作成する（初回のみ）
	$tbName = "tbmission_5";
/*	$sql = "DROP TABLE IF EXISTS ".$tbName;	//テーブルの削除 DROP or DELETE or...
	$stmt = $pdo->query($sql);
	//if DELETE:
	//$sql = "ALTER TABLE tbName auto_increment = 1"	オートインクリメントのリセット
	//$stmt = $pdo->query($sql);
*/
	
	$sql = "CREATE TABLE IF NOT EXISTS ".$tbName//tbmission_5というテーブルが無ければ
	." ("										//左のクエリを飛ばす。
	. "id INT AUTO_INCREMENT PRIMARY KEY,"		//id：int型：自動ナンバリング：主キー
	. "name char(32),"							//name：char型：32文字まで許容
	. "comment TEXT,"							//comment：TEXT型：上限指定なし
	. "date DATETIME,"							//date：DATETIME型：'YYYY/MM/DD HH:MM:SS'
	. "pass char(32)"							//pass：char：32文字まで許容
	.");";
	$stmt = $pdo->query($sql);
	//echo "テーブル準備OK<br />";


//DBを操作する
if (isset($_POST["send"])) {
	//送信ボタンが押された時
	//echo '送信ボタン。';
	//================投稿フォームの処理================
	//（名前、コメント、パス）が入っていれば
	if ($_POST["name"]!== ""and $_POST["word"]!== ""and $_POST["pass"]!== "") {
		//各値を取得
		$name = htmlspecialchars($_POST["name"], ENT_QUOTES);
		$word = htmlspecialchars($_POST["word"], ENT_QUOTES);
		$pw = htmlspecialchars($_POST["pw"], ENT_QUOTES);
		$date = date("Y/m/d H:i:s");
		//echo '値の取得完了。';
		if (isset($_POST["eIndex"])){
			//編集する投稿番号を指定されていたら
			//==============編集==============
			//編集する投稿番号を保持する
			$index = htmlspecialchars($_POST["eIndex"], ENT_QUOTES);
			//echo '編集開始。';
			//DBを編集
			$sql = 'update '.$tbName.' set name=:name,comment=:comment,date=:date,pass=:pass where id=:id';
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':name', $name, PDO::PARAM_STR);
			$stmt->bindParam(':comment', $word, PDO::PARAM_STR);
			$stmt->bindParam(':date', $date, PDO::PARAM_STR);
			$stmt->bindParam(':id', $index, PDO::PARAM_INT);
			$stmt->bindParam(':pass', $pw, PDO::PARAM_STR);
			$stmt->execute();
			//echo "編集完了。";
		}else {
			//投稿番号を指定されていなければ
			//==============新規投稿==============
			//INSERTでデータ入力
			//echo '投稿します<br />';
			$sql = $pdo -> prepare("INSERT INTO ".$tbName." (name, comment, date, pass) VALUES (:name, :comment, DATE_FORMAT(:date,'%Y/%m/%d %k:%i:%s'), :pass)");
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $word, PDO::PARAM_STR);
			$sql->bindParam(':date', $date, PDO::PARAM_STR);
			$sql->bindParam(':pass', $pw, PDO::PARAM_STR);
			$sql -> execute();
			//echo "新規投稿完了";
		}
	}

}elseif (isset($_POST["delete"])){
	//削除ボタンが押された時
	//================削除フォームの処理================
	//削除する投稿番号を指定されていたら
	if ($_POST["deleteNum"]!== ""){
		//削除する投稿番号とパスワードを保持
		$index = htmlspecialchars($_POST["deleteNum"], ENT_QUOTES);
		$pw = htmlspecialchars($_POST["pw"], ENT_QUOTES);
		//DBから指定された投稿を削除
		$sql = 'delete from '.$tbName.' where id=:id AND pass=:pass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':id', $index, PDO::PARAM_INT);
		$stmt->bindParam(':pass', $pw, PDO::PARAM_STR);
		$stmt->execute();
		//echo $index."番目の投稿の削除完了";
	}

}elseif (isset($_POST["edit"])) {
	//echo 'edit:';
	//編集ボタンが押された時
	//================編集フォームの処理================
	//編集する投稿番号を指定されていたら
	if ($_POST["editNum"]!== ""){
		//echo 'edit_num.pass=';
		//編集する投稿番号とパスワードを保持
		$index = htmlspecialchars($_POST["editNum"], ENT_QUOTES);
		$pw = htmlspecialchars($_POST["pw"], ENT_QUOTES);
		//echo $index.$pw;
		//DBから指定された投稿を編集
		$sql = 'select name,comment,pass from '.$tbName.' where id=:id AND pass=:pass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':id', $index, PDO::PARAM_INT);
		$stmt->bindParam(':pass', $pw, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetch();
		$eName = $results[0];
		$eComment = $results[1];
		$ePass = $results[2];
		//echo $eName.$eComment.$ePass;
	}
}

?>


<!DOCTYPE html>
<html>
<!-- htmlフォーム -->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>m_5-1</title>
</head>

<body>
<h3>テーマ：いま作業中のミッション</h3>
ミッション番号を書き込んでください！<br />
<div>※ パスワードは必ず指定してください ※<br />
※ ID: Name: comment： date: のタグは自動で表示されます。※
</div>
　===== 投稿フォーム =====
<form method="post", action="mission_5-1.php">
	<?php
	if (isset($_POST["edit"])) {
		//<!-- 編集モード -->
		$e = htmlspecialchars($_POST["editNum"], ENT_QUOTES);
		echo ("<input type='hidden', name='eIndex', value='$e' />");
		echo "<br />".$e."番目の投稿を編集できます。<br />お名前　：";
		echo ("<input type='text', name='name', value='$eName' />");
		echo ("<br />コメント：");
		echo ("<input type='text', name='word', value='$eComment' />");
		echo ("<br />パスワード：");
		echo ("<input type='text', name='pw', value='$ePass' />");
	}else {
		//<!-- 新規投稿モード -->
		echo ("お名前　：");
		echo ("<input type='text', name='name', value='' />");
		echo ("<br />コメント：");
		echo ("<input type='text', name='word', value='' />");
		echo ("<br />パスワード：");
		echo ("<input type='text', name='pw', value='' />");
	}
	?>
	<input type="submit" name="send" value="送信" />
</form>
<br />
<form method="post", action="mission_5-1.php">
	 　===== 編集フォーム =====
	<br /><b>編集</b>したい投稿の「番号」を入力してください。
	<br />投稿番号　：
	<input type="text", name="editNum", value="" />
	<br />パスワード：
	<input type='text', name='pw', value='' />
	<input type="submit" name="edit" value="編集" />
</form>
<br />
<form method="post", action="mission_5-1.php">
	 　===== 削除フォーム =====
	<br /><b>削除</b>したい投稿の「番号」を入力してください。
	<br />投稿番号　：
	<input type="text", name="deleteNum", value="" />
	<br />パスワード：
	<input type='text', name='pw', value='' />
	<input type="submit" name="delete" value="削除" />
</form>
<br />
<?php
//以下　投稿履歴の表示

$display = "過去のコメント一覧<hr />";
echo $display;

//以下に4-6のselectを用いて表示させる
$sql = 'SELECT * FROM '.$tbName;
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
$tags = ["ID:", " Name:", " comment:", " date:", "#pass","#"];

	//$result = $stmt->fetch();	//検索結果が1行のみの場合
foreach ($results as $row){
	echo $tags[0].$row['id'];
	echo $tags[1].$row['name'];
	echo $tags[2].$row['comment'];
	echo $tags[3].strtr($row['date'],'-','/');//.'<br />';
echo "<hr />";
}

?>

</body>
</html>
