# tech-base
企画書

目次
1．	テーマ
2．	想定するユーザ像
3．	主要ユーザ像
4．	想定するユーザ数
5．	要件定義
6．	ページ構成(※省略)
7．	課題


1．	テーマ

「家族の帰宅時間把握アプリ」

2．	想定するユーザ像

“夕食の支度前”に “家/居室”で “家族分の料理を作る人”が
“それぞれの帰宅時間を把握し、献立を立てる” ために利用する。

・高校生の子供を持つ親
・主婦/主夫
・家庭内において、主に炊事を担当する人
（・家族の近況を知りたい人）

3．	主要ユーザ像

「友人とぶらぶらしてから帰宅するために高校生の子供の帰宅時間がまちまちで、夕飯の支度を始める時間が定まらず苦心している43歳の主婦」

4．	想定するユーザ数

算出方法：
(東京都在住43歳日本人女性の総人口) * (15年前(2004年)の出生率) * (期待値)
結果：
104,540(人) * 0.088 * 0.20 = 1,840(人)

5．	要件定義

必要となる機能を以下に列挙する。

	＝＝ユーザ視点＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝
	
	・ユーザ登録機能（必須）
・ユーザログイン機能（必須）
・画像と動画のアップロード機能（必須）
・グループ作成/参加機能
　――ユーザ登録と同時に設定
　――参加後はユーザログインだけでグループへ飛ぶようにする
　――グループIDをユーザに付加
 
・（自動）1日のタイムテーブル表示機能
　――表などを用いて実装
 
・外出時刻の設定/編集/削除機能
・帰宅時刻の設定/編集/削除機能
・画像と動画の表示機能（src or thumbnail）

＝＝内部＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝

・（自動）日付が変わったらタイムテーブルを更新する機能
　――日付の情報を保持しておく
 
・DB
・ユーザテーブル（PK：ユーザID）
・グループテーブル（PK：グループID）

――以下、可能なら実装――――――――――――

・1週間のタイムテーブル表示機能
・アプリ起動時の表示を設定する機能（日or週）
・ユーザ削除機能

6．	ページ構成

図1，2を用いて、各機能の振る舞いを示す。
図1　ページ構造
※図1省略
図2　データベース構成
※図2省略

7．	課題

　グループページへの不正アクセスによって、投稿された写真などから住所等の個人情報が流出する場合が考えられる。利用者のリテラシーに加え、グループページへのアクセスに必要なパスワードの流出を防ぐ施策など、改善が必要である。

参考URL

厚生労働省｜平成 30 年(2018) 人口動態統計の年間推計

https://www.mhlw.go.jp/toukei/saikin/hw/jinkou/suikei18/dl/2018suikei.pdf　(2019/07/10)

東京都総務局統計部｜住民基本台帳による東京都の世帯と人口

http://www.toukei.metro.tokyo.jp/juukiy/2019/jy19000001.htm　(2019/07/10)
