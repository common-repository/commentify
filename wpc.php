<?php
header('Content-type: application/json; charset=utf-8');
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-config.php');

if($commentify = get_option('commentify'))		$tokenWP = $commentify['token'];
else											$tokenWP = get_option('commentify_token');

if($_GET['token'])	$token = $_GET['token'];
if($_POST['token'])	$token = $_POST['token'] ;

$token = explode('.', base64_decode($token));

// sprawdź token
if($token[1] == $tokenWP || $token[1] == $tokenWP)
	{
	// pobierz komentarze
	if($_GET['akc'] == "getComments")
		{
		$lastID		= $_GET['lastID'];
		$getStatus	= $_GET['getStatus'];
		$statusOwn	= $_GET['statusOwn'];

		// początek składni JSON
		$jsonC			= '{';

		if(!empty($lastID) && $lastID != 'null')
			{
			
			$args = array('order' => 'DESC', 'status' => $getStatus);

			$licznik		= 0;
			$komentarze		= get_comments($args);
			$ile_komentarzy	= count($komentarze);

			function clean($clean)
				{
				$clean = trim($clean);
				$clean = ereg_replace("(\t)+", "\t", $clean);
				$clean = ereg_replace("(\r\n)+", " ", $clean);
				$clean = ereg_replace("(\n)+", " ", $clean);
				$clean = str_replace(array("'", "’", "`"), "&#8217;", $clean);
                $clean = str_replace(array('„', '”'), '"', $clean);
				$clean = addslashes($clean);

				return $clean;
				}
			
			foreach($komentarze as $c) :
				if($c->comment_ID <= $_GET['lastID']) break;

				if($statusOwn == "1" || ($statusOwn == "0" && $c->user_id != $token[0]))
					{
					$p	= get_post($c->comment_post_ID);
					$jsonC .= '"'.$c->comment_ID.'" : 
						{
						"ID"			: "'.$c->comment_ID.'",
						"status"		: "'.$c->comment_approved .'",
						"date"			: "'.$c->comment_date.'",
						"author"		: "'.clean($c->comment_author).'",
						"author_email"	: "'.$c->comment_author_email.'",
						"author_url"	: "'.$c->comment_author_url.'",
						"content"		: "'.clean($c->comment_content).'",
						"type"			: "'.$c->comment_type.'",
						"parent"		: "'.$c->comment_parent.'",
						"user_ID"		: "'.$c->user_id.'",
						"post_ID"		: "'.$c->comment_post_ID.'",
						"post_url"		: "'.get_permalink($c->comment_post_ID).'",
						"post"			: "'.clean($p->post_title).'"
						},';
					$licznik++;
					}
			endforeach;
		
			if($licznik>0)	$jsonC = substr($jsonC, 0, -1);
			}
		else
			{
			$args = array('order' => 'DESC', 'number' => '1');

			$licznik		= 0;
			$komentarze		= get_comments($args);
			$ile_komentarzy	= count($komentarze);

			foreach($komentarze as $c) :

				$p = get_post($c->comment_post_ID);
				$jsonC .= '"'.$c->comment_ID.'" : 
					{
					"ID" : "'.$c->comment_ID.'"
					}';
				$licznik++;
			endforeach;
			}
		
		// koniec składni JSON
		echo $jsonC.'}';
		}
	
	if($_GET['akc'] == "changeStatus")
		{
		$commentID	= $_GET['commentID'];
		$newStatus	= $_GET['newStatus']; // 'hold', 'approve', 'spam', 'trash'

		if(wp_set_comment_status($commentID, $newStatus))	$status = $newStatus;

		echo json_encode(array("status" => $status));
		}

	// dodaj komentarz
	if($_POST['akc'] == "saveRespond")
		{
		$postID		= $_POST['postID'];
		$parentID	= $_POST['parentID'];
		$content	= $_POST['content'];
		$userID		= $token[0];
		$user		= get_userdata($userID);
		$time		= current_time('mysql');
		$userIP		= $_SERVER['REMOTE_ADDR'];

		$data = array(
			'comment_post_ID'			=> $postID,
			'comment_author'			=> $user->display_name,
			'comment_author_email'		=> $user->user_email,
			'comment_author_url'		=> $user->user_url,
			'comment_content'			=> $content,
			'comment_type'				=> '',
			'comment_parent'			=> $parentID,
			'user_id'					=> $userID,
			'comment_author_IP'			=> $userIP,
			'comment_agent'				=> 'WordPress Commentify',
			'comment_date'				=> $time,
			'comment_approved'			=> 1,
			);
		
		wp_insert_comment($data);
		wp_update_comment_count($comment_post_ID);
		}
	}
?>