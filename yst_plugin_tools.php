<?php

/**
 * Backend Class for use in all Yoast plugins, modified for Clicky plugin
 * Version 0.2
 */

if (!class_exists('Clicky_Base_Plugin_Admin')) {
	class Clicky_Base_Plugin_Admin {

		var $hook 		= '';
		var $filename	= '';
		var $longname	= '';
		var $shortname	= '';
		var $ozhicon	= '';
		var $optionname = '';
		var $homepage	= '';
		var $accesslvl	= 'manage_options';
		var $feed 		= '';
		
		function Clicky_Base_Plugin_Admin() {
			add_action( 'admin_menu', array(&$this, 'register_settings_page') );

			add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );
			add_filter( 'ozh_adminmenu_icon', array(&$this, 'add_ozh_adminmenu_icon' ) );				
			
			add_action( 'admin_print_scripts', array(&$this,'config_page_scripts') );
			add_action( 'admin_print_styles', array(&$this,'config_page_styles') );	
			
			add_action('wp_dashboard_setup', array(&$this,'widget_setup') );	
		}
		
		function add_ozh_adminmenu_icon( $hook ) {
			if ($hook == $this->hook) 
				return WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname($filename)). '/'.$this->ozhicon;
			return $hook;
		}
		
		function config_page_styles() {
			if (isset($_GET['page']) && $_GET['page'] == $this->hook) {
				wp_enqueue_style('dashboard');
				wp_enqueue_style('thickbox');
				wp_enqueue_style('global');
				wp_enqueue_style('wp-admin');
				wp_enqueue_style('clicky-admin-css', WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/yst_plugin_tools.css');
			}
		}

		function register_settings_page() {
			add_options_page($this->longname, $this->shortname, $this->accesslvl, $this->hook, array(&$this,'config_page'));
		}
		
		function plugin_options_url() {
			return admin_url( 'options-general.php?page='.$this->hook );
		}
		
		/**
		 * Add a link to the settings page to the plugins list
		 */
		function add_action_link( $links, $file ) {
			static $this_plugin;
			if( empty($this_plugin) ) $this_plugin = $this->filename;
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="' . $this->plugin_options_url() . '">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}
		
		function config_page() {
			
		}
		
		function config_page_scripts() {
			if (isset($_GET['page']) && $_GET['page'] == $this->hook) {
				wp_enqueue_script('postbox');
				wp_enqueue_script('dashboard');
			}
		}

		/**
		 * Create a Checkbox input field
		 */
		function checkbox($id, $label) {
			$options = get_option($this->optionname);
			return '<input type="checkbox" id="'.$id.'" name="'.$id.'"'. checked($options[$id],true,false).'/> <label for="'.$id.'">'.$label.'</label><br/>';
		}
		
		/**
		 * Create a Text input field
		 */
		function textinput($id, $label) {
			$options = get_option($this->optionname);
			return '<label for="'.$id.'">'.$label.':</label><br/><input size="45" type="text" id="'.$id.'" name="'.$id.'" value="'.$options[$id].'"/><br/><br/>';
		}

		/**
		 * Create a potbox widget
		 */
		function postbox($id, $title, $content) {
		?>
			<div id="<?php echo $id; ?>" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
				<h3 class="hndle"><span><?php echo $title; ?></span></h3>
				<div class="inside">
					<?php echo $content; ?>
				</div>
			</div>
		<?php
		}	


		/**
		 * Create a form table from an array of rows
		 */
		function form_table($rows) {
			$content = '<table class="form-table">';
			foreach ($rows as $row) {
				$content .= '<tr><th valign="top" scrope="row">';
				if (isset($row['id']) && $row['id'] != '')
					$content .= '<label for="'.$row['id'].'">'.$row['label'].':</label>';
				else
					$content .= $row['label'];
				if (isset($row['desc']) && $row['desc'] != '')
					$content .= '<br/><small>'.$row['desc'].'</small>';
				$content .= '</th><td valign="top">';
				$content .= $row['content'];
				$content .= '</td></tr>'; 
			}
			$content .= '</table>';
			return $content;
		}

		/**
		 * Create a "plugin like" box.
		 */
		function plugin_like() {
			$content = '<p>'.__('Why not do any or all of the following:','clickyplugin').'</p>';
			$content .= '<ul>';
			$content .= '<li><a href="'.$this->homepage.'">'.__('Link to it so other folks can find out about it.','clickyplugin').'</a></li>';
			$content .= '<li><a href="http://wordpress.org/extend/plugins/'.$this->hook.'/">'.__('Give it a good rating on WordPress.org.','clickyplugin').'</a></li>';
			$content .= '<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=2017947">'.__('Donate a token of your appreciation.','clickyplugin').'</a></li>';
			$content .= '</ul>';
			$this->postbox($this->hook.'like', 'Like this plugin?', $content);
		}	
		
		/**
		 * Info box with link to the support forums.
		 */
		function plugin_support() {
			$content = '<p>'.__('If you have any problems with this plugin or good ideas for improvements or new features, please talk about them in the','clickyplugin').' <a href="http://wordpress.org/tags/'.$this->hook.'">'.__("Support forums",'clickyplugin').'</a>.</p>';
			$this->postbox($this->hook.'support', 'Need support?', $content);
		}

		/**
		 * Box with latest news from getclicky.com
		 */
		function news() {
			require_once(ABSPATH.WPINC.'/rss.php');  
			if ( $rss = fetch_rss( $this->feed ) ) {
				$content = '<ul>';
				$rss->items = array_slice( $rss->items, 0, 3 );
				foreach ( (array) $rss->items as $item ) {
					$content .= '<li>';
					$content .= '<a class="rsswidget" href="'.clean_url( $item['link'], $protocolls=null, 'display' ).'">'. htmlentities($item['title']) .'</a> ';
					$content .= '</li>';
				}
				$content .= '<li class="rss"><a href="'.$this->feed.'">Subscribe</a></li>';
				$this->postbox('clickylatest', 'Latest news from Clicky', $content);
			} else {
				$this->postbox('clickylatest', 'Latest news from Clicky', 'Nothing to say...');
			}
		}

		function text_limit( $text, $limit, $finish = ' [&hellip;]') {
			if( strlen( $text ) > $limit ) {
		    	$text = substr( $text, 0, $limit );
				$text = substr( $text, 0, - ( strlen( strrchr( $text,' ') ) ) );
				$text .= $finish;
			}
			return $text;
		}
	}
}

?>