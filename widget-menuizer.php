<?php
/*
Plugin Name: Widget Menuizer
Plugin URI: http://cornershopcreative.com/code/widget-menuizer
Description: Embed sidebar regions in your WordPress navigation menus.
Version: 0.5.5
Author: Cornershop Creative
Author URI: http://cornershopcreative.com
License: GPLv2 or later
Text Domain: widget-menuizer
*/

/**
 * Add our tiny bit of CSS
 */
function menuizer_admin_styles() {
	wp_register_style( 'menuizer_stylesheet', plugins_url( '/widget-menuizer.css', __FILE__ ) );
	wp_enqueue_style( 'menuizer_stylesheet' );
}
add_action( 'admin_enqueue_scripts', 'menuizer_admin_styles' );

/**
 * Generate a metabox for the sidebars item.
 *
 * @since 3.0.0
 */
function wp_nav_menu_sidebar_meta_box() {
	global $_nav_menu_placeholder, $nav_menu_selected_id, $wp_registered_sidebars;

	$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
	$theme = basename( get_stylesheet_directory() );

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);

	?>
	<div class="sidebardiv posttypediv" id="sidebardiv">
		<div id="sidebar-panel" class="tabs-panel tabs-panel-active">
			<ul id="sidebar-checklist" class="form-no-clear categorychecklist">
				<?php
					foreach ( $wp_registered_sidebars as $id => $sidebar ) :
						$numeric_id = hexdec( substr(md5( $theme . $id ), 0, 7) );	//this is just a placeholder of a unique id to keep WP from getting confused
					?>
					<li>
						<label class="menu-item-title">
							<input type="checkbox" class="menu-item-checkbox" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object-id]" value="<?php echo $numeric_id; ?>">
							<?php echo $sidebar['name']; ?>
						</label>
						<input type="hidden" class="menu-item-db-id" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-db-id]" value="0" />
						<input type="hidden" class="menu-item-object" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-object]" value="<?php echo $theme; ?>" />
						<input type="hidden" class="menu-item-parent-id" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-parent-id]" value="0" />
						<input type="hidden" class="menu-item-type" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" value="sidebar" />
						<input type="hidden" class="menu-item-title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-title]" value="<?php echo esc_attr( $sidebar['name'] ); ?>" />
						<input type="hidden" class="menu-item-url" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" value="" />
						<input type="hidden" class="menu-item-target" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-target]" value="div" />
						<input type="hidden" class="menu-item-attr_title" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-attr_title]" value="" />
						<input type="hidden" class="menu-item-classes" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-classes]" value="" />
						<input type="hidden" class="menu-item-xfn" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-xfn]" value="<?php echo $id ?>" /></li>
					</li>
				<?php
					$_nav_menu_placeholder--;
				endforeach; ?>
			</ul>
		</div>

		<!-- no touch! -->
		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
					echo esc_url( add_query_arg(
						array(
							'selectall' => 1,
						),
						remove_query_arg( $removed_args )
					));
				?>#sidebardiv" class="select-all">Select All</a>
			</span>
			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-sidebar-menu-item" id="submit-sidebardiv" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.sidebardiv -->
	<?php
}

/**
 * Add our metabox to the nav-menus.php sidebar
 */
function menuizer_meta_box() {
	add_meta_box( 'add-sidebars', __( 'Sidebars', 'widget-menuizer' ), 'wp_nav_menu_sidebar_meta_box', 'nav-menus', 'side', 'low' );
}
add_action( 'admin_init', 'menuizer_meta_box' );


/**
 * Implements a new walker to display our sidebar menu item once it's in a menu
 * If WordPress ever changes Walker_Nav_Menu_Edit (defined in wp-admin/includes/nav-menu.php), we've got work to do
 * @uses Walker_Nav_Menu
 */

// Load all the nav menu interface functions
require_once( ABSPATH . 'wp-admin/includes/nav-menu.php' );

class Sidebar_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth, $wp_registered_sidebars;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = get_the_title( $original_object->ID );
		} elseif ( 'sidebar' == $item->type ) {
			$original_title = $wp_registered_sidebars[ $item->xfn ]['name'];
			$item->type_label = __( 'Sidebar' );
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		//if the menu item is a sidebar and it belongs to a theme other than the active one, it's invalid
		if ( 'sidebar' == $item->type &&  basename( get_stylesheet_directory() ) != $item->object ) {
			$item->_invalid = __('This sidebar cannot be displayed as it is not from the currently active theme', 'widget-menuizer');
		}

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 == $depth )
			$submenu_text = 'style="display: none;"';

		?>
		<li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>"><?php _e( 'Edit Menu Item' ); ?></a>
					</span>
				</dt>
			</dl>

			<div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
				<?php if( 'custom' == $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?php echo $item_id; ?>">
							<?php _e( 'URL' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif; ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-<?php echo $item_id; ?>">
						<?php _e( 'Navigation Label' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
					</label>
				</p>
				<?php if ( 'sidebar' != $item->type ) : ?>
				<p class="description description-thin">
					<label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
						<?php _e( 'Title Attribute' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>
				<p class="field-link-target description">
					<label for="edit-menu-item-target-<?php echo $item_id; ?>">
						<input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
						<?php _e( 'Open link in a new window/tab' ); ?>
					</label>
				</p>
				<?php else: ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-display-<?php echo $item_id; ?>">
						<?php _e( 'Title Display' ); ?><br />
						<select id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat" name="menu-item-attr-title[<?php echo $item_id; ?>]" >
						<?php
							$options = array( 'none' => __('None', 'widget-menuizer'), 'inside' => __('Inside container', 'widget-menuizer'), 'outside' => __('Outside container', 'widget-menuizer') );
							foreach ( $options as $value => $label ) : ?>
								<option value="<?php echo $value; ?>" <?php selected( $item->attr_title, $value ); ?>><?php echo $label; ?></option>
							<?php endforeach; ?>
						?>
						</select>
					</label>
				</p>
				<?php endif; ?>
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?php echo $item_id; ?>">
						<?php _e( 'CSS Classes (optional)' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
					</label>
				</p>
				<?php if( 'sidebar' == $item->type ) : ?>
					<input type="hidden" id="edit-menu-item-xfn-<?php echo $item_id; ?>" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					<p class="field-link-container-target-proxy description description-thin">
						<label for="edit-menu-item-target-<?php echo $item_id; ?>">
							<?php _e( 'Container Element', 'widget-menuizer' ); ?><br />
							<select id="edit-menu-item-target-<?php echo $item_id; ?>" class="widefat" name="menu-item-target[<?php echo $item_id; ?>]" >
							<?php
								$elements = array( 'div', 'span', 'ul', 'ol', 'article', 'section', 'aside', 'none' );
								foreach ( $elements as $elem ) : ?>
									<option value="<?php echo $elem; ?>" <?php selected( $item->target, $elem ); ?>><?php echo $elem; ?></option>
								<?php endforeach;
							?></select>
						</label>
					</p>
				<?php else : ?>
				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
						<?php _e( 'Link Relationship (XFN)' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
					</label>
				</p>
				<?php endif; ?>
				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?php echo $item_id; ?>">
						<?php _e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
						<span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
					</label>
				</p>

				<p class="field-move hide-if-no-js description description-wide">
					<label>
						<span><?php _e( 'Move' ); ?></span>
						<a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
						<a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
						<a href="#" class="menus-move-left"></a>
						<a href="#" class="menus-move-right"></a>
						<a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
					</label>
				</p>

				<div class="menu-item-actions description-wide submitbox">
					<?php if ( 'sidebar' == $item->type ) : ?>
						<p class="link-to-original">
							<?php printf( __('Sidebar Shown: %s', 'widget-menuizer'), '<a href="' . admin_url( 'widgets.php' ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
						<?php // flag invalid reasons
						if ( ! empty ( $item->_invalid ) ) : ?>
							<p class="warning invalid">
								<?php echo $item->_invalid; ?>
							</p>
						<?php endif; ?>

						<?php
						// if necessary, flag for potential recursion
						$current_widgets = get_option( 'sidebars_widgets' );
						$found_menu = false;
						foreach ( $current_widgets[ $item->xfn ] as $widget_type ) {
							if ( strpos( $widget_type, 'nav_menu' ) === 0 ) {
								$found_menu = true;
								break;
							}
						}
						if ( $found_menu ) : ?>
							<p class="warning recursion">
								<?php _e( 'This sidebar contains a menu widget! Please ensure the widget doesn’t contain this menu or an infinite loop will result.'); ?>
							</p>
						<?php endif; ?>
					<?php elseif( 'custom' != $item->type && $original_title !== false ) : ?>
						<p class="link-to-original">
							<?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
					echo wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete-menu-item',
								'menu-item' => $item_id,
							),
							admin_url( 'nav-menus.php' )
						),
						'delete-menu_item_' . $item_id
					); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
						?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}

} // Sidebar_Walker_Nav_Menu_Edit

/**
 * Tell wp_edit_nav_menu_walker to use our new class
 */
function override_edit_nav_menu_walker( $class, $menu_id ) {
	if ( 'Walker_Nav_Menu_Edit' == $class ) {
		return 'Sidebar_Walker_Nav_Menu_Edit';
	} else {
		// return $class;
		return 'Sidebar_Walker_Nav_Menu_Edit';
	}
}
add_filter( 'wp_edit_nav_menu_walker', 'override_edit_nav_menu_walker', 99, 2 );


/**
 * When outputting a menu, spit out our sidebar if specified
 */
function menuizer_nav_menu_start_el( $item_output, $item, $depth, $args ) {

	if ( 'sidebar' == $item->type ) {

		/**
		 * We've hacked up the normal uses of $item's properties as follows:
		 * $item->type       = sidebar
		 * $item->object_id  = an arbitrary md5-ish value to keep WP happy
		 * $item->object     = the theme this sidebar belongs to, e.g. twentyfourteen
		 * $item->target     = the container element (div, ul, ol, aside, span, etc)
		 * $item->classes    = the 'classes' textfield, as normal
		 * $item->title      = the 'title' textfield, as normal
		 * $item->xfn        = the machine name of the sidebar to show
		 * $item->attr_title = location to show the title (none|inside|outside)
		 * $item->url        = can't be used as WP only saves this if the type == 'custom'... sigh
		 */

		// output nothing if this item isn't from the currently active theme
		$theme = basename( get_stylesheet_directory() );
		if ( $theme != $item->object ) return "";

		// output nothing if the given sidebar isn't active
		if ( ! is_active_sidebar( $item->xfn ) ) return "";

		// output the title here, if desired
		if ( 'outside' == $item->attr_title ) {
			$output = '<span class="menuizer-title">' . $item->title . '</span>';
		}

		// stringify custom classes for inclusion in container
		$classes = array();
		foreach ( $item->classes as $class ) {
			if ( strpos( $class, 'menu-item' ) === false ) $classes[] = $class;
		}
		$classes = implode( " ", $classes );

		// wrap
		if ( $item->target != 'none' ) {
			$output .= '<' . $item->target . ' class="menuizer-container ' . $classes . '">';
		}
		// output the title here, if desired
		if ( 'inside' == $item->attr_title ) {
			$output .= '<span class="menuizer-title">' . $item->title . '</span>';
		}
		ob_start();
		dynamic_sidebar( $item->xfn );
		$output .= ob_get_clean();
		if ( $item->target != 'none' ) {
			$output .= '</' . $item->target . '>';
		}
		$item_output = $output;

	}

	return $item_output;
}
add_filter( 'walker_nav_menu_start_el', 'menuizer_nav_menu_start_el', 99, 4 );