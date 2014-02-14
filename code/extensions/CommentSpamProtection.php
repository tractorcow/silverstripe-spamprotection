<?php 

/**
 * Apply the spam protection to the comments module if it is installed
 *
 * @package spamprotection
 */

class CommentSpamProtection extends Extension {
	
	/**
	 * Spam mapping of field to spam service mapping. This should be customised as per the spam protector mechanism
	 * chosen. Some services (such as recaptcha) do not process these fields and is not necessary.
	 * 
	 * The default values are setup for Mollom @link http://mollom.com/api#api-content
	 * 
	 * @var array
	 * @config
	 */
	private static $spam_mapping = array(
		'Name' => 'authorName', 
		'URL' => 'authorUrl', 
		'Comment' => 'postBody', 
		'Email' => 'authorMail'
	);

	/**
	 * Disable the AJAX commenting and update the form
	 * with the {@link SpamProtectorField} which is enabled
	 */
	public function alterCommentForm(&$form) {
		$mapping = Config::inst()->get($this->owner->class, 'spam_mapping');
		SpamProtectorManager::update_form($form, null, $mapping);
	}
}
