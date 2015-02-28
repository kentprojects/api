<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class CommentControllerTest extends KentProjects_Controller_TestBase
{
	/**
	 * @return stdClass
	 */
	public function testCreateComment()
	{
		$commentBody = "This is a wicked comment!";
		$user = $this->getUserForToken("student");

		$request = $this->createSignedRequest(
			Request::POST,
			array(
				"post" => array(
					"root" => "user/" . $user->getId(),
					"comment" => $commentBody
				)
			),
			"student"
		);
		$response = new Response($request);
		$this->runController($request, $response, "Comment");
		$comment = json_decode($response->body());

		$this->assertEquals(201, $response->status());
		$this->assertObjectHasAttribute("id", $comment, "No comment ID.");
		$this->assertObjectHasAttribute("comment", $comment, "No comment content.");
		$this->assertEquals($commentBody, $comment->comment);

		return $comment;
	}

	/**
	 * @depends testCreateComment
	 * @param stdClass $comment
	 */
	public function testGetComment(stdClass $comment)
	{
		$request = $this->createSignedRequest(
			Request::GET,
			array(
				"param" => array(
					"id" => $comment->id
				)
			),
			"student"
		);
		$response = new Response($request);
		$this->runController($request, $response, "Comment");
		$this->assertEquals(200, $response->status());
		$this->assertObjectHasAttribute("id", $comment, "No comment ID.");
		$this->assertObjectHasAttribute("comment", $comment, "No comment content.");
		$this->assertObjectHasAttribute("author", $comment, "No comment author.");
		$this->assertObjectHasAttribute("id", $comment->author, "No comment author ID.");
	}

	/**
	 * @depends testCreateComment
	 * @param stdClass $comment
	 */
	public function testDeleteComment(stdClass $comment)
	{
		$request = $this->createSignedRequest(
			Request::DELETE,
			array(
				"param" => array(
					"id" => $comment->id
				)
			),
			"student"
		);
		$response = new Response($request);
		$this->runController($request, $response, "Comment");
		$this->assertEquals(204, $response->status());
	}
}