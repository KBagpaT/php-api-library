<?php
namespace Kayako\Api\Client\Object\Base;

use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Exception\BadMethodCallException;
use Kayako\Api\Client\Object\CustomField\CustomField;

/**
 * Base class for custom field group.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
abstract class CustomFieldGroupBase extends ObjectBase {

	const TYPE_TICKET = 0;
	const TYPE_USER = 1;
	const TYPE_USER_ORGANIZATION = 2;
	const TYPE_USER_LIVECHAT = 3;
	const TYPE_USER_TIME_TRACK = 4;

	static protected $object_xml_name = 'group';
	protected $read_only = true;

	/**
	 * Custom field group identifier.
	 * @apiField
	 * @var int
	 */
	protected $id;

	/**
	 * Custom field group title.
	 * @apiField
	 * @var string
	 */
	protected $title;

	/**
	 * Custom field group displayorder.
	 * @apiField
	 * @var int
	 */
	protected $displayorder;

	/**
	 * List of custom fields in this group.
	 * @var CustomField[]
	 */
	protected $fields;

	/**
	 * Type of custom field group.
	 * @see CustomFieldGroupBase::TYPE constants
	 * @var int
	 */
	protected $type;

	protected function parseData($data) {
		$this->id = intval($data['_attributes']['id']);
		$this->title = $data['_attributes']['title'];
		$this->displayorder = intval($data['_attributes']['displayorder']);

		$this->fields = array();
		if (array_key_exists('field', $data)) {
			foreach ($data['field'] as $custom_field_data) {
				$this->fields[] = CustomField::createByType($this, $custom_field_data);
			}
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		foreach ($this->getFields() as $field) {
			/* @var $field CustomField */
			$data = array_merge_recursive($data, $field->buildData($create));
		}

		return $data;
	}

	static public function get() {
		throw new BadMethodCallException(sprintf("You can't get single object of type %s.", get_called_class()));
	}

	public function refresh() {
		throw new BadMethodCallException(sprintf("You can't refresh object of type %s.", get_called_class()));
	}

	public function toString() {
		return sprintf("%s (%d fields)", $this->getTitle(), count($this->getFields()));
	}

	public function getId($complete = false) {
		return $complete ? array($this->id) : $this->id;
	}

	/**
	 * Returns type of this custom fields group (one of CustomFieldGroupBase::TYPE constants).
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Returns title of this custom fields group.
	 *
	 * @return string
	 * @filterBy
	 * @orderBy
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns displayorder of this custom fields group.
	 *
	 * @return int
	 * @filterBy
	 * @orderBy
	 */
	public function getDisplayOrder() {
		return $this->displayorder;
	}

	/**
	 * Returns list of custom fields for this group.
	 *
	 * @return ResultSet
	 */
	public function getFields() {
		return new ResultSet($this->fields, CustomField::class);
	}
}