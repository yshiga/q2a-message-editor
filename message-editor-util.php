<?php

class message_editor_util {
    public static function follow_each_other($loginuserid, $touserid)
    {
        $sql = "SELECT COUNT(*)";
        $sql.= " FROM ^userfavorites";
        $sql.= " WHERE entitytype = 'U'";
        $sql.= " AND userid = $";
        $sql.= " AND entityid = $";

        $following = qa_db_read_one_value(qa_db_query_sub($sql, $loginuserid, $touserid));

        $followed = qa_db_read_one_value(qa_db_query_sub($sql, $touserid, $loginuserid));

        var_export($loginuserid);
        var_export($touserid);
        var_export($following);
        var_export($followed);
        return $following && $followed;
    }
}