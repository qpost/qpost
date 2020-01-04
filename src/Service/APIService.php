<?php
/**
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
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

namespace qpost\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use qpost\Constants\FeedEntryType;
use qpost\Constants\NotificationType;
use qpost\Constants\PrivacyLevel;
use qpost\Entity\Block;
use qpost\Entity\FeedEntry;
use qpost\Entity\Follower;
use qpost\Entity\FollowRequest;
use qpost\Entity\Notification;
use qpost\Entity\Token;
use qpost\Entity\User;
use qpost\Repository\FollowerRepository;
use qpost\Repository\FollowRequestRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Security;
use function is_string;
use function json_decode;
use function json_encode;
use function strlen;
use function substr;

class APIService {
	/**
	 * @var APIService|null $instance
	 */
	public static $instance = null;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	/**
	 * @var RequestStack $requestStack
	 */
	private $requestStack;

	/**
	 * @var KernelInterface $kernel
	 */
	private $kernel;

	/**
	 * @var SerializerInterface $serializer
	 */
	private $serializer;

	/**
	 * @var Security $security
	 */
	private $security;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, RequestStack $requestStack, KernelInterface $kernel, Security $security) {
		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->requestStack = $requestStack;
		$this->kernel = $kernel;
		$this->security = $security;
		$this->serializer = SerializerBuilder::create()
			->setDebug($kernel->isDebug())
			->setCacheDir(__DIR__ . "/../../var/cache/" . $kernel->getEnvironment() . "/jms")
			->setPropertyNamingStrategy(
				new SerializedNameAnnotationStrategy(
					new IdenticalPropertyNamingStrategy()
				)
			)
			->build();

		self::$instance = $this;
	}

	/**
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface {
		return $this->logger;
	}

	/**
	 * @return KernelInterface
	 */
	public function getKernel(): KernelInterface {
		return $this->kernel;
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface {
		return $this->entityManager;
	}

	/**
	 * @return RequestStack
	 */
	public function getRequestStack(): RequestStack {
		return $this->requestStack;
	}

	/**
	 * @return Security
	 */
	public function getSecurity(): Security {
		return $this->security;
	}

	/**
	 * @return SerializerInterface
	 */
	public function getSerializer(): SerializerInterface {
		return $this->serializer;
	}

	public function getToken(): ?Token {
		$request = $this->requestStack->getCurrentRequest();
		$token = null;
		if ($request->cookies->has("sesstoken")) {
			$token = $request->cookies->get("sesstoken");
		} else if ($request->headers->has("Authorization")) {
			$authorization = $request->headers->get("Authorization");

			if ($authorization && is_string($authorization)) {
				$prefix = "Bearer ";

				// Check if starts with token type prefix
				if (strlen($authorization) > strlen($prefix) && substr($authorization, 0, strlen($prefix)) === $prefix) {
					$token = substr($authorization, strlen($prefix));
				}
			}
		}

		return $token ? $this->entityManager->getRepository(Token::class)->getTokenById($token) : null;
	}

	public function getUser(): ?User {
		$user = $this->security->getUser();

		return $user && $user instanceof User ? $user : null;
	}

	public function isAuthorized(): bool {
		return !is_null($this->getUser());
	}

	/**
	 * @param bool $requireAuthorization Whether or not the request has to be authorized.
	 * @return Response|null
	 */
	public function validate(bool $requireAuthorization): ?Response {
		if ($requireAuthorization && !$this->isAuthorized()) {
			$response = new JsonResponse();
			$response->setContent(json_encode(["error" => "Invalid token"]));
			$response->setStatusCode(401);

			return $response;
		}

		return null;
	}

	/**
	 * @param $content
	 * @return Response
	 */
	public function json($content, int $httpCode = 200): Response {
		$response = new JsonResponse();
		$response->setContent(json_encode($content));
		$response->setStatusCode($httpCode);

		return $response;
	}

	/**
	 * @return Response
	 */
	public function noContent(): Response {
		return (new Response())
			->setStatusCode(204)
			->setContent("");
	}

	/**
	 * @param $object
	 * @return array
	 */
	public function serialize($object): array {
		$context = new SerializationContext();
		$context->setSerializeNull(true);

		$string = $this->serializer->serialize($object, "json", $context);
		return json_decode($string, true);
	}

	/**
	 * @return ParameterBag
	 */
	public function parameters(): ParameterBag {
		$request = $this->requestStack->getCurrentRequest();

		if (!is_null($request)) {
			if ($request->isMethod("GET")) {
				return $request->query;
			} else {
				if ($content = $request->getContent()) {
					return new ParameterBag(json_decode($content, true));
				}
			}
		}

		return new ParameterBag([]);
	}

	/**
	 * @param User|FeedEntry $target
	 * @param User|null $user
	 * @return bool
	 */
	public function mayView($target, ?User $user = null): bool {
		if (!$user) $user = $this->getUser();

		if ($target instanceof FeedEntry) {
			if ($target->getType() === FeedEntryType::SHARE) {
				$parent = $target->getParent();

				if ($parent && !$this->mayView($parent, $user)) {
					return false;
				}
			}

			$targetUser = $target->getUser();
			if ($targetUser && $targetUser->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
				if ($user) {
					if ($user->getId() === $targetUser->getId()) {
						return true;
					} else {
						return $this->isFollowing($user, $targetUser);
					}
				} else {
					return false;
				}
			}

			return $this->mayView($target->getUser(), $user);
		} else if ($target instanceof User) {
			// self check
			if ($user) {
				if ($target->getId() === $user->getId()) {
					return true;
				}
			}

			// closed privacy level check
			if ($target->getPrivacyLevel() === PrivacyLevel::CLOSED) {
				return false;
			}

			// suspension check
			if ($target->isSuspended()) {
				return false;
			}

			// block check
			if ($user) {
				if ($this->entityManager->getRepository(Block::class)->isBlocked($target, $user)) {
					return false;
				}
			}
			return true;
		} else {
			return true;
		}
	}

	/**
	 * @param User $target
	 * @param User|null $user
	 * @return bool
	 */
	public function maySendNotifications(User $target, User $user = null): bool {
		if (!$user) $user = $this->getUser();
		if (!$user) return false;
		if ($target->getId() === $user->getId()) return false;
		if (!$this->mayView($target, $user)) return false;
		if ($target->getPrivacyLevel() === PrivacyLevel::PUBLIC) return true;

		if ($user->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
			return $this->isFollowing($target, $user);
		}

		return true;
	}

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public function isFollowing(User $from, User $to): bool {
		return $this->entityManager->getRepository(Follower::class)->isFollowing($from, $to);
	}

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public function follow(User $from, User $to): bool {
		if ($from->getId() === $to->getId()) return false;
		if ($from->getFollowingCount() >= 1000) return false;
		if ($to->getPrivacyLevel() === PrivacyLevel::CLOSED) return false;
		if (!$this->mayView($to, $from) && !$this->mayView($from, $to)) return false;

		/**
		 * @var FollowerRepository $followerRepository
		 */
		$followerRepository = $this->entityManager->getRepository(Follower::class);

		if ($followerRepository->isFollowing($from, $to)) return false;

		/**
		 * @var FollowRequestRepository $followRequestRepository
		 */
		$followRequestRepository = $this->entityManager->getRepository(FollowRequest::class);

		if ($to->getPrivacyLevel() === PrivacyLevel::PRIVATE) {
			// private user

			if (!$followRequestRepository->hasSentFollowRequest($from, $to)) {
				// create follow request

				$followRequest = (new FollowRequest())
					->setSender($from)
					->setReceiver($to)
					->setTime(new DateTime("now"));

				$this->entityManager->persist($followRequest);
				$this->entityManager->flush();

				return true;
			} else {
				return false;
			}
		}

		// create follower data
		$this->entityManager->persist((new Follower())
			->setSender($from)
			->setReceiver($to)
			->setTime(new DateTime("now")));

		// create notification
		if ($this->maySendNotifications($to, $from)) {
			$notification = (new Notification())
				->setUser($to)
				->setType(NotificationType::NEW_FOLLOWER)
				->setReferencedUser($from)
				->setSeen(false)
				->setNotified(false)
				->setTime(new DateTime("now"));

			$this->entityManager->persist($notification);
		}

		$this->entityManager->flush();

		return true;
	}

	/**
	 * @param User $from
	 * @param User $to
	 * @return bool
	 */
	public function unfollow(User $from, User $to): bool {
		/**
		 * @var FollowerRepository $followerRepository
		 */
		$followerRepository = $this->entityManager->getRepository(Follower::class);

		if (!$followerRepository->isFollowing($from, $to)) return false;

		$follower = $followerRepository->findOneBy([
			"sender" => $from,
			"receiver" => $to
		]);

		$feedEntry = $this->entityManager->getRepository(FeedEntry::class)->findOneBy([
			"type" => FeedEntryType::NEW_FOLLOWING,
			"user" => $from,
			"referencedUser" => $to
		]);

		if ($feedEntry) $this->entityManager->remove($feedEntry);

		$notification = $this->entityManager->getRepository(Notification::class)->findOneBy([
			"type" => NotificationType::NEW_FOLLOWER,
			"user" => $to,
			"referencedUser" => $from
		]);

		if ($notification) $this->entityManager->remove($notification);

		$return = false;

		if ($follower) {
			$this->entityManager->remove($follower);
			$return = true;
		}

		$this->entityManager->flush();

		return $return;
	}
}
