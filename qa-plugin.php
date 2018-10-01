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

//Define global constants
@define( 'MESSAGE_EDITOR_DIR', dirname( __FILE__ ) );
@define( 'MESSAGE_EDITOR_FOLDER', basename( dirname( __FILE__ ) ) );
@define( 'MESSAGE_EDITOR_RELATIVE_PATH', '../qa-plugin/'.MESSAGE_EDITOR_FOLDER.'/');
@define( 'MEDIUM_EDITOR_DIR', '../qa-plugin/q2a-medium-editor/');

// language file
qa_register_plugin_phrases('qa-message-editor-lang-*.php', 'message_editor');
// admin
qa_register_plugin_module('module', 'qa-message-editor-admin.php', 'qa_message_editor_admin', 'Message Editor');
// override
qa_register_plugin_overrides('qa-message-editor-overrides.php');
