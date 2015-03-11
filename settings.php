<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle's epict theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_epict
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Invert Navbar to dark background.
    $name = 'theme_epict/invert';
    $title = get_string('invert', 'theme_epict');
    $description = get_string('invertdesc', 'theme_epict');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Logo file setting.
    $name = 'theme_epict/logo';
    $title = get_string('logo','theme_epict');
    $description = get_string('logodesc', 'theme_epict');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Custom CSS file.
    $name = 'theme_epict/customcss';
    $title = get_string('customcss', 'theme_epict');
    $description = get_string('customcssdesc', 'theme_epict');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Footnote setting.
    $name = 'theme_epict/footnote';
    $title = get_string('footnote', 'theme_epict');
    $description = get_string('footnotedesc', 'theme_epict');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
    
    // MNET Login settings: Default login url for mnet login
    $name = 'theme_epict/alternateloginurl';
    $title = get_string('alternateloginurl','theme_epict');
    $description = get_string('alternateloginurldesc', 'theme_epict');
    $default = 0;
    $sql = "SELECT DISTINCT h.id, h.wwwroot, h.name, a.sso_jump_url, a.name as application
			FROM {mnet_host} h
			JOIN {mnet_host2service} m ON h.id = m.hostid
			JOIN {mnet_service} s ON s.id = m.serviceid
			JOIN {mnet_application} a ON h.applicationid = a.id
			WHERE s.name = ? AND h.deleted = ? AND m.publish = ?";
    $params = array('sso_sp', 0, 1);
    
    if (!empty($CFG->mnet_all_hosts_id)) {
     $sql .= " AND h.id <> ?";
     $params[] = $CFG->mnet_all_hosts_id;
    }
    
    if ($hosts = $DB->get_records_sql($sql, $params)) {
     $choices = array();
     $choices[0] = 'notset';
     foreach ($hosts as $id => $host){
      $choices[$id] = $host->name;
     }
    } else {
     $choices = array();
     $choices[0] = 'notset';
    }
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);
}
