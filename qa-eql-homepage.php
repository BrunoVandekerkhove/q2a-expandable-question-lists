<?php

/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Expandable Question Lists by Bruno Vandekerkhove © 2015
	
*/

require_once QA_INCLUDE_DIR.'qa-app-format.php';

/*

	CUSTOM HOMEPAGE

*/
class qa_expandable_homepage {

	private $directory;
	private $urltoroot;

	// Load module
	public function load_module($directory, $urltoroot) {
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}
	
	// Suggest requests
	public function suggest_requests() {
		return array(
			array(
				'title' => 'Expandable Homepage',
				'request' => qa_opt('eql_homepage'),
				'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
			),
		);
	}

	// Check if given request matches
	public function match_request( $request ) {
		$call = explode('/', $request);
		return qa_opt('qa_eql_enabled') && qa_opt('qa_eql_homepage') && strtolower($call[0]) == qa_opt('qa_homepage_url');
	}
	
	// Process request
	public function process_request( $request ) {
		
		$requestparts=explode('/', qa_request());
		$slugs=array_slice($requestparts, 1);
		$countslugs=count($slugs);
		
		$userid=qa_get_logged_in_userid();
		
		$start = qa_get_start();
		$count = qa_opt_if_loaded('page_size_activity');
		$totalcount = qa_opt('cache_qcount');
		
		$qspec = qa_db_posts_basic_selectspec($userid, false);
		qa_db_add_selectspec_opost($qspec, 'ra', false, false);
		qa_db_add_selectspec_ousers($qspec, 'rau', 'raup');
		$qspec['source'].=" JOIN (SELECT questionid, childid FROM ^homepage ORDER BY ^homepage.updated DESC) AS rcaq ON ^posts.postid=rcaq.questionid".
							" LEFT JOIN ^posts AS ra ON childid=ra.postid".
							(QA_FINAL_EXTERNAL_USERS ? "" : " LEFT JOIN ^users AS rau ON ra.userid=rau.userid").
							" LEFT JOIN ^userpoints AS raup ON ra.userid=raup.userid LIMIT #,#";	
		array_push($qspec['columns'], 'childid');
		array_push($qspec['arguments'], $start, $count);
		$qspec['sortdesc']='otime';
		
		$query = 'SELECT ';
		foreach ($qspec['columns'] as $columnas => $columnfrom)
			$query .= $columnfrom . (is_int($columnas) ? '' : (' AS '.$columnas)) . ', ';
		$query = qa_db_apply_sub(
			substr($query, 0, -2).(strlen(@$qspec['source']) ? (' FROM '.$qspec['source']) : ''),
			@$qspec['arguments']);
			
		$results = qa_db_read_all_assoc(qa_db_query_raw($query));
		qa_db_post_select($results, $qspec);
		
		list($categories, $categoryid)=qa_db_select_with_pending(
			qa_db_category_nav_selectspec($slugs, false, false, true),
			$countslugs ? qa_db_slugs_to_category_id_selectspec($slugs) : null
		);

		$questions=qa_any_sort_and_dedupe($results);
		// $questions=qa_any_sort_and_dedupe(array_merge($recentquestions,$recentanswers));
		$pagesize=qa_opt('page_size_home');
	
		if ($countslugs) {
			if (!isset($categoryid))
				return include QA_INCLUDE_DIR.'qa-page-not-found.php';
	
			$categorytitlehtml=qa_html($categories[$categoryid]['title']);
			$sometitle=qa_lang_html_sub('main/recent_qs_as_in_x', $categorytitlehtml);
			$nonetitle=qa_lang_html_sub('main/no_questions_in_x', $categorytitlehtml);
	
		} else {
			$sometitle=qa_lang_html('main/recent_qs_as_title');
			$nonetitle=qa_lang_html('main/no_questions_found');
		}
		
		require_once QA_INCLUDE_DIR.'qa-app-q-list.php';
		$qa_content=qa_q_list_page_content(
			$questions, // questions
			$pagesize, // questions per page
			$start, // start offset
			$totalcount, // total count (null to hide page links)
			$sometitle, // title if some questions
			$nonetitle, // title if no questions
			$categories, // categories for navigation
			$categoryid, // selected category id
			true, // show question counts in category navigation
			qa_opt('eql_homepage_url'), // prefix for links in category navigation
			qa_opt('feed_for_qa') ? qa_opt('eql_homepage_url') : null, // prefix for RSS feed paths (null to hide)
			(count($questions)<$pagesize) // suggest what to do next
				? qa_html_suggest_ask($categoryid)
				: qa_html_suggest_qs_tags(qa_using_tags(), qa_category_path_request($categories, $categoryid)),
			null, // page link params
			null // category nav params
		);
		
		return $qa_content;
	
	}
	
	// Get handle from user ID
	function handleForID($id) {
		$result = qa_db_query_sub('SELECT * FROM ^users WHERE userid=# LIMIT 1',$id);
		if ($row = mysql_fetch_array($result)) {
			return $row['handle'];
		}
		return '';
	}
	
	// Parse elapsed time since given time (in seconds)
	function elapsedTime($timestamp){
		$html = qa_when_to_html($timestamp,qa_opt('show_full_date_days'));
		$time = '<span>';
		if (strlen(@$html['prefix']))
			$time .= '<span>'.$html['prefix'].'</span>';
		if (strlen(@$html['data']))
			$time .= '<span>'.$html['data'].'</span>';
		if (strlen(@$html['suffix']))
			$time .= '<span>'.$html['suffix'].'</span>';
		return $time.'</span>';
	}

}
