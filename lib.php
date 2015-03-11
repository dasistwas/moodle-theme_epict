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

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_epict_process_css($css, $theme) {

    // Set the background image for the logo.
    $logo = $theme->setting_file_url('logo', 'logo');
    $css = theme_epict_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_epict_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_epict_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_epict_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'logo') {
        $theme = theme_config::load('epict');
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_epict_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_epict_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    if (!empty($page->theme->settings->logo)) {
        $return->heading = html_writer::link($CFG->wwwroot, '', array('title' => get_string('home'), 'class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.$page->theme->settings->footnote.'</div>';
    }

    return $return;
}

/**
 * Deprecated: Please call theme_epict_process_css instead.
 * @deprecated since 2.5.1
 */
function epict_process_css($css, $theme) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_epict_process_css($css, $theme);
}

/**
 * Deprecated: Please call theme_epict_set_logo instead.
 * @deprecated since 2.5.1
 */
function epict_set_logo($css, $logo) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_epict_set_logo($css, $logo);
}

/**
 * Deprecated: Please call theme_epict_set_customcss instead.
 * @deprecated since 2.5.1
 */
function epict_set_customcss($css, $customcss) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_epict_set_customcss($css, $customcss);
}
/**
 * Returns MNET Login URL instead of standard login URL. Checks the wanted url
 * of user in order to provide correct redirect url for the identity provider
 *
 * @return string login url
 */
function epict_get_login_url(){
 global $PAGE, $DB, $SESSION, $CFG;
 if ($PAGE->url->out() === $CFG->wwwroot."/login/index.php"){
  $urltogo = $SESSION->wantsurl;
 } else {
  $urltogo = $PAGE->url->out();
 }
 $authplugin = get_auth_plugin('mnet');
 $authurl = $authplugin->loginpage_idp_list($urltogo);
 // check the id of the MNET host for the idp
 $host = $DB->get_field('mnet_host', 'name', array('id' => $PAGE->theme->settings->alternateloginurl));
 if(!empty($authurl)){
  foreach($authurl as $key => $urlarray){
   if($urlarray['name'] == $host){
    $loginurl = $authurl[$key]['url'];
    return $loginurl;
   } else {
    $loginurl = "$CFG->wwwroot/login/index.php";
    if (!empty($CFG->loginhttps)) {
     $loginurl = str_replace('http:', 'https:', $loginurl);
    }
   }
  }
 } else {
  $loginurl = "$CFG->wwwroot/login/index.php";
  if (!empty($CFG->loginhttps)) {
   $loginurl = str_replace('http:', 'https:', $loginurl);
  }
 }
 return $loginurl;
}
