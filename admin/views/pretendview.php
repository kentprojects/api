<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * This is a pretend view, to test the View class and Admin_Response::render method.
 */
class PretendView extends View
{
	public function __construct()
	{
		$this->setTitle("Oh yes");
	}

	public function renderTop()
	{
		parent::renderTop();
		echo
		'<div class="awesome">',
		'<p>Hello, world!</p>';
	}

	public function renderBottom()
	{
		echo
		'<p>Welcome to the start of the Admin panel for KentProjects.</p>',
		'</div>';
		parent::renderBottom();
	}
}