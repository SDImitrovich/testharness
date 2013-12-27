<?php
// include shared code
include '../lib/common.php';
include '../lib/functions.php';
include '../lib/Forum.php';

// this form operates in two modes:
// 1. when it is first invoked, it simply displays itself
// 2. when it is invoked via POST from itself, the incoming values need to be processed and saved

$forum = new Forum();
$errMsg = null;

// if the "submitted" is set, then the form is POST-ed
// process incoming values and save a new forum if they are all correct
if (isset($_POST['submitted']) )
{
	$forum->name = trimmed_or_empty('POST', 'name');
	$forum->description = trimmed_or_empty('POST', 'description');
	// ensure all the fields validate and that the name is unique
	if ($forum->validate() ) 
	{
		$f = Forum::getByName($forum->name);
		// if no forum with this name found - this is truly a new forum -> save
		if ( ! isset($f) ) 
		{ 
			$forum->save(); 
			// redirect user to list of forums after new record has been stored
			header('Location: view.php');		
		}
		// else display error and provide link to the existing forum with this name
		else 
		{ 
			$errMsg = sprintf('Forum with this name already exists.  ' .
				' Please change the name or go <a href="view.php?fid=%d">here</a> to view this forum.',
				$f->id);
		}
	}
	else { $errMsg = 'Please make sure both Name and Description are set'; }
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
<form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>"
 method="post">
 <div>
  <label for="name">Forum Name:</label>
  <input type="input" id="name" name="name" 
	value="<?php echo htmlspecialchars($forum->name); ?>"/><br/>
  <label for="description">Description:</label>
  <input type="input" id="description" name="description" 
	value="<?php echo htmlspecialchars($forum->description); ?>"/><br/>
  <input type="hidden" name="submitted" value="true"/>
  <input type="submit" value="Create"/>
 </div>
</form>
<?php
$GLOBALS['TEMPLATE']['content'] = ob_get_clean();

// display the page
include '../templates/template-page.php';
?>

