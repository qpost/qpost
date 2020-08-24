<?php
/*
 * Copyright (C) 2018-2020 Gigadrive - All rights reserved.
 * https://gigadrivegroup.com
 * https://qpostapp.com
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

namespace qpost\Controller\API;

use Doctrine\ORM\EntityManagerInterface;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Service\Database\Pagination\PaginationService;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Service\GigadriveGeneralService;
use Psr\Log\LoggerInterface;
use qpost\Constants\APIParameterType;
use qpost\Controller\qpostController;
use qpost\Entity\Favorite;
use qpost\Entity\FeedEntry;
use qpost\Entity\Follower;
use qpost\Entity\Notification;
use qpost\Entity\Token;
use qpost\Entity\User;
use qpost\Exception\InvalidParameterIntegerRangeException;
use qpost\Exception\InvalidParameterStringLengthException;
use qpost\Exception\InvalidParameterTypeException;
use qpost\Exception\InvalidTokenException;
use qpost\Exception\MissingParameterException;
use qpost\Exception\ResourceNotFoundException;
use qpost\Service\APIService;
use qpost\Service\DataDeletionService;
use qpost\Service\StorageService;
use qpost\Service\TokenService;
use qpost\Service\TranslationService;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_filter;
use function array_values;
use function base64_decode;
use function intval;
use function is_array;
use function is_bool;
use function is_int;
use function is_null;
use function is_string;
use function preg_match;
use function strlen;
use function strtotime;
use function strval;
use const PHP_INT_MAX;

class APIController extends qpostController {
	protected $apiService;
	protected $entityManager;
	protected $logger;
	protected $dataDeletionService;
	protected $storageService;
	protected $messengerService;

	public function __construct(
		GigadriveGeneralService $generalService,
		PaginationService $paginationService,
		TranslationService $translationService,
		APIService $apiService,
		EntityManagerInterface $entityManager,
		LoggerInterface $logger,
		DataDeletionService $dataDeletionService,
		StorageService $storageService,
		TokenService $tokenService
	) {
		parent::__construct($generalService, $paginationService, $translationService, $tokenService);

		$this->apiService = $apiService;
		$this->entityManager = $entityManager;
		$this->logger = $logger;
		$this->dataDeletionService = $dataDeletionService;
		$this->storageService = $storageService;
		$this->messengerService = $apiService->messengerService;
	}

	/**
	 * @param null $data
	 * @param null $httpCode
	 * @return Response
	 */
	protected function response($data = null, $httpCode = null): Response {
		return $this->apiService->response($this->request(), $data, $httpCode);
	}

	/**
	 * @return Request|null
	 */
	protected function request(): ?Request {
		return $this->apiService->getRequestStack()->getCurrentRequest();
	}

	/**
	 * @param string $message
	 * @param int $httpCode
	 * @return Response
	 */
	protected function error(string $message, $httpCode = 400): Response {
		return $this->apiService->error($message, $httpCode);
	}

	/**
	 * @return Response
	 */
	protected function noContent(): Response {
		return $this->apiService->noContent();
	}

	/**
	 * Filters an array of User objects and removes those, which may not be viewed.
	 * @param User[] $users
	 * @return User[]
	 */
	protected function filterUsers(array $users): array {
		return array_values(array_filter($users, function (User $user) {
			return $this->apiService->mayView($user);
		}));
	}

	/**
	 * Filters an array of FeedEntry objects and removes those, which may not be viewed.
	 * @param FeedEntry[] $entries
	 * @return FeedEntry[]
	 */
	protected function filterFeedEntries(array $entries): array {
		return array_values(array_filter($entries, function (FeedEntry $feedEntry) {
			return $this->apiService->mayView($feedEntry);
		}));
	}

	/**
	 * Filters an array of Favorite objects and removes those, which may not be viewed.
	 * @param Favorite[] $favorites
	 * @return Favorite[]
	 */
	protected function filterFavorites(array $favorites): array {
		return array_values(array_filter($favorites, function (Favorite $favorite) {
			$feedEntry = $favorite->getFeedEntry();

			return !is_null($feedEntry) && $this->apiService->mayView($feedEntry);
		}));
	}

	/**
	 * Filters an array of Notification objects and removes those, which may not be viewed.
	 * @param Notification[] $notifications
	 * @return Notification[]
	 */
	protected function filterNotifications(array $notifications): array {
		return array_values(array_filter($notifications, function (Notification $notification) {
			$referencedUser = $notification->getReferencedUser();

			return $referencedUser && $this->apiService->mayView($referencedUser);
		}));
	}

	/**
	 * Filters an array of Token objects and removes those, which are expired.
	 * @param Token[] $tokens
	 * @return Token[]
	 */
	protected function filterTokens(array $tokens): array {
		return array_values(array_filter($tokens, function (Token $token) {
			return !$token->isExpired();
		}));
	}

	/**
	 * Filters an array of Follower objects and removes those, which are expired.
	 * @param Follower[] $followers
	 * @return array
	 */
	protected function filterFollowers(array $followers): array {
		return array_values(array_filter($followers, function (Follower $follower) {
			return $this->apiService->mayView($follower->getSender()) && $this->apiService->mayView($follower->getReceiver());
		}));
	}

	/**
	 * Checks whether a valid token was passed.
	 * @throws InvalidTokenException
	 */
	protected function validateAuth(): void {
		if (!$this->apiService->isAuthorized()) {
			throw new InvalidTokenException();
		}
	}

	/**
	 * Easily retrieve the 'limit' parameter value and validate it.
	 * @param int $maximum The maximum allowed value.
	 * @return int
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter does not match the specified range.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter does not match the specified type.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function limit(int $maximum = 15): int {
		$parameters = $this->parameters();

		if ($parameters->has("limit")) {
			$this->validateParameterType("limit", APIParameterType::INTEGER);
			$this->validateParameterIntegerRange("limit", 0, PHP_INT_MAX);

			return intval($parameters->get("limit"));
		}

		return $maximum;
	}

	/**
	 * @return ParameterBag
	 */
	protected function parameters(): ParameterBag {
		return $this->apiService->parameters();
	}

	/**
	 * Validates a passed parameter is of the specified type.
	 * @param string $parameterName The name of the parameter to be validated.
	 * @param string $parameterType The required type for the parameter.
	 * @param bool $required Whether or not this parameter is required.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter does not match the specified type.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function validateParameterType(string $parameterName, string $parameterType, bool $required = true): void {
		$parameters = $this->parameters();

		if (!$parameters->has($parameterName)) {
			if ($required) {
				throw new MissingParameterException($parameterName);
			}

			return;
		}

		$value = $parameters->get($parameterName);

		switch ($parameterType) {
			case APIParameterType::STRING:
				$this->validateString($parameterName, $value);
				break;
			case APIParameterType::INTEGER:
				$this->validateInteger($parameterName, $value);
				break;
			case APIParameterType::BOOLEAN:
				$this->validateBoolean($parameterName, $value);
				break;
			case APIParameterType::DATE:
				$this->validateDate($parameterName, $value);
				break;
			case APIParameterType::DATETIME:
				$this->validateDateTime($parameterName, $value);
				break;
			case APIParameterType::BASE64_ARRAY:
				$this->validateBase64Array($parameterName, $value);
				break;
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateString(string $parameterName, $value): void {
		if (!is_string($value)) {
			throw new InvalidParameterTypeException($parameterName, APIParameterType::STRING);
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateInteger(string $parameterName, $value): void {
		// https://stackoverflow.com/a/19235824/4117923
		if (!is_int($value) && !(is_string($value) && ($value === "0" || preg_match("/^-?[1-9][0-9]*$/D", $value)))) {
			throw new InvalidParameterTypeException($parameterName, APIParameterType::INTEGER);
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateBoolean(string $parameterName, $value): void {
		if (!is_bool($value)) {
			throw new InvalidParameterTypeException($parameterName, APIParameterType::BOOLEAN);
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateDate(string $parameterName, $value): void {
		if (!is_string($value) || @strtotime($value) === false) {
			throw new InvalidParameterTypeException($parameterName, APIParameterType::DATE);
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateDateTime(string $parameterName, $value): void {
		if (!is_string($value) || @strtotime($value) === false) {
			throw new InvalidParameterTypeException($parameterName, APIParameterType::DATETIME);
		}
	}

	/**
	 * @param string $parameterName
	 * @param $value
	 * @throws InvalidParameterTypeException
	 */
	private function validateBase64Array(string $parameterName, $value): void {
		$exception = new InvalidParameterTypeException($parameterName, APIParameterType::BASE64_ARRAY);

		if (!is_array($value)) {
			throw $exception;
		}

		foreach ($value as $item) {
			if (!is_string($item)) {
				throw $exception;
			}

			if (@base64_decode($item) === false) {
				throw $exception;
			}
		}
	}

	/**
	 * Validates a passed parameter is in the specified integer range.
	 * @param string $parameterName The name of the parameter to be validated.
	 * @param int $minimum The minimum value for the parameter.
	 * @param int $maximum The maximum value for the parameter. Use the PHP_INT_MAX constant to make the maximum optional.
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter does not match the specified range.
	 */
	protected function validateParameterIntegerRange(string $parameterName, int $minimum, int $maximum = PHP_INT_MAX): void {
		$param = intval($this->parameters()->get($parameterName));

		if (!($param >= $minimum && $param <= $maximum)) {
			throw new InvalidParameterIntegerRangeException($parameterName, $minimum, $maximum);
		}
	}

	/**
	 * Easily retrieve the 'offset' parameter value and validate it.
	 * @return int
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter does not match the specified range.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter does not match the specified type.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function offset(): int {
		$parameters = $this->parameters();

		if ($parameters->has("offset")) {
			$this->validateParameterType("offset", APIParameterType::INTEGER);
			$this->validateParameterIntegerRange("offset", 0, PHP_INT_MAX);

			return intval($parameters->get("offset"));
		}

		return 0;
	}

	/**
	 * Easily retrieve the 'max' parameter value and validate it.
	 * @return int|null
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter does not match the specified range.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter does not match the specified type.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function max(): ?int {
		return $this->getMinMax("max");
	}

	/**
	 * @param string $name
	 * @return int|null
	 * @throws InvalidParameterIntegerRangeException
	 * @throws InvalidParameterTypeException
	 * @throws MissingParameterException
	 */
	private function getMinMax(string $name): ?int {
		$parameters = $this->parameters();

		if ($parameters->has($name)) {
			$this->validateParameterType($name, APIParameterType::INTEGER);
			$this->validateParameterIntegerRange($name, 0);

			return intval($parameters->get($name));
		}

		return null;
	}

	/**
	 * Easily retrieve the 'min' parameter value and validate it.
	 * @return int|null
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter does not match the specified range.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter does not match the specified type.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function min(): ?int {
		return $this->getMinMax("min");
	}

	/**
	 * Gets a User object from a passed parameter value.
	 * @param string $parameterName The name of the parameter that holds the user ID.
	 * @param bool $required Whether or not this parameter is required.
	 * @param bool $mayViewCheck Whether or not to execute the mayView check.
	 * @return User|null The final User object, null if the parameter was not passed.
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter holds an invalid integer.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter is not an integer.
	 * @throws ResourceNotFoundException Thrown if the passed parameter holds an unknown ID.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 */
	protected function user(string $parameterName = "user", bool $required = true, bool $mayViewCheck = true): ?User {
		$parameters = $this->parameters();

		if ($parameters->has($parameterName)) {
			$this->validateParameterType($parameterName, APIParameterType::INTEGER);
			$this->validateParameterIntegerRange($parameterName, 0);

			$id = intval($parameters->get($parameterName));

			$user = $this->entityManager->getRepository(User::class)->getUserById($id);

			if (is_null($user) || $mayViewCheck && !$this->apiService->mayView($user)) {
				throw new ResourceNotFoundException();
			}

			return $user;
		}

		if ($required) {
			throw new MissingParameterException($parameterName);
		}

		return null;
	}

	/**
	 * Gets a FeedEntry object from a passed parameter value.
	 * @param string $parameterName The name of the parameter that holds the entry ID.
	 * @param string|null $type The desired FeedEntryType, null to ignore this check.
	 * @param bool $required Whether or not this parameter is required.
	 * @param bool $mayViewCheck Whether or not to execute the mayView check.
	 * @return User|null The final User object, null if the parameter was not passed.
	 * @throws InvalidParameterIntegerRangeException Thrown if the passed parameter holds an invalid integer.
	 * @throws InvalidParameterTypeException Thrown if the passed parameter is not an integer.
	 * @throws MissingParameterException Thrown if the parameter was marked as required, but not found.
	 * @throws ResourceNotFoundException Thrown if the passed parameter holds an unknown ID.
	 */
	protected function feedEntry(string $parameterName, ?string $type = null, bool $required = true, bool $mayViewCheck = true): ?FeedEntry {
		$parameters = $this->parameters();

		if ($parameters->has($parameterName)) {
			$this->validateParameterType($parameterName, APIParameterType::INTEGER);
			$this->validateParameterIntegerRange($parameterName, 0);

			$id = intval($parameters->get($parameterName));

			$entry = $this->entityManager->getRepository(FeedEntry::class)->getEntryById($id);

			if (is_null($entry) || $mayViewCheck && !$this->apiService->mayView($entry)) {
				throw new ResourceNotFoundException();
			}

			if (!is_null($type) && $entry->getType() !== $type) {
				throw new ResourceNotFoundException();
			}

			return $entry;
		}

		if ($required) {
			throw new MissingParameterException($parameterName);
		}

		return null;
	}

	/**
	 * Gets the currently logged in user.
	 * @return User|null
	 */
	protected function getUser(): ?User {
		$user = parent::getUser();

		return !is_null($user) && $user instanceof User ? $user : null;
	}

	/**
	 * Validates a passed string parameter is in the specified character length range.
	 * @param string $parameterName The name of the parameter to be validated.
	 * @param int $minimum The minimum value for the character length.
	 * @param int $maximum The maximum value for the character length.
	 * @throws InvalidParameterStringLengthException Thrown if the passed parameter does not match the specified range.
	 */
	protected function validateParameterStringLength(string $parameterName, int $minimum, int $maximum): void {
		$param = strval($this->parameters()->get($parameterName));
		$length = strlen($param);

		if (!($length >= $minimum && $length <= $maximum)) {
			throw new InvalidParameterStringLengthException($parameterName, $minimum, $maximum);
		}
	}
}