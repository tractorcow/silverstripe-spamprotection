<?php

/**
 * Spam Protector base interface. All Protectors should implement this interface 
 * to ensure that they contain all the correct methods.
 * 
 * @package spamprotection
 */
interface SpamProtector {
	
	/**
	 * Return the Field Associated with this protector
	 * 
	 * @param string $name Name of the field
	 * @param sstring $title Title of the field
	 * @param string $value Value to assign this field
	 * @param Form $form Parent Form object
	 * @param string $rightTitle Right title
	 * @return FormField The resulting form field
	 */
	public function getFormField($name = null, $title = null, $value = null, $form = null, $rightTitle = null);
	
}

interface SpamProtectorFeedback {
	
	/**
	 * Send Feedback to the Spam Protection. The level of feedback
	 * will depend on the Protector class.
	 *
	 * @param DataObject $object The Object which you want to send feedback about. Must have a SessionID field.
	 * @param string $feedback Feedback on the $object usually 'spam' or 'ham' for non spam entries
	 */
	public function sendObjectFeedback($object, $feedback);
}