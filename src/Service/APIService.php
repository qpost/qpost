<?php
/**
 * Copyright (C) 2018-2019 Gigadrive - All rights reserved.
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

use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use function json_decode;
use function json_encode;

class APIService extends AuthorizationService {
	/**
	 * @var APIService|null $instance
	 */
	public static $instance = null;

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var KernelInterface $kernel
	 */
	private $kernel;

	/**
	 * @var SerializerInterface $serializer
	 */
	private $serializer;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, RequestStack $requestStack, KernelInterface $kernel) {
		parent::__construct($requestStack->getCurrentRequest(), $entityManager);

		$this->logger = $logger;
		$this->kernel = $kernel;
		$this->serializer = SerializerBuilder::create()
			->setDebug($kernel->isDebug())
			->setCacheDir(__DIR__ . "/../../var/cache/" . $kernel->getEnvironment() . "/jms/")
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
		if (!is_null($this->request)) {
			if ($this->request->isMethod("GET")) {
				return $this->request->query;
			} else {
				if ($content = $this->request->getContent()) {
					return new ParameterBag(json_decode($content, true));
				}
			}
		}

		return new ParameterBag([]);
	}
}
