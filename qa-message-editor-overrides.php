<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../../');
    exit;
}

function qa_page_routing()
{
    $routing = qa_page_routing_base();
    
    $routing['message/'] = QA_HTML_THEME_LAYER_URLTOROOT.'/message.php';
    
    return $routing;
}
