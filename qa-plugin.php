<?php

/*
	Plugin Name: Expandable Question Lists
	Plugin URI: http://brunovandekerkhove.com
	Plugin Description: Makes question lists expandable. 
	Plugin Version: 1.0
	Plugin Date: 2015-01-27
	Plugin Author: Bruno Vandekerkhove
	Plugin Author URI: http://brunovandekerkhove.com
	Plugin License: none
	Plugin Minimum Question2Answer Version: 1.6
*/

if ( !defined('QA_VERSION') ) {
	header('Location: ../../');
	exit;
}

qa_register_plugin_module('page', 'qa-eql-homepage.php', 'qa_expandable_homepage', 'Expandable Homepage');
qa_register_plugin_layer('qa-eql-layer.php', 'Expandable Question Lists Layer');
qa_register_plugin_module('module', 'qa-eql-admin.php', 'qa_eql_admin', 'Expandable Question Lists Admin');
qa_register_plugin_module('event', 'qa-eql-logger.php', 'qa_eql_logger', 'Expandable Question Lists Logger');
qa_register_plugin_overrides('qa-eql-overrides.php');