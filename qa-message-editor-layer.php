<?php

class qa_html_theme_layer extends qa_html_theme_base
{
    const MEDIUM_EDITOR_DIR = './qa-plugin/q2a-medium-editor/';
    
    function head_script()
    {
        qa_html_theme_base::head_script();
        if($this->template === 'message') {
          $this->output_css();
          $this->output_js();
        }
    }
    
    private function output_css()
    {
        $components = MEDIUM_EDITOR_DIR . 'bower_components/';
        // CSS files
        $this->output('<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">');
        $css_files = array(
            'medium-editor/dist/css/medium-editor.min.css',
            'medium-editor/dist/css/themes/default.min.css',
            'medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css'
        );
        foreach ($css_files as $css) {
            $this->output('<link rel="stylesheet" type="text/css" href="' . $components . $css .'" />');
        }
        $this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'css/message.css" />');
        if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<link rel="stylesheet" type="text/css" href="'.MEDIUM_EDITOR_DIR.'css/dialog-polyfill.css" />');
        }
    }
    
    private function output_js()
    {
        $components = MEDIUM_EDITOR_DIR . 'bower_components/';
        // JS files
        $js_files = array(
            'medium-editor/dist/js/medium-editor.js',
            'handlebars/handlebars.runtime.min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'blueimp-file-upload/js/vendor/jquery.ui.widget.js',
            'blueimp-file-upload/js/jquery.iframe-transport.js',
            'blueimp-file-upload/js/jquery.fileupload.js',
            'medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js',
        );
        foreach ($js_files as $js) {
            $this->output('<script src="'. $components . $js . '"></script>');
        }
        $this->output('<script src="'. MEDIUM_EDITOR_DIR . 'js/q2a-embeds.js' . '"></script>');
        $this->output('<script src="'. MEDIUM_EDITOR_DIR . 'js/q2a-editor.js' . '"></script>');
        if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<script src="'. MEDIUM_EDITOR_DIR . 'js/q2a-images.js' . '"></script>');
            $this->output('<script src="'. MEDIUM_EDITOR_DIR . 'js/dialog-polyfill.js' . '"></script>');
        }
    }
    
    public function message_content($message)
    {
        if (!empty($message['content'])) {
            $message['content'] = $this->embed_replace($message['content']);
        }
        qa_html_theme_base::message_content($message);
    }
    
    function embed_replace($text)
    {
        $types = array(
            'youtube' => array(
                array(
                    'https{0,1}:\/\/w{0,3}\.*youtube\.com\/watch\?\S*v=([A-Za-z0-9_-]+)[^< ]*',
                    '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>'
                ),
                array(
                    'https{0,1}:\/\/w{0,3}\.*youtu\.be\/([A-Za-z0-9_-]+)[^< ]*',
                    '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>'
                ),
            ),
        );

        foreach($types as $t => $ra) {
            foreach($ra as $r) {
                $text = preg_replace('/<a[^>]+>'.$r[0].'<\/a>/i',$r[1],$text);
                $text = preg_replace('/(?<![\'"=])'.$r[0].'/i',$r[1],$text);
            }
        }
        $text = preg_replace('/class="plain_url"/i','class="video video-youtube"',$text);
        return $text;
    }
}
