<?php

/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Expandable Question Lists Plugin by Bruno Vandekerkhove © 2015
	
*/

class qa_eql_admin {

	// Default admin options
	function option_default($option) {
	    switch($option) {
			case 'qa_eql_enabled':
			case 'qa_eql_homepage':
			case 'qa_eql_button':
			case 'qa_eql_bcomments':
			case 'qa_eql_bedits':
			case 'qa_homepage_separate':
				return false;
			case 'qa_eql_include':
			case 'qa_eql_banswers':
			    return true;
			case 'qa_homepage_url':
				return 'home';
			default:
			    return null;				
	    }
	}
	
	// Initialise queries
	function init_queries( $tableslc ) {
		$tbl1 = qa_db_add_table_prefix('homepage');
		if ( in_array($tbl1, $tableslc)) {
			return null;
		}
		return "CREATE TABLE IF NOT EXISTS ^homepage (
						`questionid` int(10) unsigned NOT NULL,
						`childid` int(10) unsigned,
						`updated` datetime NOT NULL,
						PRIMARY KEY (questionid)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
	}
       
    // Allow template
	function allow_template($template) {
		return ($template!='admin');
	}       
		
	// Create admin form
	function admin_form(&$qa_content) {                       
				
		// Prepare errors
		$pagelength_error = '';
						
		// Process form input
		$ok = null;
		if (qa_clicked('qa_eql_save')) {
		
			// Save options
			qa_opt('qa_eql_enabled',(bool)qa_post_text('qa_eql_enabled'));
			qa_opt('qa_eql_homepage',(bool)qa_post_text('qa_eql_homepage'));
			qa_opt('qa_eql_button',(bool)qa_post_text('qa_eql_button'));
			qa_opt('qa_eql_include',(bool)qa_post_text('qa_eql_include'));
			qa_opt('qa_homepage_url',qa_post_text('qa_homepage_url'));
			qa_opt('qa_homepage_separate',(bool)qa_post_text('qa_homepage_separate'));
			$ok = qa_lang('admin/options_saved');
			
		}
		else if (qa_clicked('qa_eql_reset')) {
		
			// Reset options
			qa_opt('qa_eql_enabled',$this->option_default('qa_eql_enabled'));
			qa_opt('qa_eql_homepage',$this->option_default('qa_eql_homepage'));
			qa_opt('qa_eql_button',$this->option_default('qa_eql_button'));
			qa_opt('qa_eql_include',$this->option_default('qa_eql_include'));
			qa_opt('qa_homepage_url',$this->option_default('qa_homepage_url'));
			qa_opt('qa_homepage_separate',$this->option_default('qa_homepage_separate'));
			$ok = qa_lang('admin/options_reset');
			
		}
		
		// Sync database (if admin wants so or if it is appropriate given the new settings)
		if (qa_clicked('qa_eql_calchomepage')) {
			$this->sync();
			$ok = 'Database sync\'d!';
		}
                    
        // Create the form for display
		$fields = array();
		$fields[] = array(	'label' => 'Make question links expandable','tags' => 'name="qa_eql_enabled" onchange="if (this.checked) $(\'.eql-checks\').show();else $(\'.eql-checks\').hide();"',
							'value' => qa_opt('qa_eql_enabled'),'type' => 'checkbox',);
		$fields[] = array(	'type' => 'custom',
							'label' => '<span class="qa-form-tall-label eql-checks"'.(qa_opt('qa_eql_enabled') ? '' : ' style="display:none;"').'>
											<h3>Additional options</h3>
											<label><input name="qa_eql_include" type="checkbox" class="qa-form-tall-checkbox"'.(qa_opt('qa_eql_include') ? ' checked=""' : '').'>Include jquery.ias.min.js</label>
											<small>(Uncheck this if you already use Infinite AJAX Scroll on this website).</small>
											<br><br><br>
											<label><input name="qa_eql_homepage" type="checkbox" onchange="if (this.checked) $(\'.eql-hchecks\').show();else $(\'.eql-hchecks\').hide();"
													class="qa-form-tall-checkbox"'.(qa_opt('qa_eql_homepage') ? ' checked=""' : '').'>Make the homepage expandable too</label>
											<br><br>
											<span class="qa-form-tall-label eql-hchecks"'.(qa_opt('qa_eql_enabled') && qa_opt('qa_eql_homepage') ? '' : ' style="display:none;"').'>
												<h3>Homepage options</h3>
												<small>Use the button below to keep the homepage in sync (this may take a while on large fora). 
												If this is  the first time you make the homepage expandable, you\'ll have to sync first.</small><br><br>
												<label><input name="qa_homepage_separate" type="checkbox" class="qa-form-tall-checkbox"'.(qa_opt('qa_homepage_separate') ? ' checked=""' : '').'>Use a custom homepage</label>
												<br><br>
												Custom homepage URL : <input name="qa_homepage_url" type="text" class="qa-form-tall-text" value="'.qa_opt('qa_homepage_url').'" onclick="this.select();">
											</span>
										</span>',);
		return array(           
			'ok' => ($ok && !isset($error)) ? $ok : null,
			'fields' => $fields,
			'buttons' => array(
				array('label' => 'Save', 'tags' => 'NAME="qa_eql_save"',),
				array('label' => 'Sync homepage', 'tags' => 'NAME="qa_eql_calchomepage"',),
				array('label' => 'Reset', 'tags' => 'NAME="qa_eql_reset"',),
			),
		);
		
	}
	
	// Calculate the homepage by adding all relevant posts to the ^homepage table
	function sync() {
		qa_db_query_sub('REPLACE INTO ^homepage SELECT postid, NULL, created FROM ^posts WHERE type = "Q"'); // First log all the questions
		$query = '	SELECT
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
					WHERE question.type = "Q"';
		qa_db_query_sub('REPLACE INTO ^homepage '.$query); // Now the answers (more queries are necessary if you want to include comments, too)
	}
	
}

