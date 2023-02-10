<?php

require_once MESSAGE_EDITOR_DIR.'/message-editor-util.php';

class qa_html_theme_layer extends qa_html_theme_base
{


    public function body_content()
    {
	// フォームを出力しなくても未入力のエラーメッセージが出るので非表示にする

    	qa_html_theme_base::body_content();
	if($this->content['no_follow']){
		$this->output('<style>#content-error {display:none !important}</style>');
	}
    }
}