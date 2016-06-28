<?php
/**
 * Plugin Name: Email-Em
 * Description: Allows to share the link of a page
 * Plugin URI: http://bimal.org.np/
 * Author URI: http://bimal.org.np/
 * Author: Bimal Poudel
 * Version: 1.0.0
 */
add_filter('the_content', 'email_em', 20);
function email_em($content='')
{
	#if(!is_single()) return $content;
	if(!is_singular('page')) return $content;
	
	$user = wp_get_current_user();

	/**
	 * Allowed for logged in users only
	 */
	if(!$user->exists()) return $content;

	/**
	 * Use must have a valid email
	 */
	if(!is_email($user->user_email)) return $content;

	$html = '
	<form name="email-em" method="post" action="?" autocomplete="off" style="padding: 20px; border: 3px dashed gray;">
		<input type="email" name="sendto" value="" placeholder="email address">
		'.wp_nonce_field('email-em').'
		<input type="submit" value="Email-Em this URL">
	</form>
	';
	if(!empty($_POST['sendto']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'email-em'))
	{
		/**
		 * Recipient's email should be valid
		 */
		$_POST['sendto'] = sanitize_text_field($_POST['sendto']);
		if(!is_email($_POST['sendto'])) return $content;
		
		global $wp;

		$subject = "Link shared at: ".get_site_url();

		$message = "
Hi,
I would like to share a document with you.

Document: ".wp_get_document_title()."
URL: ".home_url(add_query_arg(array(), $wp->request))."

This may be a private document.
Please do NOT save the link.
Please do NOT forward this email to anyone else.

Thank you.
{$user->display_name}
";
		$headers = implode("\r\n", array(
			"From: {$user->display_name} <{$user->user_email}>",
			"Reply-To: {$user->display_name} <{$user->user_email}>",
			"Content-Type: text/plain;charset=utf-8"
		));
		
		$attachments = null;

		wp_mail($_POST['sendto'], $subject, $message, $headers, $attachments);
		$html = "<h2>A link was sent to: {$_POST['sendto']}</h2><pre>{$message}</pre>";
		
		/**
		 * @todo Handle page reload; do not send the email again.
		 */
	}
	
	return $html.$content;
}
