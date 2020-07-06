<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>newRagistration</title>
</head>
<body>
<h4>新規登録フォーム</h4>
<form method="post" action="6-2_group.php">
	新しくグループを作る<input type="checkbox" name="isNewGroup" value="" /><br />
	グループ名　　　　　：<input type="text" name="groupName" placeholder="グループ名" />　※新しいグループを作る場合にのみ必要です。<br />
	ユーザ名　　　　　　：<input type="text" name="userName" placeholder="お名前・ニックネーム" /><br />
	ユーザのパスワード　：<input type="text" name="userPass" placeholder="パスワード" /><br />
	グループのID(半角数)：<input type="text" name="groupId" placeholder="グループのID" />　※既存のグループに参加する場合にのみ必要です。<br />
	グループのパスワード：<input type="text" name="groupPass" placeholder="グループのパスワード" /><br />
	　　　　　　　　　　　<input type="submit" name="ragistrate" value="登録する" />
</body>
</html>
