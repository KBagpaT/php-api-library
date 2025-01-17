<?php
namespace Kayako\Api\Client\Object\Base;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Config;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\News\NewsComment;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;

/**
 * Base class for comment objects.
 * Known issues:
 * - creatorid is not set when logged user posts a comment from client area
 * - when creating User comment through API, exception is thrown by Kayako (NewsItem and TroubleshooterStep)
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.51.1891
 */
abstract class CommentBase extends ObjectBase {

	/**
	 * Comment creator type - Staff.
	 * @var int
	 */
	const CREATOR_TYPE_STAFF = 1;

	/**
	 * Comment creator type - User.
	 * @var int
	 */
	const CREATOR_TYPE_USER = 2;

	/**
	 * Comment status - Pending approval.
	 * @var int
	 */
	const STATUS_PENDING = 1;

	/**
	 * Comment status - Approved.
	 * @var int
	 */
	const STATUS_APPROVED = 2;

	/**
	 * Comment status - Marked as spam.
	 * @var int
	 */
	const STATUS_SPAM = 3;

	/**
	 * Comment identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Type of this comment creator.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $creator_type;

	/**
	 * Identifier of this comment creator.
	 * @apiField
	 * @var int
	 */
	protected $creator_id;

	/**
	 * Staff creator of this comment (if applicable).
	 * @var Staff
	 */
	protected $creator_staff;

	/**
	 * User creator of this comment (if applicable).
	 * @var User
	 */
	protected $creator_user;

	/**
	 * Fullname of this comment creator.
	 * @apiField name=fullname
	 * @var string
	 */
	protected $creator_fullname;

	/**
	 * E-mail of this comment creator.
	 * @apiField name=email
	 * @var string
	 */
	protected $creator_email;

	/**
	 * IP address of machine where this comment originated from.
	 * @apiField
	 * @var string
	 */
	protected $ip_address;

	/**
	 * Timestamp of when the comment was created.
	 * @apiField
	 * @var int
	 */
	protected $dateline;

	/**
	 * Identifier of parent comment (comment that this comment is a reply to).
	 * @apiField
	 * @var int
	 */
	protected $parent_comment_id;

	/**
	 * Parent comment (comment that this comment is a reply to).
	 * @var CommentBase
	 */
	protected $parent_comment;

	/**
	 * Status of this comment.
	 * @apiField name=commentstatus
	 * @var int
	 */
	protected $status;

	/**
	 * Information about browser on which this comment was created.
	 * @apiField
	 * @var string
	 */
	protected $user_agent;

	/**
	 * URL of page on which this comment was created.
	 * @apiField
	 * @var string
	 */
	protected $referrer;

	/**
	 * URL of commented item.
	 * @apiField
	 * @var string
	 */
	protected $parent_url;

	/**
	 * Contents of this comment.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	protected function parseData($data) {
		$this->id = Helper::assurePositiveInt($data['id']);
		$this->creator_type = Helper::assurePositiveInt($data['creatortype']);
		$this->creator_id = Helper::assurePositiveInt($data['creatorid']);
		$this->creator_fullname = Helper::assureString($data['fullname']);
		$this->creator_email = Helper::assureString($data['email']);
		$this->ip_address = Helper::assureString($data['ipaddress']);
		$this->dateline = Helper::assurePositiveInt($data['dateline']);
		$this->parent_comment_id = Helper::assurePositiveInt($data['parentcommentid']);
		$this->status = Helper::assurePositiveInt($data['commentstatus']);
		$this->user_agent = Helper::assureString($data['useragent']);
		$this->referrer = Helper::assureString($data['referrer']);
		$this->parent_url = Helper::assureString($data['parenturl']);
		$this->contents = Helper::assureString($data['contents']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		if ($this->creator_type === self::CREATOR_TYPE_STAFF) {
			if ($this->creator_id == null) {
				throw new GeneralException("Value for API field 'creatorid' is required for this operation to complete.");
			}
			$this->buildDataNumeric($data, 'creatorid', $this->creator_id);
		} elseif ($this->creator_type === self::CREATOR_TYPE_USER) {
			if ($this->creator_id == null && $this->creator_fullname == null) {
				throw new GeneralException("Value for API fields 'creatorid' or 'fullname' is required for this operation to complete.");
			}

			if ($this->creator_id != null) {
				$this->buildDataNumeric($data, 'creatorid', $this->creator_id);
			} else {
				$this->buildDataString($data, 'fullname', $this->creator_fullname);
			}
		}

		$this->buildDataString($data, 'contents', $this->contents);
		$this->buildDataNumeric($data, 'creatortype', $this->creator_type);
		$this->buildDataString($data, 'email', $this->creator_email);
		$this->buildDataNumeric($data, 'parentcommentid', $this->parent_comment_id);

		return $data;
	}

	/**
	 * Fetches comment from the server by its identifier.
	 *
	 * @param int $id Comment identifier.
	 * @return CommentBase|static
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s%s (author: %s, status: %d)", strtr(substr($this->getContents(), 0, 20), "\n", " "), strlen($this->getContents()) > 20 ? "..." : "", $this->getCreatorFullname(), $this->getStatus());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns creator type of the comment.
	 *
	 * @see CommentBase::CREATOR_TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorType() {
		return $this->creator_type;
	}

	/**
	 * Sets creator type of the comment.
	 *
	 * Automatically resets creator fullname to null when set to Staff.
	 *
	 * @see CommentBase::CREATOR_TYPE constants.
	 *
	 * @param int $creator_type Creator type of the comment.
	 * @return $this
	 */
	public function setCreatorType($creator_type) {
		$this->creator_type = Helper::assureConstant($creator_type, $this, 'CREATOR_TYPE');

		switch ($this->creator_type) {
			case self::CREATOR_TYPE_STAFF:
				$this->creator_fullname = null;
				$this->creator_user = null;
				break;
			case self::CREATOR_TYPE_USER:
				$this->creator_staff = null;
				break;
		}

		return $this;
	}

	/**
	 * Returns identifier of creator of this comment.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorId() {
		return $this->creator_id;
	}

	/**
	 * Sets identifier of creator of this comment.
	 *
	 * Automatically resets creator fullname to null.
	 *
	 * @param int $creator_id Identifier of creator of this comment.
	 * @return $this
	 */
	public function setCreatorId($creator_id) {
		$this->creator_id = Helper::assurePositiveInt($creator_id);
		$this->creator_fullname = null;
		return $this;
	}

	/**
	 * Returns the creator of this comment.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return User|Staff
	 * @filterBy
	 */
	public function getCreator($reload = false) {
		switch ($this->creator_type) {
			case self::CREATOR_TYPE_STAFF:
				if ($this->creator_staff !== null && !$reload)
					return $this->creator_staff;

				if ($this->creator_id === null)
					return null;

				$this->creator_staff = Staff::get($this->creator_id);
				return $this->creator_staff;
			case self::CREATOR_TYPE_USER:
				if ($this->creator_user !== null && !$reload)
					return $this->creator_user;

				if ($this->creator_id === null)
					return null;

				$this->creator_user = User::get($this->creator_id);
				return $this->creator_user;
		}

		return null;
	}

	/**
	 * Sets the creator of this comment.
	 *
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @return $this
	 */
	public function setCreator($creator) {
		if ($creator instanceof Staff) {
			$this->creator_staff = $creator;
			$this->creator_id = $this->creator_staff->getId();
			$this->creator_type = self::CREATOR_TYPE_STAFF;
			$this->creator_user = null;
		} elseif ($creator instanceof User) {
			$this->creator_user = $creator;
			$this->creator_id = $this->creator_user->getId();
			$this->creator_type = self::CREATOR_TYPE_USER;
			$this->creator_staff = null;
		} elseif (is_string($creator) && strlen($creator) > 0) {
			$this->setCreatorFullname($creator);
		} else {
			$this->creator_id = null;
			$this->creator_type = null;
			$this->creator_staff = null;
			$this->creator_user = null;
		}

		return $this;
	}

	/**
	 * Returns fullname of creator of this comment.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorFullname() {
		return $this->creator_fullname;
	}

	/**
	 * Sets fullname of creator of this comment.
	 *
	 * Automatically changes creator type to User and resets creator identifiers to null.
	 *
	 * @param string $fullname Fullname of creator of this comment.
	 * @return $this
	 */
	public function setCreatorFullname($fullname) {
		$this->creator_fullname = $fullname;
		$this->creator_type = self::CREATOR_TYPE_USER;
		$this->creator_id = null;
		$this->creator_user = null;
		$this->creator_staff = null;
		return $this;
	}

	/**
	 * Returns email of creator of this comment.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorEmail() {
		return $this->creator_email;
	}

	/**
	 * Sets email of creator of this comment.
	 *
	 * @param string $email Email of creator of this comment.
	 * @return $this
	 */
	public function setCreatorEmail($email) {
		$this->creator_email = Helper::assureString($email);
		return $this;
	}

	/**
	 * Returns IP address of machine where this comment originated from.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 * Returns date and time when the comment was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDateline($format = null) {
		if ($this->dateline == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->dateline);
	}

	/**
	 * Returns identifier of parent comment (comment that this comment is a reply to).
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getParentCommentId() {
		return $this->parent_comment_id;
	}

	/**
	 * Sets identifier of parent comment (comment that this comment is a reply to).
	 *
	 * @param int $parent_comment_id Identifier of parent comment (comment that this comment is a reply to).
	 * @return $this
	 */
	public function setParentCommentId($parent_comment_id) {
		$this->parent_comment_id = Helper::assurePositiveInt($parent_comment_id);
		$this->parent_comment = null;
		return $this;
	}

	/**
	 * Returns parent comment (comment that this comment is a reply to).
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return CommentBase
	 * @filterBy
	 * @orderBy
	 */
	public function getParentComment($reload = false) {
		if ($this->parent_comment !== null && !$reload)
			return $this->parent_comment;

		if ($this->parent_comment_id === null)
			return null;

		$this->parent_comment = NewsComment::get($this->parent_comment_id);
		return $this->parent_comment;
	}

	/**
	 * Sets the parent comment (comment that this comment is a reply to).
	 *
	 * @param CommentBase $parent_comment Parent comment (comment that this comment is a reply to).
	 * @return $this
	 */
	public function setParentComment($parent_comment) {
		$this->parent_comment = Helper::assureObject($parent_comment, get_class($this));
		$this->parent_comment_id = $this->parent_comment !== null ? $this->parent_comment->getId() : null;
		return $this;
	}

	/**
	 * Returns status of this comment.
	 *
	 * @see CommentBase::STATUS constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns information about browser on which this comment was created.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getUserAgent() {
		return $this->user_agent;
	}

	/**
	 * Returns URL of page on which this comment was created.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getReferrer() {
		return $this->referrer;
	}

	/**
	 * Returns URL of commented item.
	 *
	 * @return string
	 */
	public function getParentURL() {
		return $this->parent_url;
	}

	/**
	 * Returns contents of this comment.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets contents of this comment.
	 *
	 * @param string $contents Contents of this comment.
	 * @return $this
	 */
	public function setContents($contents) {
		$this->contents = Helper::assureString($contents);
		return $this;
	}

	/**
	 * Creates a new comment.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param User|Staff|string $creator Creator (staff object, user object or user fullname) of this comment.
	 * @param string $contents Contents of this comment.
	 * @return static
	 */
	static public function createNew($creator, $contents) {
		/** @var $new_comment CommentBase */
		$new_comment = new static();
		$new_comment->setCreator($creator);
		$new_comment->setContents($contents);
		return $new_comment;
	}
}