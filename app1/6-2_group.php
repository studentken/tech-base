<?php
//グループページ

//デバッグ用にtb名を宣言
$tb_group = "tbimakaeru_group";	//グループのtb
$tb_user = "tbimakaeru_user";	//ユーザーのtb
$tb_media = "tbimakaeru_media";	//画像・動画のtb
$tb_medianame = "tbimakaeru_medianame";	//画像名・動画名のtb

//DBへ接続
$db = new db();		//$db->　　　 のように使用
/*
//tbの削除
//$db->dorp_table($tb_medianame);
*/

//グループ・ユーザ・メディアtbを作成（作られていない時のみ）
//（引数:boolean は、DB内のtbを表示するか否かを指定）
$db->create_table(false);

//ログイン・新規登録・グループ｝ページ以外からのアクセスは許容しない
if (!isset($_POST["loginButton"])and !isset($_POST["ragistrate"])and !isset($_POST["setTime"])
		and !isset($_POST["back"])){
	jump_to("./6-2_login.php");
}

//===ユーザログイン機能（必須）===========================================
//ログインページorグループページから来たら
elseif ((isset($_POST["loginButton"])or isset($_POST["setTime"])or isset($_POST["back"]))
		and !empty($_POST["userName"])and !empty($_POST["userPass"])){
	$uname = htmlspecialchars($_POST["userName"], ENT_QUOTES);
	$upass = htmlspecialchars($_POST["userPass"], ENT_QUOTES);
	$jumpPage = "/6-2_login.php";	//遷移させるページを指定。
	login($db, $uname, $upass, $jumpPage);
	$gid = $db->get_group_id($uname, $upass);
}
//===ユーザ登録機能（必須）=============================================
//既存の名前・パスの組合せがあると登録できない仕様に。(名orパスを変えてもらう)
/*・グループ作成/参加機能
 ――ユーザ登録と同時に設定
 ――参加後はユーザログインだけでグループへ飛ぶようにする
 ――グループ ID をユーザに付加
  新規登録ページから来たら
*/
elseif (!empty($_POST["userName"])and !empty($_POST["userPass"])
								and !empty($_POST["groupPass"])){
	$uname = htmlspecialchars($_POST["userName"], ENT_QUOTES);
	$upass = htmlspecialchars($_POST["userPass"], ENT_QUOTES);
	$gpass = htmlspecialchars($_POST["groupPass"], ENT_QUOTES);
	$jumpPage = "/6-2_newRagistration.php";	//遷移させるページを指定。

	//新しくグループを作る場合
	if (isset($_POST["isNewGroup"])and !empty($_POST["groupName"])){
		$gname = htmlspecialchars($_POST["groupName"], ENT_QUOTES);
		echo "新しいグループを作ります。<br />";
		$gid = $db->create_group($gname, $gpass);
		$insert = $db->insert_newer($gid, $uname, $upass);
		echo $insert;
		login($db, $uname, $upass, $jumpPage);
		$gid = $db->get_group_id($uname, $upass);

	//既存グループへ参加する場合
	}elseif (!isset($_POST["isNewGroup"])and !empty($_POST["groupId"])){
		$gid = htmlspecialchars($_POST["groupId"], ENT_QUOTES);
		echo "既存グループへ参加します。<br />";
		//グループが無い or ユーザが重複している場合
		if (!$db->is_group_exist($gid, $gpass) ){
			//jump_to("6-2_newRagistration.php");	//登録ページへ遷移
			exit ("グループが見つかりませんでした。");
		}elseif ($db->is_duplicate($uname, $upass)) exit ("ちょうふくがあるよ。");
		//tbへ新規ユーザを登録
		$insert = $db->insert_newer($gid, $uname, $upass);
		echo $insert;
		login($db, $uname, $upass, $jumpPage);
		$gid = $db->get_group_id($uname, $upass);
	}

}

//===外出・帰宅時刻の設定機能=============================================
if (isset($_POST["setTime"])and (isset($_POST["goOut"])or isset($_POST["comeHome"]))){
	//外出時刻の設定/編集/削除機能（引数として、外出or帰宅を指定。）
	if (!empty($_POST["outTime"])){
		$uid = $db->get_u_id($uname, $upass);
		$outTime = htmlspecialchars($_POST["outTime"], ENT_QUOTES);
		//echo $outTime."goOut.<br/>";
		$setting = $db->set_time($outTime, $uid, "out");
		//echo $setting;
	}
	//帰宅時刻の設定/編集/削除機能（引数として、外出or帰宅を指定。）
	if (!empty($_POST["homeTime"])){
		$uid = $db->get_u_id($uname, $upass);
		$homeTime = htmlspecialchars($_POST["homeTime"], ENT_QUOTES);
		//echo $homeTime."comeHome.<br/>";
		$setting = $db->set_time($homeTime, $uid, "home");
		//echo $setting;
	}
}
//===1 日のタイムテーブル表示機能=============================================
//――表などを用いて実装
//――ユーザtbから、ログインしている人とグループIDが一致するレコード
//    全てを取得する。時間昇順で取得し、出来ればグラフにプロット。


//===内部=============================================
//===日付が変わったらタイムテーブルを更新する機能=============================================
// ――日付の情報を保持しておく。ログイン日が変わるとタイムテーブルリセット

//ログインしたユーザのID:名前:パスワードを表示するページへのリンク

//echo "<hr/>";
//tbの中身を確認 {tb_group, tb_user, tb_media, tb_medianame}
//$db->table_recoreds($tb_group);
//$db->table_recoreds($tb_user);

//===ユーザ定義関数=============================================
//指定されたページへ遷移させ、処理を終了する。
/*
function jump_to($page){
	header('Location: '.$page);
	exit ;
}
*/
//クラスファイルを読み込む
function __autoload($className){
  //$className（インスタンス生成時に読み込まれていないクラス名）
  $file = './' . $className . '.php';
  require $file;
}
//ログインする
function login($db, $uname, $upass, $jumpPage){
	$gid = $db->get_group_id($uname, $upass);
	//登録ユーザがない（または複数ある）場合はログインページへ遷移する
	if ($gid == '000'){
		//jump_to($jumpPage);	//指定されたページへ遷移させる。
		exit ("登録されていないよ。");
	}
	echo "ようこそ、<b>".$uname."</b>さん！<hr />";
}
?>

<!DOCUMENTTYPE html>
<html>
<head>
<meta name="viewport" content="width=320, height=480, initial-scale=1.0, minimum-scale=1.0, maximum-scale=2.0, user-scalable=yes">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>group_</title>
</head>
<body>

<h4>外出・帰宅時間設定フォーム</h4>
<form method="post" action="6-2_group.php">
	<p>外出時間を設定<input type="checkbox" name="goOut" value="" />
	<input type="datetime-local" name="outTime" step="300"></p>
	<p>帰宅時間を設定<input type="checkbox" name="comeHome" value="" />
	<input type="datetime-local" name="homeTime" step="300"></p>
	<?php
		echo "<input type='hidden' name='userName' value='$uname' />";
		echo "<input type='hidden' name='userPass' value='$upass' />";
	?>
	<input type="submit" name="setTime" value="設定する" />
</form>

<h4>画像のアップロード</h4>
<form method="post" action="upload.php">
	<?php
		echo "<input type='hidden' name='userName' value='$uname' />";
		echo "<input type='hidden' name='userPass' value='$upass' />";
	?>
	<input type="submit" name="upload" value="画像アップロードページへ移動する" />
</form>

<div>
<?php 
	echo "<h4>".$db->get_g_name($gid)."のタイムテーブル</h4>";
	$db->show_timetable($gid);
?>
</div>

<!-- ===画像と動画の表示機能（src or thumbnail）============================================= -->

</body>
</html>
