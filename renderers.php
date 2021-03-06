<?php

class theme_epict_core_renderer extends theme_bootstrapbase_core_renderer {

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">►</span>';
        $list_items = '<li>'.join("$divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }
    
    /**
     * Return the standard string that says whether you are logged in (and switched
     * roles/logged in as another user).
     * @param bool $withlinks if false, then don't include any links in the HTML produced.
     * If not set, the default is the nologinlinks option from the theme config.php file,
     * and if that is not set, then links are included.
     * @return string HTML fragment.
     */
    public function login_info($withlinks = null) {
     global $USER, $CFG, $DB, $SESSION;
    
     if (during_initial_install()) {
      return '';
     }
    
     if (is_null($withlinks)) {
      $withlinks = empty($this->page->layout_options['nologinlinks']);
     }
    
     $loginpage = ((string)$this->page->url === epict_get_login_url());
     $course = $this->page->course;
     if (session_is_loggedinas()) {
      $realuser = session_get_realuser();
      $fullname = fullname($realuser, true);
      if ($withlinks) {
       $loginastitle = get_string('loginas');
       $realuserinfo = " [<a href=\"$CFG->wwwroot/course/loginas.php?id=$course->id&amp;sesskey=".sesskey()."\"";
       $realuserinfo .= "title =\"".$loginastitle."\">$fullname</a>] ";
      } else {
       $realuserinfo = " [$fullname] ";
      }
     } else {
      $realuserinfo = '';
     }
    
     $loginurl = epict_get_login_url();
    
     if (empty($course->id)) {
      // $course->id is not defined during installation
      return '';
     } else if (isloggedin()) {
      $context = context_course::instance($course->id);
    
      $fullname = fullname($USER, true);
      // Since Moodle 2.0 this link always goes to the public profile page (not the course profile page)
      if ($withlinks) {
       $linktitle = get_string('viewprofile');
       $username = "<a href=\"$CFG->wwwroot/user/profile.php?id=$USER->id\" title=\"$linktitle\">$fullname</a>";
      } else {
       $username = $fullname;
      }
      if (is_mnet_remote_user($USER) and $idprovider = $DB->get_record('mnet_host', array('id'=>$USER->mnethostid))) {
       if ($withlinks) {
        $username .= " from <a href=\"{$idprovider->wwwroot}\">{$idprovider->name}</a>";
       } else {
        $username .= " from {$idprovider->name}";
       }
      }
      if (isguestuser()) {
       $loggedinas = $realuserinfo.get_string('loggedinasguest');
       if (!$loginpage && $withlinks) {
        $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
       }
      } else if (is_role_switched($course->id)) { // Has switched roles
       $rolename = '';
       if ($role = $DB->get_record('role', array('id'=>$USER->access['rsw'][$context->path]))) {
        $rolename = ': '.role_get_name($role, $context);
       }
       $loggedinas = get_string('loggedinas', 'moodle', $username).$rolename;
       if ($withlinks) {
        $url = new moodle_url('/course/switchrole.php', array('id'=>$course->id,'sesskey'=>sesskey(), 'switchrole'=>0, 'returnurl'=>$this->page->url->out_as_local_url(false)));
        $loggedinas .= '('.html_writer::tag('a', get_string('switchrolereturn'), array('href'=>$url)).')';
       }
      } else {
       $loggedinas = $realuserinfo.get_string('loggedinas', 'moodle', $username);
       if ($withlinks) {
        $loggedinas .= " (<a href=\"$CFG->wwwroot/login/logout.php?sesskey=".sesskey()."\">".get_string('logout').'</a>)';
       }
      }
     } else {
      $loggedinas = get_string('loggedinnot', 'moodle');
      if (!$loginpage && $withlinks) {
       $loggedinas .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
      }
     }
    
     $loggedinas = '<div class="logininfo">'.$loggedinas.'</div>';
    
     if (isset($SESSION->justloggedin)) {
      unset($SESSION->justloggedin);
      if (!empty($CFG->displayloginfailures)) {
       if (!isguestuser()) {
        if ($count = count_login_failures($CFG->displayloginfailures, $USER->username, $USER->lastlogin)) {
         $loggedinas .= '&nbsp;<div class="loginfailures">';
         if (empty($count->accounts)) {
          $loggedinas .= get_string('failedloginattempts', '', $count);
         } else {
          $loggedinas .= get_string('failedloginattemptsall', '', $count);
         }
         if (file_exists("$CFG->dirroot/report/log/index.php") and has_capability('report/log:view', context_system::instance())) {
          $loggedinas .= ' (<a href="'.$CFG->wwwroot.'/report/log/index.php'.
            '?chooselog=1&amp;id=1&amp;modid=site_errors">'.get_string('logs').'</a>)';
         }
         $loggedinas .= '</div>';
        }
       }
      }
     }
    
     return $loggedinas;
    }
	
}