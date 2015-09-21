<?php
	
	function qa_request() {
	
		$base = qa_request_base();
		
		// Direct homepage requests to custom page (if the homepage isn't explicitely set as separate)
		if (($base == '' || $base == 'qa') && qa_opt('qa_eql_enabled') && qa_opt('qa_eql_homepage') && !qa_opt('qa_homepage_separate'))
			return qa_opt('qa_homepage_url');
			
		return $base;
		
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

