<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title>Kent AP Eye</title>
	<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
	<link href="css/style.css" rel="stylesheet" type="text/css"/>
	<link href="http://dev.kentprojects.com//includes/img/kp.ico" rel="icon" type="image/x-icon"/>
</head>
<body>
<div class="container">

	<div class="masthead">
		<ul class="nav nav-pills pull-right api-selector">
			<li class="active"><a href="http://api.dev.kentprojects.com">Developer API</a></li>
			<li><a href="http://api.kentprojects.com">Live API</a></li>
		</ul>
		<h3 class="custom-h3">Kent AP Eye</h3>
	</div>

	<hr/>

	<form onsubmit="return false;">
		<div class="row-fluid">
			<div class="span8">
				<input class="pretty-url" name="url" placeholder="Enter A URL" type="text"
					   value="http://api.dev.kentprojects.com"/>
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
					<option value="developer-key" selected="selected">Developer Key</option>
					<option value="frontend-key">Frontend Key</option>
					<option value="none">None</option>
				</select>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
				<input class="btn" type="submit" value="Run Request"/>
			</div>
		</div>
	</form>

	<div class="results"></div>

	<hr/>

	<div class="footer">
		<p>&copy; Kent Projects 2014</p>
	</div>

</div>
<script id="js-jquery" src="js/jquery.min.js"></script>
<script id="js-pretty" src="js/ace/ace.js"></script>
<script id="js-pretty" src="js/pretty.js"></script>
</body>
</html>