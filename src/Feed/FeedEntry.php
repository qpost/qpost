<?php

namespace qpost\Feed;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use qpost\Account\User;
use qpost\Database\EntityManager;
use qpost\Media\MediaFile;
use qpost\Util\Util;
use function qpost\Router\API\api_get_token;

/**
 * Represents a feed entry
 *
 * @package FeedEntry
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 *
 * @ORM\Entity
 */
class FeedEntry {
	/**
	 * @access private
	 * @var int $id
	 *
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @access private
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Account\User")
	 */
	private $user;

	/**
	 * @access private
	 * @var string|null $text
	 *
	 * @ORM\Column(type="text",nullable=true)
	 */
	private $text;

	/**
	 * @access private
	 * @var User|null $following
	 *
	 * @ORM\ManyToOne(targetEntity="qpost\Account\User")
	 */
	private $following;

	/**
	 * @access private
	 * @var FeedEntry|null $post
	 *
	 * @ORM\ManyToOne(targetEntity="FeedEntry")
	 */
	private $post;

	/**
	 * @access private
	 * @var string $sessionId
	 *
	 * @ORM\Column(type="string", length=200)
	 */
	private $sessionId;

	/**
	 * @access private
	 * @var string $type
	 *
	 * @ORM\Column(type="string", length=16)
	 */
	private $type;

	/**
	 * @access private
	 * @var bool $nsfw
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $nsfw;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @access private
	 * @var MediaFile[] $attachments
	 *
	 * @ORM\ManyToMany(targetEntity="qpost\Media\MediaFile", inversedBy="posts", cascade={"persist"})
	 * @ORM\JoinTable()
	 */
	private $attachments;

	public function __construct() {
		$this->attachments = new ArrayCollection();
	}

	/**
	 * Returns the ID of the feed entry
	 *
	 * @access public
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 * @return FeedEntry
	 */
	public function setId(int $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the user object of the user that created the feed entry
	 *
	 * @access public
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return FeedEntry
	 */
	public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * Returns the text of the feed entry, null if no text is available
	 *
	 * @access public
	 * @return string|null
	 */
	public function getText(): ?string {
		return $this->text;
	}

	/**
	 * @param string|null $text
	 * @return FeedEntry
	 */
	public function setText(?string $text): self {
		$this->text = $text;
		return $this;
	}

	/**
	 * Returns the user object of the user that was followed (context of the feed entry), returns null if not available
	 *
	 * @access public
	 * @return User
	 */
	public function getFollowing(): ?User {
		return $this->following;
	}

	/**
	 * @param User|null $following
	 * @return FeedEntry
	 */
	public function setFollowing(?User $following): self {
		$this->following = $following;
		return $this;
	}

	/**
	 * Returns the feed entry object of the post that was shared/replied to (context of the feed entry), returns null if not available
	 *
	 * @access public
	 * @return FeedEntry|null
	 */
	public function getPost(): ?FeedEntry {
		return $this->post;
	}

	/**
	 * @param FeedEntry|null $post
	 * @return FeedEntry
	 */
	public function setPost(?FeedEntry $post): self {
		$this->post = $post;
		return $this;
	}

	/**
	 * Returns the session ID used by $user when creating this feed entry
	 *
	 * @access public
	 * @return string
	 */
	public function getSessionId(): string {
		return $this->sessionId;
	}

	/**
	 * @param string $sessionId
	 * @return FeedEntry
	 */
	public function setSessionId(string $sessionId): self {
		$this->sessionId = $sessionId;
		return $this;
	}

	/**
	 * Returns the type of the feed entry
	 *
	 * @access public
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return FeedEntry
	 */
	public function setType(string $type): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Returns whether this post was marked as NSFW
	 *
	 * @access public
	 * @return bool
	 */
	public function isNSFW(): bool {
		return $this->nsfw;
	}

	/**
	 * @param bool $nsfw
	 * @return FeedEntry
	 */
	public function setNSFW(bool $nsfw): self {
		$this->nsfw = $nsfw;
		return $this;
	}

	/**
	 * Returns an array of the IDs of the attachments
	 *
	 * @access public
	 * @return Collection|MediaFile[]
	 */
	public function getAttachments(): Collection {
		return $this->attachments;
	}

	/**
	 * @param MediaFile $attachment
	 * @return FeedEntry
	 */
	public function addAttachment(MediaFile $attachment): self {
		if (!$this->attachments->contains($attachment)) {
			$this->attachments[] = $attachment;
		}

		return $this;
	}

	/**
	 * @param MediaFile $attachment
	 * @return FeedEntry
	 */
	public function removeAttachment(MediaFile $attachment): self {
		if ($this->attachments->contains($attachment)) {
			$this->attachments->removeElement($attachment);
		}

		return $this;
	}

	/**
	 * Returns the timestamp of when the feed entry was created
	 *
	 * @access public
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return FeedEntry
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getReplyCount(): int {
		return EntityManager::instance()->getRepository(FeedEntry::class)->count([
			"post" => $this,
			"type" => FeedEntryType::POST
		]);
	}

	/**
	 * @return int
	 */
	public function getShareCount(): int {
		return EntityManager::instance()->getRepository(FeedEntry::class)->count([
			"post" => $this,
			"type" => FeedEntryType::SHARE
		]);
	}

	/**
	 * @return int
	 */
	public function getFavoriteCount(): int {
		return EntityManager::instance()->getRepository(Favorite::class)->count([
			"post" => $this
		]);
	}

	/**
	 * @return bool|null
	 */
	public function isShared(): bool {
		$token = api_get_token();

		if (!is_null($token) && $token->getUser()) {
			return Share::hasShared($token->getUser(), $this);
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	public function isFavorited(): bool {
		$token = api_get_token();

		if (!is_null($token) && $token->getUser()) {
			return Favorite::hasFavorited($token->getUser(), $this);
		}

		return false;
	}

	/**
	 * Deletes the feed entry and all its references from the database
	 *
	 * @access public
	 */
	public function delete(){
		$entityManager = EntityManager::instance();

		/**
		 * @var FeedEntry[] $shares
		 */
		$shares = $entityManager->getRepository(FeedEntry::class)->findBy([
			"post" => $this
		]);

		foreach ($shares as $share) {
			$entityManager->remove($share);
		}

		/**
		 * @var Notification[] $notifications
		 */
		$notifications = $entityManager->getRepository(Notification::class)->findBy([
			"post" => $this
		]);

		foreach ($notifications as $notification) {
			$entityManager->remove($notification);
		}

		/**
		 * @var Favorite[] $favorites
		 */
		$favorites = $entityManager->getRepository(Favorite::class)->findBy([
			"post" => $this
		]);

		foreach ($favorites as $favorite) {
			$entityManager->remove($favorite);
		}

		$entityManager->remove($this);
		$entityManager->flush();
	}

	/**
	 * Returns whether the current user may view this feed entry
	 *
	 * @access public
	 * @return bool
	 */
	public function mayView(User $user = null) {
		return $this->getUser()->mayView($user) && (!is_null($this->getPost()) ? $this->getPost()->getUser()->mayView($user) : true);
	}

	/**
	 * Returns HTML code to use in a feed entry list (search, profile, home feed, ...)
	 *
	 * @access public
	 * @param bool $noBorder If false, the additional HTML for easier use in bootstrap lists won't be included
	 * @param bool $hideAttachments If true, the attachments will be hidden
	 * @return string
	 */
	public function toListHTML($noBorder = false, $hideAttachments = false){
		if(!$this->mayView()) return "";

		$user = $this->getUser();
		if(is_null($user)) return "";

		$s = "";

		if ($this->getType() == FeedEntryType::POST) {
			if($noBorder == false) $s .= '<li class="list-group-item px-0 py-0 feedEntry statusTrigger" data-status-render="' . $this->getId() . '" data-entry-id="' . $this->getId() . '">';
			$s .= '<div class="px-4 py-2">';
			$s .= '<div class="row">';
			$s .= '<div class="float-left">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick float-left">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $user->getAvatarURL() . '" width="40" height="40"/>';
			$s .= '</a>';
			$s .= '<p class="float-left ml-1 mb-0">';
			$s .= '<a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold convertEmoji">' . $user->getDisplayName() . 'checkmark' . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $user->getUsername() . ' </span>';

			$s .= '<br/>';

			$s .= '<span class="small text-muted"><i class="far fa-clock"></i> ';
			$s .= $this->getTime()->format("YYYY-mm-dd HH:ii:ss"); # TODO
			$s .= '</span>';

			$s .= '</p>';
			$s .= '</div>';

			if($this->nsfw){
				$s .= '</div>';
				$s .= '</div>';

				$s .= '<div class="nsfwInfo ignoreParentClick bg-monochrome text-qp-gray text-center py-4">';
				$s .= '<div style="font-size: 26px;"><i class="fas fa-exclamation-triangle"></i></div>';
				$s .= 'This post was marked as NSFW. Click to show.';
				$s .= '</div>';

				$s .= '<div class="px-4">';
				$s .= '<div class="row">';
			}

			if(!is_null($this->getText())) $s .= '<div class="float-left ml-1 my-2" style="width: 100%">';

			if(!is_null($this->getText())){
				$s .= '<p class="mb-0 convertEmoji' . ($this->nsfw ? ' hiddenNSFW d-none' : '') . '" style="word-wrap: break-word;">';
				$s .= Util::convertPost($this->getText());
				$s .= '</p>';
			}

			if($hideAttachments == false && count($this->attachments) > 0){
				if(!is_null($this->getText())) $s .= '</div>';
				$s .= '</div>';
				$s .= '</div>';

				if($this->nsfw){
					$s .= '<div class="hiddenNSFW d-none">';
				}

				$attachments = [];
				foreach ($this->getAttachments() as $attachment) {
					array_push($attachments, $attachment->getMediaFile());
				}

				$s .= Util::renderAttachmentEmbeds($attachments, $this->id);

				if($this->nsfw){
					$s .= '</div>';
				}

				$s .= '<div class="px-4">';
				$s .= '<div class="row">';
				$s .= '<div class="float-left ml-1 my-2" style="width: 100%">';
			}

			$s .= Util::getPostActionButtons($this);
			$s .= '</div>';
			$s .= '</div>';
			$s .= '</div>';
			if($noBorder == false) $s .= '</li>';

			return $s;
		} else if ($this->getType() == FeedEntryType::NEW_FOLLOWING) {
			$u2 = $this->getFollowing();
			if (is_null($u2)) return "";

			if($noBorder == false) $s .= '<li class="list-group-item px-2 py-2" data-entry-id="' . $this->getId() . '">';

			if (Util::isLoggedIn() && Util::getCurrentUser()->getId() == $this->getUser()->getId()) {
				$s .= '<div class="float-right">';
				$s .= '<span class="deleteButton ml-2" data-post-id="' . $this->getId() . '" data-toggle="tooltip" title="Delete">';
				$s .= '<i class="fas fa-trash-alt"></i>';
				$s .= '</span>';
				$s .= '</div>';
			}

			$s .= '<i class="fas fa-user-plus text-info"></i> <b><a href="/' . $user->getUsername() . '" class="clearUnderline convertEmoji">' . $user->getDisplayName() . "CHECKMARK" . '</a></b> is now following <a href="/' . $u2->getUsername() . '" class="clearUnderline convertEmoji">' . $u2->getDisplayName() . '</a> &bull; <span class="text-muted">' . $this->getTime()->format("y-m-d H:i:s") . '</span>';
			if($noBorder == false) $s .= '</li>';

			return $s;
		} else if ($this->getType() == FeedEntryType::SHARE) {
			$sharedPost = $this->getPost();
			$sharedUser = $sharedPost->getUser();

			if(is_null($sharedPost) || is_null($sharedUser))
				return "";

			if($noBorder == false) $s .= '<li class="list-group-item px-0 py-0 feedEntry statusTrigger" data-status-render="' . $sharedPost->getId() . '" data-entry-id="' . $this->getId() . '">';
			$s .= '<div class="px-4 py-2">';
			$s .= '<div class="small text-muted">';
			$s .= '<i class="fas fa-share-alt text-blue"></i> Shared by <a href="/' . $user->getUsername() . '" class="clearUnderline ignoreParentClick">' . $user->getDisplayName() . "CHECKMARK" . '</a> &bull; ' . $this->getTime()->format("YYYY-mm-dd HH:ii:ss");
			$s .= '</div>';
			$s .= '<div class="row">';
			$s .= '<div class="float-left">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick float-left">';
			$s .= '<img class="rounded mx-1 my-1" src="' . $sharedUser->getAvatarURL() . '" width="40" height="40"/>';
			$s .= '</a>';
			$s .= '<p class="float-left ml-1 mb-0">';
			$s .= '<a href="/' . $sharedUser->getUsername() . '" class="clearUnderline ignoreParentClick">';
			$s .= '<span class="font-weight-bold convertEmoji">' . $sharedUser->getDisplayName() . "CHECKMARK" . '</span>';
			$s .= '</a>';

			$s .= '<span class="text-muted font-weight-normal"> @' . $sharedUser->getUsername() . ' </span>';

			$s .= '<br/>';

			$s .= '<span class="small text-muted"><i class="far fa-clock"></i> ';
			$s .= $sharedPost->getTime()->format("YYYY-mm-dd HH:ii:ss");
			$s .= '</span>';

			$s .= '</p>';
			$s .= '</div>';

			if($sharedPost->isNSFW()){
				$s .= '</div>';
				$s .= '</div>';

				$s .= '<div class="nsfwInfo ignoreParentClick bg-monochrome text-qp-gray text-center py-4">';
				$s .= '<div style="font-size: 26px;"><i class="fas fa-exclamation-triangle"></i></div>';
				$s .= 'This post was marked as NSFW. Click to show.';
				$s .= '</div>';

				$s .= '<div class="px-4">';
				$s .= '<div class="row">';
			}

			if(!is_null($sharedPost->getText())) $s .= '<div class="float-left ml-1" style="width: 100%">';

			$parent = $sharedPost->getPost();
			if(!is_null($parent)){
				$parentCreator = $parent->getUser();

				if(!is_null($parentCreator)){
					$s .= '<div class="text-muted small">';
					$s .= 'Replying to <a href="/' . $parentCreator->getUsername() . '">@' . $parentCreator->getUsername() . '</a>';
					$s .= '</div>';
				}
			}

			if(!is_null($sharedPost->getText())){
				$s .= '<p class="mb-0 convertEmoji' . ($sharedPost->isNSFW() ? ' hiddenNSFW d-none' : '') . '" style="word-wrap: break-word;">';
				$s .= Util::convertPost($sharedPost->getText());
				$s .= '</p>';
			}

			if($hideAttachments == false && count($sharedPost->attachments) > 0){
				if(!is_null($sharedPost->getText())) $s .= '</div>';
				$s .= '</div>';
				$s .= '</div>';

				if($sharedPost->isNSFW()){
					$s .= '<div class="hiddenNSFW d-none">';
				}

				$attachments = [];
				foreach ($sharedPost->getAttachments() as $attachment) {
					array_push($attachments, $attachment->getMediaFile());
				}

				$s .= Util::renderAttachmentEmbeds($attachments, $this->id);

				if($sharedPost->isNSFW()){
					$s .= '</div>';
				}

				$s .= '<div class="px-4">';
				$s .= '<div class="row">';
				$s .= '<div class="float-left ml-1 my-2" style="width: 100%">';
			}

			$s .= Util::getPostActionButtons($sharedPost);
			$s .= '</div>';
			$s .= '</div>';
			$s .= '</div>';
			if($noBorder == false) $s .= '</li>';

			return $s;
		}

		return "";
	}
}