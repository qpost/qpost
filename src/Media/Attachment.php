<?php

namespace qpost\Media;

use Doctrine\ORM\Mapping as ORM;
use qpost\Feed\FeedEntry;

/**
 * Class Attachment
 * @package qpost\Media
 *
 * @ORM\Entity
 */
class Attachment {
	/**
	 * @var integer $id
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var FeedEntry $post
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Feed\FeedEntry", inversedBy="attachments")
	 */
	private $post;

	/**
	 * @var MediaFile $mediaFile
	 *
	 * @ORM\ManyToOne(targetEntity="MediaFile")
	 */
	private $mediaFile;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return Attachment
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return FeedEntry
	 */
	public function getPost(): FeedEntry {
		return $this->post;
	}

	/**
	 * @param FeedEntry $post
	 * @return Attachment
	 */
	public function setPost(FeedEntry $post): self {
		$this->post = $post;
		return $this;
	}

	/**
	 * @return MediaFile
	 */
	public function getMediaFile(): MediaFile {
		return $this->mediaFile;
	}

	/**
	 * @param MediaFile $mediaFile
	 * @return Attachment
	 */
	public function setMediaFile(MediaFile $mediaFile): self {
		$this->mediaFile = $mediaFile;
		return $this;
	}
}