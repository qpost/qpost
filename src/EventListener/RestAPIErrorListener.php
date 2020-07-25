<?php
/**
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

namespace qpost\EventListener;

use qpost\Exception\RestAPIErrorException;
use qpost\Service\APIService;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class RestAPIErrorListener {
	/**
	 * @var APIService $apiService
	 */
	private $apiService;

	public function __construct(APIService $apiService) {
		$this->apiService = $apiService;
	}

	public function onKernelException(ExceptionEvent $event) {
		$exception = $event->getThrowable();

		if (!$exception instanceof RestAPIErrorException) {
			return;
		}

		$event->setResponse($this->apiService->error($exception->getMessage(), $exception->getErrorCode()));
	}
}