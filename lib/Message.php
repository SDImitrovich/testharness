<?php
require_once 'Entity.php';

class Message extends Entity
{
    // initialize a User object
    public function __construct()
    {
		parent::__construct();
    }

	protected function initFields()
	{
        $this->fields = array('forumId' => 0,
							  'parentId' => null,
                              'subject' => null,
							  'messageText' => null,
							  'messageDate' => null,
							  'children' => null);
    }

    
    // return an object populated based on the record's id
	// or NULL if none found
    public static function getById($id)
    {
        $message = null;

        $query = sprintf('SELECT ID, PARENT_ID, FORUM_ID, SUBJECT, MESSAGE_TEXT, ' .
						' UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE ' . 
						' FROM MESSAGE WHERE ID = %d ', 
						$id);
        $result = mysql_query($query, $GLOBALS['DB']);

        if (mysql_num_rows($result) && $row = mysql_fetch_assoc($result))
        {
			$message = new Message();
			Message::setValuesFromDB($message, $row);
        }
        mysql_free_result($result);

        return $message;
    }

    // return an array of top-most messages associated with the specified Forum ID
	// in descending order by message date
	// returns empty array if no messages found
    public static function getByForumId($forumId)
    {
        $query = sprintf('SELECT ID, PARENT_ID, FORUM_ID, SUBJECT, MESSAGE_TEXT, ' .
						' UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE ' . 
						' FROM MESSAGE WHERE FORUM_ID = %d AND PARENT_ID IS NULL ' .
						' ORDER BY MESSAGE_DATE DESC', 
						$forumId);
		return Message::getListBasedOnQuery($query);		
	}

    // return an array of messages immediately associated with the specified parent message
	// in descending order by message date
	// this function is NOT recursive
	// returns empty array if no messages found
    public static function getByParentId($parentId)
    {
        $query = sprintf('SELECT ID, PARENT_ID, FORUM_ID, SUBJECT, MESSAGE_TEXT, ' .
						' UNIX_TIMESTAMP(MESSAGE_DATE) AS MESSAGE_DATE ' . 
						' FROM MESSAGE WHERE PARENT_ID = %d ' .
						' ORDER BY MESSAGE_DATE DESC', 
						$parentId);
		return Message::getListBasedOnQuery($query);		
	}
	
	// executes specified query and returns the result set
	private static function getListBasedOnQuery($query)
	{
        $messages = array();

        $result = mysql_query($query, $GLOBALS['DB']);		
        if (mysql_num_rows($result))
        {
			while ($row = mysql_fetch_assoc($result))
			{	
				$m = new Message();
				Message::setValuesFromDB($m, $row);
				array_push($messages, $m);				
			}
		}
		mysql_free_result($result);
        return $messages;
	}
	
	private static function setValuesFromDB($message, $row)
	{
		$message->id = $row['ID'];
		$message->forumId = $row['FORUM_ID'];
		$message->parentId = $row['PARENT_ID'];
		$message->subject = $row['SUBJECT'];
		$message->messageText = $row['MESSAGE_TEXT'];
		$message->messageDate = $row['MESSAGE_DATE'];
	}
	
	// loads the immediate children of this message, NON-recursively
	public function loadChildren()
	{
		$this->children = Message::getByParentId($this->id);
	}
	
	// validates the internal values
	// returns TRUE if validation passes, FALSE otherwise
	// requirements:
	// -> forum ID must be set
	// -> subject must not be empty
	// -> message text must not be empty
	public function validate()
	{
		return ($this->forumId > 0) &&
				is_not_empty_string($this->subject) &&
				is_not_empty_string($this->messageText);
	}

    // save the record to the database
    public function save()
    {
		// if the ID is set - this is an update
        if ($this->id)
        {
            $query = sprintf('UPDATE MESSAGE SET FORUM_ID = %d,' .
				' PARENT_ID = %s,' . // dealing with possible NULL value here, so setting it as %s rather than %d
				' SUBJECT = "%s",' .
				' MESSAGE_TEXT = "%s" ' .
				// we will not set message date explicitly - the database will set it
                ' WHERE ID = %d' ,
				$this->forumId,
				($this->parentId) ? $this->parentId : 'NULL',				
                mysql_real_escape_string($this->subject, $GLOBALS['DB']),
                mysql_real_escape_string($this->messageText, $GLOBALS['DB']),
                $this->id);
//			echo sprintf("Message.save(): query='%s'", $query);
            mysql_query($query, $GLOBALS['DB']);
        }
		// otherwise - it's an insert
        else
        {
            $query = sprintf('INSERT INTO MESSAGE (FORUM_ID, PARENT_ID, SUBJECT, MESSAGE_TEXT) VALUES (%d, %s, "%s", "%s")',
                $this->forumId,
				($this->parentId) ? $this->parentId : 'NULL',
                mysql_real_escape_string($this->subject, $GLOBALS['DB']),
                mysql_real_escape_string($this->messageText, $GLOBALS['DB']));
//			echo sprintf("Message.save(): query='%s'", $query);
            mysql_query($query, $GLOBALS['DB']);

            $this->id = mysql_insert_id($GLOBALS['DB']);
		}
    }
}
?>
