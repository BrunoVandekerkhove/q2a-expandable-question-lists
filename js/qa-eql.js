/*

	Question2Answer (c) Gideon Greenspan
	http://www.question2answer.org/
	
	Expandable Question Lists Plugin by Bruno Vandekerkhove © 2014
		
*/

// Parameters below can be altered, they're self-explanatory (note that 'QA_PLUGINDIR' is replaced in the layer code)
$(function(){
	if($(".qa-q-list").length && $(".qa-page-links-list").length) {
		$.ias({
			container: ".qa-q-list"
			,item: ".qa-q-list-item"
			,pagination: ".qa-page-links-list"
			,next: ".qa-page-next"
			,loader: '<br><br><center><img class="qa-eql-loader" src="QA_PLUGINDIRimages/loader.gif"/></center>'
			,noneleft: '<div class="qa-eql-noneleft"><br><br><center><small>No more threads</small></center></div>'
			,loaderDelay: 600
			,triggerPageThreshold: 5
			,trigger: '<div class="qa-eql-noneleft"><br><br><center><small>Load more posts</small></center></div>'
			,thresholdMargin: 0
			,history: 1
			,scrollContainer: $(window)
		});
	}
});