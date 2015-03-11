<?php

//require_once('config.php'); // WARNING! Pay attention to the file location when handling multiple instances!
require_once($CFG->libdir . '/adminlib.php');

$oldname = 'dataform'; // OLD NAME of the module
$newname = 'datalynx'; // NEW NAME of the module (MUST be the same length!)

$dbman = $DB->get_manager();

// Rename tables and recreate foreign keys

$oldtable = new xmldb_table($oldname);
$dbman->rename_table($oldtable, $newname);

$tables = array("{$oldname}_views" => "{$newname}_views",
                "{$oldname}_entries" => "{$newname}_entries",
                "{$oldname}_fields" => "{$newname}_fields",
                "{$oldname}_filters" => "{$newname}_filters",
                "{$oldname}_rules" => "{$newname}_rules");
foreach ($tables as $oldtablename => $newtablename) {
    $oldtable = new xmldb_table($oldtablename);
    $dbman->rename_table($oldtable, $newtablename);
    $newtable = new xmldb_table($newtablename);
    $oldkey = new xmldb_key('dataid', XMLDB_KEY_FOREIGN, array('dataid'), $oldname, array('id'));
    $dbman->drop_key($newtable, $oldkey);
    $newkey = new xmldb_key('dataid', XMLDB_KEY_FOREIGN, array('dataid'), $newname, array('id'));
    $dbman->add_key($newtable, $newkey);
}

$oldtablename = "{$oldname}_contents";
$newtablename = "{$newname}_contents";
$oldtable = new xmldb_table($oldtablename);
$dbman->rename_table($oldtable, $newtablename);
$newtable = new xmldb_table($newtablename);
$oldkey = new xmldb_key('entryid', XMLDB_KEY_FOREIGN, array('entryid'), "{$oldname}_entries", array('id'));
$dbman->drop_key($newtable, $oldkey);
$newkey = new xmldb_key('entryid', XMLDB_KEY_FOREIGN, array('entryid'), "{$newname}_entries", array('id'));
$dbman->add_key($newtable, $newkey);
$oldkey = new xmldb_key('fieldid', XMLDB_KEY_FOREIGN, array('fieldid'), "{$oldname}_fields", array('id'));
$dbman->drop_key($newtable, $oldkey);
$newkey = new xmldb_key('fieldid', XMLDB_KEY_FOREIGN, array('fieldid'), "{$newname}_fields", array('id'));
$dbman->add_key($newtable, $newkey);

// Replace any string occurrences in the database

db_replace_hacked($oldname, $newname);
db_replace_hacked(ucfirst($oldname), ucfirst($newname));
db_replace_hacked(strtoupper($oldname), strtoupper($newname));

die;

function db_replace_hacked($search, $replace) {
    global $DB, $OUTPUT;

    // TODO: this is horrible hack, we should do whitelisting and each plugin should be responsible for proper replacing...
    $skiptables = array('upgrade_log', 'log', 'filter_config', 'sessions', 'events_queue', 'repository_instance_config', 'block_instances', '');

    // Turn off time limits, sometimes upgrades can be slow.
    @set_time_limit(0);

    if (!$tables = $DB->get_tables() ) {    // No tables yet at all.
        return false;
    }
    foreach ($tables as $table) {

        if (in_array($table, $skiptables)) {      // Don't process these
            continue;
        }

        if ($columns = $DB->get_columns($table)) {
            $DB->set_debug(true);
            foreach ($columns as $column => $data) {
                if (in_array($data->meta_type, array('C', 'X'))) {  // Text stuff only
                    //TODO: this should be definitively moved to DML driver to do the actual replace, this is not going to work for MSSQL and Oracle...
                    $DB->execute("UPDATE {".$table."} SET $column = REPLACE($column, ?, ?)", array($search, $replace));
                }
            }
            $DB->set_debug(false);
        }
    }

    // delete modinfo caches
    rebuild_course_cache(0, true);

    // TODO: we should ask all plugins to do the search&replace, for now let's do only blocks...
    $blocks = get_plugin_list('block');
    foreach ($blocks as $blockname=>$fullblock) {
        if ($blockname === 'NEWBLOCK') {   // Someone has unzipped the template, ignore it
            continue;
        }

        if (!is_readable($fullblock.'/lib.php')) {
            continue;
        }

        $function = 'block_'.$blockname.'_global_db_replace';
        include_once($fullblock.'/lib.php');
        if (!function_exists($function)) {
            continue;
        }

        echo $OUTPUT->notification("Replacing in $blockname blocks...", 'notifysuccess');
        $function($search, $replace);
        echo $OUTPUT->notification("...finished", 'notifysuccess');
    }

    return true;
}
