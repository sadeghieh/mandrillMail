<?php
/**
 * @file index.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Wrapper for Mandrill Mail plugin.
 * @package plugins.generic.mandrillMail
 *
 */
require_once('plugins/generic/mandrillMail/MandrillMailPlugin.inc.php');

return new MandrillMailPlugin();

?>
