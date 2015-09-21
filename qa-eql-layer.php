<?php

/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Expandable Question Lists Plugin by Bruno Vandekerkhove © 2014
	
*/

class qa_html_theme_layer extends qa_html_theme_base {

	/*
	
		OVERRIDE THEME FUNCTIONS
		
	*/	
	
	// Body tags
	public function body_tags() {
		$class = 'qa-template-qa';// qa-template-'.qa_html($this->template);
		if (isset($this->content['categoryids'])) {
			foreach ($this->content['categoryids'] as $categoryid)
				$class .= ' qa-category-'.qa_html($categoryid);
		}
		$this->output('class="'.$class.' qa-body-js-off"');
	}
	
	// Include the JS
	function head_custom() {
		parent::head_custom();
		if (qa_opt('qa_eql_enabled')) {
			$ias_call = file_get_contents(QA_HTML_THEME_LAYER_DIRECTORY.'js/qa-eql.js');
			$ias_call = str_replace('QA_PLUGINDIR', QA_HTML_THEME_LAYER_URLTOROOT, $ias_call);
			$this->output_raw('<script>'.$ias_call.'</script>');
			if (qa_opt('qa_eql_include'))
				$this->output_raw('<script type="text/javascript" src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/jquery-ias-min.js"></script>');
		}
	}
	
	// Remove page links label
	function page_links_label($label) {}

}
