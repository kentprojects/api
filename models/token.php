<?php
/**
 * @author: KentProjects <developer@kentprojects.com>
 * @license: Copyright KentProjects
 * @link: http://kentprojects.com
 */
class Model_Token extends Model
{
	/**
	 * @param Model_Application $application
	 * @param Model_User $user
	 * @return Model_Token
	 */
	public static function generate(Model_Application $application, Model_User $user)
	{
		$token = static::getByApplicationUser($application, $user);
		if (empty($token))
		{
			$token = new Model_Token($application, $user);
		}
		$token->save();

		return $token;
	}

	/**
	 * Get a Model_Token by an application & a user.
	 *
	 * @param Model_Application $application
	 * @param Model_User $user
	 * @return Model_Token
	 */
	public static function getByApplicationUser(Model_Application $application, Model_User $user)
	{
		$cacheKey = static::cacheName() . ".app." . $application->getId() . ".user." . $user->getId();
		$id = Cache::get($cacheKey);
		if (empty($id))
		{
			$id = Database::prepare(
				"SELECT t.`token`
				 FROM `Token` t
				 JOIN `Application` a USING (`application_id`)
				 JOIN `User` u USING (`user_id`)
				 WHERE a.`status` = 1 AND u.`status` = 1
				 AND t.`application_id` = ? AND u.`user_id` = ?", "ii"
			)->execute($application->getId(), $user->getId())->singleval();
			!empty($id) && Cache::set($cacheKey, $id, Cache::HOUR);
		}
		return !empty($id) ? static::getByToken($id) : null;
	}

	/**
	 * Get a Model_Token by it's token.
	 *
	 * @param string $id
	 * @return Model_Token
	 */
	public static function getByToken($id)
	{
		if (empty($id))
		{
			return null;
		}
		/** @var Model_Application $application */
		$token = Cache::get(static::cacheName() . "." . $id);
		if (empty($token))
		{
			$token = Database::prepare(
				"SELECT
					t.`application_id` AS 'application',
					t.`user_id` AS 'user',
					t.`token` AS 'token',
					t.`created` AS 'created',
					t.`updated` AS 'updated'
				 FROM `Token` t
				 JOIN `Application` a USING (`application_id`)
				 JOIN `User` u USING (`user_id`)
				 WHERE a.`status` = 1 AND u.`status` = 1
				 AND t.`token` = ?",
				"s", __CLASS__
			)->execute($id)->singleton();
			Cache::store($token);
		}
		return $token;
	}

	/**
	 * @var Model_Application
	 */
	protected $application;
	/**
	 * @var Model_User
	 */
	protected $user;
	/**
	 * @var string
	 */
	protected $token;
	/**
	 * @var string
	 */
	protected $created;
	/**
	 * @var string
	 */
	protected $updated;

	/**
	 * @var bool
	 */
	private $hasTokenRegenerated = false;

	/**
	 * @param Model_Application $application
	 * @param Model_User $user
	 */
	public function __construct(Model_Application $application = null, Model_User $user = null)
	{
		if (!empty($this->application) && !empty($this->user))
		{
			/** @noinspection PhpParamsInspection */
			$this->application = Model_Application::getById($this->application);
			/** @noinspection PhpParamsInspection */
			$this->user = Model_User::getById($this->user);
		}
		else
		{
			$this->application = $application;
			$this->user = $user;
		}
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return sprintf("application:%d/user:%d", $this->application->getId(), $this->user->getId());
	}

	/**
	 * @return Model_Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @return Model_User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return array(
			"application" => $this->application,
			"user" => $this->user,
			"token" => $this->token,
			"created" => $this->created,
			"updated" => $this->updated
		);
	}

	/**
	 * Regenerates a token.
	 * Don't forget to save!
	 *
	 * @return void
	 */
	public function regenerate()
	{
		$this->token = md5(uniqid());
		$this->hasTokenRegenerated = true;
	}

	/**
	 * @return void
	 */
	public function save()
	{
		if (empty($this->token))
		{
			$this->regenerate();
			$this->created = Date::format(Date::TIMESTAMP, time());
		}

		Database::prepare(
			"INSERT INTO `Token` (`application_id`, `user_id`, `token`, `created`)
			 VALUES (?, ?, ?, CURRENT_TIMESTAMP)
			 ON DUPLICATE KEY UPDATE `token` = VALUES(`token`)",
			"iis"
		)->execute(
			$this->application->getId(), $this->user->getId(), $this->token
		);

		$this->updated = Date::format(Date::TIMESTAMP, time());
		$this->hasTokenRegenerated = false;
		parent::save();
	}
}