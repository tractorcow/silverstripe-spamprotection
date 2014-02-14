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
	
		function getFormField() {
			// Generate field from protector
			$protector = SpamProtectorManager::instance();
			if(!$protector) return false;
			$field = $protector->getFormField($this->Name, $this->Title, null);
			
			// Extract saved field mappings and save in this field
			$spamProtector = Config::inst()->get('SpamProtectorManager', 'spam_protector');
			if($spamProtector) {
				$mappableFields = Config::inst()->get($spamProtector, 'mappable_fields');
				if($mappableFields) {
					
					// Extract and assign mappings
					$map = array();
					foreach($this->getSiblingFields() as $otherField) {
						$mapName = "Map-{$otherField->Name}";
						$mapValue = $this->getSetting($mapName);
						if($mapValue && in_array($mapValue, $mappableFields)) {
							$map[$otherField->Name] = $mapValue;
						}
					}
					Debug::dump($map);
					
					$field->setFieldMapping($map);
				}
			}
			
			return $field;
		}
		
		protected function getSiblingFields() {	
			// Get anti-spam field candidates
			return $this
				->Parent()
				->Fields() // Get sibling fields
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
					foreach($this->getSiblingFields() as $otherField) {
						$mapName = "Map-{$otherField->Name}";
						$mapGroup->push(DropdownField::create(
							$this->getSettingName($mapName),
							$otherField->Title,
							$mappableFieldMerged,
							$this->getSetting($mapName)
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
