<?php
namespace Kayako\Api\Client\Object\CustomField;

use Kayako\Api\Client\Common\Helper;

/**
 * Class for linked select custom field.
 *
 * @author Tomasz Sawicki (https://github.com/Furgas)
 * @since Kayako version 4.40.1079
 */
class CustomFieldLinkedSelect extends CustomFieldSelect {

	/**
	 * Separator of main and linked select values.
	 * @var string
	 */
	const PARENT_CHILD_SEPARATOR = ' &gt; ';

	protected function parseData($data) {
		parent::parseData($data);

		if (strpos($data['_contents'], self::PARENT_CHILD_SEPARATOR) !== false) {
			list(, $child_value) = explode(self::PARENT_CHILD_SEPARATOR, $data['_contents']);
			$this->option = $this->getOption($child_value);
		}
	}

	public function buildData($create) {
		$this->checkRequiredAPIFields($create);

		$data = array();

		if ($this->option !== null) {
			if (is_numeric($this->option->getParentOptionId())) {
				$data[sprintf('%s[0]', $this->name)] = $this->option->getParentOptionId();
				$data[sprintf('%s[1][%d]', $this->name, $this->option->getParentOptionId())] = $this->option->getId();
			} else {
				$data[sprintf('%s[0]', $this->name)] = $this->option->getId();
			}
		}

		return $data;
	}

	/**
	 * Sets the field selected option.
	 *
	 * @param CustomFieldOption $option Child (linked) field option.
	 * @return CustomFieldLinkedSelect
	 */
	public function setSelectedOption($option) {
		$this->option = Helper::assureObject($option, CustomFieldOption::class);

		if ($this->option !== null) {
			$parent_option = $this->getOption($this->option->getParentOptionId());
			$this->raw_value = implode(self::PARENT_CHILD_SEPARATOR, array($parent_option->getValue(), $this->option->getValue()));
		} else {
			$this->raw_value = null;
		}

		return $this;
	}
}