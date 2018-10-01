<?php

class qa_message_editor_admin
{
    public function init_queries($tableslc)
    {
        return;
    }
    
    public function option_default($option)
    {
        switch ($option) {
            default:
                return;
        }
    }

    public function allow_template($template)
    {
        return $template !== 'admin';
    }

    public function admin_form(&$qa_content)
    {
        // process the admin form if admin hit Save-Changes-button
        $ok = null;
        if (qa_clicked('qa_message_editor_save')) {
            qa_opt('message_editor_no_post_html', qa_post_text('message_editor_no_post_html'));
            $ok = qa_lang('admin/options_saved');
        }

        // form fields to display frontend for admin
        $fields = array();

        $fields[] = array(
            'label' => qa_lang('message_editor/no_post_html'),
            'tags' => 'NAME="message_editor_no_post_html"',
            'value' => qa_opt('message_editor_no_post_html'),
            'type' => 'textarea',
            'rows' => 5,
        );

        return array(
            'ok' => ($ok && !isset($error)) ? $ok : null,
            'fields' => $fields,
            'buttons' => array(
                array(
                    'label' => qa_lang_html('main/save_button'),
                    'tags' => 'name="qa_message_editor_save"',
                ),
            ),
        );
    }
}
