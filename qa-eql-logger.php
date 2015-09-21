<?php

/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Expandable Question Lists Plugin by Bruno Vandekerkhove © 2014
	
*/

class qa_eql_logger {
	
	// Intercept events
	function process_event($event, $userid, $handle, $cookieid, $params) {
		
		// Process relevant events, add them to the database
		switch ($event) {
				
			/* QUESTION EVENTS */
			case 'q_requeue':
			case 'q_close':
			case 'q_hide':
				qa_db_query_sub("DELETE FROM ^homepage WHERE `questionid` = # LIMIT 1", $params['postid']);
				break;
			case 'q_post':
			case 'q_reopen':
			case 'q_reshow':
			case 'q_approve':
				$this->resync_question($params['postid'], true);
				break;
				
			/* ANSWER EVENTS */
			case 'a_post':
			case 'a_hide':
			case 'a_reshow':
			case 'a_requeue':
			case 'a_approve':
				$this->resync_question($params['parentid'], false);
				break;
				
			default:
				break;
				
		}
					
	}
	
	// Convert a value to text
	function value_to_text($value) {
		if (is_array($value))
			$text='array('.count($value).')';
		elseif (strlen($value)>40)
			$text=substr($value, 0, 38).'...';
		else
			$text=$value;
		return strtr($text, "\t\n\r", '   ');
	}
	
	// Resync a post in the database to reflect changes in the ^posts table
	function resync_question($postid,$voidparent) {
		if ($voidparent)
			qa_db_query_sub('REPLACE INTO ^homepage SELECT postid, NULL, created FROM ^posts WHERE type = "Q" AND postid = # LIMIT 1', $postid);
		else
			qa_db_query_sub('REPLACE INTO ^homepage SELECT postid, parentid, created FROM ^posts WHERE type = "Q" AND postid = # LIMIT 1', $postid);
		$query = '			SELECT
								question.postid,
								Answers.postid AS answerid,
								Answers.date AS answerid
							FROM ^posts question
							INNER JOIN
							(
								SELECT 
									parentid,
									postid,
									MAX(created) AS date
								FROM ^posts
								WHERE `type` = "A"
								GROUP BY postid
							) AS Answers ON Answers.parentid = question.postid
							WHERE question.type = "Q" AND question.postid = #';
		qa_db_query_sub('REPLACE INTO ^homepage '.$query, $postid);
	}
	
}
	

/*
	Omit PHP closing tag to help avoid accidental output
*/