<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpo.st
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://gnu.org/licenses/>
 */

namespace qpost\Feed\CountSample;

use qpost\Account\User;

/**
 * Represents a count sample used to display a sample of users that shared or favorited a feed entry
 *
 * @package CountSample
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class CountSample {
	public const SAMPLE_LIMIT = 10;

	/**
	 * @access protected
	 * @var User[] $users
	 */
	protected $users;

	/**
	 * @access protected
	 * @var bool $showMore
	 */
	protected $showMore;

	/**
	 * @access protected
	 * @var int $showMoreCount
	 */
	protected $showMoreCount;

	/**
	 * Gets an array of users to show in the sample tooltip.
	 *
	 * @access public
	 * @return User[]
	 */
	public function getUsers(){
		return $this->users;
	}

	/**
	 * Gets whether a "and x more" information should be shown.
	 *
	 * @access public
	 * @return bool
	 */
	public function showsMore(){
		return $this->showMore;
	}

	/**
	 * Gets how many users in the "and x more" information should be shown.
	 *
	 * @access public
	 * @return int
	 */
	public function getShowMoreCount(){
		return $this->showMoreCount;
	}
}