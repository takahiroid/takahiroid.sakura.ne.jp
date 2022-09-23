<?php
/**
 * The base configurations of the WordPress.
 *
 * このファイルは、MySQL、テーブル接頭辞、秘密鍵、言語、ABSPATH の設定を含みます。
 * より詳しい情報は {@link http://wpdocs.sourceforge.jp/wp-config.php_%E3%81%AE%E7%B7%A8%E9%9B%86 
 * wp-config.php の編集} を参照してください。MySQL の設定情報はホスティング先より入手できます。
 *
 * このファイルはインストール時に wp-config.php 作成ウィザードが利用します。
 * ウィザードを介さず、このファイルを "wp-config.php" という名前でコピーして直接編集し値を
 * 入力してもかまいません。
 *
 * @package WordPress
 */

// 注意: 
// Windows の "メモ帳" でこのファイルを編集しないでください !
// 問題なく使えるテキストエディタ
// (http://wpdocs.sourceforge.jp/Codex:%E8%AB%87%E8%A9%B1%E5%AE%A4 参照)
// を使用し、必ず UTF-8 の BOM なし (UTF-8N) で保存してください。

// ** MySQL 設定 - こちらの情報はホスティング先から入手してください。 ** //
/** WordPress のためのデータベース名 */
define('DB_NAME', 'takahiroid_db');

/** MySQL データベースのユーザー名 */
define('DB_USER', 'takahiroid');

/** MySQL データベースのパスワード */
define('DB_PASSWORD', '0819rock');

/** MySQL のホスト名 */
define('DB_HOST', 'mysql424.db.sakura.ne.jp');

/** データベースのテーブルを作成する際のデータベースのキャラクターセット */
define('DB_CHARSET', 'utf8');

/** データベースの照合順序 (ほとんどの場合変更する必要はありません) */
define('DB_COLLATE', '');

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 * 後でいつでも変更して、既存のすべての cookie を無効にできます。これにより、すべてのユーザーを強制的に再ログインさせることになります。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'SD?X|i=-uB2U6znm;@+UkzP: r,~7l,XAdFu{|~ntkOSA+8t8Nk/Ps1+3Y-~SH}s');
define('SECURE_AUTH_KEY',  'cTz8yw,l*>_ZA9-Y9Mi7I_[7;tri;v-Ms:F6PNNs+NhCV(Q@tM2NUdD+/||Hd.>S');
define('LOGGED_IN_KEY',    'b#j^DYsyET@,$~|B6Z3bwmL|fXz/q{RCz_xeo6O^DS=DU}dx6%@@u6aC|-?>h^5q');
define('NONCE_KEY',        '3!uZCKF&CcgFg5>xYeRD(^0<^dF)7T3&e#l+0L|-D^-Q1>{iHNh/g)+t=&/k4cYL');
define('AUTH_SALT',        '<(,sI%MZ$>f-PG8~?D[RGmvuwJ|IQ0KF-|RrB<>wV`|m|;(82Qc$p+H-rtHBc3kz');
define('SECURE_AUTH_SALT', 'H_={WGC>K|-M2{t[6>(2-[fP=q+I# rOG|+8+)8WgF54,),vuPjc@-&;zhW2.d2b');
define('LOGGED_IN_SALT',   'K513ld73awc7]8D--n9,`L]b#EgWIa=b-w&#wuB}dl1dwDn~Hk5L{,m4|rc(`HX&');
define('NONCE_SALT',       '+7+-%hQ-5|#r;$.Bvcz|uF~0,9KYv:)JLzVDQrt_B7XNXG5axD.|-<jO4n8[]J-O');

/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
$table_prefix  = 'wp_rumi_';

/**
 * ローカル言語 - このパッケージでは初期値として 'ja' (日本語 UTF-8) が設定されています。
 *
 * WordPress のローカル言語を設定します。設定した言語に対応する MO ファイルが
 * wp-content/languages にインストールされている必要があります。例えば de_DE.mo を
 * wp-content/languages にインストールし WPLANG を 'de_DE' に設定することでドイツ語がサポートされます。
 */
define('WPLANG', 'ja');

/**
 * 開発者へ: WordPress デバッグモード
 *
 * この値を true にすると、開発中に注意 (notice) を表示します。
 * テーマおよびプラグインの開発者には、その開発環境においてこの WP_DEBUG を使用することを強く推奨します。
 */
define('WP_DEBUG', false);

/* 編集が必要なのはここまでです ! WordPress でブログをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
