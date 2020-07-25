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

namespace qpost\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Controller\GigadriveController;
use qpost\Constants\MiscConstants;
use qpost\Entity\User;
use qpost\Twig\Twig;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailController extends GigadriveController {
	/**
	 * @Route("/verify-email/{userId}/{activationToken}")
	 *
	 * @param int $userId
	 * @param string $activationToken
	 * @param EntityManagerInterface $entityManager
	 * @return Response
	 */
	public function verifyEmail(int $userId, string $activationToken, EntityManagerInterface $entityManager) {
		/**
		 * @var User $user
		 */
		$user = $entityManager->getRepository(User::class)->findOneBy([
			"id" => $userId,
			"emailActivated" => false,
			"emailActivationToken" => $activationToken
		]);

		if ($user) {
			$user->setEmailActivated(true);
			$entityManager->persist($user);
			$entityManager->flush();

			return $this->render("pages/verifyEmail.html.twig", Twig::param([
				"title" => "Verify your email address",
				MiscConstants::CANONICAL_URL => $this->generateUrl("qpost_verifyemail_verifyemail", ["userId" => $userId, "activationToken" => $activationToken], UrlGeneratorInterface::ABSOLUTE_URL)
			]));
		} else {
			throw $this->createNotFoundException("Invalid token-user combination.");
		}
	}
}