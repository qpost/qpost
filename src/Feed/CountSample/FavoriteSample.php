<?php

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

            if($feedEntry->getFavorites() > 0 && $feedEntry->getType() === FEED_ENTRY_TYPE_POST){
                $mysqli = \Database::Instance()->get();

                $feedEntryId = $feedEntry->getId();

                $stmt = $mysqli->prepare("SELECT u.* FROM `favorites` AS f INNER JOIN `users` AS u ON f.user = u.id WHERE f.post = ? ORDER BY f.time DESC LIMIT " . CountSample::SAMPLE_LIMIT);
                $stmt->bind_param("i",$feedEntryId);

                if($stmt->execute()){
                    $result = $stmt->get_result();

                    if($result->num_rows){
                        while($row = $result->fetch_assoc()){
                            array_push($this->users,User::getUserByData($row["id"],$row["gigadriveId"],$row["displayName"],$row["username"],$row["password"],$row["email"],$row["avatar"],$row["bio"],$row["token"],$row["birthday"],$row["privacy.level"],$row["featuredBox.title"],$row["featuredBox.content"],$row["lastGigadriveUpdate"],$row["gigadriveJoinDate"],$row["time"],$row["emailActivated"],$row["emailActivationToken"],$row["lastUsernameChange"],$row["verified"]));
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