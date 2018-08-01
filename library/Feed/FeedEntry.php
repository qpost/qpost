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
     * Returns a feed entry object created from the specified data
     * 
     * @access public
     * @return FeedEntry
     */
    public static function getEntryFromData($id,$user,$text,$following,$sessionId,$type,$time){
        $entry = new self($id);

        $entry->id = $id;
        $entry->user = $user;
        $entry->text = $text;
        $entry->following = $following;
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
                $this->sessionId = $row["sessionId"];
                $this->type = $row["type"];
                $this->time = $row["time"];

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

    public function saveToCache(){
        \CacheHandler::setToCache("feedEntry_" . $this->id,$this,20*60);
    }

    public function removeFromCache(){
        \CacheHandler::deleteFromCache("feedEntry_" . $this->id);
    }
}