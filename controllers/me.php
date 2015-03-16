<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
final class Controller_Me extends Controller
{
	/**
	 * @var string
	 */
	protected $authentication = Auth::USER;

	/**
	 * /me
	 * /me/:id
	 *
	 * GET / PUT
	 *
	 * Get a collection of information for the current user, or a clever method to update yourself.
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_index()
	{
		$this->validateMethods(Request::GET, Request::PUT);
		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No ID should be passed to the ME controller.");
		}

		$user = $this->auth->getUser();

		if ($this->request->getMethod() === Request::PUT)
		{
			$user->update($this->request->getPostData());
			$user->save();
		}

		$details = array(
			"user" => $user
		);
		if ($user->isStudent())
		{
			$details["group"] = Model_Group::getByUser($user);
			if (!empty($details["group"]))
			{
				$details["project"] = Model_Project::getByGroup($details["group"]);
			}
		}

		// $details["notifications"] = new UserNotificationMap($user);
		$details["settings"] = $this->auth->getToken()->getSettings();

		$this->response->status(200);
		$this->response->body($details);
	}

	/**
	 * /me/notifications
	 * /me/:id/notifications
	 *
	 * GET / PUT
	 *
	 * Gets a list of notifications for the user.
	 * Append `?unread=1` to get only unread notifications.
	 * Check the header `X-Notification-Count` for a count if required.
	 *
	 * To mark notifications as read, send a PUT request to /me/notifications with a JSON array of IDs:
	 *   PUT /me/notifications
	 *   { ids: [ 1, 2, 3, 4, 5 ] }
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_notifications()
	{
		$this->validateMethods(Request::GET, Request::PUT);
		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No ID should be passed to the ME controller.");
		}

		$notifications = new UserNotificationMap($this->auth->getUser());

		if ($this->request->getMethod() === Request::PUT)
		{
			$ids = $this->request->post("ids", array());
			if (empty($ids) || !is_array($ids))
			{
				throw new HttpStatusException(400, "Please supply an array of IDs to mark notifications as read.");
			}

			$notifications->markAsReadByIds($ids);
			$notifications->save();
		}

		if ($this->request->query("unread") !== null)
		{
			$notifications = $notifications->getUnread();
		}

		$this->response->status(200);
		$this->response->header("X-Notification-Count", count($notifications));
		$this->response->body($notifications);
	}

	/**
	 * /me/settings
	 * /me/:id/settings
	 *
	 * GET / PUT
	 *
	 * Gets and sets settings for the user.
	 *
	 * @throws HttpStatusException
	 * @return void
	 */
	public function action_settings()
	{
		$this->validateMethods(Request::GET, Request::PUT);
		if ($this->request->param("id") !== null)
		{
			throw new HttpStatusException(400, "No ID should be passed to the ME controller.");
		}

		$token = $this->auth->getToken();

		if ($this->request->getMethod() === Request::PUT)
		{
			/**
			 * PUT /me/settings
			 */
			$token->setSettings($this->request->getPostData());
			$token->save();
		}

		Log::debug($this->request->getMethod(), $token, $token->getSettings());

		$this->response->status(200);
		$this->response->body($token->getSettings());
	}
}