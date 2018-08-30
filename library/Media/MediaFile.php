<?php

class MediaFile {
    /**
     * Returns a media file object associated with an ID
     * 
     * @access public
     * @param int $id
     * @return MediaFile
     */
    public static function getMediaFileFromID($id){
        $n = "media_id_" . $id;

        if(CacheHandler::existsInCache($n)){
            return CacheHandler::getFromCache($n);
        } else {
            $media = new self($id);
            $media->reload();

            if($media->exists == true){
                return $media;
            } else {
                return null;
            }
        }
    }

    /**
     * Returns a media file object associated with a SHA256 hash
     * 
     * @access public
     * @param string $sha
     * @return MediaFile
     */
    public static function getMediaFileFromSHA($sha){
        $n = "media_sha256_" . $sha;

        if(CacheHandler::existsInCache($n)){
            return CacheHandler::getFromCache($n);
        } else {
            $media = null;

            $mysqli = Database::Instance()->get();

            $stmt = $mysqli->prepare("SELECT `id` FROM `media` WHERE `sha256` = ?");
            $stmt->bind_param("s",$sha);
            if($stmt->execute()){
                $result = $stmt->get_result();

                if($result->num_rows){
                    $row = $result->fetch_assoc();

                    $media = self::getMediaFileFromID($media);
                }
            }
            $stmt->close();

            return $media;
        }
    }

    /**
     * Returns whether an ID has already been used for a media object
     * 
     * @access public
     * @param string $id
     * @return bool
     */
    public static function isIDAvailable($id){
        $b = true;
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT COUNT(`id`) AS `count` FROM `media` WHERE `id` = ?");
        $stmt->bind_param("s",$id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                if($row["count"] > 0)
                    $b = false;
            }
        }
        $stmt->close();

        return $b;
    }

    /**
     * Returns a new, unused ID for a media object
     * 
     * @access public
     * @return string
     */
    public static function generateNewID(){
        $id = null;

        while(is_null($id) || !self::isIDAvailable($id)){
            $id = Util::getRandomString(128);
        }

        return $id;
    }

    /**
     * @access private
     * @var string $id
     */
    private $id;

    /**
     * @access private
     * @var string $sha256
     */
    private $sha256;

    /**
     * @access private
     * @var string $url
     */
    private $url;

    /**
     * @access private
     * @var int $originalUploader
     */
    private $originalUploader;

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
     * Returns the media object's id
     * 
     * @access public
     * @return string
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Returns the sha256 hash of the media object
     * 
     * @access public
     * @return string
     */
    public function getSHA256(){
        return $this->sha256;
    }

    /**
     * Returns the URL on the Gigadrive CDN
     * 
     * @access public
     * @return string
     */
    public function getURL(){
        return $this->url;
    }

    /**
     * Returns the URL of the thumbnail image
     * 
     * @access public
     * @return string
     */
    public function getThumbnailURL(){
        return "/mediaThumbnail?id=" . urlencode($this->id);
    }

    /**
     * Returns the id of the original uploader
     * 
     * @access public
     * @return int
     */
    public function getOriginalUploaderId(){
        return $this->originalUploader;
    }

    /**
     * Returns the user object of the original uploader, null if not available
     * 
     * @access public
     * @return User
     */
    public function getOriginalUploader(){
        return !is_null($this->originalUploader) ? User::getUserById($this->originalUploader) : null;
    }

    /**
     * Returns HTML code to display a clickable thumbnail
     * 
     * @access public
     * @param int $postId The id of the post to use for the media modal, if null the thumbnail won't be clickable
     * @return string
     */
    public function toThumbnailHTML($postId = null){
        $s = "";

        $s .= '<img src="' . $this->getThumbnailURL() . '" width="100" height="100" class="rounded border border-primary bg-dark ignoreParentClick mr-2"' . (!is_null($postId) ? ' style="cursor: pointer" onclick="showMediaModal(\'' . $this->id . '\',' . $postId . ');"' : "") . '/>';

        return $s;
    }

    /**
     * Reloads data from the database
     * 
     * @access public
     */
    public function reload(){
        $mysqli = Database::Instance()->get();

        $stmt = $mysqli->prepare("SELECT * FROM `media` WHERE `id` = ?");
        $stmt->bind_param("s",$this->id);
        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows){
                $row = $result->fetch_assoc();

                $this->id = $row["id"];
                $this->sha256 = $row["sha256"];
                $this->url = $row["url"];
                $this->originalUploader = $row["originalUploader"];
                $this->time = $row["time"];

                $this->exists = true;

                $this->saveToCache();
            }
        }
        $stmt->close();
    }

    /**
     * Saves the media object to the cache
     * 
     * @access public
     */
    public function saveToCache(){
        CacheHandler::setToCache("media_id_" . $this->id,$this,20*60);
        CacheHandler::setToCache("media_sha256_" . $this->sha256,$this,20*60);
    }
}