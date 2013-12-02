<?php
/*
Plugin Name: WP Meetup User List
Description: Adds additional profile form fields and provides the shorcode [wpmeetup_user_list] to print a user list
Version: 0.2
Author: Bernhard Kau
Author URI: http://kau-boys.de
*/

function wpmeetup_user_list_init() {
	load_plugin_textdomain( 'wpmeetup-user-list', false, dirname( plugin_basename( __FILE__ ) ) );
}
add_action( 'plugins_loaded', 'wpmeetup_user_list_init' );

function wpmeetup_user_list_styles() {
	wp_register_style( 'wpmeetup-user-list', plugins_url( 'wpmeetup-user-list.css', __FILE__ ), false, '0.2.21' );
	wp_enqueue_style( 'wpmeetup-user-list' );
}
add_action( 'wp_enqueue_scripts', 'wpmeetup_user_list_styles' );

function wpmeetup_get_user_list(){
	global $wpdb;
	
	$wpmeetup_user_list = '<ul id="user_list" class="wpmeetup-members-list">';
	
	$users = get_users( 'meta_key=list_participant&meta_value=true&orderby=display_name' );
	
	foreach( $users as $user ) {
		$social_networks = array();
		
		if ( $googleplus = get_the_author_meta( 'googleplus', $user->ID ) ) {
			$social_networks['googleplus'] = esc_attr( $googleplus );
		}
		if ( $facebook = get_the_author_meta( 'facebook', $user->ID ) ) {
			$social_networks['facebook'] = esc_attr( $facebook );
		}
		if ( $xing = get_the_author_meta( 'xing', $user->ID ) ) {
			$social_networks['xing'] = esc_attr( $xing );
		}
		if ( $twitter = get_the_author_meta( 'twitter', $user->ID ) ) {
			$social_networks['twitter'] = 'https://twitter.com/' . esc_attr( $twitter );
		}
		if ( $github = get_the_author_meta( 'github', $user->ID ) ) {
			$social_networks['github'] = 'https://github.com/' . esc_attr( $github );
		}
		if ( $gist = get_the_author_meta( 'gist', $user->ID ) ) {
			$social_networks['gist'] = 'https://gist.github.com/' . esc_attr( $gist );
		}		
		
		$public_email = get_the_author_meta( 'public_email', $user->ID );
		$public_website = get_the_author_meta( 'public_website', $user->ID );
		$public_description = get_the_author_meta( 'public_description', $user->ID );
		
		$wpmeetup_user_list .= '<li class="wpmeetup-user">' . "\n";
		$wpmeetup_user_list .= '	<div class="wpmeetup-user-avatar">' . get_avatar( $user->ID, $size = '96' ) . '</div>' . "\n";
		$wpmeetup_user_list .= '	<div class="wpmeetup-user-name">' . $user->display_name . '</div>' . "\n";
		
		if( !empty( $public_email ) ) {
			$email_parts = explode( '@', $public_email );
			$wpmeetup_user_list .= '	<div class="wpmeetup-user-email" data-name="' . esc_attr( $email_parts[0] ) . '" data-domain="' . esc_attr( $email_parts[1] ) . '"></div>' . "\n";
		}
		if( !empty( $public_website ) ) {
			$wpmeetup_user_list .= '	<div class="wpmeetup-user-website">Website: <a href="' . esc_attr( $public_website ) . '">' .  esc_html( $public_website ) . '</a></div>' . "\n";
		}
		
		if( !empty ( $social_networks ) ) {
			$wpmeetup_user_list .= '		<div class="wpmeetup-user-social">' . __( 'Find me on:', 'wpmeetup-user-list' ) . '</div>' . "\n";
			$wpmeetup_user_list .= '		<ul class="wpmeetup-sociallinks">' . "\n";
			foreach ( $social_networks as $network_name => $network_url) {
				if ( !empty( $network_url ) ) {
					$wpmeetup_user_list .= '			<li><a href="' .  esc_attr( $network_url ) . '" class="wpmeetup-' . $network_name . '" title="' . __( $network_name, 'wpmeetup-user-list' ) . '" target="_blank"></a></li>' . "\n";
				}
			}
			$wpmeetup_user_list .= '		</ul>' . "\n";
		}
		$wpmeetup_user_list .= '	<div class="wpmeetup-user-description">' . $public_description . '</div>';
		$wpmeetup_user_list .= '</li>' . "\n";
	}
	
	$wpmeetup_user_list .= '</ul>' . "\n";
	$wpmeetup_user_list .= '<script type="text/javascript">
								jQuery(".wpmeetup-user-email").each(function(i){
									var email = jQuery(this).data("name") + "@" + jQuery(this).data("domain");
									jQuery(this).html("E-Mail: <a href=\"mailto:" + email + "\">" + email + "</a>"); 
								});
							</script>' . "\n";
	
	return $wpmeetup_user_list;
}
add_shortcode( 'wpmeetup_user_list', 'wpmeetup_get_user_list' );

function wpmeetup_remove_old_user_contactmethod( $contactmethods ) {

	// Remove Yahoo IM
	if ( isset( $contactmethods['yim'] ) ) {
		unset( $contactmethods['yim'] );
	}

	// Remove AIM
	if ( isset( $contactmethods['aim'] ) ) {
		unset( $contactmethods['aim'] );
	}

	// Remove Jabber
	if ( isset( $contactmethods['jabber'] ) ) {
		unset( $contactmethods['jabber'] );
	}

	return $contactmethods;
}
add_filter( 'user_contactmethods', 'wpmeetup_remove_old_user_contactmethod', 10, 1 );

function wpmeetup_add_custom_user_profile_fields( $user ) {

	$list_user = get_the_author_meta( 'list_participant', $user->ID );

?>
<h3><?php _e( 'WP Meetup Profile' ); ?></h3>

<table class="form-table wpmeetup-profile">
	<tr>
		<th scope="row"><?php _e( 'Show on participants list', 'wpmeetup-user-list' ); ?></th>
		<td><label for="list_participant"><input name="list_participant" type="checkbox" id="list_participant" value="true" <?php if ( ! empty( $list_user ) ) checked( 'true', $list_user ); ?> /> <?php _e( 'List my profile with the following information in the public participants list.', 'wpmeetup-user-list' ); ?></label></td>
	</tr>
	<tr>
		<th><label for="googleplus"><?php _e( 'Google+ Profile URL', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="url" name="googleplus" id="googleplus" value="<?php echo esc_attr( get_the_author_meta( 'googleplus', $user->ID ) ); ?>" class="regular-text" title="<?php _e( 'Please start the URL with a protocol (such as http://).', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="facebook"><?php _e( 'Facebook Profile URL', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="url" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text" title="<?php _e( 'Please start the URL with a protocol (such as http://).', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="xing"><?php _e( 'Xing-Profile URL', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="url" name="xing" id="xing" value="<?php echo esc_attr( get_the_author_meta( 'xing', $user->ID ) ); ?>" class="regular-text" title="<?php _e( 'Please start the URL with a protocol (such as http://).', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="twitter"><?php _e( 'Twitter Username', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" pattern="[\w]{0,15}" title="<?php _e( 'Please only provide your username, not the full profile URL.', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="github"><?php _e( 'Github Username', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="text" name="github" id="github" value="<?php echo esc_attr( get_the_author_meta( 'github', $user->ID ) ); ?>" class="regular-text" pattern="[\w]*" title="<?php _e( 'Please only provide your username, not the full profile URL.', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="gist"><?php _e( 'Gist Username', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="text" name="gist" id="gist" value="<?php echo esc_attr( get_the_author_meta( 'gist', $user->ID ) ); ?>" class="regular-text" pattern="[\w]*" title="<?php _e( 'Please only provide your username, not the full profile URL.', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="public_email"><?php _e( 'Public email address', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="email" name="public_email" id="public_email" value="<?php echo esc_attr( get_the_author_meta( 'public_email', $user->ID ) ); ?>" class="regular-text" /></td>
	</tr>
	<tr>
		<th><label for="public_website"><?php _e( 'Public website', 'wpmeetup-user-list' ); ?></label></th>
		<td><input type="url" name="public_website" id="public_website" value="<?php echo esc_attr( get_the_author_meta( 'public_website', $user->ID ) ); ?>" class="regular-text" title="<?php _e( 'Please start the URL with a protocol (such as http://).', 'wpmeetup-user-list' ); ?>" /></td>
	</tr>
	<tr>
		<th><label for="public_description"><?php _e( 'Additional Info', 'wpmeetup-user-list' ); ?></label></th>
		<td><textarea name="public_description" id="public_description" rows="5" cols="30"><?php echo esc_textarea( get_the_author_meta( 'public_description', $user->ID ) ); ?></textarea>
	</tr>
</table>
<?
}
add_action( 'show_user_profile', 'wpmeetup_add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'wpmeetup_add_custom_user_profile_fields' );

function wpmeetup_save_custom_user_profile_fields( $user_id ) {
	
	if ( !current_user_can( 'edit_user', $user_id ) ) {
		return FALSE;
	}
	
	update_usermeta( $user_id, 'googleplus', $_POST['googleplus'] );
	update_usermeta( $user_id, 'facebook', $_POST['facebook'] );
	update_usermeta( $user_id, 'xing', $_POST['xing'] );
	update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
	update_usermeta( $user_id, 'github', $_POST['github'] );
	update_usermeta( $user_id, 'gist', $_POST['gist'] );
	update_usermeta( $user_id, 'public_email', $_POST['public_email'] );
	update_usermeta( $user_id, 'public_website', $_POST['public_website'] );
	update_usermeta( $user_id, 'public_description', $_POST['public_description'] );
	update_usermeta( $user_id, 'list_participant', $_POST['list_participant'] );
}
add_action( 'personal_options_update', 'wpmeetup_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'wpmeetup_save_custom_user_profile_fields' );