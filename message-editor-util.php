<?php

class message_editor_util {

    /*
     * 相互フォローかチェック
     */
    public static function follow_each_other($loginuserid, $touserid)
    {
        $sql = "SELECT COUNT(*)";
        $sql.= " FROM ^userfavorites";
        $sql.= " WHERE entitytype = 'U'";
        $sql.= " AND userid = $";
        $sql.= " AND entityid = $";

        $following = qa_db_read_one_value(qa_db_query_sub($sql, $loginuserid, $touserid));

        $followed = qa_db_read_one_value(qa_db_query_sub($sql, $touserid, $loginuserid));

        return $following && $followed;
    }

    /*
     * 管理者は無条件に利用できる
     * 自分または相手のメッセージのオプションがONの場合
     * 相互フォローしていないと利用できない
     */
    public static function allow_message($loginuserid, $toaccount)
    { 
        // サイト設定でOFFの場合や送信先がおかしい場合はfalse返す(UIや文言との不整合が起こる)
        if(!qa_opt('allow_private_messages') || !is_array($toaccount)) {
            return false;
        }

        // 管理人は無条件OK 
        if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN
            || $toaccount['level'] >= QA_USER_LEVEL_ADMIN) {
            return true;
        }
        // 相互フォローも無条件OK
        if(self::follow_each_other($loginuserid, $toaccount['userid'])) {
             return true;
        }

        $to_ok = !($toaccount['flags'] & QA_USER_FLAGS_NO_MESSAGES);
        $me_ok = !(qa_get_logged_in_flags() & QA_USER_FLAGS_NO_MESSAGES);

        // 両方ともOkなら、送信可能
        if($to_ok && $me_ok) {
            return true;
        }

        return false;
    }
}

