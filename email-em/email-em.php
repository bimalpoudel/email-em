<?php
/**
 * Plugin Name: Email-Em
 * Description: Allows to share the link of a page
 * Plugin URI: http://bimal.org.np/
 * Author URI: http://bimal.org.np/
 * Author: Bimal Poudel
 * Version: 1.0.0
 */
show_admin_bar(false);
add_filter('the_content', 'email_em', 20);
function email_em($content='')
{
	#if(!is_single()) return $content;
	if(!is_singular('page')) return $content;
	
	$user = wp_get_current_user();
	if(!$user->exists()) return $content;

	$html = '
	<form name="email-em" method="post" action="?" autocomplete="off" style="padding: 20px; border: 3px dashed gray;">
		<input type="email" name="sendto" value="" placeholder="email address">
		'.wp_nonce_field('email-em').'
		<input type="submit" name="email-em" value="Email-Em this URL">
	</form>
	';
	if(!empty($_POST['email-em']) && !empty($_POST['sendto']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'email-em'))
	{
		global $wp;
		
		$subject = "Link Shared at: ".get_site_url();

		$message = "
Hi,
I would like to share a document with you.

Document: ".wp_get_document_title()."
Please click on: ".home_url(add_query_arg(array(), $wp->request))."

This may be a private document.
Please do NOT save the link.

Thank you.
{$user->display_name}
";

		$headers = null;
		$headers = "From: {$user->display_name} <{$user->user_email}>\r\n";
		
		$attachments = null;

		wp_mail($_POST['sendto'], $subject, $message, $headers, $attachments);
		$html = "<h2>Link Sent to: {$_POST['sendto']}</h2><pre>{$message}</pre>";
		
		/**
		 * @todo Handle page reload
		 */
	}
	
	return $html.$content;
}
