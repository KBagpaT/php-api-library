<?php
namespace Kayako\Api\Client\Object\Base;

use Kayako\Api\Client\Common\Helper;
use Kayako\Api\Client\Common\ResultSet;
use Kayako\Api\Client\Exception\BadMethodCallException;
use Kayako\Api\Client\Exception\GeneralException;
use Kayako\Api\Client\Object\CustomField\CustomField;
use Kayako\Api\Client\Object\CustomField\CustomFieldDefinition;
use Kayako\Api\Client\Object\CustomField\CustomFieldFile;

/**
 * Base class for Kayako object with custom fields.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
abstract class ObjectWithCustomFieldsBase extends ObjectBase {

	/**
	 * Name of class representing object custom field group.
	 * @var string
	 */
	static protected $custom_field_group_class = null;

	/**
	 * Name of URL parameter for sending object identifier.
	 * @var string
	 */
	static protected $object_id_field = null;

	/**
	 * For fast lookup of custom fields based on their name.
	 * @var array
	 */
	protected $custom_fields = null;

	/**
	 * Object custom field groups.
	 * @var ResultSet
	 */
	protected $custom_field_groups = null;

	/**
	 * Adds updating custom fields to base object update.
	 *
	 * @see ObjectBase::update()
	 */
	public function update() {
		parent::update();
		if (!$this->isNew()) {
			$this->updateCustomFields();
		}
	}

	/**
	 * Returns list of custom field groups for this object.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @throws BadMethodCallException
	 * @return ResultSet
	 */
	protected function loadCustomFieldGroups($reload = false) {
		if ($this->isNew())
			throw new BadMethodCallException("Custom fields are not available for new objects. Save the object before accessing itd custom fields.");

		if ($this->custom_field_groups !== null && !$reload)
			return $this->custom_field_groups;

		$custom_field_group_class = static::$custom_field_group_class;

		/** @noinspection PhpUndefinedMethodInspection */
		$this->custom_field_groups = $custom_field_group_class::getAll($this->getId());
		$this->initFields();

		return $this->custom_field_groups;
	}

	/**
	 * Prepares local array for custom field fast lookup based on its name.
	 */
	private function initFields() {
		$this->custom_fields = array();

		foreach ($this->custom_field_groups as $custom_field_groups) {
			/* @var $custom_field_groups CustomFieldGroupBase */
			foreach ($custom_field_groups->getFields() as $field) {
				/** @var $field CustomField */
				$this->custom_fields[$field->getName()] = $field;
			}
		}
	}

	/**
	 * Returns list of custom fields.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return ResultSet
	 */
	public function getCustomFields($reload = false) {
		$this->loadCustomFieldGroups($reload);

		return new ResultSet(array_values($this->custom_fields));
	}

	/**
	 * Returns list of custom fields groups.
	 * Result is cached until the end of script.
	 *
	 * @param bool $reload True to reload data from server. False to use the cached value (if present).
	 * @return ResultSet
	 */
	public function getCustomFieldGroups($reload = false) {
		$this->loadCustomFieldGroups($reload);

		return $this->custom_field_groups;
	}

	/**
	 * Returns custom field based on its name.
	 *
	 * @param string $name Field name.
	 * @return CustomField
	 */
	public function getCustomField($name) {
		$this->loadCustomFieldGroups();

		if (!array_key_exists($name, $this->custom_fields))
			return null;

		return $this->custom_fields[$name];
	}

	/**
	 * Returns value of this custom field.
	 * Value interpretation depends on field type.
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	public function getCustomFieldValue($name) {
		$this->getCustomField($name)->getValue();
	}

	/**
	 * Sets custom field value.
	 *
	 * @param string $name Field name.
	 * @param mixed $value New field value.
	 * @return ObjectWithCustomFieldsBase
	 * @throws GeneralException
	 */
	public function setCustomFieldValue($name, $value) {
		$this->loadCustomFieldGroups();

		$custom_field = $this->getCustomField($name);
		$custom_field_definition = $custom_field->getDefinition();
		if (!$custom_field_definition->getIsUserEditable())
			throw new GeneralException(sprintf("usereditable flag is disabled for custom field %s.", $custom_field->getTitle()));

		if ($custom_field_definition->getIsRequired() && empty($value)) {
			throw new GeneralException(sprintf("Field '%s' is required, cannot be empty", $custom_field->getTitle()));
		}

		$this->getCustomField($name)->setValue($value);
		return $this;
	}

	/**
	 * Sets custom field values using POST data from current PHP request.
	 *
	 * @throws \Exception
	 */
	public function setCustomFieldValuesFromPOST() {
		foreach ($this->getCustomFields() as $custom_field) {
			/* @var $custom_field CustomField */

			/** @var $custom_field_definition CustomFieldDefinition */
			$custom_field_definition = $custom_field->getDefinition();

			if (!$custom_field_definition->getIsUserEditable())
				throw new GeneralException(sprintf("usereditable flag is disabled for custom field %s.", $custom_field->getTitle()));

			if ($custom_field_definition->getType() === CustomFieldDefinition::TYPE_FILE) {
				/** @var $custom_field CustomFieldFile */
				if (array_key_exists($custom_field->getName(), $_FILES) && $_FILES[$custom_field->getName()]['error'] != UPLOAD_ERR_NO_FILE) {
					if ($_FILES[$custom_field->getName()]['error'] != UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$custom_field->getName()]['tmp_name']))
						throw new GeneralException(sprintf("Error uploading file '%s'.", $custom_field->getTitle()));

					$file_data = $_FILES[$custom_field->getName()];
					$custom_field->setContentsFromFile($file_data['tmp_name'], $file_data['name']);
				} else {
					if ($custom_field_definition->getIsRequired())
						throw new GeneralException(sprintf("Field '%s' is required.", $custom_field->getTitle()));
				}
			} else {
				$custom_field->setValue(Helper::getPostValue($custom_field_definition));
			}
		}
	}

	/**
	 * Updates all custom fields values on Kayako server.
	 *
	 * @return ObjectWithCustomFieldsBase
	 */
	public function updateCustomFields() {
		//ignore saving fields if they weren't even loaded
		if ($this->custom_field_groups === null)
			return $this;

		$custom_field_group_class = static::$custom_field_group_class;

		//collect all field values into request data
		$data = array();
		foreach ($this->getCustomFieldGroups() as $custom_field_group) {
			/* @var $custom_field_group CustomFieldGroupBase */
			$data = array_merge_recursive($data, $custom_field_group->buildData(true));
		}

		if (count($data) === 0)
			return $this;

		//prepare URL controller and parameters
		$parameters = array(static::$object_id_field => $this->getId());
		/** @noinspection PhpUndefinedMethodInspection */
		$controller = $custom_field_group_class::getController();

		//get files from data
		$files = array();
		if (array_key_exists(ObjectBase::FILES_DATA_NAME, $data)) {
			$files = $data[ObjectBase::FILES_DATA_NAME];
			unset($data[ObjectBase::FILES_DATA_NAME]);
		}

		//send request
		self::getRESTClient()->post($controller, $parameters, $data, $files);

		//reload custom fields from server, there's no way (yet) to update objects state based on POST response
		$this->loadCustomFieldGroups(true);

		return $this;
	}
}