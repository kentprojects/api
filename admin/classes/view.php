<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class View
 * This represents anything that is Viewable.
 */
abstract class View
{
	/**
	 * The title of the current page.
	 * @var string
	 */
	private static $pageTitle;

	/**
	 * A list of the children under this view.
	 * @var array
	 */
	private $children = array();

	/**
	 * Translate the current view to a string.
	 *
	 * @throws Exception
	 * @return string
	 */
	public function __toString()
	{
		ob_start();
		try
		{
			$this->render();
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
		return ob_get_clean();
	}

	/**
	 * Add a text child to this view.
	 *
	 * @param mixed $child
	 * @return void
	 */
	public function addTextChild($child)
	{
		$this->children[] = (string)$child;
	}

	/**
	 * Add a child View object to this view.
	 *
	 * @param View $child
	 * @return void
	 */
	public function addViewChild(View $child)
	{
		$this->children[] = $child;
	}

	/**
	 * Returns the number of children this View has.
	 *
	 * @return int
	 */
	public function countChildren()
	{
		return count($this->children);
	}

	/**
	 * Render the view.
	 * @return void
	 */
	public function render()
	{
		$this->renderTop();
		$this->renderChildren();
		$this->renderBottom();
	}

	/**
	 * Render the top half of the view.
	 * @return void
	 */
	public function renderTop()
	{
		/** @noinspection SpellCheckingInspection */
		echo
		'<!DOCTYPE html>',
		'<html lang="en">',
		'<head>';
		$this->renderHead();
		echo
		'</head>',
		'<body>';
	}

	/**
	 * Specifically render items in the <head /> tag of a view.
	 * @return void
	 */
	public function renderHead()
	{
		echo
		'<title>', $this->pageTitle, '</title>',
		'<link href="/admin/assets/apple-touch-icon.png" rel="apple-touch-icon"/>',
		'<link href="/admin/assets/css/style.css" rel="stylesheet" type="text/css"/>';
	}

	/**
	 * Render the children inside the view.
	 * @return void
	 */
	public function renderChildren()
	{
		if (count($this->children) > 0)
		{
			foreach ($this->children as $child)
			{
				if ($child instanceof View)
				{
					$child->render();
				}
				else
				{
					echo $child;
				}
			}
		}
	}

	/**
	 * Specifically render items where <script /> tags should be.
	 * @return void
	 */
	public function renderScripts()
	{
		echo
		'<script src="/admin/assets/js/jquery-1.11.2.min.js" type="text/javascript"></script>',
		'<script src="/admin/assets/js/flat-ui-pro.min.js" type="text/javascript"></script>';
	}

	/**
	 * Render the bottom half of the view.
	 * @return void
	 */
	public function renderBottom()
	{
		$this->renderScripts();
		echo '<script src="/admin/assets/js/script.js" type="text/javascript"></script>';
		echo '</body></html>';
	}

	/**
	 * Set a new page title.
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title)
	{
		static::$pageTitle = $title . " &raquo; KentProjects";
	}
}