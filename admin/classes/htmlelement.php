<?php
/**
 * @author: James Dryden <james.dryden@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 *
 * Class HtmlElement
 * This represents an individual HTML element.
 * This is like a micro-bot from Big Hero Six - it's fairly minimal on it's own, but when it connects with other classes
 *   like itself, it can build complex structures and, well, pretty much anything!
 */
class HtmlElement extends View
{
	/**
	 * A key-value list of attributes for this element.
	 * @var array
	 */
	private $attributes = array();
	/**
	 * The tag name for this particular element.
	 * @var string
	 */
	private $tag;

	/**
	 * Build a new HtmlElement.
	 *
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
	 * Add more attributes to this HtmlElement.
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function addAttributes(array $attributes)
	{
		$this->attributes = array_filter(array_merge($this->attributes, $attributes));
	}

	/**
	 * Add a child element to this element.
	 *
	 * @param HtmlElement $content
	 * @return void
	 */
	public function addElement(HtmlElement $content)
	{
		parent::addViewChild($content);
	}

	/**
	 * Render the HTML element.
	 * @return void
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

	/**
	 * Render the top of the HtmlElement.
	 * @return void
	 */
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

	/**
	 * Render the bottom of the HtmlElement.
	 * @return void
	 */
	public function renderBottom()
	{
		echo '</', $this->tag, '>';
	}
}