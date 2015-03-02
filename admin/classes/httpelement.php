<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class HtmlElement extends View
{
	private $attributes = array();
	private $tag;

	/**
	 * @param string $tag
	 * @param array $attributes
	 * @param string $content
	 */
	public function __construct($tag, array $attributes = array(), $content = null)
	{
		$this->tag = (string)$tag;

		$this->addAttributes($attributes);
		$this->addTextChild($content);
	}

	/**
	 * @param array $attributes
	 * @return void
	 */
	public function addAttributes(array $attributes)
	{
		$this->attributes = array_filter(array_merge($this->attributes, $attributes));
	}

	/**
	 * @param HtmlElement $content
	 * @return void
	 */
	public function addElement(HtmlElement $content)
	{
		parent::addViewChild($content);
	}

	/**
	 * Render the HTML element.
	 */
	public function render()
	{
		$this->renderTop();
		if ($this->countChildren() > 0)
		{
			$this->renderChildren();
			$this->renderBottom();
		}
	}

	public function renderTop()
	{
		echo '<', $this->tag;
		if (count($this->attributes) > 0)
		{
			echo ' ';
			foreach ($this->attributes as $name => $attribute)
			{
				echo ' ' . $name . '="' . $attribute . '"';
			}
		}
		echo($this->countChildren() === 0 ? ' ' : ''), '/>';
	}

	public function renderBottom()
	{
		echo '</', $this->tag, '>';
	}
}