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
    public static function getEntryFromData($id,$user,$text,$following,$post,$sessionId,$type,$time){
        $entry = self::isCached($id) ? self::getEntryById($id) : new self($id);

        $entry->id = $id;
        $entry->user = $user;
        $entry->text = $text;
        $entry->following = $following;
        $entry->post = $post;
        $entry->sessionId = $sessionId;
        $entry->type = $type;
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
     * Returns the ID of the post that was shared (context of the feed entry), returns null if not available
     * 
     * @access public
     * @return int
     */
    public function getPostId(){
        return $this->post;
    }

    /**
     * Returns the feed entry object of the post that was shared (context of the feed entry), returns null if not available
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
     * Returns how often the feed entry was shared
     * 
     * @access public
     * @return int
     */
    public function getShares(){
        if(is_null($this->shares)){
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
        }

        return $this->shares;
    }

    /**
     * Reloads the share count
     * 
     * @access public
     */
    public function reloadShares(){
        $this->shares = null;
        $this->getShares();
    }

    /**
     * Returns how often the feed entry was favorized
     * 
     * @access public
     * @return int
     */
    public function getFavorites(){
        if(is_null($this->favorites)){
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
        }

        return $this->favorites;
    }

    /**
     * Reloads the favorite count
     * 
     * @access public
     */
    public function reloadFavorites(){
        $this->favorites = null;
        $this->getFavorites();
    }

    public function saveToCache(){
        \CacheHandler::setToCache("feedEntry_" . $this->id,$this,20*60);
    }

    public function removeFromCache(){
        \CacheHandler::deleteFromCache("feedEntry_" . $this->id);
    }
}