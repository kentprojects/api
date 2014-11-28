<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class QueryTest extends KentProjects_TestBase
{
	public function testBaseQuery()
	{
		$query = new Query("project_id", "Project");

		$this->assertEquals("Project", $query->getTable());
		$this->assertEquals(array("project_id"), $query->getFields());

		$result = $query->execute(true);
		$this->assertEquals("SELECT `project_id` FROM `Project`", $result->sql);
		$this->assertEquals("", $result->types);
		$this->assertEquals(array(), $result->values);

		return $query;
	}

	public function testSingleWhere()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"value" => 0
		));
		$result = $query->execute(true);
		$this->assertEquals("SELECT `project_id` FROM `Project` WHERE `Project`.`status` = 0", $result->sql);
		$this->assertEquals("", $result->types);
		$this->assertEquals(array(), $result->values);
	}

	public function testSingleWhereType()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"type" => "i",
			"value" => 2
		));
		$result = $query->execute(true);
		$this->assertEquals("SELECT `project_id` FROM `Project` WHERE `Project`.`status` = ?", $result->sql);
		$this->assertEquals("i", $result->types);
		$this->assertEquals(array(2), $result->values);
	}

	public function testMultiWhereAND()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::AAND,
			"values" => array(0, 1)
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE (`Project`.`status` = 0 AND `Project`.`status` = 1)",
			$result->sql
		);
		$this->assertEquals("", $result->types);
		$this->assertEquals(array(), $result->values);
	}

	public function testMultiWhereTypeAND()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::AAND,
			"type" => "i",
			"values" => array("0", "1")
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE (`Project`.`status` = ? AND `Project`.`status` = ?)",
			$result->sql
		);
		$this->assertEquals("ii", $result->types);
		$this->assertEquals(array("0", "1"), $result->values);
	}

	public function testMultiWhereOR()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::OOR,
			"values" => array(0, 1)
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE (`Project`.`status` = 0 OR `Project`.`status` = 1)",
			$result->sql
		);
		$this->assertEquals("", $result->types);
		$this->assertEquals(array(), $result->values);
	}

	public function testMultiWhereTypeOR()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::OOR,
			"type" => "i",
			"values" => array("0", "1")
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE (`Project`.`status` = ? OR `Project`.`status` = ?)",
			$result->sql
		);
		$this->assertEquals("ii", $result->types);
		$this->assertEquals(array("0", "1"), $result->values);
	}

	public function testMultiWhereIN()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::IN,
			"values" => array(0, 1)
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE `Project`.`status` IN (0, 1)",
			$result->sql
		);
		$this->assertEquals("", $result->types);
		$this->assertEquals(array(), $result->values);
	}

	public function testMultiWhereTypeIN()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "status",
			"operator" => Query::IN,
			"type" => "i",
			"values" => array("0", "1")
		));
		$result = $query->execute(true);
		$this->assertEquals(
			"SELECT `project_id` FROM `Project` WHERE `Project`.`status` IN (?, ?)",
			$result->sql
		);
		$this->assertEquals("ii", $result->types);
		$this->assertEquals(array("0", "1"), $result->values);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Missing field / value.
	 */
	public function testWhereExceptionMissingField()
	{
		$query = new Query("project_id", "Project");
		$query->where(array());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Missing field / value.
	 */
	public function testWhereExceptionMissingValue()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "SomeField"
		));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Values should be an array.
	 */
	public function testWhereExceptionValuesNotAnArray()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "SomeField",
			"values" => "SomeValues"
		));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Missing operator (AAND|OOR|IN) with values.
	 */
	public function testWhereExceptionValuesWithMissingOperator()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "SomeField",
			"values" => array("Some", "More", "Values")
		));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid operator for where statement.
	 */
	public function testWhereExceptionInvalidOperator()
	{
		$query = new Query("project_id", "Project");
		$query->where(array(
			"field" => "SomeField",
			"operator" => Query::USING,
			"values" => array("Some", "More", "Values")
		));
	}
}