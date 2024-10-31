<?php
/*
* Plugin Name: Send Email
* Description: Send Email allows you to send emails directly through WordPress dashboard conveniently. 
* Version: 2.0.0
* Plugin URI: https://sitefreelancing.com/product/send-emails/
* Author URI: https://sitefreelancing.com/
* Author: webdeveloperphp6
*/

// add the admin menu option
add_action( 'admin_menu', 'sendemail_add_admin' );
function sendemail_add_admin() {
	add_submenu_page( 'tools.php', __("Send	Email", "send-email"), __("Send Email", "send-email"), 'edit_users', 'sendemail', 'sendemail' );
}

// add the JavaScript
add_action( 'admin_head', 'sendemail_add_js' );
function sendemail_add_js() {
	if ( isset( $_GET["page"] ) && $_GET["page"] == "sendemail" ) {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".sendemail-hide").hide();
			jQuery("#sendemail_autoheaders,#sendemail_customheaders").bind("change", function(){
				if (jQuery("#sendemail_autoheaders").is(":checked")){
					jQuery("#customheaders").hide();
					jQuery("#autoheaders").show();
				}
				if (jQuery("#sendemail_customheaders").is(":checked")){
					jQuery("#autoheaders").hide();
					jQuery("#customheaders").show();
				}
			});
		});
		</script>
		';
	}
}
// add the CSS
add_action( 'admin_head', 'sendemail_add_css' );
function sendemail_add_css() {
	if ( isset( $_GET["page"] ) && $_GET["page"] == "sendemail" ) {
		echo '
		<style type="text/css">
		#sendemail label {
			width: 16em;
			float: left;
		}
		#sendemail .text {
			width: 30em;
		}
		#sendemail p, #sendemail pre {
			clear: left;
		}
		#autoheaders {
			display:none;
		}
		.hide {
			display: none;
		}
		</style>
		';
	}
}

// load the send email admin page
function sendemail() {
	global $current_user;

	$from_email = apply_filters( 'wp_mail_from', $current_user->user_email );
	//$from_name = apply_filters( 'wp_mail_from_name', $from_name );

	echo '
	<div id="sendemail" class="wrap">
	';
	
	if ( isset( $_POST["sendemail_to"]) && $_POST["sendemail_to"] != "" && isset( $_POST["sendemail_subject"]) && $_POST["sendemail_subject"] != "" && isset( $_POST["sendemail_message"]) && $_POST["sendemail_message"] != "" && isset( $_POST["sendemail_headers"]) && $_POST["sendemail_headers"] != "" )
	{
		$nonce = $_REQUEST['_wpnonce'];
		if ( wp_verify_nonce( $nonce, 'sendemail' ) ) {			
			$headers = sendemail_send( $_POST["sendemail_to"], $_POST["sendemail_subject"], $_POST["sendemail_message"], $_POST["sendemail_headers"] );
			echo '<div class="updated"><p>' . __( 'The email has been sent from your WordPress Admin email. The headers sent were:', "send-email" ) . '</p><pre>' . str_replace( chr( 10 ), '\n' . "\n", str_replace( chr( 13 ), '\r', $headers ) ) . '</pre></div>';
		} else {
			echo '<div class="updated"><p>' . __( 'Security check failed', "send-email" ) . '</p></div>';
		}
	}
	else {
		echo "<b style='color:red;'>Please enter all fields</b>";
	}
		
	echo '
	<h2>' . __( "Send Email" ) . '</h2>
	
	<h3>' . __( "Current mail settings", "send-email" ) . '</h3>
	<p>' . __( "SendMail path (UNIX):", "send-email" ) . ' ' . ini_get("sendmail_path") . '</p>
	<p>' . __( "SMTP server (Windows):", "send-email" ) . ' ' . ini_get("SMTP") . '</p>
	<p>' . __( "SMTP port (Windows):", "send-email" ) . ' ' . ini_get("smtp_port") . '</p>
	<p>' . __( "Add X header:", "send-email" ) . ' ' . ini_get("mail.add_x_header") . '</p>
	
	<h3>' . __( "Send an email", "send-email" ) . '</h3>
	<form action="tools.php?page=sendemail" method="post">
	<p><label for="sendemail_to">' . __( "Send email to:", "send-email" ) . '</label>
	<input type="text" name="sendemail_to" id="sendemail_to" class="text"';
		if ( isset( $_POST["sendemail_to"] ) ) {
			echo ' value="' . esc_attr( $_POST["sendemail_to"] ) . '"';
		}
		echo ' /></p>
		
	<p><label for="sendemail_subject">' . __( "Subject:", "send-email" ) . '</label>
	<input type="text" name="sendemail_subject" id="sendemail_subject" class="text"';
		if ( isset( $_POST["sendemail_subject"] ) ) {
			echo ' value="' . esc_attr( $_POST["sendemail_subject"] ) . '"';
		}
		echo ' /></p>		
	<p><label for="sendemail_message">' . __( "Message:", "send-email" ) . '</label>
	<input type="text" name="sendemail_message" id="sendemail_message" class="text"';
		if ( isset( $_POST["sendemail_message"] ) ) {
			echo ' value="' . esc_attr( $_POST["sendemail_message"] ) . '"';
		}
		echo ' /></p>		
		
		
	<p class="hide"><label for="sendemail_autoheaders">' . __( "Use standard headers", "send-email" ) . '</label>
	<input class="hide" type="radio" id="sendemail_autoheaders" name="sendemail_headers" value="auto"';
	if ( !isset($_POST["sendemail_headers"]) || $_POST["sendemail_headers"] == "auto" ){
		echo ' checked="checked"';
	}
	echo '	/></p>
	<pre id="autoheaders"';
	if ( isset($_POST["sendemail_headers"]) && $_POST["sendemail_headers"] == "custom" ){
		echo ' class="sendemail-hide"';
	}
	echo '>MIME-Version: 1.0
From: ' . $from_email . '
Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . '"</pre>
	<p class="hide"><label class="hide" for="sendemail_customheaders">' . __( "Use custom headers", "send-email" ) . '</label>
	<input class="hide" type="radio" id="sendemail_customheaders" name="sendemail_headers" value="custom"';
	if ( isset($_POST["sendemail_headers"]) && $_POST["sendemail_headers"] == "custom" ){
		echo ' checked="checked"';
	}
	echo '	/></p>
	<div id="customheaders"';
	if ( !isset($_POST["sendemail_headers"]) || $_POST["sendemail_headers"] == "auto" ){
		echo ' class="sendemail-hide"';
	}
	echo '>
		<p>' . __( "Set your custom headers below", "send-email" ) . '</p>
		<p><label for="sendemail_mime">' . __( "MIME Version", "send-email" ) . '</label>
		<input type="text" name="sendemail_mime" id="sendemail_mime" value="';
		if ( isset( $_POST["sendemail_mime"] ) ) {
			echo esc_attr( $_POST["sendemail_mime"] );
		} else {
			echo '1.0';
		}
		echo '" /></p>
		<p><label for="sendemail_type">' . __( "Content type", "send-email" ) . '</label>
		<input type="text" name="sendemail_type" id="sendemail_type" value="';
		if ( isset( $_POST["sendemail_type"] ) ) {
			echo esc_attr( $_POST["sendemail_type"] );
		} else {
			echo 'text/html; charset=iso-8859-1';
		}
		echo '" class="text"  /></p>
		<p><label for="sendemail_from">' . __( "From", "send-email" ) . '</label>
		<input type="text" name="sendemail_from" id="sendemail_from" value="';
		if ( isset( $_POST["sendemail_from"] ) ) {
			echo esc_attr( $_POST["sendemail_from"] );
		} else {
			echo $from_email;
		}
		echo '" class="text"  /></p>
		<p><label for="sendemail_cc">' . __( "CC", "send-email" ) . '</label>
		<textarea name="sendemail_cc" id="sendemail_cc" cols="30" rows="4" class="text">';
		if ( isset( $_POST["sendemail_cc"] ) ) {
			echo esc_textarea( $_POST["sendemail_cc"] );
		}
		echo '</textarea></p>
		<p><label for="sendemail_break_n">' . __( "Header line break type", "send-email" ) . '</label>
		<input type="radio" name="sendemail_break" id="sendemail_break_n" value="\n"';
		if ( !isset( $_POST["sendemail_break"] ) || $_POST["sendemail_break"] == '\n' ) {
			echo ' checked="checked"';
		}
		echo ' /> \n
		<input type="radio" name="sendemail_break" id="sendemail_break_rn" value="\r\n"';
		if ( isset( $_POST["sendemail_break"] ) && $_POST["sendemail_break"] == '\r\n' ) {
			echo ' checked="checked"';
		}
		echo ' /> \r\n</p>';

		
		
		
	echo '</div>
	<p><label for="sendemail_go" class="sendemail-hide">' . __( "Send", "send-email" ) . '</label>
	<input type="submit" name="sendemail_go" id="sendemail_go" class="button-primary" value="' . __( "Send email", "send-email" ) . '" /></p>
	<br><p>To bulk send emails in once, buy <a href="https://sitefreelancing.com/product/send-emails/">Pro version</a>.
	';
	wp_nonce_field( 'sendemail' );
	echo '</form>
	
	</div>
	';
		
}

// send an email
function sendemail_send($to, $subject, $message, $headers = "auto") {
	global $current_user;

	$from_email = apply_filters( 'wp_mail_from', $current_user->user_email );
	$from_name = apply_filters( 'wp_mail_from_name', $from_name );

	if ( $headers == "auto" ) {
		$headers = "MIME-Version: 1.0\r\n" .
		"From: " . $from_email . "\r\n" .
		"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n";
	} else {
		$break = chr( 10 );
		if ( stripslashes( $_POST["sendemail_break"] ) == '\r\n' ) {
			$break = chr( 13 ) . chr( 10 );
		}
		if ( isset( $_POST["sendemail_mime"]) && $_POST["sendemail_mime"] != "" && isset( $_POST["sendemail_from"]) && $_POST["sendemail_from"] != "" && isset( $_POST["sendemail_cc"]) && $_POST["sendemail_cc"] != "" && isset( $_POST["sendemail_type"]) && $_POST["sendemail_type"] != ""  ) {
			$mime_type = sanitize_mime_type($_POST["sendemail_mime"]);
			$email_from = sanitize_email( $_POST["sendemail_from"] );
			$email1_cc = sanitize_email( $_POST["sendemail_cc"] );
			$content = sanitize_text_field( $_POST["sendemail_type"] );
			
			$headers = "MIME-Version: " . trim( $mime_type ) . $break .
			"From: " . trim( $email_from ) . $break .
			"Cc: " . trim( $email1_cc ) . $break .
			"Content-Type: " . trim( $content ) . $break;
		}
		else {
			$headers = '';
		}
	}
	wp_mail( $to, $subject, $message, $headers );
	return $headers;
}