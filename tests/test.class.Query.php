<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
require_once __DIR__ . "/functions.php";

$query = new Query("project_id", "Projects");
$query->where(array("field" => "status", "value" => 1));
$query->where(array(
	"field" => "year",
	"type" => "i",
	"value" => "2014"
));
$query->where(array(
	"field" => "project_id",
	"operator" => "IN",
	"type" => "i",
	"values" => array(
		1, 2, 3, 4, 5, 6, 7
	)
));
$query->where(array(
	"field" => "supervisor",
	"operator" => "OR",
	"type" => "i",
	"values" => array(
		1, 2, 3
	)
));
$query->where(array(
	"field" => "supervisor",
	"operator" => "OR",
	"values" => array(
		1, 2, 3
	)
));
stdout($query->execute(true));