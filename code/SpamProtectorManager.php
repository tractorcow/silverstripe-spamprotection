<?php
/** 
 * This class is responsible for setting an system-wide spam protecter field 
 * and add the protecter field to a form
 */
class SpamProtectorManager {
	
	static $spam_protector = null;
	
	/**
	 * Set the name of the spam protecter class
	 * @param 	string 	the name of protecter field class
	 */
	static function set_spam_protector($protector) {
		self::$spam_protector = $protector;
	}
	
	/**
	 * Get the name of the spam protector class
	 */
	static function get_spam_protector() {
		return self::$spam_protector;
	}
	
	/**
	 * Add the spam protector field to a form
	 * @param 	Form 	the form that the protecter field added into 
	 * @param 	string	the name of the field that the protecter field will be added in front of
	 * @param 	array 	an associative array 
	 * 					with the name of the spam web service's field, for example post_title, post_body, author_name
	 * 					and a string of field names (seperated by comma) as a value.
	 *                 	The naming of the fields is based on the implementation of the subclass of SpamProtecterField.
	 * 					*** Most of the web service doesn't require this.   
	 * @return 	SpamProtector 	object on success or null if the spamprotecter class is not found 
	 *							also null if spamprotecterfield creation fails. 					
	 */
	static function update_form($form, $before=null, $fieldsToSpamServiceMapping=null) {
		$check = null;
		$protectorClass = self::$spam_protector;
		if(!class_exists($protectorClass)) return null;
		
		$protector = new $protectorClass();
		try {
			$check = $protecter->updateForm($form, $before, $fieldsToSpamServiceMapping);
		} catch (Exception $e) {
			user_error("SpamProtecterManager::update_form(): '$protectorClass' is not correctly set up.", E_USER_WARNING);
		}
		
		if(!$check) return null;
		
		return $protector;
	}
	
	/**
	 * Send Feedback to the Spam Protection. The level of feedback
	 * will depend on the Protector class.
	 *
	 * @param DataObject The Object which you want to send feedback about. Must have a 
	 *						SessionID field.
	 * @param String Feedback on the $object usually 'spam' or 'ham' for non spam entries
	 */
	static function send_feedback($object, $feedback) {
		$protector = new self::$spam_protector();
		return $protector->sendFeedback($object, $feedback);
	}
}
?>