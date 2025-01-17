<?php
namespace Kayako\Api\Client\Object\Ticket;

use InvalidArgumentException;
use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Config;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\Base\ObjectWithCustomFieldsBase;
use Kayako\Api\Client\Object\Department\Department;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;
use Kayako\Api\Client\Object\User\UserOrganization;

/**
 * Kayako Ticket object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+Ticket
 * @since Kayako version 4.40.1079
 */
class Ticket extends ObjectWithCustomFieldsBase {

	const FLAG_NONE = 0;
	const FLAG_PURPLE = 1;
	const FLAG_ORANGE = 2;
	const FLAG_GREEN = 3;
	const FLAG_YELLOW = 4;
	const FLAG_RED = 5;
	const FLAG_BLUE = 6;

	const CREATOR_AUTO = 0;
	const CREATOR_STAFF = 1;
	const CREATOR_USER = 2;
	const CREATOR_CLIENT = 2;

	const CREATION_MODE_SUPPORTCENTER = 1;
	const CREATION_MODE_STAFFCP = 2;
	const CREATION_MODE_EMAIL = 3;
	const CREATION_MODE_API = 4;
	const CREATION_MODE_SITEBADGE = 5;

	const CREATION_TYPE_DEFAULT = 'default';
	const CREATION_TYPE_PHONE = 'phone';

	/**
	 * Flag for searching using query - search the Ticket ID & Mask ID.
	 * @var string
	 */
	const SEARCH_TICKET_ID = 'ticketid';

	/**
	 * Flag for searching using query - search the Ticket Post Contents.
	 * @var string
	 */
	const SEARCH_CONTENTS = 'contents';

	/**
	 * Flag for searching using query - search the Full Name & Email.
	 * @var string
	 */
	const SEARCH_AUTHOR = 'author';

	/**
	 * Flag for searching using query - search the Email Address (Ticket & Posts).
	 * @var string
	 */
	const SEARCH_EMAIL = 'email';

	/**
	 * Flag for searching using query - search the Email Address (only Tickets).
	 * @var string
	 */
	const SEARCH_CREATOR_EMAIL = 'creatoremail';

	/**
	 * Flag for searching using query - search the Full Name.
	 * @var string
	 */
	const SEARCH_FULL_NAME = 'fullname';

	/**
	 * Flag for searching using query - search the Ticket Notes.
	 * @var string
	 */
	const SEARCH_NOTES = 'notes';

	/**
	 * Flag for searching using query - search the User Group.
	 * @var string
	 */
	const SEARCH_USER_GROUP = 'usergroup';

	/**
	 * Flag for searching using query - search the User Organization.
	 * @var string
	 */
	const SEARCH_USER_ORGANIZATION = 'userorganization';

	/**
	 * Flag for searching using query - search the User (Full Name, Email).
	 * @var string
	 */
	const SEARCH_USER = 'user';

	/**
	 * Flag for searching using query - search the Ticket Tags.
	 * @var string
	 */
	const SEARCH_TAGS = 'tags';

	static protected $controller = '/Tickets/Ticket';
	static protected $object_xml_name = 'ticket';
	static protected $custom_field_group_class = TicketCustomFieldGroup::class;
	static protected $object_id_field = 'ticketid';

	/**
	 * Default status identifier for new tickets.
	 * @see Ticket::setDefaults
	 * @var int
	 */
	static private $default_status_id = null;

	/**
	 * Default priority identifier for new tickets.
	 * @see Ticket::setDefaults
	 * @var int
	 */
	static private $default_priority_id = null;

	/**
	 * Default type identifier for new tickets.
	 * @see Ticket::setDefaults
	 * @var int
	 */
	static private $default_type_id = null;

	/**
	 * Default status identifier for new tickets.
	 * @see Ticket::setDefaults
	 * @var int
	 */
	static private $auto_create_user = true;

	/**
	 * Ticket identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket flag type.
	 *
	 * @see Ticket::FLAG constants.
	 *
	 * @apiField
	 * @var int
	 */
	protected $flag_type;

	/**
	 * Ticket display identifier.
	 * @apiField
	 * @var string
	 */
	protected $display_id;

	/**
	 * Ticket department identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $department_id;

	/**
	 * Ticket status identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $status_id;

	/**
	 * Ticket priority identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $priority_id;

	/**
	 * Ticket type identifier.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $type_id;

	/**
	 * Identifier of the user ticket was created by.
	 * @apiField
	 * @var int
	 */
	protected $user_id;

	/**
	 * Name of the organization of the user ticket was created by.
	 * @apiField
	 * @var string
	 */
	protected $user_organization_name;

	/**
	 * Identifier of the organization of the user ticket was created by.
	 * @apiField
	 * @var int
	 */
	protected $user_organization_id;

	/**
	 * Identifier of staff user who owns the ticket.
	 * @apiField
	 * @var int
	 */
	protected $owner_staff_id;

	/**
	 * Full name of staff user who owns the ticket.
	 * @apiField
	 * @var string
	 */
	protected $owner_staff_name;

	/**
	 * Full name of creator of the ticket.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $full_name;

	/**
	 * E-mail of creator of the ticket.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $email;

	/**
	 * Full name of the last replier to this ticket.
	 * @apiField
	 * @var string
	 */
	protected $last_replier;

	/**
	 * Ticket subject.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $subject;

	/**
	 * Timestamp of when this ticket was created.
	 * @apiField
	 * @var int
	 */
	protected $creation_time;

	/**
	 * Timestamp of last activity in this ticket.
	 * @apiField
	 * @var int
	 */
	protected $last_activity;

	/**
	 * Timestamp of last staff user reply.
	 * @apiField
	 * @var int
	 */
	protected $last_staff_reply;

	/**
	 * Timestamp of last user reply.
	 * @apiField
	 * @var int
	 */
	protected $last_user_reply;

	/**
	 * Service Level Agreement plan identifier.
	 * @apiField
	 * @var int
	 */
	protected $sla_plan_id;

	/**
	 * Timestamp of when the next replay is due.
	 * @apiField
	 * @var int
	 */
	protected $next_reply_due;

	/**
	 * Timestamp of when resolution of the ticket is due.
	 * @apiField
	 * @var int
	 */
	protected $resolution_due;

	/**
	 * Reply count.
	 * @apiField
	 * @var int
	 */
	protected $replies;

	/**
	 * IP address from which the ticket was created.
	 * @apiField
	 * @var string
	 */
	protected $ip_address;

	/**
	 * Type of the ticket creator.
	 *
	 * @see Ticket::CREATOR constants.
	 *
	 * @apiField
	 * @var int
	 */
	protected $creator;

	/**
	 * Ticket creation mode.
	 *
	 * @see Ticket::CREATION_MODE constants.
	 *
	 * @apiField
	 * @var int
	 */
	protected $creation_mode;

	/**
	 * Ticket creation type.
	 *
	 * @see Ticket::CREATION_TYPE constants.
	 *
	 * @apiField alias=type
	 * @var int
	 */
	protected $creation_type;

	/**
	 * Is this ticket escalated.
	 * @apiField
	 * @var bool
	 */
	protected $is_escalated;

	/**
	 * Escalation rule identifier.
	 * @apiField
	 * @var int
	 */
	protected $escalation_rule_id;

	/**
	 * Template group identifier.
	 * @apiField getter=getTemplateGroupId setter=setTemplateGroup alias=templategroup
	 * @var int
	 */
	protected $template_group_id;

	/**
	 * Template group name.
	 * @apiField getter=getTemplateGroupName setter=setTemplateGroup
	 * @var string
	 */
	protected $template_group_name;

	/**
	 * Ticket tags.
	 * @apiField
	 * @var string
	 */
	protected $tags;

	/**
	 * Ticket watchers.
	 * @apiField
	 * @var array
	 */
	protected $watchers;

	/**
	 * Ticket workflows.
	 * @apiField
	 * @var array
	 */
	protected $workflows;

	/**
	 * Identifier os staff user who will create this ticket.
	 * @apiField
	 * @var int
	 */
	protected $staff_id;

	/**
	 * Ticket contents.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents = null;

	/**
	 * Option to disable autoresponder e-mail.
	 * @apiField
	 * @var bool
	 */
	protected $ignore_auto_responder = false;

	/**
	 * Ticket status.
	 * @var TicketStatus
	 */
	private $status;

	/**
	 * Ticket priority.
	 * @var TicketPriority
	 */
	private $priority;

	/**
	 * Ticket type.
	 * @var TicketType
	 */
	private $type;

	/**
	 * User, the creator of this ticket.
	 * @var User
	 */
	private $user;

	/**
	 * Organization of user who created this ticket.
	 * @var UserOrganization
	 */
	private $user_organization;

	/**
	 * Staff user, the creator of this ticket.
	 * @var Staff
	 */
	private $staff;

	/**
	 * Staff user who is the owner of this ticket.
	 * @var Staff
	 */
	private $owner_staff;

	/**
	 * Department of this ticket.
	 * @var Department
	 */
	private $department = null;

	/**
	 * List of ticket notes.
	 * @var TicketNote[]
	 */
	private $notes = null;

	/**
	 * List of ticket time tracks.
	 * @var TicketTimeTrack[]
	 */
	private $time_tracks = null;

	/**
	 * List of ticket posts.
	 * @var TicketPost[]
	 */
	private $posts = null;

	/**
	 * List of ticket attachments.
	 * @var TicketAttachment[]
	 */
	private $attachments = null;

	/**
	 * Tickets statistic.
	 * @var array
	 */
	static private $statistics = null;

	/**
	 * Sets default status, priority and type for newly created tickets.
	 *
	 * @param int $status_id Default ticket status identifier.
	 * @param int $priority_id Default ticket priority identifier.
	 * @param int $type_id Default ticket type identifier.
	 * @param bool $auto_create_user True to automatically create user if none is provided as creator. False otherwise.
	 */
	static public function setDefaults($status_id, $priority_id, $type_id, $auto_create_user = true) {
		self::$default_status_id = $status_id;
		self::$default_priority_id = $priority_id;
		self::$default_type_id = $type_id;
		self::$auto_create_user = $auto_create_user;
	}

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->flag_type = intval($data['_attributes']['flagtype']);
		$this->display_id = $data['displayid'];
		$this->department_id = Helper::assurePositiveInt($data['departmentid']);
		$this->status_id = Helper::assurePositiveInt($data['statusid']);
		$this->priority_id = Helper::assurePositiveInt($data['priorityid']);
		$this->type_id = Helper::assurePositiveInt($data['typeid']);
		$this->user_id = Helper::assurePositiveInt($data['userid']);
		$this->user_organization_name = $data['userorganization'];
		$this->user_organization_id = Helper::assurePositiveInt($data['userorganizationid']);
		$this->owner_staff_id = Helper::assurePositiveInt($data['ownerstaffid']);
		$this->owner_staff_name = $data['ownerstaffname'];
		$this->full_name = $data['fullname'];
		$this->email = $data['email'];
		$this->last_replier = $data['lastreplier'];
		$this->subject = $data['subject'];
		$this->creation_time = Helper::assurePositiveInt($data['creationtime']);
		$this->last_activity = Helper::assurePositiveInt($data['lastactivity']);
		$this->last_staff_reply = Helper::assurePositiveInt($data['laststaffreply']);
		$this->last_user_reply = Helper::assurePositiveInt($data['lastuserreply']);
		$this->sla_plan_id = Helper::assurePositiveInt($data['slaplanid']);
		$this->next_reply_due = Helper::assurePositiveInt($data['nextreplydue']);
		$this->resolution_due = Helper::assurePositiveInt($data['resolutiondue']);
		$this->replies = intval($data['replies']);
		$this->ip_address = $data['ipaddress'];
		$this->creator = intval($data['creator']);
		$this->creation_mode = intval($data['creationmode']);
		$this->creation_type = intval($data['creationtype']);
		$this->is_escalated = Helper::assureBool($data['isescalated']);
		$this->escalation_rule_id = Helper::assurePositiveInt($data['escalationruleid']);
		$this->template_group_id = Helper::assurePositiveInt($data['templategroupid']);
		if (is_numeric($data['templategroupid']) && !empty($data['templategroupid']) && isset($data['templategroupname'])) {
			$this->template_group_name = $data['templategroupname'];
		}
		$this->tags = $data['tags'];

		$this->watchers = array();
		if (array_key_exists('watcher', $data)) {
			foreach ($data['watcher'] as $watcher) {
				$this->watchers[] = array('staff_id' => intval($watcher['_attributes']['staffid']), 'name' => $watcher['_attributes']['name']);
			}
		}

		$this->workflows = array();
		if (array_key_exists('workflow', $data)) {
			foreach ($data['workflow'] as $workflow) {
				$this->workflows[] = array('id' => intval($workflow['_attributes']['id']), 'title' => $workflow['_attributes']['title']);
			}
		}

		/**
		 * Notes and time tracks.
		 */
		if (array_key_exists('note', $data)) {
			foreach ($data['note'] as $note_data) {
				/*
				 * Includes workaround for old format of TimeTrack object - if "timeworked" key is present than it's a time track.
				 */
				if ($note_data['_attributes']['type'] === 'timetrack' || array_key_exists('timeworked', $note_data['_attributes'])) {
					if ($this->time_tracks === null)
						$this->time_tracks = array();

					$this->time_tracks[] = new TicketTimeTrack($note_data);
				} else {
					if ($this->notes === null)
						$this->notes = array();

					$this->notes[] = new TicketNote($note_data);
				}
			}
		}

		/**
		 * Posts.
		 */
		if (array_key_exists('posts', $data)) {
			$this->posts = array();
			// This is a change from the Kayako PHP library on GitHub.  We
			// may have tickets with no posts due to migration from NetSuite
			// which violates the data model for Kayako.  Added an if statement
			// here to handle this situation.
			if (is_array($data['posts']) && count($data['posts']) > 0) {
				foreach ($data['posts'][0]['post'] as $post_data) {
					$this->posts[] = new TicketPost($post_data);
				}
			}
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['subject'] = $this->subject;
		$data['fullname'] = $this->full_name;
		$data['email'] = $this->email;
		$data['departmentid'] = $this->department_id;
		$data['ticketstatusid'] = $this->status_id;
		$data['ticketpriorityid'] = $this->priority_id;
		$data['tickettypeid'] = $this->type_id;

		if ($this->owner_staff_id > 0) {
			$data['ownerstaffid'] = $this->owner_staff_id;
		}

		$data['templategroup'] = is_numeric($this->template_group_id) ? $this->template_group_id : $this->template_group_name;

		if ($create) {
			switch ($this->creator) {
				case self::CREATOR_STAFF:
					$data['staffid'] = $this->staff_id;
					break;

				case self::CREATOR_USER:
					$data['userid'] = $this->user_id;
					break;

				/** @noinspection PhpMissingBreakStatementInspection */
				case self::CREATOR_AUTO:
					if (self::$auto_create_user) {
						$data['autouserid'] = 1;
						break;
					}

				default:
					throw new GeneralException("Value for API fields 'staffid' or 'userid' is required or automatic ticket user creation should be enabled for this operation to complete.");
					break;
			}

			$data['contents'] = $this->contents;
			$data['type'] = $this->creation_type;
			$data['ignoreautoresponder'] = $this->ignore_auto_responder;
		} else {
			$data['userid'] = $this->user_id;
		}

		return $data;
	}

	/**
	 * Searches for tickets based on provided data. You must provide at least one department identifier.
	 *
	 * @param Department|ResultSet $departments Non-empty list of department identifiers.
	 * @param array|ResultSet|TicketStatus $ticket_statuses List of ticket status identifiers.
	 * @param array|ResultSet|Staff $owner_staffs List of staff (ticket owners) identifiers.
	 * @param array|ResultSet|User $users List of user (ticket creators) identifiers.
	 * @param int $max_items
	 * @param int $starting_ticket_id
	 * @return ResultSet|Ticket[]
	 */
	static public function getAll($departments, $ticket_statuses = array(), $owner_staffs = array(), $users = array(), $max_items = null, $starting_ticket_id = null) {
		$search_parameters = array('ListAll');

		$department_ids = array();
		if ($departments instanceof Department) {
			$department_ids = array($departments->getId());
		} elseif ($departments instanceof ResultSet) {
			/** @noinspection PhpUndefinedMethodInspection */
			$department_ids = $departments->collectId();
		}

		//department
		if (count($department_ids) === 0)
			throw new InvalidArgumentException('You must provide at least one department to search for tickets.');

		$ticket_status_ids = array();
		if ($ticket_statuses instanceof TicketStatus) {
			$ticket_status_ids = array($ticket_statuses->getId());
		} elseif ($ticket_statuses instanceof ResultSet) {
			/** @noinspection PhpUndefinedMethodInspection */
			$ticket_status_ids = $ticket_statuses->collectId();
		} elseif (is_array($ticket_statuses)) {
			$ticket_status_ids = $ticket_statuses;
		}

		$owner_staff_ids = array();
		if ($owner_staffs instanceof Staff) {
			$owner_staff_ids = array($owner_staffs->getId());
		} elseif ($owner_staffs instanceof ResultSet) {
			/** @noinspection PhpUndefinedMethodInspection */
			$owner_staff_ids = $owner_staffs->collectId();
		}

		$user_ids = array();
		if ($users instanceof User) {
			$user_ids = array($users->getId());
		} elseif ($users instanceof ResultSet) {
			/** @noinspection PhpUndefinedMethodInspection */
			$user_ids = $users->collectId();
		}

		$search_parameters[] = implode(',', $department_ids);

		//ticket status
		if (count($ticket_status_ids) > 0) {
			$search_parameters[] = implode(',', $ticket_status_ids);
		} else {
			$search_parameters[] = '-1';
		}

		//owner staff
		if (count($owner_staff_ids) > 0) {
			$search_parameters[] = implode(',', $owner_staff_ids);
		} else {
			$search_parameters[] = '-1';
		}

		//user
		if (count($user_ids) > 0) {
			$search_parameters[] = implode(',', $user_ids);
		} else {
			$search_parameters[] = '-1';
		}

		if (is_numeric($max_items)) {
			$search_parameters[] = $max_items;
		}

		if (is_numeric($starting_ticket_id) && $starting_ticket_id > 0) {
			if (!is_numeric($max_items) || $max_items <= 0) {
				$search_parameters[] = 1000;
			}
			$search_parameters[] = $starting_ticket_id;
		}

		return parent::genericGetAll($search_parameters);
	}

	/**
	 * Fetches ticket from the server by its identifier.
	 *
	 * @param int $id Ticket identifier.
	 * @return Ticket
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	/**
	 * Searches objects from server using query and search flags.
	 * Example:
	 * Ticket::search("something", array(Ticket::SEARCH_CONTENTS, Ticket::SEARCH_NOTES));
	 *
	 * @param string $query What to search for.
	 * @param array $areas List of areas where to search for as array with Ticket::SEARCH constants.
	 * @return ResultSet
	 */
	static public function search($query, $areas) {
		$data = array();
		$data['query'] = $query;

		foreach ($areas as $area) {
			$data[$area] = 1;
		}

		$result = self::getRESTClient()->post('/Tickets/TicketSearch', array(), $data);

		$objects = array();
		if (array_key_exists(static::$object_xml_name, $result)) {
			foreach ($result[static::$object_xml_name] as $object_data) {
				$objects[] = new static($object_data);
			}
		}
		return new ResultSet($objects);
	}

	public function toString() {
		return sprintf("%s %s (creator: %s)", $this->getDisplayId(), $this->getSubject(), $this->getFullName());
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Sets the creator (User or Staff) of this post.
	 *
	 * @see Ticket::CREATOR constants.
	 *
	 * @param int|User|Staff $creator User identifier OR staff identifier OR user OR staff user.
	 * @param int $type Creator type. Required only when $creator is an identifier.
	 * @return Ticket
	 */
	public function setCreator($creator, $type = null) {
		if (is_numeric($creator)) {
			switch ($type) {
				case self::CREATOR_USER:
					$this->setUserId($creator);
					break;

				case self::CREATOR_STAFF:
					$this->setStaffId($creator);
					break;
			}
		} elseif ($creator instanceof User) {
			$this->setUser($creator);
		} elseif ($creator instanceof Staff) {
			$this->setStaff($creator);
		}

		return $this;
	}

	/**
	 * Sets the creator to be automatically created (or assigned) by the server based on specified data.
	 *
	 * @param string $full_name Full name of the creator.
	 * @param string $email E-mail of the creator.
	 * @return Ticket
	 */
	public function setCreatorAuto($full_name, $email) {
		$this->setFullName($full_name);
		$this->setEmail($email);
		$this->creator = self::CREATOR_AUTO;

		$this->user = null;
		$this->user_id = null;
		$this->staff = null;
		$this->staff_id = null;
		return $this;
	}

	/**
	 * Returns flag type for the ticket.
	 *
	 * @see Ticket::FLAG constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getFlagType() {
		return $this->flag_type;
	}

	/**
	 * Return display identifier of the ticket.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayId() {
		return $this->display_id;
	}

	/**
	 * Returns identifier of department associated with the ticket.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDepartmentId() {
		return $this->department_id;
	}

	/**
	 * Sets identifier of department the ticket belongs to.
	 *
	 * @param int $department_id Department identifier.
	 * @return Ticket
	 */
	public function setDepartmentId($department_id) {
		$this->department_id = Helper::assurePositiveInt($department_id);
		$this->department = null;
		return $this;
	}

	/**
	 * Returns department object associated with the ticket.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Department
	 */
	public function getDepartment($reload = false) {
		if ($this->department !== null && !$reload)
			return $this->department;

		if ($this->department_id === null)
			return null;

		$this->department = Department::get($this->department_id);
		return $this->department;
	}

	/**
	 * Sets the department the ticket belongs to.
	 *
	 * @param Department $department Department.
	 * @return Ticket
	 */
	public function setDepartment($department) {
		$this->department = Helper::assureObject($department, Department::class);
		$this->department_id = $this->department !== null ? $this->department->getId() : null;
		return $this;
	}

	/**
	 * Returns identifier of this ticket status.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getStatusId() {
		return $this->status_id;
	}

	/**
	 * Sets identifier of this ticket status.
	 *
	 * @param int $ticket_status_id Ticket status identifier.
	 * @return Ticket
	 */
	public function setStatusId($ticket_status_id) {
		$this->status_id = Helper::assurePositiveInt($ticket_status_id);
		$this->status = null;
		return $this;
	}

	/**
	 * Returns ticket status.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TicketStatus
	 */
	public function getStatus($reload = false) {
		if ($this->status !== null && !$reload)
			return $this->status;

		if ($this->status_id === null)
			return null;

		$this->status = TicketStatus::get($this->status_id);
		return $this->status;
	}

	/**
	 * Sets ticket status.
	 *
	 * @param TicketStatus $ticket_status Ticket status.
	 * @return Ticket
	 */
	public function setStatus($ticket_status) {
		$this->status = Helper::assureObject($ticket_status, TicketStatus::class);
		$this->status_id = $this->status !== null ? $this->status->getId() : null;
		return $this;
	}

	/**
	 * Returns identifier of this ticket priority.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getPriorityId() {
		return $this->priority_id;
	}

	/**
	 * Sets identifier of this ticket priority.
	 * @param int $ticket_priority_id Ticket priority identifier.
	 * @return Ticket
	 */
	public function setPriorityId($ticket_priority_id) {
		$this->priority_id = Helper::assurePositiveInt($ticket_priority_id);
		$this->priority = null;
		return $this;
	}

	/**
	 * Returns this ticket priority.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TicketPriority
	 */
	public function getPriority($reload = false) {
		if ($this->priority !== null && !$reload)
			return $this->priority;

		if ($this->priority_id === null)
			return null;

		$this->priority = TicketPriority::get($this->priority_id);
		return $this->priority;
	}

	/**
	 * Sets this ticket priority.
	 *
	 * @param TicketPriority $ticket_priority
	 * @return Ticket
	 */
	public function setPriority($ticket_priority) {
		$this->priority = Helper::assureObject($ticket_priority, TicketPriority::class);
		$this->priority_id = $this->priority !== null ? $this->priority->getId() : null;
		return $this;
	}

	/**
	 * Returns identifier of this ticket type.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTypeId() {
		return $this->type_id;
	}

	/**
	 * Sets identifier of this ticket type.
	 *
	 * @param int $ticket_type_id Ticket type identifier.
	 * @return Ticket
	 */
	public function setTypeId($ticket_type_id) {
		$this->type_id = Helper::assurePositiveInt($ticket_type_id);
		$this->type = null;
		return $this;
	}

	/**
	 * Returns this ticket type.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return TicketType
	 */
	public function getType($reload = false) {
		if ($this->type !== null && !$reload)
			return $this->type;

		if ($this->type_id === null)
			return null;

		$this->type = TicketType::get($this->type_id);
		return $this->type;
	}

	/**
	 * Sets this ticket type.
	 *
	 * @param TicketType $ticket_type Ticket type.
	 * @return Ticket
	 */
	public function setType($ticket_type) {
		$this->type = Helper::assureObject($ticket_type, TicketType::class);
		$this->type_id = $this->type !== null ? $this->type->getId() : null;
		return $this;
	}

	/**
	 * Returns identifier of user, the creator of this ticket.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Sets identifier of user, the creator of this ticket.
	 *
	 * @param int $user_id User identifier.
	 * @return Ticket
	 */
	public function setUserId($user_id) {
		$this->user_id = Helper::assurePositiveInt($user_id);
		$this->creator = $this->user_id > 0 ? self::CREATOR_USER : null;
		$this->user = $this->user_id > 0 ? User::get($this->user_id) : null;
		if ($this->user !== null) {
			$this->full_name = $this->user->getFullName();
			$this->email = $this->user->getEmail();
		}

		$this->staff_id = null;
		$this->staff = null;
		return $this;
	}

	/**
	 * Returns user, the creator of this ticket.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return User
	 */
	public function getUser($reload = false) {
		if ($this->user !== null && !$reload)
			return $this->user;

		if ($this->user_id === null)
			return null;

		$this->user = User::get($this->user_id);
		return $this->user;
	}

	/**
	 * Sets user, the creator of this post.
	 *
	 * @param User $user User.
	 * @return TicketPost
	 */
	public function setUser($user) {
		$this->user = Helper::assureObject($user, User::class);
		$this->user_id = $this->user !== null ? $this->user->getId() : null;
		$this->creator = $this->user !== null ? self::CREATOR_USER : null;
		if ($this->user !== null) {
			$this->full_name = $this->user->getFullName();
			$this->email = $this->user->getEmail();
		}

		$this->staff_id = null;
		$this->staff = null;
		return $this;
	}

	/**
	 * Returns name of organization the user who created the ticket belongs to.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getUserOrganizationName() {
		return $this->user_organization_name;
	}

	/**
	 * Returns identifier of organization the user who created the ticket belongs to.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserOrganizationId() {
		return $this->user_organization_id;
	}

	/**
	 * Return organization the user who created the ticket belongs to.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return UserOrganization
	 */
	public function getUserOrganization($reload = false) {
		if ($this->user_organization !== null && !$reload)
			return $this->user_organization;

		if ($this->user_organization_id === null)
			return null;

		$this->user_organization = UserOrganization::get($this->user_organization_id);
		return $this->user_organization;
	}

	/**
	 * Sets identifier of staff user, the creator of this ticket.
	 *
	 * @param int $staff_id Staff user identifier.
	 * @return TicketPost
	 */
	public function setStaffId($staff_id) {
		$this->staff_id = Helper::assurePositiveInt($staff_id);
		$this->creator = $this->staff_id > 0 ? self::CREATOR_STAFF : null;
		$this->staff = $this->staff_id > 0 ? Staff::get($this->staff_id) : null;
		if ($this->staff !== null) {
			$this->full_name = $this->staff->getFullName();
			$this->email = $this->staff->getEmail();
		}

		$this->user_id = null;
		$this->user = null;
		return $this;
	}

	/**
	 * Sets staff user, the creator of this ticket.
	 *
	 * @param Staff $staff Staff user.
	 * @return TicketPost
	 */
	public function setStaff($staff) {
		$this->staff = Helper::assureObject($staff, Staff::class);
		$this->staff_id = $this->staff !== null ? $this->staff->getId() : null;
		$this->creator = $this->staff !== null ? self::CREATOR_STAFF : null;
		if ($this->staff !== null) {
			$this->full_name = $this->staff->getFullName();
			$this->email = $this->staff->getEmail();
		}

		$this->user_id = null;
		$this->user = null;
		return $this;
	}

	/**
	 * Returns full name of the staff user, owner of this ticket.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getOwnerStaffName() {
		return $this->owner_staff_name;
	}

	/**
	 * Returns identifier of the staff user, owner of this ticket.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getOwnerStaffId() {
		return $this->owner_staff_id;
	}

	/**
	 * Sets identifier of the staff user, owner of this ticket.
	 *
	 * @param int $owner_staff_id Staff user identifier.
	 * @return Ticket
	 */
	public function setOwnerStaffId($owner_staff_id) {
		$this->owner_staff_id = Helper::assurePositiveInt($owner_staff_id);
		$this->owner_staff = null;
		return $this;
	}

	/**
	 * Return staff user, owner of this ticket.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Staff
	 */
	public function getOwnerStaff($reload = false) {
		if ($this->owner_staff !== null && !$reload)
			return $this->owner_staff;

		if ($this->owner_staff_id === null)
			return null;

		$this->owner_staff = Staff::get($this->owner_staff_id);
		return $this->owner_staff;
	}

	/**
	 * Sets staff user, owner of this ticket.
	 *
	 * @param Staff $owner_staff Staff user.
	 * @return Ticket
	 */
	public function setOwnerStaff($owner_staff) {
		$this->owner_staff = Helper::assureObject($owner_staff, Staff::class);
		$this->owner_staff_id = $this->owner_staff !== null ? $this->owner_staff->getId() : null;
		return $this;
	}

	/**
	 * Returns the creator full name.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getFullName() {
		return $this->full_name;
	}

	/**
	 * Sets the creator full name.
	 *
	 * @param string $full_name Creator full name.
	 * @return Ticket
	 */
	public function setFullName($full_name) {
		$this->full_name = Helper::assureString($full_name);
		return $this;
	}

	/**
	 * Return the creator e-mail address.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the creator e-mail address.
	 *
	 * @param string $email Creator e-mail.
	 * @return Ticket
	 */
	public function setEmail($email) {
		$this->email = Helper::assureString($email);
		return $this;
	}

	/**
	 * Returns full name of the last person replied to this ticket.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastReplier() {
		return $this->last_replier;
	}

	/**
	 * Returns the subject of this ticket.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets the subject of this ticket.
	 *
	 * @param string $subject Ticket subject.
	 * @return Ticket
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Returns date and time this ticket was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreationTime($format = null) {
		if ($this->creation_time == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->creation_time);
	}

	/**
	 * Returns date and time of last activity on this ticket.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastActivity($format = null) {
		if ($this->last_activity == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->last_activity);
	}

	/**
	 * Returns date and time of last staff user reply to this ticket.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastStaffReply($format = null) {
		if ($this->last_staff_reply == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->last_staff_reply);
	}

	/**
	 * Returns date and time of last user reply to this ticket.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getLastUserReply($format = null) {
		if ($this->last_user_reply == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->last_user_reply);
	}

	/**
	 * Returns Service Level Agreement plan identifier.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getSLAPlanId() {
		return $this->sla_plan_id;
	}

	/**
	 * Returns next ticket reply due date and time.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getNextReplyDue($format = null) {
		if ($this->next_reply_due == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->next_reply_due);
	}

	/**
	 * Returns ticket resolution due date and time.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getResolutionDue($format = null) {
		if ($this->resolution_due == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->resolution_due);
	}

	/**
	 * Returns replies count.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getReplies() {
		return $this->replies;
	}

	/**
	 * Returns IP address from which this post was created.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 * Returns creator type.
	 *
	 * @see Ticket::CREATOR constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorType() {
		return $this->creator;
	}

	/**
	 * Returns creation mode.
	 *
	 * @see Ticket::CREATION_MODE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreationMode() {
		return $this->creation_mode;
	}

	/**
	 * Returns creation type.
	 *
	 * @see Ticket::CREATION_TYPE constants.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreationType() {
		return $this->creation_type;
	}

	/**
	 * Sets the creation type for this ticket.
	 *
	 * @see Ticket::CREATION_TYPE constants.
	 *
	 * @param int $creation_type Creation type.
	 * @return Ticket
	 */
	public function setCreationType($creation_type) {
		$this->creation_type = Helper::assureConstant($creation_type, $this, 'CREATION_TYPE');
		return $this;
	}

	/**
	 * Returns whether this ticket has escalated.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsEscalated() {
		return $this->is_escalated;
	}

	/**
	 * Returns escalation rule identifier.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getEscalationRuleId() {
		return $this->escalation_rule_id;
	}

	/**
	 * Returns ticket tags.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * Returns template group identifier.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getTemplateGroupId() {
		return $this->template_group_id;
	}

	/**
	 * Sets the template group identifier.
	 * Resets template group name.
	 *
	 * @param int $template_group_id Template group identifier.
	 * @return Ticket
	 */
	public function setTemplateGroupId($template_group_id) {
		$this->template_group_id = Helper::assurePositiveInt($template_group_id);
		$this->template_group_name = null;
		return $this;
	}

	/**
	 * Returns template group name.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTemplateGroupName() {
		return $this->template_group_name;
	}

	/**
	 * Sets the template group name.
	 * Resets template group identifier.
	 *
	 * @param string $template_group_name Template group name.
	 * @return Ticket
	 */
	public function setTemplateGroupName($template_group_name) {
		$this->template_group_name = Helper::assureString($template_group_name);
		$this->template_group_id = null;
		return $this;
	}

	/**
	 * Sets the template group. You can provide name or identifier.
	 *
	 * @param string|int $template_group_id_or_name Template group name or identifier.
	 * @return Ticket
	 */
	public function setTemplateGroup($template_group_id_or_name) {
		if (is_numeric($template_group_id_or_name)) {
			$this->setTemplateGroupId($template_group_id_or_name);
		} else {
			$this->setTemplateGroupName($template_group_id_or_name);
		}
		return $this;
	}

	/**
	 * Sets whether to ignore (disable) autoresponder e-mail.
	 *
	 * @param bool $ignore_auto_responder Whether to ignore (disable) autoresponder e-mail.
	 * @return Ticket
	 */
	public function setIgnoreAutoResponder($ignore_auto_responder) {
		$this->ignore_auto_responder = Helper::assureBool($ignore_auto_responder);
		return $this;
	}

	/**
	 * Returns tickets watchers.
	 * Format of returned array:
	 * <pre>
	 * array(
	 *   array(
	 *     'staff_id' => <staff identifier>,
	 *     'name' => '<staff full name>'
	 *   ),
	 *   ...
	 * )
	 * </pre>
	 *
	 * @return array
	 */
	public function getWatchers() {
		return Helper::assureArray($this->watchers);
	}

	/**
	 * Returns tickets workflows.
	 * Format of returned array:
	 * <pre>
	 * array(
	 *   array(
	 *     'id' => <workflow identifier>,
	 *     'title' => '<workflow title>'
	 *   ),
	 *   ...
	 * )
	 * </pre>
	 *
	 * @return array
	 */
	public function getWorkflows() {
		return Helper::assureArray($this->workflows);
	}

	/**
	 * Returns list of ticket notes.
	 *
	 * Result is cached till the end of script.
	 *
	 * @param bool $reload True to reload notes from server.
	 * @return ResultSet
	 */
	public function getNotes($reload = false) {
		if ($this->notes === null || $reload) {
			$this->notes = TicketNote::getAll($this->getId())->getRawArray();
		}
		return new ResultSet($this->notes);
	}

	/**
	 * Returns list of ticket time tracks.
	 *
	 * Result is cached till the end of script.
	 *
	 * @param bool $reload True to reload time tracks from server.
	 * @return ResultSet
	 */
	public function getTimeTracks($reload = false) {
		if ($this->time_tracks === null || $reload) {
			$this->time_tracks = TicketTimeTrack::getAll($this->getId())->getRawArray();
		}
		return new ResultSet($this->time_tracks);
	}

	/**
	 * Returns list of ticket posts.
	 *
	 * Result is cached till the end of script.
	 *
	 * @param bool $reload True to reload posts from server.
	 * @return ResultSet
	 */
	public function getPosts($reload = false) {
		if ($this->posts === null || $reload) {
			$this->posts = TicketPost::getAll($this->getId())->getRawArray();
		}
		return new ResultSet($this->posts);
	}

	/**
	 * Returns first post of this ticket.
	 *
	 * @return TicketPost
	 */
	public function getFirstPost() {
		return $this->getPosts()->first();
	}

	/**
	 * Returns list of attachments in all posts of this ticket.
	 *
	 * Result is cached till the end of script.
	 *
	 * @param bool $reload True to reload attachments from server.
	 * @return TicketAttachment[]
	 */
	public function getAttachments($reload = false) {
		if ($this->attachments === null || $reload) {
			$this->attachments = TicketAttachment::getAll($this->id)->getRawArray();
		}
		return new ResultSet($this->attachments);
	}

	/**
	 * Sets the contents of this ticket (first ticket post).
	 *
	 * @param string $contents Ticket contents.
	 * @return Ticket
	 */
	public function setContents($contents) {
		$this->contents = Helper::assureString($contents);
		return $this;
	}

	/**
	 * Generic method for creating new ticket (without setting creator).
	 *
	 * @param Department $department Department where new ticket will be created.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return Ticket
	 */
	static private function createNewGeneric($department, $contents, $subject) {
		$new_ticket = new Ticket();
		$new_ticket->setStatusId(self::$default_status_id);
		$new_ticket->setPriorityId(self::$default_priority_id);
		$new_ticket->setTypeId(self::$default_type_id);
		$new_ticket->setDepartment($department);
		$new_ticket->setSubject($subject);
		$new_ticket->setContents($contents);
		return $new_ticket;
	}

	/**
	 * Creates new ticket with implicit user or staff as creator.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param Department $department Department where new ticket will be created.
	 * @param User|Staff $creator Creator (User or Staff) of new ticket.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return Ticket
	 */
	static public function createNew(Department $department, $creator, $contents, $subject) {
		$new_ticket = self::createNewGeneric($department, $contents, $subject);
		$new_ticket->setCreator($creator);
		return $new_ticket;
	}

	/**
	 * Creates new ticket with creator user automatically created by server using provided name and e-mail.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param Department $department Department where new ticket will be created.
	 * @param string $creator_full_name Creator full name.
	 * @param string $creator_email Creator e-mail.
	 * @param string $contents Contents of the first post.
	 * @param string $subject Subject of new ticket.
	 * @return Ticket
	 */
	static public function createNewAuto(Department $department, $creator_full_name, $creator_email, $contents, $subject) {
		$new_ticket = self::createNewGeneric($department, $contents, $subject);
		$new_ticket->setCreatorAuto($creator_full_name, $creator_email);
		return $new_ticket;
	}

	/**
	 * Creates new post in this ticket.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param User|Staff $creator Creator (User or Staff) of new post.
	 * @param string $contents Contents of new post.
	 * @return TicketPost
	 */
	public function newPost($creator, $contents) {
		return TicketPost::createNew($this, $creator, $contents);
	}

	/**
	 * Creates new note in this ticket.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param Staff $creator Creator (staff) of new note.
	 * @param string $contents Contents of new note.
	 * @return TicketNote
	 */
	public function newNote(Staff $creator, $contents) {
		return TicketNote::createNew($this, $creator, $contents);
	}

	/**
	 * Creates new ticket time track for this ticket.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $contents Note contents.
	 * @param Staff $staff Staff user - both creator and worker.
	 * @param string $time_worked Worked time formatted as hh:mm. Work date will be set to current datetime.
	 * @param string $time_billable Billable time formatted as hh:mm. Bill date will be set to current datetime.
	 * @return TicketTimeTrack
	 */
	public function newTimeTrack($contents, Staff $staff, $time_worked, $time_billable) {
		return TicketTimeTrack::createNew($this, $contents, $staff, $time_worked, $time_billable);
	}

	/**
	 * Returns statistics for all tickets in database. Result is cached.
	 * Format or result:
	 * <pre>
	 *    array(
	 *        'departments' => array( //statistics per department (if there are no tickets in department then there will be no record with its id here)
	 *            <department id> => array( //tickets assigned to department with this id
	 *                'last_activity' => <date and time of last activity on tickets in this department>,
	 *                'total_items' => <total amount of tickets in this department>,
	 *                'total_unresolved_items' => <total amount of unresolved tickets in this department>,
	 *                'ticket_statuses' => array( //statistics per ticket status in the department
	 *                    <ticket status id> => array(
	 *                        'last_activity' => <date and time of last activity on tickets with this status in this department>,
	 *                        'total_items' => <total amount of tickets with this status in this department>
	 *                    ),
	 *                    ...
	 *                ),
	 *                'ticket_types' => array( //statistics per ticket type in the department
	 *                    <ticket type id> => array(
	 *                        'last_activity' => <date and time of last activity on tickets of this type in this department>,
	 *                        'total_items' => <total amount of tickets of this type in this department>,
	 *                        'total_unresolved_items' => <total amount of unresolved tickets of this type in this department>,
	 *                    ),
	 *                    ...,
	 *                    'unknown' => array(  //in Kayako 4.01.204 all ticket types will be unknown because of a bug (http://dev.kayako.com/browse/SWIFT-1465)
	 *                        ...
	 *                    )
	 *                )
	 *                'ticket_owners' => array( //statistics per ticket owner in the department
	 *                    <owner staff id> => array(
	 *                        'last_activity' => <date and time of last activity on tickets assigned to this staff in this department>,
	 *                        'total_items' => <total amount of tickets assigned to this staff in this department>,
	 *                        'total_unresolved_items' => <total amount of unresolved tickets assigned to this staff in this department>,
	 *                    ),
	 *                    ...,
	 *                    'unassigned' => array(  //tickets not assigned to any staff
	 *                        ...
	 *                    )
	 *                )
	 *            ),
	 *            ...,
	 *            'unknown' => array( //tickets in Trash
	 *                ...
	 *            )
	 *        ),
	 *        'ticket_statuses' => array( //statistics per ticket status in all departments
	 *            <ticket status id> => array(
	 *                'last_activity' => <date and time of last activity on tickets with this status in all departments>,
	 *                'total_items' => <total amount of tickets with this status in all departments>
	 *            ),
	 *            ...
	 *        ),
	 *        'ticket_owners' => array( //statistics per ticket owner in all departments
	 *            <owner staff id> => array(
	 *                'last_activity' => <date and time of last activity on tickets assigned to this staff in all department>,
	 *                'total_items' => <total amount of tickets assigned to this staff in all department>,
	 *                'total_unresolved_items' => <total amount of unresolved tickets assigned to this staff in all department>,
	 *            ),
	 *            ...,
	 *            'unassigned' => array(  //tickets not assigned to any staff no matter what department
	 *                ...
	 *            )
	 *        )
	 *    )
	 * </pre>
	 *
	 * @param bool $reload True to reload statistics data from server.
	 * @return array
	 */
	static public function getStatistics($reload = false) {
		if (self::$statistics !== null && !$reload)
			return self::$statistics;

		self::$statistics = array('departments' => array(), 'ticket_statuses' => array(), 'ticket_owners' => array());
		$raw_stats = self::getRESTClient()->get('/Tickets/TicketCount', array());

		foreach ($raw_stats['departments'][0]['department'] as $department_raw_stats) {
			$department_id = intval($department_raw_stats['_attributes']['id']);

			$department_stats = array();
			$department_stats['last_activity'] = intval($department_raw_stats['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $department_raw_stats['lastactivity']) : null;
			$department_stats['total_items'] = $department_raw_stats['totalitems'];
			$department_stats['total_unresolved_items'] = $department_raw_stats['totalunresolveditems'];

			foreach ($department_raw_stats['ticketstatus'] as $ticket_status_raw_stats) {
				$ticket_status_id = intval($ticket_status_raw_stats['_attributes']['id']);

				$ticket_status_stats = array();
				$ticket_status_stats['last_activity'] = intval($ticket_status_raw_stats['_attributes']['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $ticket_status_raw_stats['_attributes']['lastactivity']) : null;
				$ticket_status_stats['total_items'] = $ticket_status_raw_stats['_attributes']['totalitems'];

				$department_stats['ticket_statuses'][$ticket_status_id] = $ticket_status_stats;
			}

			//this is broken in Kayako 4.01.240, tickettype id is always 0 (unknown) - http://dev.kayako.com/browse/SWIFT-1465
			foreach ($department_raw_stats['tickettype'] as $ticket_type_raw_stats) {
				$ticket_type_id = intval($ticket_type_raw_stats['_attributes']['id']);

				$ticket_type_stats = array();
				$ticket_type_stats['last_activity'] = intval($ticket_type_raw_stats['_attributes']['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $ticket_type_raw_stats['_attributes']['lastactivity']) : null;
				$ticket_type_stats['total_items'] = $ticket_type_raw_stats['_attributes']['totalitems'];
				$ticket_type_stats['total_unresolved_items'] = $ticket_type_raw_stats['_attributes']['totalunresolveditems'];

				$department_stats['ticket_types'][$ticket_type_id > 0 ? $ticket_type_id : 'unknown'] = $ticket_type_stats;
			}

			foreach ($department_raw_stats['ownerstaff'] as $owner_staff_raw_stats) {
				$staff_id = intval($owner_staff_raw_stats['_attributes']['id']);

				$owner_staff_stats = array();
				$owner_staff_stats['last_activity'] = intval($owner_staff_raw_stats['_attributes']['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $owner_staff_raw_stats['_attributes']['lastactivity']) : null;
				$owner_staff_stats['total_items'] = $owner_staff_raw_stats['_attributes']['totalitems'];
				$owner_staff_stats['total_unresolved_items'] = $owner_staff_raw_stats['_attributes']['totalunresolveditems'];

				$department_stats['ticket_owners'][$staff_id > 0 ? $staff_id : 'unassigned'] = $owner_staff_stats;
			}

			//unknown department is for example for tickets in Trash
			self::$statistics['departments'][$department_id > 0 ? $department_id : 'unknown'] = $department_stats;
		}

		foreach ($raw_stats['statuses'][0]['ticketstatus'] as $ticket_status_raw_stats) {
			$ticket_status_id = intval($ticket_status_raw_stats['_attributes']['id']);

			$ticket_status_stats = array();
			$ticket_status_stats['last_activity'] = intval($ticket_status_raw_stats['_attributes']['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $ticket_status_raw_stats['_attributes']['lastactivity']) : null;
			$ticket_status_stats['total_items'] = $ticket_status_raw_stats['_attributes']['totalitems'];

			self::$statistics['ticket_statuses'][$ticket_status_id] = $ticket_status_stats;
		}

		foreach ($raw_stats['owners'][0]['ownerstaff'] as $owner_staff_raw_stats) {
			$staff_id = intval($owner_staff_raw_stats['_attributes']['id']);

			$owner_staff_stats = array();
			$owner_staff_stats['last_activity'] = intval($owner_staff_raw_stats['_attributes']['lastactivity']) > 0 ? date(Config::get()->getDatetimeFormat(), $owner_staff_raw_stats['_attributes']['lastactivity']) : null;
			$owner_staff_stats['total_items'] = $owner_staff_raw_stats['_attributes']['totalitems'];
			$owner_staff_stats['total_unresolved_items'] = $owner_staff_raw_stats['_attributes']['totalunresolveditems'];

			self::$statistics['ticket_owners'][$staff_id > 0 ? $staff_id : 'unassigned'] = $owner_staff_stats;
		}
		return self::$statistics;
	}
}
