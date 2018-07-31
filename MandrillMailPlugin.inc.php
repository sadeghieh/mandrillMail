<?php

/**
 * @file MandrillMailPlugin.inc.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.mandrillMailer
 * @class MandrillMailPlugin
 * @brief Plugin that will directs all emails sent by the system to Mandrill via cURL API.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class MandrillMailPlugin extends GenericPlugin {

	/** @var $_notification PKPNotification */
	private $_notification;

	/** @var $emailLogEntryDao EmailLogDAO */
	protected $emailLogEntryDao;

	//
	// Implement methods from PKPPlugin.
	//
	/**
	 * @copydoc LazyLoadPlugin::register()
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			HookRegistry::register('Mail::send', array($this, 'mailSendCallback'));
			HookRegistry::register('PKPNotificationOperationManager::sendNotificationEmail', array($this, 'recordNotificationDetails'));
		}

		return $success;
	}

	/**
	 * @copydoc LazyLoadPlugin::getName()
	 */
	function getName() {
		return 'mandrillmailplugin';
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.mandrillMailer.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */ 
	function getDescription() {
		return __('plugins.generic.mandrillMailer.description');
	}

	/**
	 * @copydoc Plugin::isSitePlugin()
	 */
	function isSitePlugin() {
		return true;
	}

	/**
	 * PKPNotificationOperationManager::sendNotificationMail() callback
	 * to store notification details to be later used in log entry.
	 * @param $hookName string
	 * @args array
	 * @return boolean 
	 */
	function recordNotificationDetails($hookName, $args) {
		$notification = current($args);
		$this->_notification = $notification;

		return false;
	}
	
	/**
	 * Mail send callback.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean 
	 */
	function mailSendCallback($hookName, $args) {

	}
