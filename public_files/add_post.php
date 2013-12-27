<?php
// include shared code
include '../lib/common.php';
include '../lib/functions.php';
include '../lib/Forum.php';
include '../lib/Message.php';

// this form operates in two modes:
// 1. when it is first invoked, it simply displays itself
// 2. when it is invoked via POST from itself, the incoming values need to be processed and saved

// When operating in mode #1, this page accepts on of the two URL parameters:
// mid (message ID) - when set, the new message will be a post (child) of the specified message
// fid (forum ID) - when set, the new message will be a top-most message (topic) of the specified forum
// IMPORTANT: if forum ID and message ID conflict, message ID wins as it is a more specific value
// IMPORTANT: at least one parameter must be set because a message must be either a topic or a post

$parentMessage = null;
$parentForum = null;
$message = new Message();
$errMsg = null;
$title = '';

if (isset($_POST['submitted']) )
// we're in Mode #2
{
	$redirectUrl = 'view.php';
	$message->subject = trimmed_or_empty('POST', 'subject');
	$message->messageText = trimmed_or_empty('POST', 'msgText');
	// when in this mode, hidden inputs containing either parent message id or parent forum id
	// should have been embedded in the form - retrieve them and set the variables
	$parentMessage = Message::getById(int_or_zero('post', 'msgId') );	
	$parentForum = Forum::getById(int_or_zero('post', 'forumId') ); 
	// if the parent message is set, we'll inherit its forum
	if (isset($parentMessage)  )
	{  
		$message->parentId = $parentMessage->id;
		$message->forumId = $parentMessage->forumId;
		$redirectUrl .= ('?mid=' . $parentMessage->id);
	}
	// otherwise, we'll retrieve the forum id from POST and consider this new message a new topic
	else if (isset($parentForum) )
	{
		$message->parentId = null;
		$message->forumId = $parentForum->id;
		$redirectUrl .= ('?fid=' . $parentForum->id);
	}
	else 
	{ 
		$errMsg = 'Something\'s fishy... neither forum nor parent message id were set correctly. ' .
			' Please go to the <a href="view.php">list of forums</a> ' .
			' to select a forum or a thread that you want to contribute to.';
	}	
	// if no error yet - validate and save
	if (! isset($errMsg) )
	{
		// ensure all the fields validate
		if ($message->validate() ) 
		{
				$message->save(); 
				// redirect user to either the thread or forum view				
				header('Location: ' . $redirectUrl);		
		}
		else { $errMsg = 'Please make sure both Subject and Text are set'; }
	}
}
else
// we're in Mode #1
{
	$parentMessage = Message::getById(int_or_zero('get', 'mid') );
	$parentForum = Forum::getById(int_or_zero('get', 'fid') );

	// if the message id points to the existing message - we're adding a post to an existing thread
	if (isset($parentMessage) )
	{
		// we're on Mode #1 - display the form, slightly customized as "Add Post"
		$title = $parentMessage->subject . ' - Add New Post';
	}
	// if the forum id points to the existing forum - we're adding a new thread to this forum
	else if (isset($parentForum) )
	{
		// we're on Mode #1 - display the form, slightly customized as "Add Thread"
		$title = $parentForum->name . ' - Add New Thread';
	}
	else 
	{ 
		$errMsg = 'Something\'s fishy... neither forum nor parent message id were set correctly. ' .
			' Please go to the <a href="view.php">list of forums</a> ' .
			' to select a forum or a thread that you want to contribute to.';
	}
}

// generate the form
ob_start();

echo '<p>GET:</p>';
var_dump($_GET);
echo '<br/><p>POST:</p>';
var_dump($_POST);

if (isset($errMsg))
{
    echo sprintf('<p id="error">%s</p>', $errMsg);
}
?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>">
 <div>
  <label for="subject">Subject:</label>
  <input type="input" id="subject" name="subject" value="<?php
   echo htmlspecialchars($message->subject); ?>"/><br/>
  <label for="msgText">Post:</label>
  <textarea id="msgText" name="msgText"><?php
   echo htmlspecialchars($message->messageText); ?></textarea>
  <br/>
  <input type="hidden" name="submitted" value="1"/>
<?php
	if (isset($parentMessage) ) 
	{ 
		echo sprintf('<input type="hidden" name="msgId" id="msgId" value="%d"/>', $parentMessage->id);
	}
	else if (isset($parentForum) )
	{
		echo sprintf('<input type="hidden" name="forumId" id="forumId" value="%d"/>', $parentForum->id);
	}
?>  
  <input type="submit" value="Create"/>
 </div>
</form>
<?php

$GLOBALS['TEMPLATE']['title'] = htmlspecialchars($title);
$GLOBALS['TEMPLATE']['content'] = ob_get_clean(); 

// display the page
include '../templates/template-page.php';
?>
