<?php

/**
 * Represents a privacy level an account can have
 * 
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class PrivacyLevel {
	/**
	 * @access public
	 * @var string PUBLIC Privacy level for public accounts, visible to everyone
	 */
	public const PUBLIC = "PUBLIC";

	/**
	 * @access public
	 * @var string PRIVATE Privacy level for private accounts, only visible for followers and followers must be confirmed
	 */
	public const PRIVATE = "PRIVATE";

	/**
	 * @access public
	 * @var string CLOSED Privacy level for closed accounts, only visible for self
	 */
	public const CLOSED = "CLOSED";
}