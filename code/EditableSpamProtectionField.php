<?php

/**
 * Editable Spam Protecter Field. Used with the User Defined Forms module (if 
 * installed) to allow the user to have captcha fields with their custom forms
 * 
 * @package spamprotection
 */
if(class_exists('EditableFormField')) {
	
	class EditableSpamProtectionField extends EditableFormField {
	
		private static $singular_name = 'Spam Protection Field';
	
		private static $plural_name = 'Spam Protection Fields';
		
		/**
		 * Fields to include spam detection for
		 *
		 * @var array
		 * @config
		 */
		private static $check_fields = array(
			'EditableEmailField',
			'EditableTextField',
			'EditableNumericField'
		);
	
		public function getFormField() {
			
			// Generate field from protector
			$protector = SpamProtectorManager::instance();
			if(!$protector) return false;
			$field = $protector->getFormField($this->Name, $this->Title);
			
			// Extract saved field mappings and update this field. This method is substituted for the execution
			// of SpamProtectorManager::update_form, since we can't call this on the form directly
			$spamProtector = Config::inst()->get('SpamProtectorManager', 'spam_protector');
			if($spamProtector) {
				$mappableFields = Config::inst()->get($spamProtector, 'mappable_fields');
				if($mappableFields) {
					
					// Extract and assign mappings
					$map = array();
					foreach($this->getDetectableFields() as $otherField) {
						$mapSetting = "Map-{$otherField->Name}";
						$mapValue = $this->getSetting($mapSetting);
						if($mapValue && in_array($mapValue, $mappableFields)) {
							$map[$otherField->Name] = $mapValue;
						}
					}
					
					$field->setFieldMapping($map);
				}
			}
			
			return $field;
		}
		
		/**
		 * Gets the list of all candidate spam detectable fields on this field's form
		 * 
		 * @return DataList
		 */
		protected function getDetectableFields() {
			
			// Get list of all configured classes available for spam detection
			$types = Config::inst()->get('EditableSpamProtectionField', 'check_fields');
			$typesInherit = array();
			foreach($types as $type) {
				$subTypes = ClassInfo::subclassesFor($type);
				$typesInherit = array_merge($typesInherit, $subTypes);
			}
			
			// Get anti-spam field candidates
			return $this
				->Parent()
				->Fields() // Get sibling fields
				->filter('ClassName', $typesInherit)
				->exclude('ID', $this->ID)
				->exclude('Title', ''); // Ignore this field and those without titles
		}
		
		public function getFieldConfiguration() {
			$fields = parent::getFieldConfiguration();
			
			// Determine if fields need to be configured for the selected spam manager
			$spamProtector = Config::inst()->get('SpamProtectorManager', 'spam_protector');
			if($spamProtector) {
				
				// If this protector has mappable fields then present these as options
				$mappableFields = Config::inst()->get($spamProtector, 'mappable_fields');
				if($mappableFields) {
				
					// Generate spam configuration fields
					$mappableFieldMerged = array_combine($mappableFields, $mappableFields);
					$mapGroup = new FieldGroup('Spam Field Mapping');
					$mapGroup->setDescription('Select the form fields that correspond to any relevant spam protection identifiers');
					foreach($this->getDetectableFields() as $otherField) {
						$mapSetting = "Map-{$otherField->Name}";
						$mapGroup->push(DropdownField::create(
							$this->getSettingName($mapSetting),
							$otherField->Title,
							$mappableFieldMerged,
							$this->getSetting($mapSetting)
						)->setEmptyString(''));
					}
					$fields->push($mapGroup);
				}
			} else {
				$fields->push(new LiteralField(
					$this->getSettingName('SpamError'),
					'<p class="message">Please configure a spam protector to customise</p>'
				));
			}
			
			return $fields;
		}
		
		function getFieldValidationOptions() {
			return new FieldList();
		}
		
		function getRequired() {
			return false;
		}

		public function Icon() {
			return 'spamprotection/images/' . strtolower($this->class) . '.png';
		}
	
		function showInReports() {
			return false;
		}
	}
}
