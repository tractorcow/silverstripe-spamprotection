<?php

/** 
 * This class is responsible for setting an system-wide spam protector field 
 * and add the protecter field to a form.
 * 
 * @package spamprotection
 */
class SpamProtectorManager {
	
	/**
	 * Class name of SpamProtector implementation used on the site
	 *
	 * @var string
	 * @config
	 */
	private static $spam_protector = null;
	
	/**
	 * @deprecated 3.2 Use the "SpamProtectorManager.spam_protector" config setting instead
	 */
	public static function set_spam_protector($protector) {
		Deprecation::notice('3.2', 'Use the "SpamProtectorManager.spam_protector" config setting instead');
		Config::inst()->update('SpamProtectorManager', 'spam_protector', $protector);
	}
	
	/**
	 * Get the name of the spam protector class
	 * 
	 * @return string
	 */
	public static function get_spam_protector() {
		return Config::inst()->get('SpamProtectorManager', 'spam_protector');
	}
	
	/**
	 * Returns the instance of the configured SpamProtector
	 * 
	 * @return SpamProtector The instance of the SpamProtector, if configured
	 */
	public static function instance() {
		$protectorClass = Config::inst()->get('SpamProtectorManager', 'spam_protector');
		
		// Don't update if no protector is set
		if(!$protectorClass) return null;
		
		if(!class_exists($protectorClass)) {
			return user_error(
				"Spam Protector class '$protectorClass' does not exist. Please define a valid Spam Protector",
				E_USER_ERROR
			);
		}
		
		return Injector::inst()->get($protectorClass);
	}
	
	/**
	 * Add the spam protector field to a form
	 * 
	 * @param Form $form the form that the protecter field added into 
	 * @param string $before the name of the field that the protecter field will be added in front of
	 * @param array $fieldsToSpamServiceMapping an associative array with the name of the spam web service's
	 * field, for example postTitle, postBody, authorName and a string of field names
	 * @param string $title Title for the captcha field
	 * @param string $rightTitle RightTitle for the captcha field
	 * @return SpamProtector The $protectorClass object on success or null if the spamprotector class is not found 
	 * also null if spamprotectorfield creation fails. 					
	 */
	static function update_form($form, $before = null, $fieldsToSpamServiceMapping = array(), $title = null, $rightTitle = null) {
		
		$protector = self::instance();
		
		// Don't update if no protector is set
		if(!$protector) return false;
					
		// Generate field and insert into form as necessary
		$field = $protector->getFormField("Captcha", $title, null, $form, $rightTitle);
		if($field) {

			$field->setForm($form);
			if ($rightTitle) $field->setRightTitle($rightTitle);

			// update the mapping
			$field->setFieldMapping($fieldsToSpamServiceMapping);

			// add the form field
			if($before && $form->Fields()->fieldByName($before)) {
				$form->Fields()->insertBefore($field, $before);
			}
			else {
				$form->Fields()->push($field);
			}	
		}
	}
	
	/**
	 * Send Feedback to the Spam Protection. The level of feedback
	 * will depend on the Protector class.
	 *
	 * @param DataObject $object The Object which you want to send feedback about. Must have a SessionID field.
	 * @param string $feedback Feedback on the $object usually 'spam' or 'ham' for non spam entries
	 */
	static function send_feedback($object, $feedback) {
		
		$protector = self::instance();
		if(!$protector) return false;
		
		if($protector instanceof SpamProtectorFeedback) {
			return $protector->sendObjectFeedback($object, $feedback);
		}
	}
}
