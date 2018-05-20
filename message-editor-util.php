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
        if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN
            || $toaccount['level'] >= QA_USER_LEVEL_ADMIN) {
            return true;
        }
        if ( !qa_opt('allow_private_messages') || !is_array($toaccount)
          || ((!($toaccount['flags'] & !QA_USER_FLAGS_NO_MESSAGES)
          || !(qa_get_logged_in_flags() & !QA_USER_FLAGS_NO_MESSAGES))
          && !self::follow_each_other($loginuserid, $toaccount['userid'])) ) {
            return false;
        } else {
            return true;
        }
    }
}
