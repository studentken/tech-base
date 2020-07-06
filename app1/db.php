<?php
/*
DBを操作する関数をまとめたクラス。主に、
｛グループ・ユーザ・メディアtbの作成・操作を行う。
*/
class db{
	protected $pdo = null;
	protected $tb_group = "tbimakaeru_group";	//グループのtb
	protected $tb_user = "tbimakaeru_user";	//ユーザーのtb
	protected $tb_media = "tbimakaeru_media";	//画像・動画のtb
	protected $tb_medianame = "tbimakaeru_medianame";	//画像名・動画名のtb
    protected $date = null;

	//DB接続	
    function __construct(){
        try{
			$dsn = 'mysql:dbname=*****;host=localhost';//<--半角空白入れるとerror
			$user = '******';		//ユーザ名
			$password = '******';	//パスワード
			error_reporting(E_ALL & ~E_NOTICE);	// PHPのエラーを表示するように設定
			$this->$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
			//echo "接続OK";
			$this->date = date("Y-m-d H:i:s");
        }catch(PDOException $e){
            var_dump($e);
        }
    }

	//booleanを引数に取る。true：テーブルを表示する
	function create_table($show_table){
		$pdo = $this->$pdo;
		//テーブルを作成する（tbが無い場合のみ）
											//$this->$tb_group とすると、PDOオブジェクトとして扱われる！！
		$sql = "CREATE TABLE IF NOT EXISTS ".$this->tb_group//tbimakaeru_groupというテーブルが無ければ
		." ("
		. "g_id INT AUTO_INCREMENT PRIMARY KEY,"	//u_id：int型：自動ナンバリング：主キー
		. "g_name char(32),"		//u_name：char型：32文字まで許容
		. "g_pass char(32)"
		.");";
		$stmt = $pdo->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS ".$this->tb_user//tbimakaeru_userというテーブルが無ければ
		." ("
		. "u_id INT AUTO_INCREMENT PRIMARY KEY,"	//u_id：int型：自動ナンバリング：主キー
		. "g_id INT,"				//外部キー
		. "u_name char(32),"		//u_name：char型：32文字まで許容
		. "u_pass char(32),"
		. "time2out DATETIME,"		//time2out：DATETIME型：'YYYY/MM/DD HH:MM:SS'
		. "time2home DATETIME,"
		. "FOREIGN KEY(g_id) REFERENCES ".$this->tb_group."(g_id)"	//グループテーブル.グループID
		.");";
		$stmt = $pdo->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS ".$this->tb_media	//tbimakaeru_groupというテーブルが無ければ
		." ("
		. "media_id INT AUTO_INCREMENT PRIMARY KEY,"	//media_id：int型：自動ナンバリング,主キー
		. "g_id INT NOT NULL,"						//g_id:int型:外部キー
		. "fname TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,"	//"fname TEXT NOT NULL,"	//
		. "extension TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,"	//"extension TEXT NOT NULL,"	//
		. "raw_data LONGBLOB,"		//raw_data：longblob型：???
		. "FOREIGN KEY(g_id) REFERENCES ".$this->tb_group."(g_id)"
		.");";
		//echo $sql;
		$stmt = $pdo->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS ".$this->tb_medianame//tbimakaeru_medianameというテーブルが無ければ
		." ("
		. "mn_id INT AUTO_INCREMENT PRIMARY KEY,"	//mn_id：int型：自動ナンバリング：主キー
		. "g_id INT NOT NULL,"						//g_id:int型:外部キー
		. "fname TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,"
		. "extension TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,"
		. "FOREIGN KEY(g_id) REFERENCES ".$this->tb_group."(g_id)"
		.");";
		$stmt = $pdo->query($sql);

		//echo "：テーブル準備OK<br />";

		if ($show_table){
			$sql ='SHOW TABLES';			//作成したテーブルの確認
			$result = $pdo->query($sql);
			foreach ($result as $row){
				echo $row[0];
				echo '<br>';
			}
			echo "<hr>";
		}

	}

	function show_table($tbname){
		$pdo = $this->$pdo;
		$sql ='SHOW CREATE TABLE '.$tbname;
		$result = $pdo -> query($sql);
		foreach ($result as $row){
			echo $row[1];
		}
		echo "<hr>";
	}

	function table_recoreds($tbname){
		$pdo = $this->$pdo;
		if ($tbname == $this->tb_group){
			$label = ['g_id:','g_name:','g_pass:'];
			$range = 3;
		}else if($tbname == $this->tb_user){
			$label = ['u_id:','g_id:','u_name:','u_pass:','time_out:','time_home:'];
			$range = 6;
		}else if($tbname == $this->tb_media){
			$label = ['media_id:','g_id:','fname:','extension:','raw_data:'];
			$range = 5;
		}else if($tbname == $this->tb_medianame){
			$label = ['mn_id:','g_id:','fname:','extension:'];
			$range = 4;
		}else {
			exit('エラー：テーブル名が間違っています。<br />');
		}
		$sql = 'select * from '.$tbname;
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchall();
		foreach ($result as $row){
			for ($i=0; $i<$range; $i++){
				if (!empty($row[$i])) echo $label[$i].$row[$i];
			}
			echo '<hr />';
		}
	}


	//===DB操作に関連する関数

	//ID,パスワードが一致するグループ(tbのレコード)がある場合：true
	function is_group_exist($gid, $gpass){
		$pdo = $this->$pdo;
		$sql = 'select g_id,g_name from '.$this->tb_group.' where g_id=:gid AND g_pass=:gpass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':gid', $gid, PDO::PARAM_INT);
		$stmt->bindParam(':gpass', $gpass, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$g_id = $result[0];
		//$g_name = $result[1];
		return $g_id != "";
	}

	//ID,パスワードが一致するユーザ(tbのレコード)がある場合：true
	function is_duplicate($name, $pass){
		$pdo = $this->$pdo;
		$sql = 'select count(*) from '.$this->tb_user.' where u_name=:uname AND u_pass=:upass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':uname', $name, PDO::PARAM_INT);
		$stmt->bindParam(':upass', $pass, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$counter = $result[0];
		return $counter != 0;
	}

	//ユーザ名・ユーザパスワードから、ユーザIDを取得する
	function get_u_id($uname, $upass){
		$pdo = $this->$pdo;
		$sql = 'select u_id from '.$this->tb_user.' where u_name=:uname AND u_pass=:upass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
		$stmt->bindParam(':upass', $upass, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$u_id = $result[0];
		return $u_id;
	}

	//ユーザ名・ユーザパスワードから、グループIDを取得する
	function get_group_id($uname, $upass){
		$pdo = $this->$pdo;
		$sql = 'select count(*),'.$this->tb_user.'.g_id from '
		. $this->tb_user.' where u_name=:uname AND u_pass=:upass '
		. 'GROUP BY g_id;';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
		$stmt->bindParam(':upass', $upass, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		$usercount = $result[0];		//登録済みのユーザ数(居る：1、居ない：0)
		if ($usercount == 1){
			//未登録 or 重複が無ければ,所属するグループIDを返す。
			return $result[1];			//ユーザの所属グループのID;
		}else {
			return 000;
		}
	}

	//グループ名・パスワードから、グループIDを取得する
	function get_g_id($gname, $gpass){
		$pdo = $this->$pdo;
		$sql = 'select g_id from '.$this->tb_group.' where g_name=:gname AND g_pass=:gpass';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':gname', $gname, PDO::PARAM_STR);
		$stmt->bindParam(':gpass', $gpass, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		$g_id = $result[0];
		return $g_id;
	}

	function get_g_name($gid){
		$pdo = $this->$pdo;
		$sql = 'select g_name from '.$this->tb_group.' where g_id=:g_id';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':g_id', $gid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		return $result[0];
	}

	//新しいグループ(グループtbのレコード)を作成
	function create_group($gname, $gpass){
		$pdo = $this->$pdo;
		//echo "INSERT INTO ".$tbGroup." (g_name, g_pass)"." VALUES (:g_name, :g_pass) ";
		$sql = "INSERT INTO ".$this->tb_group." (g_name, g_pass)"." VALUES (:g_name, :g_pass) ";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':g_name', $gname, PDO::PARAM_STR);
		$stmt->bindParam(':g_pass', $gpass, PDO::PARAM_STR);
		$stmt -> execute();
		return $this->get_g_id($gname, $gpass);
	}

	//新しいユーザ(ユーザtbのレコード)を作成
	function insert_newer($gid, $uname, $upass){
		$pdo = $this->$pdo;
		if (!$this->is_duplicate($uname, $upass)){		//既存ユーザとの重複が無いことを確認している。
			$def="0001/01/01 00:00:00";
			$sql = "INSERT INTO ".$this->tb_user." (g_id, u_name, u_pass, time2out, time2home)"
						. " VALUES (:g_id, :u_name, :u_pass, :time2out, :time2home)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':g_id', $gid, PDO::PARAM_INT);
			$stmt->bindParam(':u_name', $uname, PDO::PARAM_STR);
			$stmt->bindParam(':u_pass', $upass, PDO::PARAM_STR);
			$stmt->bindParam(':time2out', $def, PDO::PARAM_STR);
			$stmt->bindParam(':time2home', $def, PDO::PARAM_STR);
			$stmt -> execute();
			return "登録完了！";
		}
		return "登録失敗…“ユーザ名”または“ユーザパスワード”を変更してください。";
	}

	//グループIDが一致するレコードの外出・帰宅時間をそれぞれ取得する。
	//引数…$gid:int:ユーザの所属するグループのID
	function show_timetable($gid){
		$pdo = $this->$pdo;
		$sql = 'select u_name,time2out,time2home from '.$this->tb_user.' where g_id=:gid';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':gid', $gid, PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetchAll();
		//ユーザテーブルから取得した各値を分割し、表にして表示する
		echo "<table border='1'>"
			. "<thead><tr><th>お名前</th><th>日付</th><th>外出時刻</th><th>帰宅時刻</th>"
			. "</tr></thead>"
			. "<tbody>";
		for ($i=0; $i<count($results); $i++){
			echo "<tr><td align='center'>".$results[$i][0]."</td><td align='center'>".substr($results[$i][1],0,10)."</td><td align='center'>".substr($results[$i][1],11,5)."</td><td align='center'>".substr($results[$i][2],11,5)."</td>"
				. "</tr>";
		}
		echo "</tbody>";
	}


	//外出・帰宅時刻を設定する。ユーザ別のカラムに格納。
	//引数…$time:string:日時，$id:ログイン中のユーザID, $OUTorHOME:string:out/homeのどちらを変更するか
	function set_time($time, $uid, $OUTorHOME){
		$pdo = $this->$pdo;
		$time = mb_ereg_replace('[A-Z]', ' ', $time);	//.":00";
		//$time = mb_ereg_replace('-', '/', $time);		//年月日の区切りを“/”にする。ここでは入力となるため無意味
		if ($OUTorHOME == "out" or $OUTorHOME == "home"){
			$setTo = "time2".$OUTorHOME;
		}else return "argument error";

		$sql = "update ".$this->tb_user." set ".$setTo."=:time where u_id=:u_id";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(':time', $time, PDO::PARAM_STR);
		$stmt -> bindParam(':u_id', $uid, PDO::PARAM_INT);
		$stmt -> execute();
		//return "update challenged<br/>";
	}

//===画像のアップロードと表示===============================================================

	//mediaテーブルから画像のバイナリデータを取得して表示する。DBのカラム：g_idが一致するものを取得する。
	//引数はユーザが所属するグループのID
	function show_media($g_id){
		$pdo = $this->$pdo;
		//g_idが一致するレコードを取得する
		$sql = "SELECT * FROM ".$this->tb_media." WHERE g_id=:g_id ORDER BY media_id;";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(':g_id', $g_id, PDO::PARAM_STR);
		$stmt -> execute();
		//拡張子で場合分け後、画像などを表示する
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			echo ($row["media_id"]."<br/>");
			//動画と画像で場合分け
			$target = $row["fname"];
			if($row["extension"] == "mp4"){
				echo ("<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>");
			}
			elseif($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif"){

				//画像を表示する
				echo ("<img src='import_media.php?target=$target'>");	//参考サイトの記述方法
	//--!	バイナリデータを直に表示しようとするとブラウザが止まる
				//echo ("<img src=".$this->import_media($target)." alt=$target width='320'>");
			}
			else{
				echo ("there is not the file.");
			}
			echo ("<br/><br/>");
	 	}
	}

	//medianameテーブルから画像名を取得して表示する。DBのカラム：g_idが一致するものを取得する。
	//引数はユーザが所属するグループのID
	function show_media_byname($g_id){
		$pdo = $this->$pdo;
		//g_idが一致するレコードを取得する
		$sql = "SELECT * FROM ".$this->tb_medianame." WHERE g_id=:g_id ORDER BY mn_id;";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(':g_id', $g_id, PDO::PARAM_INT);
		$stmt -> execute();
		//拡張子で場合分け後、画像などを表示する
		while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
			//echo ($row["mn_id"]."<br/>");
			//動画と画像で場合分け
			$target = $row["fname"];
			if($row["extension"] == "mp4"){
				//動画を表示する
				echo ("<video src='./images/$target' width='426' height='240' controls></video>");
			}
			elseif($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif"){
				//画像を表示する
				//echo ("<img src='import_media.php?target=$target'>");	//参考サイトの記述方法
				echo ("<img src='./images/$target' width='320'>");		// alt=$target width='320'>
			}
			else{
				echo ("there is not the file.");
			}
			echo ("<br/><br/>");
	 	}
	}

/*
	//DBから引数のファイル名に一致する画像等のバイナリデータを取得する
	function import_media($target){
		$pdo = $this->$pdo;
		//拡張子の情報を“Content-Type:”に用いるための配列
		$MIMETypes = array(
			'png' => 'image/png',
			'jpeg' => 'image/jpeg',
			'gif' => 'image/gif',
			'mp4' => 'video/mp4'
		);
		//ファイル名が引数の文字列と一致するレコードを取得する
		$sql = "SELECT * FROM ".$this->tb_media." WHERE fname = :target;";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindValue(":target", $target, PDO::PARAM_STR);
		$stmt -> execute();
		return $stmt -> fetch(PDO::FETCH_ASSOC);

		//$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		//header("Content-Type: ".$MIMETypes[$row["extension"]]);
		//echo ($row["raw_data"]);
	}
/*
	//引数に指定された値を持つレコードをDBに挿入する（バイナリデータ用）
	function insert_media($g_id, $fname, $extension, $raw_data){
		$pdo = $this->$pdo;
		$sql = "INSERT INTO ".$this->tb_media." (g_id, fname, extension, raw_data) VALUES (:g_id, :fname, :extension, :raw_data)";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(":g_id",$g_id, PDO::PARAM_INT);
		$stmt -> bindParam(":fname",$fname, PDO::PARAM_STR);
		$stmt -> bindParam(":extension",$extension, PDO::PARAM_STR);
		$stmt -> bindParam(":raw_data",$raw_data, PDO::PARAM_STR);
		$stmt -> execute();
		//echo $sql;
	}
*/
	//引数に指定された値を持つレコードをDBに挿入する（ディレクトリ::ファイル名保存用）
	function insert_media($g_id, $fname, $extension){
		$pdo = $this->$pdo;
		$sql = "INSERT INTO ".$this->tb_medianame." (g_id, fname, extension) VALUES (:g_id, :fname, :extension)";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(":g_id",$g_id, PDO::PARAM_INT);
		$stmt -> bindParam(":fname",$fname, PDO::PARAM_STR);
		$stmt -> bindParam(":extension",$extension, PDO::PARAM_STR);
		$stmt -> execute();
		//echo $sql;
	}

	function count_mediafiles($g_id){
		$pdo = $this->$pdo;
		$sql = "SELECT count(*) FROM ".$this->tb_medianame." WHERE g_id = :g_id;";
		$stmt = $pdo->prepare($sql);
		$stmt -> bindValue(":g_id", $g_id, PDO::PARAM_INT);
		$stmt -> execute();
		$row = $stmt -> fetch(PDO::FETCH_ASSOC);
		foreach ($row as $r){
			$n = $r;
			echo $row[0];
		}
		return $n;
	}

	function dorp_table($tbname){
		$pdo = $this->$pdo;
		$sql = "DROP TABLE IF EXISTS ".$tbname;	//テーブルの削除 DROP or DELETE or...
		$stmt = $pdo->query($sql);
		//DELETEを使う場合:
		//$sql = "ALTER TABLE $tbname auto_increment = 1"	オートインクリメントのリセット
		//$stmt = $pdo->query($sql);
	}

}
?>
