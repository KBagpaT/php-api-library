<?php
namespace Kayako\Api\Client\Object\Ticket;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Config;
use Kayako\Api\Client\Exception\BadMethodCallException;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\Base\ObjectBase;
use Kayako\Api\Client\Object\Staff\Staff;
use Kayako\Api\Client\Object\User\User;
use Kayako\Api\Client\Object\User\UserOrganization;

/**
 * Kayako TicketNote object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+TicketNote
 * @since Kayako version 4.01.240
 */
class TicketNote extends ObjectBase {

	/**
	 * Color of note - yellow.
	 *
	 * @var int
	 */
	const COLOR_YELLOW = 1;

	/**
	 * Color of note - purple.
	 *
	 * @var int
	 */
	const COLOR_PURPLE = 2;

	/**
	 * Color of note - blue.
	 *
	 * @var int
	 */
	const COLOR_BLUE = 3;

	/**
	 * Color of note - green.
	 *
	 * @var int
	 */
	const COLOR_GREEN = 4;

	/**
	 * Color of note - red.
	 *
	 * @var int
	 */
	const COLOR_RED = 5;

	/**
	 * Note type - connected to ticket.
	 *
	 * @var string
	 */
	const TYPE_TICKET = 'ticket';

	/**
	 * Note type - connected to user.
	 *
	 * @var string
	 */
	const TYPE_USER = 'user';

	/**
	 * Note type - connected to user organization.
	 *
	 * @var string
	 */
	const TYPE_USER_ORGANIZATION = 'userorganization';

	static protected $controller = '/Tickets/TicketNote';
	static protected $object_xml_name = 'note';

	/**
	 * Ticket note identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Ticket identifier - if this note is associated with ticket.
	 * @apiField required_create=true
	 * @var int
	 */
	protected $ticket_id;

	/**
	 * User identifier - if this note is associated with user who created the ticket.
	 * @apiField
	 * @var int
	 */
	protected $user_id;

	/**
	 * User organization identifier - if this note is associated with user organization of user who created the ticket.
	 * @apiField
	 * @var int
	 */
	protected $user_organization_id;

	/**
	 * Note type.
	 *
	 * @see TicketNote::TYPE constants.
	 *
	 * @apiField
	 * @var string
	 */
	protected $type = self::TYPE_TICKET;

	/**
	 * Ticket note color.
	 *
	 * @see TicketNote::COLOR constants.
	 *
	 * @apiField
	 * @var int
	 */
	protected $note_color;

	/**
	 * Identifier of staff user who created this note.
	 * @apiField alias=staffid
	 * @var int
	 */
	protected $creator_staff_id;

	/**
	 * Full name of staff user who created this note.
	 * @apiField getter=getCreatorName setter=setCreator alias=fullname
	 * @var string
	 */
	protected $creator_staff_name;

	/**
	 * Identifier staff user that this note is intended for.
	 * @apiField
	 * @var int
	 */
	protected $for_staff_id;

	/**
	 * Timestamp of when this ticket note was created.
	 * @apiField
	 * @var int
	 */
	protected $creation_date;

	/**
	 * Ticket note contents.
	 * @apiField required_create=true
	 * @var string
	 */
	protected $contents;

	/**
	 * Staff user who created this note.
	 * @var Staff
	 */
	private $creator_staff = null;

	/**
	 * Staff user that this note is intended for.
	 * @var Staff
	 */
	private $for_staff = null;

	/**
	 * Ticket - if this note is associated with ticket.
	 * @var Ticket
	 */
	private $ticket = null;

	/**
	 * User - if this note is associated with user who created the ticket.
	 * @var User
	 */
	private $user = null;

	/**
	 * User organization - if this note is associated with user organization of user who created the ticket.
	 * @var UserOrganization
	 */
	private $user_organization = null;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->type = $data['_attributes']['type'];
		$this->note_color = intval($data['_attributes']['notecolor']);
		$this->creator_staff_id = Helper::assurePositiveInt($data['_attributes']['creatorstaffid']);
		$this->creator_staff_name = $data['_attributes']['creatorstaffname'];
		$this->for_staff_id = Helper::assurePositiveInt($data['_attributes']['forstaffid']);
		$this->creation_date = Helper::assurePositiveInt($data['_attributes']['creationdate']);
		$this->contents = $data['_contents'];

		if ($this->getType() === TicketNote::TYPE_TICKET) {
			$this->ticket_id = Helper::assurePositiveInt($data['_attributes']['ticketid']);
		} elseif ($this->getType() === TicketNote::TYPE_USER) {
			$this->user_id = Helper::assurePositiveInt($data['_attributes']['userid']);
		} elseif ($this->getType() === TicketNote::TYPE_USER_ORGANIZATION) {
			$this->user_organization_id = Helper::assurePositiveInt($data['_attributes']['userorganizationid']);
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['ticketid'] = $this->ticket_id;
		$data['notecolor'] = $this->note_color;
		if (is_numeric($this->creator_staff_id)) {
			$data['staffid'] = $this->creator_staff_id;
		} elseif (strlen($this->creator_staff_name) > 0) {
			$data['fullname'] = $this->creator_staff_name;
		} else {
			throw new GeneralException("Value for API fields 'staffid' or 'fullname' is required for this operation to complete.");
		}
		$data['forstaffid'] = $this->for_staff_id;
		$data['contents'] = $this->contents;

		return $data;
	}

	/**
	 * Returns all notes of the ticket.
	 *
	 * @param Ticket|int $ticket Ticket object or ticket identifier.
	 * @return ResultSet|TicketNote[]
	 */
	static public function getAll($ticket) {
		if ($ticket instanceof Ticket) {
			$ticket_id = $ticket->getId();
		} else {
			$ticket_id = $ticket;
		}

		$search_parameters = array('ListAll');

		$search_parameters[] = $ticket_id;

		return parent::genericGetAll($search_parameters);
	}

	/**
	 * Returns ticket note.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @param int $id Ticket note identifier.
	 * @return TicketNote
	 */
	static public function get($ticket_id, $id) {
		return parent::genericGet(array($ticket_id, $id));
	}

	public function create() {
		if ($this->getType() !== self::TYPE_TICKET)
			throw new BadMethodCallException('You can create only note of type "ticket"');

		parent::create();
	}

	public function update() {
		throw new BadMethodCallException("You can't update objects of type TicketNote.");
	}

	public function delete() {
		if ($this->getType() !== self::TYPE_TICKET)
			throw new BadMethodCallException('You can delete only note of type "ticket"');

		self::getRESTClient()->delete(static::$controller, array($this->ticket_id, $this->id));
	}

	public function toString() {
		return sprintf("%s (type: %s)", substr($this->getContents(), 0, 50) . (strlen($this->getContents()) > 50 ? '...' : ''), $this->getType());
	}

	public function getId($complete = false) {
		switch ($this->getType()) {
			case self::TYPE_USER:
				return $complete ? array($this->user_id, $this->id) : $this->id;
			case self::TYPE_USER_ORGANIZATION:
				return $complete ? array($this->user_organization_id, $this->id) : $this->id;
			default:
				return $complete ? array($this->ticket_id, $this->id) : $this->id;
		}
	}

	/**
	 * Returns identifier of ticket that this note is connected with.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_TICKET.
	 *
	 * @return int
	 */
	public function getTicketId() {
		if ($this->getType() !== self::TYPE_TICKET)
			return null;

		return $this->ticket_id;
	}

	/**
	 * Sets identifier of the ticket that this note will be connected with.
	 *
	 * @param int $ticket_id Ticket identifier.
	 * @return TicketNote
	 */
	public function setTicketId($ticket_id) {
		$this->ticket_id = intval($ticket_id) > 0 ? intval($ticket_id) : null;
		$this->ticket = null;
		$this->type = $this->ticket_id !== null ? self::TYPE_TICKET : null;
		return $this;
	}

	/**
	 * Returns the ticket that this note is connected with.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_TICKET.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Ticket
	 */
	public function getTicket($reload = false) {
		if ($this->getType() !== self::TYPE_TICKET)
			return null;

		if ($this->ticket !== null && !$reload)
			return $this->ticket;

		if ($this->ticket_id === null || $this->ticket_id <= 0)
			return null;

		$this->ticket = Ticket::get($this->ticket_id);
		return $this->ticket;
	}

	/**
	 * Sets the ticket that the note will be connected with.
	 *
	 * @param Ticket $ticket
	 * @return TicketNote
	 */
	public function setTicket(Ticket $ticket) {
		$this->ticket = $ticket instanceof Ticket ? $ticket : null;
		$this->ticket_id = $this->ticket !== null ? $this->ticket->getId() : null;
		$this->type = $this->ticket !== null ? self::TYPE_TICKET : null;
		return $this;
	}

	/**
	 * Returns identifier of the user that this note is connected to.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_USER.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserId() {
		if ($this->getType() !== self::TYPE_USER)
			return null;

		return $this->user_id;
	}

	/**
	 * Return the user that this note is connected to.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_USER.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return User
	 */
	public function getUser($reload = false) {
		if ($this->getType() !== self::TYPE_USER)
			return null;

		if ($this->user !== null && !$reload)
			return $this->user;

		if ($this->user_id === null || $this->user_id <= 0)
			return null;

		$this->user = User::get($this->user_id);
		return $this->user;
	}

	/**
	 * Returns identifier of the user organization that this note is connecte with.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_USER_ORGANIZATION.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getUserOrganizationId() {
		if ($this->getType() !== self::TYPE_USER_ORGANIZATION)
			return null;

		return $this->user_organization_id;
	}

	/**
	 * Returns the user organization that this note is connecte with.
	 *
	 * Applicable only for notes of type TicketNote::TYPE_USER_ORGANIZATION.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return UserOrganization
	 */
	public function getUserOrganization($reload = false) {
		if ($this->getType() !== self::TYPE_USER_ORGANIZATION)
			return null;

		if ($this->user_organization !== null && !$reload)
			return $this->user_organization;

		if ($this->user_organization_id === null || $this->user_organization_id <= 0)
			return null;

		$this->user_organization = UserOrganization::get($this->user_organization_id);
		return $this->user_organization;
	}

	/**
	 * Returns type of this ticket.
	 *
	 * @see TicketNote::TYPE constants.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns this ticket note color.
	 *
	 * @see TicketNote::COLOR constants.
	 *
	 * @return int
	 * @filterBy
	 */
	public function getNoteColor() {
		return $this->note_color;
	}

	/**
	 * Sets the color of this ticket note.
	 *
	 * @see TicketNote::COLOR constants.
	 *
	 * @param int $note_color Note color.
	 * @return TicketNote
	 */
	public function setNoteColor($note_color) {
		$this->note_color = $note_color;
		return $this;
	}

	/**
	 * Returns identifier of staff user who created this note.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorStaffId() {
		return $this->creator_staff_id;
	}

	/**
	 * Returns staff who created this note.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Staff
	 */
	public function getCreatorStaff($reload = false) {
		if ($this->creator_staff !== null && !$reload)
			return $this->creator_staff;

		if ($this->creator_staff_id === null)
			return null;

		$this->creator_staff = Staff::get($this->creator_staff_id);
		return $this->creator_staff;
	}

	/**
	 * Returns name of the creator of this ticket note.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreatorName() {
		return $this->creator_staff_name;
	}

	/**
	 * Sets creator of this note.
	 *
	 * @param Staff|int|string $creator Staff OR Staff identifier OR creator name (if the ticket is to be created without providing a staff user, ex: System messages, Alerts etc.).
	 * @return TicketNote
	 */
	public function setCreator($creator) {
		if ($creator instanceof Staff) {
			$this->creator_staff = $creator;
			$this->creator_staff_id = $creator->getId();
			$this->creator_staff_name = $creator->getFullName();
		} elseif (is_numeric($creator)) {
			$this->creator_staff = null;
			$this->creator_staff_id = intval($creator);
			$this->creator_staff_name = null;
		} else {
			$this->creator_staff = null;
			$this->creator_staff_id = null;
			$this->creator_staff_name = $creator !== null ? strval($creator) : null;
		}
		return $this;
	}

	/**
	 * Returns identifier of the staff who this note is for.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getForStaffId() {
		return $this->for_staff_id;
	}

	/**
	 * Sets identifier of the staff who this note is for.
	 *
	 * @param int $for_staff_id
	 * @return TicketNote
	 */
	public function setForStaffId($for_staff_id) {
		$this->for_staff_id = intval($for_staff_id) > 0 ? intval($for_staff_id) : null;
		$this->for_staff = null;
		return $this;
	}

	/**
	 * Returns the staff who this note is for.
	 *
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return Staff
	 */
	public function getForStaff($reload = false) {
		if ($this->for_staff !== null && !$reload)
			return $this->for_staff;

		if ($this->for_staff_id === null)
			return null;

		$this->for_staff = Staff::get($this->for_staff_id);
		return $this->for_staff;
	}

	/**
	 * Sets the staff who this note is for.
	 *
	 * @param Staff $for_staff
	 * @return TicketNote
	 */
	public function setForStaff($for_staff) {
		$this->for_staff = $for_staff instanceof Staff ? $for_staff : null;
		$this->for_staff_id = $this->for_staff !== null ? $this->for_staff->getId() : null;
		return $this;
	}

	/**
	 * Returns date and time this note was created.
	 *
	 * @see http://www.php.net/manual/en/function.date.php
	 *
	 * @param string $format Output format of the date. If null the format set in client configuration is used.
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getCreationDate($format = null) {
		if ($this->creation_date == null)
			return null;

		if ($format === null) {
			$format = Config::get()->getDatetimeFormat();
		}

		return date($format, $this->creation_date);
	}

	/**
	 * Returns ticket note contents.
	 *
	 * @return string
	 * @filterBy
	 */
	public function getContents() {
		return $this->contents;
	}

	/**
	 * Sets the ticket note contents.
	 *
	 * @param string $contents
	 * @return TicketNote
	 */
	public function setContents($contents) {
		$this->contents = strval($contents);
		return $this;
	}

	/**
	 * Creates new ticket note.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param Ticket $ticket Ticket in which to create the post.
	 * @param Staff $creator Creator (staff) of new note.
	 * @param string $contents Contents of new note.
	 * @return TicketNote
	 */
	static public function createNew(Ticket $ticket, Staff $creator, $contents) {
		$new_ticket_note = new TicketNote();

		$new_ticket_note->setTicketId($ticket->getId());
		$new_ticket_note->setCreator($creator);
		$new_ticket_note->setContents($contents);

		return $new_ticket_note;
	}
}