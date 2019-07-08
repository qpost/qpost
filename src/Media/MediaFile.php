<?php

namespace qpost\Media;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Account\User;
use qpost\Util\Util;

/**
 * Class MediaFile
 * @package qpost\Media
 *
 * @ORM\Entity
 */
class MediaFile {
	/**
	 * @access private
	 * @var string $id
	 *
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 * @ORM\Column(type="string")
	 */
	private $id;

	/**
	 * @access private
	 * @var string $sha256
	 *
	 * @ORM\Column(type="string", unique=true)
	 */
	private $sha256;

	/**
	 * @access private
	 * @var string $url
	 *
	 * @ORM\Column(type="string")
	 */
	private $url;

	/**
	 * @access private
	 * @var User|null $originalUploader
	 *
	 * @ORM\OneToOne(targetEntity="User")
	 * @ORM\Column(nullable=true)
	 */
	private $originalUploader;

	/**
	 * @access private
	 * @var string $type
	 *
	 * @ORM\Column(type="string")
	 */
	private $type;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * Returns the media object's id
	 *
	 * @access public
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return MediaFile
	 */
	public function setId(string $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the sha256 hash of the media object
	 *
	 * @access public
	 * @return string
	 */
	public function getSHA256(): string {
		return $this->sha256;
	}

	/**
	 * @param string $sha256
	 * @return MediaFile
	 */
	public function setSHA256(string $sha256): self {
		$this->sha256 = $sha256;
		return $this;
	}

	/**
	 * Returns the URL on the Gigadrive CDN
	 *
	 * @access public
	 * @return string
	 */
	public function getURL(): string {
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return MediaFile
	 */
	public function setURL(string $url): self {
		$this->url = $url;
		return $this;
	}

	/**
	 * Returns the user object of the original uploader, null if not available
	 *
	 * @access public
	 * @return User|null
	 */
	public function getOriginalUploader(): ?User {
		return $this->originalUploader;
	}

	/**
	 * @param User|null $originalUploader
	 * @return MediaFile
	 */
	public function setOriginalUploader(?User $originalUploader): self {
		$this->originalUploader = $originalUploader;
		return $this;
	}

	/**
	 * Returns the media file type
	 *
	 * @access public
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return MediaFile
	 */
	public function setType(string $type): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Returns this object as json object to be used in the API
	 *
	 * @access public
	 * @param bool $encode If true, will return a json string, else an associative array
	 * @return string|array
	 */
	public function toAPIJson($encode = true){
		$a = [
			"id" => $this->id,
			"sha256" => $this->sha256,
			"url" => $this->url,
			"type" => $this->type,
			"time" => $this->time
		];

		return $encode == true ? json_encode($a) : $a;
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

		if($this->type == "IMAGE"){
			$s .= '<img src="' . $this->getThumbnailURL() . '" width="100" height="100" class="rounded border border-mainColor bg-dark ignoreParentClick mr-2 mediaModalTrigger"' . (!is_null($postId) ? ' style="cursor: pointer" data-media-id="' . $this->id . '" data-post-id="' . $postId . '"' : "") . '/>';
		} else if($this->type == "VIDEO"){
			$s .= Util::getVideoEmbedCodeFromURL($this->url);
		} else if($this->type == "LINK"){
			// TODO
		}

		return $s;
	}
}