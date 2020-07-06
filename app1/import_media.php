<?php
//格納されたDBからメディアデータを取り出し，画面上に表示する
//	https://qiita.com/NULLchar/items/7bdc6685be0aa909e8fe (2019/08/14)

//DBへ接続
$db = new db();

	if(isset($_get["target"]) && $_get["target"] !== ""){
		$target = $_get["target"];
	}
	else{
		echo "error: empty";
	}
	$MIMETypes = array(
		'png' => 'image/png',
		'jpeg' => 'image/jpeg',
		'gif' => 'image/gif',
		'mp4' => 'video/mp4'
	);

	$row = $db->import_media($target);
	header("Content-Type: ".$MIMETypes[$row["extension"]]);
	echo ($row["raw_data"]);

	//===ユーザ定義関数=============================================
	//クラスファイルを読み込む
	function __autoload($className){
	  //$className（インスタンス生成時に読み込まれていないクラス名）
	  $file = './' . $className . '.php';
	  require $file;
	}
?>