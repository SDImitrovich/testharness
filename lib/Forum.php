<?php

require_once 'Entity.php';

class Forum extends Entity
{
    // initialize a User object
    public function __construct()
    {
		parent::__construct();
    }

	protected function initFields()
	{
        $this->fields = array('name' => '',
                              'description' => '',
							  'totalMsgCount' => null,
							  'latestMsgDate' => null,
							  'topics' => null);
    }

	
    // return true if the name is in valid format, 
	// false otherwise
    public static function validateName($name)
    {
        return preg_match('/^[A-Za-z0-9 ]{2,50}$/', $name);
    }

	    
    // return an array listing all available forums
	// in descending order by total message count
	// returns empty array if no forums found
    public static function getAll()
    {
        $forums = array();

        $query = 'select f.ID, f.NAME, f.DESCRIPTION, ' . 
				' COUNT(m.ID) as MESSAGE_COUNT, ' .
				' UNIX_TIMESTAMP(MIN(m.MESSAGE_DATE)) as LATEST_DATE ' .
				' from FORUM f ' .
				' left join MESSAGE m ' .
				' on f.ID = m.FORUM_ID ' .
				' group by f.ID, f.NAME, f.DESCRIPTION ' .
				' order by COUNT(m.ID) desc';           
        $result = mysql_query($query, $GLOBALS['DB']);
		
        if (mysql_num_rows($result))
        {
			while ($row = mysql_fetch_assoc($result))
			{	
				$f = new Forum();
				Forum::setValuesFromDB($f, $row);
				// add the question to the list, using its id as a key
				array_push($forums, $f);
			}
		}
		mysql_free_result($result);
        return $forums;		
    }

    
    // return an object populated based on the record's id
	// returns NULL if forum with the specified id was not found
    public static function getById($id)
    {
        $forum = null;

        $query = 'select f.ID, f.NAME, f.DESCRIPTION, ' . 
				' COUNT(m.ID) as MESSAGE_COUNT, ' .
				' UNIX_TIMESTAMP(MIN(m.MESSAGE_DATE)) as LATEST_DATE ' .
				' from FORUM f ' .
				' left join MESSAGE m ' .
				' on f.ID = m.FORUM_ID ' .
				' where f.ID = '. $id .
				' group by f.ID, f.NAME, f.DESCRIPTION ' .
				' order by COUNT(m.ID) desc';           
        $result = mysql_query($query, $GLOBALS['DB']);

        if (mysql_num_rows($result) && $row = mysql_fetch_assoc($result))
        {
			$forum = new Forum();
			Forum::setValuesFromDB($forum, $row);
		}
        mysql_free_result($result);

        return $forum;
    }

    // return an object populated based on the record's name (should be unique)
	// returns NULL if forum with the specified name was not found
    public static function getByName($name)
    {
        $forum = null;

        $query = sprintf('select f.ID, f.NAME, f.DESCRIPTION, ' . 
				' COUNT(m.ID) as MESSAGE_COUNT, ' .
				' UNIX_TIMESTAMP(MIN(m.MESSAGE_DATE)) as LATEST_DATE ' .
				' from FORUM f ' .
				' left join MESSAGE m ' .
				' on f.ID = m.FORUM_ID ' .
				' where f.NAME = "%s" '. 
				' group by f.ID, f.NAME, f.DESCRIPTION ' .
				' order by COUNT(m.ID) desc',
				mysql_real_escape_string($name, $GLOBALS['DB']));
        $result = mysql_query($query, $GLOBALS['DB']);

        if (mysql_num_rows($result) && $row = mysql_fetch_assoc($result))
        {
			$forum = new Forum();
			Forum::setValuesFromDB($forum, $row);
		}
        mysql_free_result($result);
        return $forum;
    }
	
	private static function setValuesFromDB($forum, $row)
	{
		$forum->id = $row['ID'];
		$forum->name = $row['NAME'];
		$forum->description = $row['DESCRIPTION'];
		$forum->totalMsgCount = $row['MESSAGE_COUNT'];
		$forum->latestMsgDate = $row['LATEST_DATE'];
	}
	
	// loads the top-most messages (a.k.a. topics) for this forum, NON-recursively
	public function loadTopics()
	{
		$this->topics = Message::getByForumId($this->id);
	}

	// validates the internal values
	// returns TRUE if validation passes, FALSE otherwise
	// requirements:
	// -> name should not be empty
	// -> description should not be empty
	public function validate()
	{
		return Forum::validateName($this->name) &&
			   is_not_empty_string($this->description);
	}
	
    // save the record to the database
    public function save()
    {
        if ($this->id)
        {
            $query = sprintf('UPDATE FORUM SET NAME = "%s", DESCRIPTION = "%s" WHERE ID = %d',
                mysql_real_escape_string($this->name, $GLOBALS['DB']),
                mysql_real_escape_string($this->description, $GLOBALS['DB']),
                $this->id);
//			echo sprintf("Forum->save(): query='%s'", $query);
            mysql_query($query, $GLOBALS['DB']);
        }
        else
        {
            $query = sprintf('INSERT INTO FORUM (NAME, DESCRIPTION) VALUES ("%s", "%s")',
                mysql_real_escape_string($this->name, $GLOBALS['DB']),
                mysql_real_escape_string($this->description, $GLOBALS['DB']));
//			echo sprintf("Forum->save(): query='%s'", $query);
            mysql_query($query, $GLOBALS['DB']);

            $this->id = mysql_insert_id($GLOBALS['DB']);
        }
    }
}
?>
