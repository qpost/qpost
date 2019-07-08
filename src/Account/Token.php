<?php

namespace qpost\Account;

use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use qpost\Database\EntityManager;

/**
 * Represents a token to be used with the API for a user
 *
 * @package Account
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 *
 * @ORM\Entity
 */
class Token {
	/**
	 * @param User $user
	 * @param string $userAgent
	 * @param string $ip
	 * @return Token
	 */
	public static function createToken(User $user, string $userAgent, string $ip): Token {
		$entityManager = EntityManager::instance();

		$token = new Token();

		$expiry = new DateTime("now");
		$expiry->add(DateInterval::createFromDateString("6 month"));

		$token->setUser($user)
			->setUserAgent($userAgent)
			->setIP($ip)
			->setLastAccessTime(new DateTime("now"))
			->setExpiryTime($expiry)
			->setTime(new DateTime("now"));

		$entityManager->persist($token);
		$entityManager->flush();

		return $token;
	}

	/**
	 * @access private
	 * @var string $id
	 *
	 * @ORM\Id
	 * @ORM\Column(type="string", length=128)
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="qpost\Database\UniqueIdGenerator")
	 */
	private $id;

	/**
	 * @access private
	 * @var User $user
	 *
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	private $user;

	/**
	 * @access private
	 * @var string $lastIP
	 *
	 * @ORM\Column(type="string", length=255)
	 */
	private $lastIP;

	/**
	 * @access private
	 * @var string $userAgent;
	 *
	 * @ORM\Column(type="text")
	 */
	private $userAgent;

	/**
	 * @access private
	 * @var DateTime $time
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $time;

	/**
	 * @access private
	 * @var DateTime $lastAccessTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $lastAccessTime;

	/**
	 * @access private
	 * @var DateTime $expiry
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $expiry;

	/**
	 * Returns the ID of the token
	 *
	 * @access public
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return Token
	 */
	public function setId(string $id): self {
		$this->id = $id;
		return $this;
	}

	/**
	 * Returns the object of the token user
	 *
	 * @access public
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}

	/**
	 * @param User $user
	 * @return Token
	 */
	public function setUser(User $user): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * Returns the last IP that the token was used from
	 *
	 * @access public
	 * @return string
	 */
	public function getIP(): string {
		return $this->lastIP;
	}

	/**
	 * @param string $ip
	 * @return Token
	 */
	public function setIP(string $ip): self {
		$this->lastIP = $ip;
		return $this;
	}

	/**
	 * Returns information of the last IP that the token was used from
	 *
	 * @access public
	 * @return IPInformation|null
	 */
	public function getIPInformation(): ?IPInformation {
		return IPInformation::getInformationFromIP($this->lastIP);
	}

	/**
	 * Returns the last user agent this token was used with
	 *
	 * @access public
	 * @return string
	 */
	public function getUserAgent(): string {
		return $this->userAgent;
	}

	/**
	 * @param string $userAgent
	 * @return Token
	 */
	public function setUserAgent(string $userAgent): self {
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * Returns the time this token was created
	 *
	 * @access public
	 * @return DateTime
	 */
	public function getTime(): DateTime {
		return $this->time;
	}

	/**
	 * @param DateTime $time
	 * @return Token
	 */
	public function setTime(DateTime $time): self {
		$this->time = $time;
		return $this;
	}

	/**
	 * Returns the time the token was last used
	 *
	 * @access public
	 * @return DateTime|null
	 */
	public function getLastAccessTime(): ?DateTime {
		return $this->lastAccessTime;
	}

	/**
	 * @param DateTime|null $lastAccessTime
	 * @return Token
	 */
	public function setLastAccessTime(?DateTime $lastAccessTime): self {
		$this->lastAccessTime = $lastAccessTime;
		return $this;
	}

	/**
	 * Returns the time this token will expire
	 *
	 * @access public
	 * @return DateTime
	 */
	public function getExpiryTime(): DateTime {
		return $this->expiry;
	}

	/**
	 * @param DateTime $expiryTime
	 * @return DateTime
	 */
	public function setExpiryTime(DateTime $expiryTime): self {
		$this->expiry = $expiryTime;
		return $this;
	}

	/**
	 * Returns whether the token has expired
	 *
	 * @access public
	 * @return bool
	 */
	public function isExpired(){
		return !is_null($this->expiry) && $this->expiry <= new DateTime("now");
	}

	/**
	 * Renews the token expiry date
	 *
	 * @access public
	 */
	public function renew(){
		if(!$this->isExpired()){
			$this->lastAccessTime = new DateTime("now");

			$this->expiry = new DateTime("now");
			$this->expiry->add(DateInterval::createFromDateString("6 month"));
		}
	}

	/**
	 * Expires the token
	 *
	 * @access public
	 */
	public function expire(){
		if(!$this->isExpired()){
			$this->expiry = new DateTime("now");

			$entityManager = EntityManager::instance();
			$entityManager->persist($this);
			$entityManager->flush();
		}
	}
}