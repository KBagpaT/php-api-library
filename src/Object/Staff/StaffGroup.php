<?php
namespace Kayako\Api\Client\Object\Staff;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Object\Base\ObjectBase;

/**
 * Kayako StaffGroup object.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @link http://wiki.kayako.com/display/DEV/REST+-+StaffGroup
 * @since Kayako version 4.01.204
 */
class StaffGroup extends ObjectBase {

	static protected $controller = '/Base/StaffGroup';
	static protected $object_xml_name = 'staffgroup';

	/**
	 * Staff group identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Staff group title.
	 * @apiField required=true
	 * @var string
	 */
	protected $title;

	/**
	 * Is this administrator group.
	 * @apiField required_create=true
	 * @var bool
	 */
	protected $is_admin = false;

	protected function parseData($data) {
		$this->id = intval($data['id']);
		$this->title = $data['title'];
		$this->is_admin = Helper::assureBool($data['isadmin']);
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		$data['title'] = $this->title;
		$data['isadmin'] = $this->is_admin ? 1 : 0;

		return $data;
	}

	/**
	 * Fetches all staff groups from the server.
	 *
	 * @return ResultSet|StaffGroup[]
	 */
	static public function getAll() {
		return parent::genericGetAll();
	}

	/**
	 * Fetches staff group from the server by its identifier.
	 *
	 * @param int $id Staff group identifier.
	 * @return StaffGroup
	 */
	static public function get($id) {
		return parent::genericGet(array($id));
	}

	public function toString() {
		return sprintf("%s (isadmin: %s)", $this->getTitle(), $this->getIsAdmin() ? "yes" : "no");
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns title of the staff group.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets title of the staff group.
	 *
	 * @param string $title Title of the staff group.
	 * @return StaffGroup
	 */
	public function setTitle($title) {
		$this->title = Helper::assureString($title);
		return $this;
	}

	/**
	 * Returns whether staff members assigned to this group are Administrators.
	 *
	 * @return bool
	 * @filterBy
	 * @orderBy
	 */
	public function getIsAdmin() {
		return $this->is_admin;
	}

	/**
	 * Sets whether staff members assigned to this group are Administrators.
	 *
	 * @param bool $is_admin True, if you want staff members assigned to this group to be Administrators. False (default), otherwise.
	 * @return StaffGroup
	 */
	public function setIsAdmin($is_admin) {
		$this->is_admin = Helper::assureBool($is_admin);
		return $this;
	}

	/**
	 * Creates new staff group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $title Title of new staff group.
	 * @param bool $is_admin True, if you want staff members assigned to this group to be Administrators. False (default), otherwise.
	 * @return StaffGroup
	 */
	static public function createNew($title, $is_admin = false) {
		$new_staff_group = new StaffGroup();
		$new_staff_group->setTitle($title);
		$new_staff_group->setIsAdmin($is_admin);
		return $new_staff_group;
	}

	/**
	 * Creates new staff user in this staff group.
	 * WARNING: Data is not sent to Kayako unless you explicitly call create() on this method's result.
	 *
	 * @param string $first_name First name of new staff user.
	 * @param string $last_name Last name of new staff user.
	 * @param string $user_name Login username of new staff user.
	 * @param string $email E-mail address of new staff user.
	 * @param string $password Password for new staff user.
	 * @return Staff
	 */
	public function newStaff($first_name, $last_name, $user_name, $email, $password) {
		return Staff::createNew($first_name, $last_name, $user_name, $email, $this, $password);
	}
}