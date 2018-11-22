<?php

/**
 * @file MandrillMailPlugin.inc.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.mandrillMail
 * @class MandrillMailPlugin
 * @brief Plugin that will directs all emails sent by the system to Mandrill via cURL API.
 */

import('lib.pkp.classes.plugins.GenericPlugin');
require_once 'src/Mandrill.php';

class MandrillMailPlugin extends GenericPlugin
{
    /**
     * @copydoc LazyLoadPlugin::register()
     */
    function register($category, $path)
    {
        $success = parent::register($category, $path);
        if ($success) {
            HookRegistry::register('Mail::send', array($this, 'mailSendCallback'));
            //HookRegistry::register('PKPNotificationOperationManager::sendNotificationEmail', array($this, 'recordNotificationDetails'));
        }

        return $success;
    }

    /**
     * @copydoc LazyLoadPlugin::getName()
     */
    function getName()
    {
        return 'mandrillMail';
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    function getDisplayName()
    {
        return __('plugins.generic.mandrillMail.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    function getDescription()
    {
        return __('plugins.generic.mandrillMail.description');
    }

    /**
     * @copydoc Plugin::isSitePlugin()
     */
    function isSitePlugin()
    {
        return true;
    }

    /**
     * @copydoc Plugin::getInstallSitePluginSettingsFile()
     */
    function getInstallSitePluginSettingsFile() {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Mail send callback.
     * @param $hookName string
     * @param $args array
     * @return boolean
     */
    function mailSendCallback($hookName, $args)
    {
        $mail = current($args);
        // Replace all the private parameters for this message.
        $body = $mail->getBody();
        if (is_array($mail->privateParams)) {
            foreach ($mail->privateParams as $name => $value) {
                $body = str_replace($name, $value, $body);
            }
        }
        $from = $mail->getFrom();
        $to = array();
        foreach ((array) $mail->getRecipients() as $recipientInfo) {
            $to[] = array('email' => $recipientInfo['email'], 'name' => $recipientInfo['name'], 'type' => 'to');
        }
        foreach ((array) $mail->getCcs() as $ccInfo) {
            $to[] = array('email' => $ccInfo['email'], 'name' => $ccInfo['name'], 'type' => 'cc');
        }
        foreach ((array) $mail->getBccs() as $bccInfo) {
            $to[] = array('email' => $bccInfo['email'], 'name' => $bccInfo['name'], 'type' => 'bcc');
        }

        $headers = $mail->getHeaders();
        $remoteAddr = Request::getRemoteAddr();
        if ($remoteAddr != '') $headers[] = "X-Originating-IP: $remoteAddr";

        if (Config::getVar('email', 'mandrill_api_key')) {
            $api = Config::getVar('email', 'mandrill_api_key');
        }

        $message = array(
            'html' => $body,
            'text' => PKPString::html2text($body),
            'subject' => $mail->getSubject(),
            'from_email' => $from['email'],
            'from_name' => $from['name'],
            'to' => $to,
            'headers' => $headers,
        );

        try {
            $mandrill = new Mandrill($api);
            $async = false;
            $success = $mandrill->messages->send($message, $async);
            if (!$success) {
                error_log($mandrill->castError($success));
                return false;
            }

        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            throw $e;
        }


    }
}

