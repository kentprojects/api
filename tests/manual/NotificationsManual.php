<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * A manual test (surprise surprise) is one that must be run by hand!
 */
require_once __DIR__ . "/../../functions.php";

$user = Model_User::getById(4);
Notification::queue("user_got_a_notification", $user, array(), array("user/" . $user->getId()));