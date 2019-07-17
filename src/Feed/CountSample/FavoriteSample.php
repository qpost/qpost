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
use qpost\Database\Database;
use qpost\Feed\FeedEntry;
use qpost\Feed\FeedEntryType;

/**
 * Represents a count sample used to display a sample of users that favorited a feed entry
 *
 * @package CountSample
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class FavoriteSample extends CountSample {
	/**
	 * Constructor that loads the data from the database.
	 *
	 * @access private
	 * @param FeedEntry $feedEntry
	 */
	public function __construct($feedEntry){
		if(!is_null($feedEntry)){
			$this->users = [];

			if ($feedEntry->getFavorites() > 0 && $feedEntry->getType() === FeedEntryType::POST) {
				$mysqli = Database::Instance()->get();

				$feedEntryId = $feedEntry->getId();

				$stmt = $mysqli->prepare("SELECT `id` FROM `favorites` AS f INNER JOIN `users` AS u ON f.user = u.id WHERE f.post = ? ORDER BY f.time DESC LIMIT " . CountSample::SAMPLE_LIMIT);
				$stmt->bind_param("i",$feedEntryId);

				if($stmt->execute()){
					$result = $stmt->get_result();

					if($result->num_rows){
						while($row = $result->fetch_assoc()){
							array_push($this->users,User::getUserById($row["id"]));
						}
					}
				}

				$stmt->close();

				if($feedEntry->getFavorites() > count($this->users)){
					$this->showMore = true;
					$this->showMoreCount = ($feedEntry->getFavorites()-count($this->users));
				} else {
					$this->showMore = false;
					$this->showMoreCount = 0;
				}
			}
		}
	}
}