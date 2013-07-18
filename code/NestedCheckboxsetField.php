<?php
class NestedCheckboxSetField extends CheckboxSetField {
	private $rootClass;

	private $rootTitle;

	private $childRelation;

	private $childTitle;

	/**
	 * @param $rootClass string Sets the root class (which the relationships should be held within)
	 * @return NestedCheckboxSetField This object (for chaining)
	 */
	public function setRootClass($rootClass) {
		$this->rootClass = $rootClass;
		return $this;
	}

	public function setRootTitle($rootTitle) {
		$this->rootTitle = $rootTitle;
		return $this;
	}

	public function setChildRelation($childRelation) {
		$this->childRelation = $childRelation;
		return $this;
	}

	public function setChildTitle($childTitle) {
		$this->childTitle = $childTitle;
		return $this;
	}

	public function Field($properties = array()) {
		Requirements::css(MODULE_NESTEDCHECKBOXSETFIELD_DIR . '/css/NestedCheckboxSetField.css');

		$rootSourceParam = $this->rootClass;
		$rootTitleParam = $this->rootTitle;
		$childRelationParam = $this->childRelation;
		$childTitleParam = $this->childTitle;
		$source = $this->source;
		$values = $this->value;
		$items = array();

		// Get values from the join, if available
		if(is_object($this->form)) {
			$record = $this->form->getRecord();
			if(!$values && $record && $record->hasMethod($this->name)) {
				$funcName = $this->name;
				$join = $record->$funcName();
				if($join) {
					foreach($join as $joinItem) {
						$values[] = $joinItem->ID;
					}
				}
			}
		}

		// Source is not an array
		if(!is_array($source) && !is_a($source, 'SQLMap')) {
			if(is_array($values)) {
				$items = $values;
			} else {
				// Source and values are DataObject sets.
				if($values && is_a($values, 'SS_List')) {
					foreach($values as $object) {
						if(is_a($object, 'DataObject')) {
							$items[] = $object->ID;
						}
					}
				} elseif($values && is_string($values)) {
					$items = explode(',', $values);
					$items = str_replace('{comma}', ',', $items);
				}
			}
		} else {
			// Sometimes we pass a singluar default value thats ! an array && !SS_List
			if($values instanceof SS_List || is_array($values)) {
				$items = $values;
			} else {
				$items = explode(',', $values);
				$items = str_replace('{comma}', ',', $items);
			}
		}

		$rootSources = $rootSourceParam::get()->sort("$rootTitleParam ASC");
		$rootOptions = array();
		$rootOdd = 0;

		foreach($rootSources as $source) {
			// $source is an instance of $this->rootClass, which we can call $this->childRelation() on
			$childSources = $source->$childRelationParam()->sort("$childTitleParam ASC");
			$rootTitle = $source->$rootTitleParam;
			$childArray = array();
			$childOdd = 0;

			foreach($childSources as $childSource) {
				$title = $childSource->$childTitleParam;
				$value = $childSource->ID;
				$itemID = $this->ID() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', $value);
				$childOdd = ($childOdd + 1) % 2;
				$extraClass = $childOdd ? 'odd' : 'even';
				$extraClass .= ' val' . preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $value);

				$childArray[] = new ArrayData(array(
					'ID' => $itemID,
					'Class' => $extraClass,
					'Name' => "{$this->name}[{$value}]",
					'Value' => $value,
					'Title' => $title,
					'isChecked' => in_array($value, $items) || in_array($value, $this->defaultItems),
					'isDisabled' => $this->disabled || in_array($value, $this->disabledItems)
				));
			}

			$rootOdd = ($rootOdd + 1) % 2;
			$extraClass = $rootOdd ? 'odd' : 'even';

			$rootOptions[] = new ArrayData(array(
				'Title' => $rootTitle,
				'Class' => $extraClass,
				'Options' => new ArrayList($childArray)
			));
		}

		// $rootOptions is now the complete ArrayData of options => sub-options
		$properties = array_merge($properties, array('Options' => new ArrayList($rootOptions)));

		return $this->customise($properties)->renderWith($this->getTemplates());
	}
}