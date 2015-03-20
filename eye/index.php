<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
require_once __DIR__ . "/../functions.php";
/**
 * @var Model_Application[] $applications
 */
$applications = array(
	Model_Application::getById(1),
	Model_Application::getById(2)
);
/**
 * @var Model_User[] $users
 */
$users = array(
	Model_User::getByEmail("J.C.Hernandez-Castro@kent.ac.uk"),
	Model_User::getByEmail("jsd24@kent.ac.uk"),
	Model_User::getByEmail("mh471@kent.ac.uk"),
	Model_User::getByEmail("supervisor2@kent.ac.uk")
);
/**
 * @var string
 */
$defaultHost = !empty($_SERVER["VAGRANT_ENV"]) ? "api.kentprojects.local" : "api.dev.kentprojects.com";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Kent AP Eye</title>
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<link href="http://dev.kentprojects.com//includes/img/kp.ico" rel="icon" type="image/x-icon" />
</head>
<body>
<div class="container">

	<div class="masthead">
		<h3 class="custom-h3">Kent AP Eye</h3>
	</div>

	<hr />

	<form onsubmit="return false;">
		<div class="row-fluid">
			<div class="span8">
				<input class="pretty-url" name="url" placeholder="Enter A URL" type="text"
					value="http://<?php echo $defaultHost; ?>/" />
			</div>
			<div class="span2">
				<select class="pretty-method" name="method">
					<option value="GET">GET</option>
					<option value="POST">POST</option>
					<option value="PUT">PUT</option>
					<option value="DELETE">DELETE</option>
				</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span6">
				<ul class="unstyled pretty-parameters"></ul>
				<div class="pretty-body">
					<pre id="pretty-editor"></pre>
				</div>
				<textarea id="pretty-field" name="params-body" placeholder="Body"></textarea>
			</div>
			<div class="offset2 span4">
				<select class="pretty-key" name="key">
					<?php foreach ($applications as $application)
					{
						?>
						<option value="<?php echo $application->getKey(); ?>">
							<?php echo ucfirst($application->getName()); ?> Key
						</option>
					<?php } ?>
					<option value="">None</option>
				</select>

				<select class="pretty-user" name="user">
					<option value="">None</option>
					<?php foreach ($users as $user)
					{
						?>
						<option value="<?php echo $user->getId(); ?>"><?php echo $user->getName(); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<input class="btn" type="submit" value="Run Request" />
			</div>
		</div>
	</form>

	<div class="results"></div>

	<hr />

	<div class="footer">
		<p>&copy; Kent Projects 2014</p>
	</div>

</div>
<script id="js-jquery" src="js/jquery.min.js"></script>
<script id="js-pretty" src="vendor/ace/ace.js"></script>
<script id="js-pretty" src="js/pretty.js"></script>
</body>
</html>