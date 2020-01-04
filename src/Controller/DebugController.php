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

namespace qpost\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\FeedEntry;
use qpost\Service\APIService;
use qpost\Twig\Twig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebugController extends AbstractController {
	/**
	 * @Route("/debug", condition="'dev' === '%kernel.environment%'")
	 * @param EntityManagerInterface $entityManager
	 * @param LoggerInterface $logger
	 * @param APIService $apiService
	 * @return Response
	 */
	public function debugAction(EntityManagerInterface $entityManager, LoggerInterface $logger, APIService $apiService) {
		$value = $entityManager->getRepository(FeedEntry::class)->getFeed($this->getUser());

		return $this->render("debug.html.twig", Twig::param([
			"value" => $value
		]));
	}
}