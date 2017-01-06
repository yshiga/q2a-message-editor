<?php

/*
	Plugin Name: Message Editor Plugin
	Plugin URI:
	Plugin Description: Change message page editor to Medium Editor
	Plugin Version: 1.0
	Plugin Date: 2017-01-06
	Plugin Author: 38qa.net
	Plugin Author URI: http://38qa.net/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

// layer
qa_register_plugin_layer('qa-message-editor-layer.php','Message Editor Layer');
// override
qa_register_plugin_overrides('qa-message-editor-overrides.php');
