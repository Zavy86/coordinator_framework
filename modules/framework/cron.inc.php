<?php
/**
 * Framework - Cron
 *
 * @package Coordinator\Modules\Framework
 * @author  Manuel Zavatta <manuel.zavatta@gmail.com>
 * @link    http://www.coordinator.it
 */
 // definitions
 $logs=array();
 // delete expired sessions
 $deleted_sessions_start=$GLOBALS['database']->queryExecute("DELETE FROM `framework__sessions` WHERE `startTimestamp`<'".(time()-36000)."'");
 $deleted_sessions_last=$GLOBALS['database']->queryExecute("DELETE FROM `framework__sessions` WHERE `lastTimestamp`<'".(time()-$GLOBALS['settings']->sessions_idle_timeout)."'");
 // log
 $logs[]="Expired sessions deleted (".intval($deleted_sessions_start+$deleted_sessions_last).")";
 // send mails
 $processed_mails=api_mail_processAll();
 // log
 $logs[]="Mails processed (".intval($processed_mails).")";
 // debug
 api_dump($logs,"framework");
?>