<?php

    if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
        header('Location: ../');
        exit;
    }

    require_once QA_INCLUDE_DIR.'db/selects.php';
    require_once QA_INCLUDE_DIR.'app/users.php';
    require_once QA_INCLUDE_DIR.'app/format.php';
    require_once QA_INCLUDE_DIR.'app/limits.php';

    $handle = qa_request_part(1);
    $loginuserid = qa_get_logged_in_userid();
    $fromhandle = qa_get_logged_in_handle();

    $qa_content = qa_content_prepare();


//    Check we have a handle, we're not using Q2A's single-sign on integration and that we're logged in

    if (QA_FINAL_EXTERNAL_USERS)
        qa_fatal_error('User accounts are handled by external code');

    if (!strlen($handle))
        qa_redirect('users');

    if (!isset($loginuserid)) {
        $qa_content['error'] = qa_insert_login_links(qa_lang_html('misc/message_must_login'), qa_request());
        return $qa_content;
    }

    if ($handle === $fromhandle) {
        // prevent users sending messages to themselves
        $qa_content['error'] = qa_lang_html('users/no_permission');
        return $qa_content;
    }


//    Find the user profile and their recent private messages

    list($toaccount, $torecent, $fromrecent) = qa_db_select_with_pending(
        qa_db_user_account_selectspec($handle, false),
        qa_db_recent_messages_selectspec($loginuserid, true, $handle, false),
        qa_db_recent_messages_selectspec($handle, false, $loginuserid, true)
    );


//    Check the user exists and work out what can and can't be set (if not using single sign-on)

    if ( !qa_opt('allow_private_messages') || !is_array($toaccount) || ($toaccount['flags'] & QA_USER_FLAGS_NO_MESSAGES) )
        return include QA_INCLUDE_DIR.'qa-page-not-found.php';


//    Check that we have permission and haven't reached the limit, but don't quit just yet

    switch (qa_user_permit_error(null, QA_LIMIT_MESSAGES)) {
        case 'limit':
            $pageerror = qa_lang_html('misc/message_limit');
            break;

        case false:
            break;

        default:
            $pageerror = qa_lang_html('users/no_permission');
            break;
    }


//    Process sending a message to user

    // check for messages or errors
    $state = qa_get_state();
    $messagesent = $state == 'message-sent';
    if ($state == 'email-error')
        $pageerror = qa_lang_html('main/email_error');

    if (qa_post_text('domessage')) {
        
        qa_get_post_content('editor', 'content', $in['editor'], $in['content'], $in['format'], $in['text']);
        
        $inmessage = strip_spaces($in['content']);
        
        if (isset($pageerror)) {
            // not permitted to post, so quit here
            $qa_content['error'] = $pageerror;
            return $qa_content;
        }

        if ( !qa_check_form_security_code('message-'.$handle, qa_post_text('code')) )
            $pageerror = qa_lang_html('misc/form_security_again');

        else {
            if (empty($inmessage))
                $errors['message'] = qa_lang('misc/message_empty');

            if (empty($errors)) {
                require_once QA_INCLUDE_DIR.'db/messages.php';
                require_once QA_INCLUDE_DIR.'app/emails.php';

                if (qa_opt('show_message_history'))
                    $messageid = qa_db_message_create($loginuserid, $toaccount['userid'], $inmessage, 'html', false);
                else
                    $messageid = null;

                $canreply = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);

                $more = strtr(qa_lang($canreply ? 'emails/private_message_reply' : 'emails/private_message_info'), array(
                    '^f_handle' => $fromhandle,
                    '^url' => qa_path_absolute($canreply ? ('message/'.$fromhandle) : ('user/'.$fromhandle)),
                ));

                $subs = array(
                    '^message' => $inmessage,
                    '^f_handle' => $fromhandle,
                    '^f_url' => qa_path_absolute('user/'.$fromhandle),
                    '^more' => $more,
                    '^a_url' => qa_path_absolute('account'),
                );

                $messagesent = true;

                qa_report_event('u_message', $loginuserid, qa_get_logged_in_handle(), qa_cookie_get(), array(
                    'userid' => $toaccount['userid'],
                    'handle' => $toaccount['handle'],
                    'messageid' => $messageid,
                    'message' => $inmessage,
                ));

                // show message as part of general history
                if (qa_opt('show_message_history'))
                    qa_redirect(qa_request(), array('state' => ($messagesent ? 'message-sent' : 'email-error')));
            }
        }
    }


//    Prepare content for theme

    $hideForm = !empty($pageerror) || $messagesent;

    $qa_content['title'] = qa_lang_html('misc/private_message_title');

    $qa_content['error'] = @$pageerror;
    
    $editorname=isset($in['editor']) ? $in['editor'] : qa_opt('editor_for_qs');
    $editor=qa_load_editor(@$in['content'], @$in['format'], $editorname);

    $field=qa_editor_load_field($editor, $qa_content, @$in['content'], @$in['format'], 'content', 12, false);
    $field['label']=qa_lang_html_sub('misc/message_for_x', qa_get_one_user_html($handle, false));
    $field['error']=qa_html(@$errors['content']);
    
    $qa_content['form_message'] = array(
        'tags' => 'method="post" action="'.qa_self_html().'"',

        'style' => 'tall',

        'ok' => $messagesent ? qa_lang_html('misc/message_sent') : null,

        'fields' => array(
            'message' => $field,
        ),

        'buttons' => array(
            'send' => array(
                'tags' => 'onclick="qa_show_waiting_after(this, false); '. (method_exists($editor, 'update_script') ? $editor->update_script('content') : '') . '"',
                'label' => qa_lang_html('main/send_button'),
            ),
        ),

        'hidden' => array(
            'domessage' => '1',
            'code' => qa_get_form_security_code('message-'.$handle),
        ),
    );

    $qa_content['focusid'] = 'message';

    if ($hideForm) {
        unset($qa_content['form_message']['buttons']);

        if (qa_opt('show_message_history'))
            unset($qa_content['form_message']['fields']['message']);
        else {
            unset($qa_content['form_message']['fields']['message']['note']);
            unset($qa_content['form_message']['fields']['message']['label']);
        }
    }


//    If relevant, show recent message history

    if (qa_opt('show_message_history')) {
        $recent = array_merge($torecent, $fromrecent);

        qa_sort_by($recent, 'created');

        $showmessages = array_slice(array_reverse($recent, true), 0, QA_DB_RETRIEVE_MESSAGES);

        if (count($showmessages)) {
            $qa_content['message_list'] = array(
                'title' => qa_lang_html_sub('misc/message_recent_history', qa_html($toaccount['handle'])),
            );

            $options = qa_message_html_defaults();

            foreach ($showmessages as $message)
                $qa_content['message_list']['messages'][] = qa_message_html_fields($message, $options);
        }

        $qa_content['navigation']['sub'] = qa_messages_sub_navigation();
    }


    $qa_content['raw']['account'] = $toaccount; // for plugin layers to access

    return $qa_content;


    function strip_spaces($content)
    {
        
        $pat = '/<p class=""><br><\/p>/i';
        $pat2 = '/<p class="">(&nbsp;\s?)*<\/p>/i';

        $result = preg_replace($pat, "", $content);
        $result2 = preg_replace($pat2, "", $result);
        
        if (empty($result2)) {
            $return_content = "";
        } else {
            $return_content = $content;
        }
        return $return_content;
    }
/*
    Omit PHP closing tag to help avoid accidental output
*/
