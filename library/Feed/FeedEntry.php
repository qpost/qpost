<?php

/**
 * Represents a feed entry
 * 
 * @package FeedEntry
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class FeedEntry {
    /**
     * Returns a feed entry object fetched by the ID of the entry
     * 
     * @access public
     * @param int $id
     * @return FeedEntry
     */
    public static function getEntryById($id){
        $n = "feedEntry_" . $id;

        if(\CacheHandler::existsInCache($n)){
            return \CacheHandler::getFromCache($n);
        } else {
            $entry = new self($id);
            $entry->reload();

            if($entry->exists()){
                return $entry;
            } else {
                return null;
            }
        }
    }

    /**
     * Returns whether a feed entry is cached by ID
     * 
     * @access public
     * @param int $id
     * @return bool
     */
    public static function isCached($id){
        return \CacheHandler::existsInCache("feedEntry_" . $id);
    }

    /**
     * Returns a feed entry object created from the specified data
     * 
     * @access public
     * @return FeedEntry
     */
    public static function getEntryFromData($id,$user,$text,$following,$post,$sessionId,$type,$replies,$shares,$favorites,$time){
        $entry = self::isCached($id) ? self::getEntryById($id) : new self($id);

        $entry->id = $id;
        $entry->user = $user;
        $entry->text = $text;
        $entry->following = $following;
        $entry->post = $post;
        $entry->sessionId = $sessionId;
        $entry->type = $type;
        $entry->replies = $replies;
        $entry->shares = $shares;
        $entry->favorites = $favorites;
        $entry->time = $time;
        $entry->exists = true;
        $entry->saveToCache();

        return $entry;
    }

    /**
     * @access private
     * @var int $id
     */
    private $id;

    /**
     * @access private
     * @var int $user
     */
    private $user;

    /**
     * @access private
     * @var string $text
     */
    private $text;

    /**
     * @access private
     * @var int $following
     */
    private $following;

    /**
     * @access private
     * @var int $post
     */
    private $post;

    /**
     * @access private
     * @var string $sessionId
     */
    private $sessionId;

    /**
     * @access private
     * @var string $type
     */
    private $type;

    /**
     * @access private
     * @var string $time
     */
    private $time;

    /**
     * @access private
     * @var bool $exists
     */
    private $exists;

    /**
     * @access private
     * @var int $replies
     */
    private $replies;

    /**
     * @access private
     * @var int $shares
     */
    private $shares;

    /**
     * @access private
     * @var int $favorites
     */
    private $favorites;

    /**
     * Constructor
     * 
     * @access protected
     * @param int $id
     */
    protected function __construct($id){
        $this->id = $id;
    }

    /**
     * Reloads data from the database
     * 
     * @access public
     */
    public function reload(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT * FROM `feed` WHERE `id` = ?");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->id = $row["id"];
                $this->user = $row["user"];
                $this->text = $row["text"];
                $this->following = $row["following"];
                $this->post = $row["post"];
                $this->sessionId = $row["sessionId"];
                $this->type = $row["type"];
                $this->replies = $row["count.replies"];
                $this->shares = $row["count.shares"];
                $this->favorites = $row["count.favorites"];
                $this->time = $row["time"];
                $this->exists = true;

                $this->saveToCache();
            }
        }
        $stmt->close();
    }

    /**
     * Returns the ID of the feed entry
     * 
     * @access public
     * @return int
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Returns the ID of the user that created the feed entry
     * 
     * @access public
     * @return int
     */
    public function getUserId(){
        return $this->user;
    }

    /**
     * Returns the user object of the user that created the feed entry
     * 
     * @access public
     * @return User
     */
    public function getUser(){
        return User::getUserById($this->user);
    }

    /**
     * Returns the text of the feed entry, null if no text is available
     * 
     * @access public
     * @return string
     */
    public function getText(){
        return $this->text;
    }

    /**
     * Returns the ID of the user that was followed (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return int
     */
    public function getFollowingId(){
        return $this->following;
    }

    /**
     * Returns the user object of the user that was followed (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return User
     */
    public function getFollowing(){
        return !is_null($this->following) ? User::getUserById($this->following) : null;
    }

    /**
     * Returns the ID of the post that was shared/replied to (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return int
     */
    public function getPostId(){
        return $this->post;
    }

    /**
     * Returns the feed entry object of the post that was shared/replied to (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return FeedEntry
     */
    public function getPost(){
        return !is_null($this->post) ? self::getEntryById($this->post) : null;
    }

    /**
     * Returns the session ID used by $user when creating this feed entry
     * 
     * @access public
     * @return string
     */
    public function getSessionId(){
        return $this->sessionId;
    }

    /**
     * Returns the type of the feed entry
     * 
     * @access public
     * @return string
     */
    public function getType(){
        return $this->type;
    }

    /**
     * Returns the timestamp of when the feed entry was created
     * 
     * @access public
     * @return string
     */
    public function getTime(){
        return $this->time;
    }

    public function exists(){
        return $this->exists;
    }

    /**
     * Returns how often the feed entry was replied to
     * 
     * @access public
     * @return int
     */
    public function getReplies(){
        return $this->replies;
    }

    /**
     * Reloads the reply count
     * 
     * @access public
     */
    public function reloadReplies(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `post` = ? AND `type` = 'POST'");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->replies = $row["count"];

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.replies` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->replies,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Returns how often the feed entry was shared
     * 
     * @access public
     * @return int
     */
    public function getShares(){
        return $this->shares;
    }

    /**
     * Reloads the share count
     * 
     * @access public
     */
    public function reloadShares(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `feed` WHERE `post` = ? AND `type` = 'SHARE'");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->shares = $row["count"];

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.shares` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->shares,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Returns how often the feed entry was favorized
     * 
     * @access public
     * @return int
     */
    public function getFavorites(){
        return $this->favorites;
    }

    /**
     * Reloads the favorite count
     * 
     * @access public
     */
    public function reloadFavorites(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(*) AS `count` FROM `favorites` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->favorites = $row["count"];

                $this->saveToCache();
            }
        }
        $stmt->close();

        $stmt = $mysqli->prepare("UPDATE `feed` SET `count.favorites` = ? WHERE `id` = ?");
        $stmt->bind_param("ii",$this->favorites,$this->id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Deletes the feed entry and all its references from the database
     * 
     * @access public
     */
    public function delete(){
        $mysqli = Database::Instance()->get();

        $this->removeFromCache();

        $stmt = $mysqli->prepare("DELETE FROM `feed` WHERE `id` = ? OR (`post` = ? AND `type` = 'SHARE')");
        $stmt->bind_param("ii",$this->id,$this->id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM `notifications` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM `favorites` WHERE `post` = ?");
        $stmt->bind_param("i",$this->id);
        $stmt->execute();
        $stmt->close();

        $parent = $this->getPost();
        if(!is_null($parent) && $this->type == "POST"){
            $this->getPost()->reloadReplies();
        }

        if($this->type == "POST") $this->getUser()->reloadPostsCount();
    }

    /**
     * Returns HTML code to use in a feed entry list (search, profile, home feed, ...)
     * 
     * @access public
     * @param int $maxWidth Max width of the content part in px
     * @return string
     */
    public function toListHTML($maxWidth = 658){
        $user = $this->getUser();

        if($this->getType() == "POST"){
            $s = '<li class="list-group-item feedEntry statusTrigger px-4 py-2" data-status-render="' . $this->getId() . '" data-entry-id="' . $this->getId() . '">';
			$s .= '<div class="row">';
            $s .= '<div class="float-left">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $user->getAvatarURL() . '" width="40" height="40"/>';
			$s .= '</a>';
			$s .= '</div>';

			$s .= '<div class="float-left ml-1" style="max-width: ' . $maxWidth . 'px">';
			$s .= '<p class="mb-0">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold">' . $user->getDisplayName() . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $user->getUsername() . ' </span>';

			$s .= '&bull; ';

			$s .= Util::timeago($this->getTime());
			$s .= '</p>';

			$s .= '<p class="mb-0 convertEmoji">';
            $s .= Util::convertPost($this->getText());
			$s .= '</p>';

			$s .= Util::getPostActionButtons($this);
			$s .= '</div>';
			$s .= '</div>';
            $s .= '</li>';

            return $s;
        } else if($this->getType() == "NEW_FOLLOWING"){
            $u2 = $this->getFollowing();
				
            $s = '<li class="list-group-item px-2 py-2" data-entry-id="<?= $this->getId() ?>">';
            
			if(Util::isLoggedIn() && Util::getCurrentUser()->getId() == $this->getUserId()){
			    $s .= '<div class="float-right">';
			    $s .= '<span class="deleteButton ml-2" data-post-id="' . $this->getId() . '" data-toggle="tooltip" title="Delete">';
                $s .= '<i class="fas fa-trash-alt"></i>';
			    $s .= '</span>';
			    $s .= '</div>';
            }
            
			$s .= '<i class="fas fa-user-plus text-info"></i> <b><a href="/' . $user->getUsername() . '" class="clearUnderline">' . $user->getDisplayName() . '</a></b> is now following <a href="/' . $u2->getUsername() . '" class="clearUnderline">' . $u2->getDisplayName() . '</a> &bull; <span class="text-muted">' . Util::timeago($this->getTime()) . '</span>';
            $s .= '</li>';
            
            return $s;
        } else if($this->getType() == "SHARE"){
            $sharedPost = $this->getPost();
			$sharedUser = $sharedPost->getUser();

			if(is_null($sharedPost) || is_null($sharedUser))
				return "";

			$s = '<li class="list-group-item feedEntry statusTrigger px-4 py-2" data-status-render="' . $sharedPost->getId() . '" data-entry-id="' . $this->getId() . '">';
			$s .= '<div class="small text-muted">';
			$s .= '<i class="fas fa-share-alt text-primary"></i> Shared by <a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">' . $user->getDisplayName() . '</a> &bull; ' . Util::timeago($this->getTime());
			$s .= '</div>';
			$s .= '<div class="row">';
			$s .= '<div class="float-left">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $sharedUser->getAvatarURL() . '" width="40" height="40"/>';
			$s .= '</a>';
            $s .= '</div>';

            $s .= '<div class="float-left ml-1">';
			$s .= '<p class="mb-0">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold">' . $sharedUser->getDisplayName() . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $sharedUser->getUsername() . ' </span>';

			$s .= '&bull; ';

            $s .= Util::timeago($sharedPost->getTime());
			$s .= '</p>';

			$s .= '<p class="mb-0 convertEmoji">';
			$s .= Util::convertPost($sharedPost->getText());
			$s .= '</p>';

			$s .= Util::getPostActionButtons($sharedPost);
			$s .= '</div>';
			$s .= '</div>';
            $s .= '</li>';
            
            return $s;
        }

        return "";
    }

    public function saveToCache(){
        \CacheHandler::setToCache("feedEntry_" . $this->id,$this,20*60);
    }

    public function removeFromCache(){
        \CacheHandler::deleteFromCache("feedEntry_" . $this->id);
    }
}