<?php
/*
Plugin Name: Commentify
Plugin URI: http://commentify.info
Description: WordPress Commentify token generator
Version: 1.2.0
Author: Łukasz Więcek
Author URI: http://mydiy.pl
*/
$commentify = get_option('commentify');

function commentifyMenu()	{add_options_page('Commentify', 'Commentify', 7, __FILE__, 'commentify');}
function commentify()
	{
	global $commentify;
	$user		= wp_get_current_user();
	$user_ID	= $user->ID;
	$adres		= WP_PLUGIN_URL.'/commentify/wpc.php';

	// generowanie tablicy z ustawieniami
	if(!get_option('commentify'))
		{
		if(get_option('commentify_token'))	$token = get_option('commentify_token');
		else								$token = substr(md5(microtime(true)), 0, 13);

		// zapisane wartości domyślnych
		$commentify = array(
			'token'		=> $token,
			'display'	=> 'yes',
			'where'		=> 'author',
			'text'		=> 'via %commentify%'
			);
		add_option('commentify', $commentify);
		}
	
	if($_POST['commentifySave'])
		{
		// zapisanie ustawień
		$commentify = array(
			'token'		=> $commentify['token'],
			'display'	=> $_POST['commentify_display_via'],
			'where'		=> $_POST['commentify_where_via'],
			'text'		=> $_POST['commentify_text_via']
			);

		update_option('commentify', $commentify);
		}
	
	$token64 = base64_encode($user_ID.'.'.$commentify['token']);
	?>
	<div id="commentifyBox">
		<p>Install the <a href="https://chrome.google.com/webstore/detail/cpkabmcjgjigdkgkkmahpgkccagmajhp">WordPress Commentify</a> plugin on your blog and configure it by copying the data shown below in the appropriate fields.</p>

		<table>
			<tr><th><?php _e('Addres:', 'commentify') ?></th><td><?php echo $adres ?></td></tr>
			<tr><th><?php _e('Token:', 'commentify') ?></th><td><?php echo $token64 ?></td></tr>
		</table>
		
		<p>If you want to help us develop this extension, let your readers know that you reply to their comments using WordPress Commentify.</p>
		
		<form action="options-general.php?page=commentify/commentify.php" method="post" id="commentify">
			<p style="margin-left: 20px;">
				<input type="checkbox" name="commentify_display_via" id="commentify_display_via" value="yes"<?php if($commentify['display'] == 'yes') echo ' checked'; ?> />
				<label for="commentify_display_via"> Add to my comments </label>
				
				<select name="commentify_where_via" id="commentify_where_via">
					<option value="author"<?php if($commentify['where'] == 'author') echo ' selected="selected"'; ?>>next to my name</option>
					<option value="comment"<?php if($commentify['where'] == 'comment') echo ' selected="selected"'; ?>>at the end</option>
				</select>
				
				<label for="commentify_text_via"> this text: </label>
				
				<input type="text" name="commentify_text_via" id="commentify_text_via" style="width: 300px;" value="<?php echo $commentify['text']; ?>" />
				<input type="submit" name="commentifySave" value="save" />
			</p>

			<p style="margin-left: 20px;">%commentify% will be automatically changed into a link to WordPress Commentify. Remember that you can always switch off this option and stop displaying information about using this extension :)</p>
			<p style="margin-left: 20px;">If you would like to change the style of this text, simply add a new rule to your CSS for <span style="text-decoration: underline;">.commentify</span></p>
		</form>
	</div>

	<?php
	}

add_action('admin_head', 'commentifyAdminCSS');
function commentifyAdminCSS()
	{
	global $ss_name, $ss_dir;

	echo '<link rel="stylesheet" media="screen" type="text/css" href="'.WP_PLUGIN_URL.'/commentify/css/admin.css" />';
	}

add_action('admin_menu','commentifyMenu');

//	COMMENTIFY AUTHOR
if($commentify['display'] == 'yes')
	{
	if($commentify['where'] == 'author')	add_filter('get_comment_author_link', 'commentify_author');
	if($commentify['where'] == 'comment')	add_filter('get_comment_text', 'commentify_comment');
	}

function commentify_author($c)
	{
	global $comment, $commentify;
	$text = str_replace('%commentify%', '<a href="http://commentify.info" rel="external nofollow">WordPress Commentify</a>', $commentify['text']);
	if($comment->comment_agent == 'WordPress Commentify') $c .= '<span class="commentify"> '.$text.'</span>';
	return $c;
	}

function commentify_comment($c)
	{
	global $comment, $commentify;
	$text = str_replace('%commentify%', '<a href="http://commentify.info" rel="external nofollow">WordPress Commentify</a>', $commentify['text']);
	if($comment->comment_agent == 'WordPress Commentify') $c .= '</p><p class="commentify"> '.$text;
	return $c;
	}
?>