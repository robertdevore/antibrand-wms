<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @package WMS
 * @subpackage Administration
 */

// We are installing.
define( 'WP_INSTALLING', true );

// We are unaware of anything.
define( 'WP_SETUP_CONFIG', true );

// Define the application name.
if ( ! defined( 'APP_NAME' ) ) {
	define( 'APP_NAME', 'WebsiteApp' );
}

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging.
 */
error_reporting(0);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );
}

require( ABSPATH . 'wp-settings.php' );

// Load administration upgrade API.
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

// Load translation installation API.
require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

nocache_headers();

// Support wp-config-sample.php one level up.
if ( file_exists( ABSPATH . 'wp-config-sample.php' ) ) {
	$config_file = file( ABSPATH . 'wp-config-sample.php' );
} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config-sample.php' ) ) {
	$config_file = file( dirname( ABSPATH ) . '/wp-config-sample.php' );
} else {
	wp_die( sprintf(
		/* translators: %s: wp-config-sample.php */
		__( 'Sorry, we need a %s file to work from. Please re-upload this file to the root directory of your installation.' ),
		'<code>wp-config-sample.php</code>'
	) );
}

// Check if wp-config.php has been created.
if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
	wp_die( '<p>' . sprintf(
			/* translators: 1: wp-config.php 2: install.php */
			__( 'The file %1$s already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href="%2$s">installing now</a>.' ),
			'<code>wp-config.php</code>',
			'install.php'
		) . '</p>'
	);
}

// Check if wp-config.php exists above the root directory but is not part of another installation.
if ( @file_exists( ABSPATH . '../wp-config.php' ) && ! @file_exists( ABSPATH . '../wp-settings.php' ) ) {
	wp_die( '<p>' . sprintf(
			/* translators: 1: wp-config.php 2: install.php */
			__( 'The file %1$s already exists one level above your installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href="%2$s">installing now</a>.' ),
			'<code>wp-config.php</code>',
			'install.php'
		) . '</p>'
	);
}

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : -1;

/**
 * Display setup wp-config.php file header
 *
 * @ignore
 * @since WP 2.3.0
 *
 * @global string    $wp_local_package
 * @global WP_Locale $wp_locale
 *
 * @param string|array $body_classes
 */
function setup_config_display_header( $body_classes = [] ) {

	$body_classes   = (array) $body_classes;
	$body_classes[] = 'wp-core-ui';
	if ( is_rtl() ) {
		$body_classes[] = 'rtl';
	}

	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php _e( 'Configuration File Setup' ); ?></title>
	<?php wp_admin_css( 'install', true ); ?>
</head>
<body class="<?php echo implode( ' ', $body_classes ); ?>">
<?php
} // End function setup_config_display_header();

$language = '';

if ( ! empty( $_REQUEST['language'] ) ) {
	$language = preg_replace( '/[^a-zA-Z0-9_]/', '', $_REQUEST['language'] );
} elseif ( isset( $GLOBALS['wp_local_package'] ) ) {
	$language = $GLOBALS['wp_local_package'];
}

switch( $step ) {
	case -1:
		if ( wp_can_install_language_pack() && empty( $language ) && ( $languages = wp_get_available_translations() ) ) {
			setup_config_display_header( 'language-chooser' );
			echo '<h1>Begin Installation</h1>';
			echo '<p>Select a default language.</p>';
			echo '<form id="setup" method="post" action="?step=0">';
			wp_install_language_form( $languages );
			echo '</form>';
			break;
		}

		// Deliberately fall through if we can't reach the translations API.

	case 0:
		if ( ! empty( $language ) ) {
			$loaded_language = wp_download_language_pack( $language );
			if ( $loaded_language ) {
				load_default_textdomain( $loaded_language );
				$GLOBALS['wp_locale'] = new WP_Locale();
			}
		}

		setup_config_display_header();
		$step_1 = 'setup-config.php?step=1';

		if ( isset( $_REQUEST['noapi'] ) ) {
			$step_1 .= '&amp;noapi';
		}

		if ( ! empty( $loaded_language ) ) {
			$step_1 .= '&amp;language=' . $loaded_language;
		}
?>
<h1><?php _e( 'Begin Installation' ) ?></h1>
<p><?php _e( 'Before getting started we need some information on the database. You will need to know the following items before proceeding.' ) ?></p>
<ol>
	<li><?php _e( 'Database name' ); ?></li>
	<li><?php _e( 'Database username' ); ?></li>
	<li><?php _e( 'Database password' ); ?></li>
	<li><?php _e( 'Database host' ); ?></li>
	<li><?php _e( 'Table prefix (if you want to run more than one website management system in a single database)' ); ?></li>
</ol>
<p><?php
	/* translators: %s: wp-config.php */
	printf( __( 'We&#8217;re going to use this information to create a %s file.' ),
		'<code>wp-config.php</code>'
	);
	?>
	<strong><?php
		/* translators: 1: wp-config-sample.php, 2: wp-config.php */
		printf( __( 'If for any reason this automatic file creation doesn&#8217;t work, don&#8217;t worry. All this does is fill in the database information to a configuration file. You may also simply open %1$s in a text editor, fill in your information, and save it as %2$s.' ),
			'<code>wp-config-sample.php</code>',
			'<code>wp-config.php</code>'
		);
	?></strong>
</p>
<p><?php _e( 'In all likelihood, these items were supplied to you by your web host. If you don&#8217;t have this information, then you will need to contact them before you can continue. If you&#8217;re ready&hellip;' ); ?></p>

<p class="step"><a href="<?php echo $step_1; ?>" class="button button-large"><?php _e( 'Continue' ); ?></a></p>
<?php
	break;

	case 1:
		load_default_textdomain( $language );
		$GLOBALS['wp_locale'] = new WP_Locale();

		setup_config_display_header();
	?>
<h1><?php _e( 'Database Connection' ) ?></h1>
<form method="post" action="setup-config.php?step=2">
	<p><?php _e( 'Enter an application name to be used throughout the website management system. This allows you to "white label" the application and can be changed at any time in the <code>app-config</code> file.' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="appname"><?php _e( 'Application Name' ); ?></label></th>
			<td><input name="appname" id="appname" type="text" size="25" value="WebsiteApp" /></td>
			<td><?php _e( 'Enter the name to use for your application.' ); ?></td>
		</tr>
	</table>
	<p><?php _e( 'Enter your database connection details below. If you&#8217;re not sure about these, contact your host.' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname"><?php _e( 'Database Name' ); ?></label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="Enter name" /></td>
			<td><?php _e( 'The name of the database you want to use.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="uname"><?php _e( 'Username' ); ?></label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'root', 'example username' ), ENT_QUOTES ); ?>" /></td>
			<td><?php _e( 'Your database username.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd"><?php _e( 'Password' ); ?></label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'mysql', 'example password' ), ENT_QUOTES ); ?>" autocomplete="off" /></td>
			<td><?php _e( 'Your database password.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost"><?php _e( 'Database Host' ); ?></label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td><?php
				/* translators: %s: localhost */
				printf( __( 'You should be able to get this info from your web host, if %s doesn&#8217;t work.' ),'<code>localhost</code>' );
			?></td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix"><?php _e( 'Table Prefix' ); ?></label></th>
			<td><input name="prefix" id="prefix" type="text" value="app_<?php echo esc_attr( md5( time() ) ); ?>_" size="25" /></td>
			<td><?php echo sprintf(
				'%1s <em>app_%2s_</em><br />%3s',
				esc_html__( 'Random table prefix is:' ),
				md5( time() ),
				esc_html__( 'Change this if you want to define your own prefix.' )

			); ?></td>
		</tr>
	</table>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="1" /><?php } ?>
	<input type="hidden" name="language" value="<?php echo esc_attr( $language ); ?>" />
	<p class="step"><input name="submit" type="submit" value="<?php echo htmlspecialchars( __( 'Submit' ), ENT_QUOTES ); ?>" class="button button-large" /></p>
</form>
<?php
	break;

	case 2:
	load_default_textdomain( $language );
	$GLOBALS['wp_locale'] = new WP_Locale();

	$appname = trim( wp_unslash( $_POST['appname'] ) );
	$dbname  = trim( wp_unslash( $_POST['dbname'] ) );
	$uname   = trim( wp_unslash( $_POST['uname'] ) );
	$pwd     = trim( wp_unslash( $_POST['pwd'] ) );
	$dbhost  = trim( wp_unslash( $_POST['dbhost'] ) );
	$prefix  = trim( wp_unslash( $_POST['prefix'] ) );

	$step_1  = 'setup-config.php?step=1';
	$install = 'install.php';
	if ( isset( $_REQUEST['noapi'] ) ) {
		$step_1 .= '&amp;noapi';
	}

	if ( ! empty( $language ) ) {
		$step_1  .= '&amp;language=' . $language;
		$install .= '?language=' . $language;
	} else {
		$install .= '?language=en_US';
	}

	$tryagain_link = '</p><p class="step"><a href="' . $step_1 . '" onclick="javascript:history.go(-1);return false;" class="button button-large">' . __( 'Try again' ) . '</a>';

	if ( empty( $prefix ) ) {
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link ) );
	}

	// Validate $prefix: it can only contain letters, numbers and underscores.
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) ) {
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link ) );
	}

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define( 'DB_NAME', $dbname );
	define( 'DB_USER', $uname );
	define( 'DB_PASSWORD', $pwd );
	define( 'DB_HOST', $dbhost );
	/**#@-*/

	// Re-construct $wpdb with these new values.
	unset( $wpdb );
	require_wp_db();

	/*
	 * The wpdb constructor bails when WP_SETUP_CONFIG is set, so we must
	 * fire this manually. We'll fail here if the values are no good.
	 */
	$wpdb->db_connect();

	if ( ! empty( $wpdb->error ) ) {
		wp_die( $wpdb->error->get_error_message() . $tryagain_link );
	}

	$errors = $wpdb->hide_errors();
	$wpdb->query( "SELECT $prefix" );
	$wpdb->show_errors( $errors );

	if ( ! $wpdb->last_error ) {
		// MySQL was able to parse the prefix as a value, which we don't want. Bail.
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" is invalid.' ) );
	}

	// Generate keys and salts using secure CSPRNG; fallback to API if enabled; further fallback to original wp_generate_password().
	try {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
		$max = strlen($chars) - 1;
		for ( $i = 0; $i < 8; $i++ ) {
			$key = '';
			for ( $j = 0; $j < 64; $j++ ) {
				$key .= substr( $chars, random_int( 0, $max ), 1 );
			}
			$secret_keys[] = $key;
		}
	} catch ( Exception $ex ) {
		$no_api = isset( $_POST['noapi'] );

		if ( ! $no_api ) {
			$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
		}

		if ( $no_api || is_wp_error( $secret_keys ) ) {
			$secret_keys = array();
			for ( $i = 0; $i < 8; $i++ ) {
				$secret_keys[] = wp_generate_password( 64, true, true );
			}
		} else {
			$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );
			foreach ( $secret_keys as $k => $v ) {
				$secret_keys[$k] = substr( $v, 28, 64 );
			}
		}
	}

	$key = 0;
	foreach ( $config_file as $line_num => $line ) {
		if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
			$config_file[ $line_num ] = '$table_prefix  = \'' . addcslashes( $prefix, "\\'" ) . "';\r\n";
			continue;
		}

		// Replace the sample definitions with user-provided definitions.
		if ( ! preg_match( '/^define\(\s*\'([A-Z_]+)\',([ ]+)/', $line, $match ) ) {
			continue;
		}

		$constant = $match[1];
		$padding  = $match[2];

		switch ( $constant ) {
			case 'DB_NAME'     :
			case 'DB_USER'     :
			case 'DB_PASSWORD' :
			case 'DB_HOST'     :
				$config_file[ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "' );\r\n";
				break;
			case 'DB_CHARSET'  :
				if ( 'utf8mb4' === $wpdb->charset || ( ! $wpdb->charset && $wpdb->has_cap( 'utf8mb4' ) ) ) {
					$config_file[ $line_num ] = "define( '" . $constant . "'," . $padding . "'utf8mb4' );\r\n";
				}
				break;
			case 'AUTH_KEY'         :
			case 'SECURE_AUTH_KEY'  :
			case 'LOGGED_IN_KEY'    :
			case 'NONCE_KEY'        :
			case 'AUTH_SALT'        :
			case 'SECURE_AUTH_SALT' :
			case 'LOGGED_IN_SALT'   :
			case 'NONCE_SALT'       :
				$config_file[ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . $secret_keys[$key++] . "' );\r\n";
				break;
			case 'APP_NAME'     :
				$config_file[ $line_num ] = "define( '" . $constant . "'," . $padding . "'" . $appname . "' );\r\n";
				break;
		}
	}
	unset( $line );

	if ( ! is_writable(ABSPATH) ) :
		setup_config_display_header();
?>
<p><?php
	/* translators: %s: wp-config.php */
	printf( __( 'Sorry, but I can&#8217;t write the %s file.' ), '<code>wp-config.php</code>' );
?></p>
<p><?php
	/* translators: %s: wp-config.php */
	printf( __( 'You can create the %s file manually and paste the following text into it.' ), '<code>wp-config.php</code>' );
?></p>
<textarea id="wp-config" cols="98" rows="15" class="code" readonly="readonly"><?php
		foreach ( $config_file as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8' );
		}
?></textarea>
<p><?php _e( 'After you&#8217;ve done that, click &#8220;Run the installation.&#8221;' ); ?></p>
<p class="step"><a href="<?php echo $install; ?>" class="button button-large"><?php _e( 'Run the installation' ); ?></a></p>
<script>
(function(){
if ( ! /iPad|iPod|iPhone/.test( navigator.userAgent ) ) {
	var el = document.getElementById( 'wp-config' );
	el.focus();
	el.select();
}
})();
</script>
<?php
	else :
		/*
		 * If this file doesn't exist, then we are using the wp-config-sample.php
		 * file one level up, which is for the develop repo.
		 */
		if ( file_exists( ABSPATH . 'wp-config-sample.php' ) )
			$path_to_wp_config = ABSPATH . 'wp-config.php';
		else
			$path_to_wp_config = dirname( ABSPATH ) . '/wp-config.php';

		$handle = fopen( $path_to_wp_config, 'w' );
		foreach ( $config_file as $line ) {
			fwrite( $handle, $line );
		}
		fclose( $handle );
		chmod( $path_to_wp_config, 0666 );
		setup_config_display_header();
?>
<h1><?php _e( 'Successful Database Connection' ) ?></h1>
<p><?php _e( 'The website management system can now communicate with your database. If you are ready&hellip;' ); ?></p>

<p class="step"><a href="<?php echo $install; ?>" class="button button-large"><?php _e( 'Run the installation' ); ?></a></p>
<?php
	endif;
	break;
}
?>
<?php wp_print_scripts( 'language-chooser' ); ?>
</body>
</html>