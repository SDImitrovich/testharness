<?php
// include shared code
include '../lib/common.php';
include '../lib/functions.php';
include '../lib/Forum.php';
include '../lib/Message.php';

// start or continue session
session_start();

// this page accepts two optional URL parameters:
// mid (message ID) - when set, displays the list of top-most children of the specified message
// fid (forum ID) - when set, and provided message ID is not set, 
//		displays the list of top-most messages for the specified forum
// IMPORTANT: if forum ID and message ID conflict, message ID wins as it is a more specific value
// if neither is set - this page displays the list of forums

// start with message ID
$message = null;
$msgId = (isset($_GET['mid'])) ? (int)$_GET['mid'] : 0;
if ($msgId)
{
	$message = Message::getById($msgId);
}

// if the message isn't set or wasn't found, proceed to dealing with forum id
$forum = null;
$forumId = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;
if ($forumId)
{
	$forum = Forum::getById($forumId);
}


// buffer the output and determine the mode of this form based on whatever input we've got
ob_start();

echo '<p>GET:</p>';
var_dump($_GET);

$title = '';

// if message was set - we're in the "Thread View" mode
if (isset($message) )
{
	$title = $message->subject;
	// link back to thread view
	echo sprintf('<p><a href="view.php?fid=%d">Back to forum threads.</a></p>', $message->forumId);
	// if parent ID is set, link back to the parent thread
	if ($message->parentId)
	{
		echo sprintf('<p><a href="view.php?mid=%d">Back to parent message.</a></p>', $message->parentId);
	}
	// display option to add new post to the thread
	echo sprintf('<p><a href="add_post.php?mid=%d">Post new message.</a></p>', $message->id);
	// display messages
	$message->loadChildren();
	if (count($message->children) > 0) { display_messages($message->children); }
	else { echo '<p>This thread contains no replies.</p>'; }
}

// if the message was not set, but the forum was - we're in the "Forum View" mode
else if (isset($forum))
{
	$title = $forum->name;
	// link back to forum list
	echo '<p><a href="view.php">Back to forum list.</a></p>';
	// display option to add new thread to this forum
	echo sprintf('<p><a href="add_post.php?fid=%d">Create new thread.</a></p>', $forum->id);
	// display messages
	$forum->loadTopics();
	if (count($forum->topics) > 0) { display_messages($forum->topics); }
	else { echo '<p>This forum contains no messages.</p>'; }
}

// finally, if nothing was set, we're in the "List Forums" mode
else
{
	$title = 'Forums';
	// display link to create new forum
	echo '<p><a href="add_forum.php">Create new forum.</a></p>';
	// list all forums
	$forums = Forum::getAll();
    echo '<table width="50%">';
	echo '<th>Forum Name</th>';
	echo '<th>Forum Description</th>';
	echo '<th>Total Posts</th>';
	echo '<th>Latest Post</th>';
    foreach ($forums as $f) 
    {
		echo '<tr>';
		echo '<td width="25%" style="vertical-align:middle; padding:5px;">';
		echo sprintf('<a href="%s?fid=%d">%s</a>', 
			htmlspecialchars($_SERVER['PHP_SELF']), 
			$f->id,
			htmlspecialchars($f->name) );
		echo '</td>';
		echo '<td width="50%">' . htmlspecialchars($f->description) . '</td>';
		echo '<td style="vertical-align:middle; text-align:center; padding:5px;">' . $f->totalMsgCount . '</td>';
		if ($f->latestMsgDate)
		{
			$latestDate = date('Y-m-d H:i', $f->latestMsgDate);
			echo '<td width="15%" style="vertical-align:middle; padding:5px;">' . $latestDate . '</td>';
		}
		echo '</tr>';
    }
    echo '</table>';	
}

// list messages in an HTML form
function display_messages($messages)
{
    echo '<table>';
	foreach ($messages as $m)
    {
        echo '<tr>';
		$msgDate = date('Y-m-d<\b\r/>H:i', $m->messageDate);
        echo sprintf('<td style="vertical-align:middle; white-space:nowrap; padding:15px;">%s</td>', $msgDate); 
        echo '<td style="vertical-align:top;">';
		echo sprintf('<div><strong><a href="%s?mid=%d">%s</a></strong></div>', 
			htmlspecialchars($_SERVER['PHP_SELF']), 
			$m->id,
			htmlspecialchars($m->subject) );
        echo sprintf('<div>%s</div>', htmlspecialchars($m->messageText) );
        echo sprintf('<div style="text-align: right;"><a href="add_post.php?mid=%d">Reply</a></div>', $m->id);
        echo '</td></tr>';
    }
    echo '</table>';	
}

// an example of pagination
/*
	// accept the display offset
	$start = (isset($_GET['start']) && ctype_digit($_GET['start']) &&
		$_GET['start'] <= $total) ? $_GET['start'] : 0;

	// move the data pointer to the appropriate starting record
	mysql_data_seek($result, $start);

	// display 25 entries
	echo '<ul>';
	$count = 0;
	while ($count++ < 25 && $row = mysql_fetch_array($result))
	{
		echo '<li><a href="view.php?fid=' . $forum_id . '&mid=' . 
			$row['MESSAGE_ID'] . '">';
		echo date('m/d/Y', $row['MESSAGE_DATE']) . ': ';
		echo htmlspecialchars($row['SUBJECT']) . '</li>';
	}
	echo '</ul>';
	
	// Generate the paginiation menu.
	echo '<p>';
	if ($start > 0)
	{
		echo '<a href="view.php?fid=' . $forum_id . '&start=' .
			($start - 25) . '">&lt;PREV</a>';
	}
	if ($total > $start + 25)
	{
		echo '<a href="view.php?fid=' . $forum_id . '&start=' .
			($start + 25) . '">NEXT&gt;</a>';
	}
	echo '</p>';

*/

$GLOBALS['TEMPLATE']['title'] = htmlspecialchars($title);
$GLOBALS['TEMPLATE']['content'] = ob_get_clean(); 

// display the page
include '../templates/template-page.php';
?>
