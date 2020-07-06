<?php
/*
新しく作ります。
登録完了！
Warning: mkdir(): No such file or directory in /public_html/6-2/6-2_group.php on line 73
*/
//DBへ接続
$db = new db();

//ログイン
if (!isset($_POST["upfile"])and !isset($_POST["upload"])){
	//jump_to("./6-2_login.php");
	exit ("からです。");
}
if (!empty($_POST["userName"])and !empty($_POST["userPass"])){
	$uname = htmlspecialchars($_POST["userName"], ENT_QUOTES);
	$upass = htmlspecialchars($_POST["userPass"], ENT_QUOTES);
	$jumpPage = "/6-2_login.php";	//遷移させるページを指定。
	$g_id = login($db, $uname, $upass, $jumpPage);	//ログイン
}

/*
//デバッグ用ログイン情報
$uname = "イチカ";
$upass = "イチカ";
$jumpPage = "/6-2_login.php";	//遷移させるページを指定。
$g_id = login($db, $uname, $upass, $jumpPage);
*/

//ファイルアップロードがあったとき
if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error']) && $_FILES["upfile"]["name"] !== ""){
	//エラーチェック
	//echo "エラーチェック<br/>";
	switch ($_FILES['upfile']['error']) {
	case UPLOAD_ERR_OK: // OK
		break;
	case UPLOAD_ERR_NO_FILE:   // 未選択
		throw new RuntimeException('ファイルが選択されていません', 400);
	case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
		throw new RuntimeException('ファイルサイズが大きすぎます', 400);
	default:
		throw new RuntimeException('その他のエラーが発生しました', 500);
	}

	//画像を保存するディレクトリー
	$dir="./images/";

/*
	//日本語を省くための正規表現
	$pattern="/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3}$/";
	//ファイル名に日本語が入ってるかチェック
	if(!preg_match($pattern,$upfile)){
		$er["jp"]="日本語はダメ";
	}
*/
	//拡張子を見る
	$tmp = pathinfo($_FILES["upfile"]["name"]);
	$extension = $tmp["extension"];
	if($extension === "jpg" || $extension === "jpeg" || $extension === "JPG" || $extension === "JPEG"){
		$extension = "jpeg";
	}
	elseif($extension === "png" || $extension === "PNG"){
		$extension = "png";
	}
	elseif($extension === "gif" || $extension === "GIF"){
		$extension = "gif";
	}
	elseif($extension === "mp4" || $extension === "MP4"){
		$extension = "mp4";
	}
	else{
		echo "非対応ファイルです．<br/>";
		echo ("<a href='upload.php'>戻る</a><br/>");
		exit(1);
	}

	//ファイル名を変更
	$upfile="group-".$g_id."_".((int)$db->count_mediafiles($g_id)+1).".".$extension;

	//今回はファイル名を毎回変更するので、重複が生まれる。
	//ファイル重複をチェックするために,ディレクトリー内のファイルを取得する
	$filelist=scandir($dir);
	foreach($filelist as $file){
		//is_dir関数でディレクトリー以外のファイル（つまり画像のみ）を調べる
		if(!is_dir($file)and $upfile==$file){
			$er["double"]="重複しているのでアップできません。";
			echo "重複しているのでアップできません。<br/>";
		}
	}
	//echo "91-";

	//エラーの配列をチェックして空だった場合・・つまりエラーがなければ画像をアップロードする
	if(empty ($er)){
		//echo "94-";
		move_uploaded_file($_FILES["upfile"]["tmp_name"],$dir.$upfile);
		$db->insert_media($g_id, $upfile, $extension);
	}
	//echo "97-";
}


//===ユーザ定義関数=============================================
//クラスファイルを読み込む
function __autoload($className){
  //$className（インスタンス生成時に読み込まれていないクラス名）
  $file = './' . $className . '.php';
  require $file;
}
//指定されたページへ遷移させ、処理を終了する。
function jump_to($page){
	header('Location: '.$page);
	exit ;
}
//ログインする
function login($db, $uname, $upass, $jumpPage){
	$gid = $db->get_group_id($uname, $upass);
	//登録ユーザがない（または複数がある）場合はログインページへ遷移する
	if ($gid == '000'){
		//jump_to($jumpPage);	//指定されたページへ遷移させる。
		exit ($uname.":".$upass."は、登録されていないよ。");
	}
	echo "ようこそ、<b>".$uname."</b>さん！<br/>";
	return $gid;
}
?>

<!DOCTYPE HTML>

<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>media</title>
</head>
<body>
	<form method="post" action="6-2_group.php">
		<?php
			echo "<input type='hidden' name='userName' value='$uname' />";
			echo "<input type='hidden' name='userPass' value='$upass' />";
		?>
		<input type="submit" name="back" value="グループページへ移動する" />
	</form>
	<hr/>
	<form action="upload.php" enctype="multipart/form-data" method="post">
		<label>画像/動画アップロード</label>
		<input type="file" name="upfile">
		<br>
		<?php
			echo "<input type='hidden' name='userName' value='$uname' />";
			echo "<input type='hidden' name='userPass' value='$upass' />";
		?>
		※画像はjpeg方式，png方式，gif方式に対応しています．動画はmp4方式のみ対応しています．<br>
		<input type="submit" name="upload" value="アップロード">
	</form>

	<?php
	//medianameテーブルからファイル名を取得して表示する．
	$db->show_media_byname($g_id);
	?>

</body>
</html>