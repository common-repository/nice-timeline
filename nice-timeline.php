<?php

/*

Plugin Name: Nice Timeline
Plugin URI:
Version: 1.01
Description: Display nice timelines easily!
Author: Manu225
Author URI: 
Network: false
Text Domain: nice-timeline
Domain Path: 

*/

register_activation_hook( __FILE__, 'nice_timeline_install' );
register_uninstall_hook(__FILE__, 'nice_timeline_desinstall');

function nice_timeline_install()
{
	global $wpdb;

	$nice_timeline_table = $wpdb->prefix . "nice_timeline";
	$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "
        CREATE TABLE `".$nice_timeline_table."` (
          id int(11) NOT NULL AUTO_INCREMENT,          
          name varchar(50) NOT NULL,
          direction int(2) NOT NULL,
          lines_color varchar(20) NOT NULL,
          info_color varchar(20) NOT NULL,
          info_bg_color varchar(20) NOT NULL,
          title_size int(3) NOT NULL,
          icon_size int(3) NOT NULL,
          more_text varchar(50) NOT NULL,
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";

    dbDelta($sql);

    $sql = "
        CREATE TABLE `".$nice_timeline_contents_table."` (
          id int(11) NOT NULL AUTO_INCREMENT,          
          title varchar(500) NOT NULL,
          text text NOT NULL,
          icon varchar(500) NOT NULL,
          link varchar(500) NOT NULL,
          blank int(1) NOT NULL,
          `order` int(11) NOT NULL,
          id_timeline int(11),
          PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";
    
    dbDelta($sql);
}

function nice_timeline_desinstall()
{
	global $wpdb;

	$nice_timeline_table = $wpdb->prefix . "nice_timeline";
	$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

	//suppression des tables
	$sql = "DROP TABLE ".$nice_timeline_table.";";
	$wpdb->query($sql);

    $sql = "DROP TABLE ".$nice_timeline_contents_table.";";   
	$wpdb->query($sql);
}

add_action( 'admin_menu', 'register_nice_timeline_menu' );
function register_nice_timeline_menu() {

	add_menu_page('Nice timeline', 'Nice timeline', 'edit_pages', 'nice_timeline', 'nice_timeline', plugins_url( 'img/icon.png', __FILE__ ), 31);

}

add_action('admin_print_styles', 'nice_timeline_css' );
function nice_timeline_css() {
    wp_enqueue_style( 'FNiceTimelineStylesheet', plugins_url('css/admin.css', __FILE__) );
    wp_enqueue_style( 'wp-color-picker' );
}

// UPLOAD ENGINE
function nice_timeline_scripts() {
    wp_enqueue_media();
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
}
add_action( 'admin_enqueue_scripts', 'nice_timeline_scripts' );

function nice_timeline()
{
	global $wpdb;

	$nice_timeline_table = $wpdb->prefix . "nice_timeline";
	$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

	if(is_numeric($_GET['id']))
	{
		//récupère la timeline à manager
		$timeline = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$nice_timeline_table." WHERE id=%d", $_GET['id'] ));
		$contents = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$nice_timeline_contents_table." WHERE id_timeline = %d ORDER BY `order` ASC", $_GET['id'], OBJECT) );
		include(plugin_dir_path( __FILE__ ) . 'views/timeline.php');
	}
	else
	{
		if(sizeof($_POST) > 0)
		{
			if(empty($_POST['name']))
				echo '<h2>You must enter a name for your timeline!</h2>';
			else if(!is_numeric($_POST['id'])) //nouvelle timeline
			{
				check_admin_referer( 'new_ntl' );
				$query = $wpdb->prepare( "INSERT INTO ".$nice_timeline_table." (`name`, `direction`, `lines_color`, `info_color`, `info_bg_color`, `title_size`, `icon_size`, `more_text`)
				VALUES (%s, %d, %s, %s, %s, %d, %d, %s)", sanitize_text_field(stripslashes_deep($_POST['name'])), $_POST['direction'], $_POST['lines_color'], $_POST['info_color'], $_POST['info_bg_color'], $_POST['title_size'], $_POST['icon_size'], sanitize_text_field(stripslashes_deep($_POST['more_text'])));
				$wpdb->query($query);
			}
			else //mise à jour d'une future gallery
			{
				check_admin_referer( 'update_ntl_'.$_POST['id'] );
				$query = $wpdb->prepare( "UPDATE ".$nice_timeline_table." SET `name` = %s, `direction` = %d, `lines_color` = %s, `info_color` = %s, `info_bg_color` = %s, `title_size` = %d, `icon_size` = %d, `more_text` = %s WHERE id = %d",	sanitize_text_field(stripslashes_deep($_POST['name'])), $_POST['direction'], $_POST['lines_color'], $_POST['info_color'], $_POST['info_bg_color'], $_POST['title_size'], $_POST['icon_size'], sanitize_text_field(stripslashes_deep($_POST['more_text'])), $_POST['id'] );
				$wpdb->query($query);
			}
		}

		$query = "SELECT * FROM ".$nice_timeline_table." ORDER BY name ASC";
		$timelines = $wpdb->get_results( $query );
		include(plugin_dir_path( __FILE__ ) . 'views/timelines.php');
	}
}

//Ajax : nouveau événement
add_action( 'wp_ajax_etl_new', 'new_etl' );

function etl_new() {

	check_ajax_referer( 'new_etl' );

	global $wpdb;

	$nice_timeline_table = $wpdb->prefix . "nice_timeline";
	$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

	$query = $wpdb->prepare( "INSERT INTO ".$nice_timeline_contents_table." (`id_timeline`, `title`, `text`, `icon`, `link`, `blank`, `order`)
	VALUES (%d, %s, %s, %s, %s, %d, %d)", sanitize_text_field(stripslashes_deep($_POST['title'])), stripslashes_deep($_POST['text']), $_POST['icon'], $_POST['link'], $_POST['blank'], $_POST['order']);
	$wpdb->query($query);

	wp_die();

}

//Ajax : mise à jour événement
add_action( 'wp_ajax_etl_update', 'update_etl' );

function update_etl() {

	check_ajax_referer( 'update_etl_'.$_POST['id'] );

	global $wpdb;

	$nice_timeline_table = $wpdb->prefix . "nice_timeline";
	$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

	$query = $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `title` = %s, `text` = %s, `icon` = %s, `link` = %s, `blank` = %d
	WHERE id = %d", sanitize_text_field(stripslashes_deep($_POST['title'])), stripslashes_deep($_POST['text']), $_POST['icon'], $_POST['link'], $_POST['blank'], $_POST['id']);
	//die($q);
	$wpdb->query($query);

	wp_die();

}

//Ajax : suppression d'une timeline
add_action( 'wp_ajax_remove_ntl', 'remove_ntl' );

function remove_ntl() {

	check_ajax_referer( 'remove_ntl' );

	if (is_admin()) {

		global $wpdb;

		$nice_timeline_table = $wpdb->prefix . "nice_timeline";
		$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

		if(is_numeric($_POST['id']))
		{
			//supprime tous les contenus
			$query = $wpdb->prepare( 
				"DELETE FROM ".$nice_timeline_contents_table."
				 WHERE id=%d", $_POST['id']
			);
			$res = $wpdb->query( $query	);
			//supprime la timeline
			$query = $wpdb->prepare( 
				"DELETE FROM ".$nice_timeline_table."
				 WHERE id=%d", $_POST['id']
			);
			$res = $wpdb->query( $query	);
		}
		wp_die();
	}
}

//Ajax : ajout d'un contenu à la timeline
add_action( 'wp_ajax_etl_new', 'ntl_add_content' );

function ntl_add_content() {

	check_admin_referer( 'new_etl' );

	if (is_admin()) {

		global $wpdb;

		$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";


		if(is_numeric($_POST['id_timeline']) && !empty($_POST['title']))
		{
			$max_order = $wpdb->get_row( $wpdb->prepare( "SELECT MAX(`order`) as max_order FROM ".$nice_timeline_contents_table." WHERE id_timeline = %d", $_POST['id_timeline'] ));
			if($max_order)
				$max_order = ($max_order->max_order+1);
			else
				$max_order = 1;
			$query = $wpdb->prepare( "INSERT INTO ".$nice_timeline_contents_table." (`icon`, `title`, `text`, `link`, `blank`, `order`, `id_timeline`)
				VALUES (%s, %s, %s, %s, %d, %d, %d)", sanitize_text_field(stripslashes_deep($_POST['icon'])), sanitize_text_field(stripslashes_deep($_POST['title'])), stripslashes_deep($_POST['text']), sanitize_text_field(stripslashes_deep($_POST['link'])), ($_POST['blank'] == '1' ? 1 : 0), $max_order, $_POST['id_timeline'] );

			$res = $wpdb->query( $query	);
		}
		wp_die($wpdb->insert_id);
	}
}

//Ajax : update d'un contenu	
add_action( 'wp_ajax_ntl_save_content', 'ntl_save_content');
function ntl_save_content() {

	check_admin_referer( 'update_content_ntl' );

	if (is_admin()) {	
		if(is_numeric($_POST['id']) && !empty($_POST['title']) && !empty($_POST['icon']))
		{
			global $wpdb;

			$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

			$query = $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `title` = %s, `icon` = %s, `text` = %s, `link` = %s, `blank` = %d WHERE id = %d",
			sanitize_text_field(stripslashes_deep($_POST['title'])), sanitize_text_field(stripslashes_deep($_POST['icon'])), stripslashes_deep($_POST['text']), sanitize_text_field(stripslashes_deep($_POST['link'])), ($_POST['blank'] == '1' ? 1 : 0), $_POST['id'] );
			$res = $wpdb->query( $query	);
		}
	}
}

//Ajax : suppression d'un contenu	
add_action( 'wp_ajax_remove_etl', 'ntl_remove_content');
function ntl_remove_content() {

	check_ajax_referer( 'ntl_remove_content' );

	if (is_admin()) {	
		if(is_numeric($_POST['id']))
		{
			global $wpdb;

			$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

			//on récupère le order du menu a supprimer
			$query = "SELECT `order` FROM ".$nice_timeline_contents_table." WHERE id = %d";
			$content = $wpdb->get_row( $wpdb->prepare( $query, $_POST['id'] ));
			if($content)
			{
				//on met à jour les orders des contenus suivants
				$wpdb->query( $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `order` = `order` - 1 WHERE `order` > %d", $content->order));

				//supprime le contenu
				$query = $wpdb->prepare( 
					"DELETE FROM ".$nice_timeline_contents_table."
					 WHERE id=%d", $_POST['id']
				);
				$res = $wpdb->query( $query	);
			}
		}
	}

	wp_die();
}

//Ajax : changement de position d'une icone
add_action( 'wp_ajax_ntl_order_content', 'ntl_order_content' );

function ntl_order_content() {

	check_ajax_referer( 'ntl_order_content' );

	if (is_admin()) {
		global $wpdb;

		$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

		if(is_numeric($_POST['id']) && is_numeric($_POST['order']))
		{
			$timeline = $wpdb->get_row( $wpdb->prepare( "SELECT id_timeline, `order` FROM ".$nice_timeline_contents_table." WHERE id = %d", $_POST['id'] ));
			if($_POST['order'] > $timeline->order)
				$wpdb->query( $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `order` = `order` - 1 WHERE id_timeline = %d AND `order` <= %d AND `order` > %d", $timeline->id_timeline, $_POST['order'], $timeline->order ));
			else
				$wpdb->query( $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `order` = `order` + 1 WHERE id_timeline = %d AND `order` >= %d AND `order` < %d", $timeline->id_timeline, $_POST['order'], $timeline->order ));
			$wpdb->query( $wpdb->prepare( "UPDATE ".$nice_timeline_contents_table." SET `order` = %d WHERE id = %d", $_POST['order'], $_POST['id'] ));
			
		}
		wp_die();
	}
}

//Ajax : autocomplète icons
add_action( 'wp_ajax_ntl_fa_icons_list', 'ntl_fa_icons_list' );

function ntl_fa_icons_list() {

	if(current_user_can('edit_pages'))
	{

		check_ajax_referer( 'ntl_fa_icons_list' );

		require_once(plugin_dir_path( __FILE__ ) . 'icons_lists.php');

		global $fa_icons;

		if($_POST['q'])
			$icons_list = preg_grep("/^(.*)".$_POST['q']."(.*)$/", $fa_icons);
		else
			$icons_list = $fa_icons;

		if(sizeof($icons_list) > 0)
		{
			include(plugin_dir_path( __FILE__ ) . 'views/icons_list.php');
		}
		else
			echo 'No icon found !';
	}
	wp_die();
}

add_shortcode('nice-timeline', 'display_nice_timeline');
function display_nice_timeline($atts)
{
	if(is_numeric($atts['id']))
	{
		global $wpdb;

		$nice_timeline_table = $wpdb->prefix . "nice_timeline";
		$nice_timeline_contents_table = $wpdb->prefix . "nice_timeline_contents";

		$query = $wpdb->prepare( "SELECT * FROM ".$nice_timeline_table." WHERE id = %d", $atts['id'] );
		$timeline = $wpdb->get_row( $query );

		if($timeline)
		{
			$query = $wpdb->prepare( "SELECT * FROM ".$nice_timeline_contents_table." WHERE id_timeline = %d ORDER BY `order` ASC", $atts['id'] );
			$contents = $wpdb->get_results( $query );

			wp_enqueue_script( 'jquery');
			wp_enqueue_script( 'NiceTimelineFrontJS', plugins_url( 'js/front.js', __FILE__ ));
			wp_enqueue_style( 'NiceTimelineFrontStylesheet', plugins_url('css/front.css', __FILE__) );

			ob_start();
			include( plugin_dir_path( __FILE__ ) . 'views/nice_timeline.tpl.php' );
			return ob_get_clean();
		}
		else
			return 'Timeline ID '.$atts['id'].' not found!';
	}
	else
		return 'Missing ID of Timeline to display!';
}