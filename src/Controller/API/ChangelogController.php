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

use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use qpost\Entity\ChangelogEntry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function strlen;
use function substr;
use function version_compare;

/**
 * @Route("/api")
 */
class ChangelogController extends APIController {
	/**
	 * @Route("/changelog", methods={"GET"})
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function info(): Response {
		$this->validateAuth();
		$user = $this->getUser();
		$repository = $this->entityManager->getRepository(ChangelogEntry::class);

		$currentVersion = $_ENV["VERSION"];
		$latestSeenVersion = $this->cutV(Util::def($user->getLatestSeenChangelog(), "1.0.0"));

		// Don't query if latest seen version is the current one
		if ($currentVersion === $latestSeenVersion) return $this->response(null);

		/**
		 * @var ChangelogEntry[] $latest
		 */
		$latest = $repository->getLatest();

		$entry = null;
		foreach ($latest as $changelogEntry) {
			$tag = $this->cutV($changelogEntry->getTag());

			if (
				version_compare($latestSeenVersion, $tag, "<") && // has to be newer than last seen version
				version_compare($currentVersion, $tag, ">=") // has to be at most current version
			) {
				$entry = $changelogEntry;
				break;
			}
		}

		if (!is_null($entry)) {
			$user->setLatestSeenChangelog($entry->getTag());
			$this->entityManager->persist($user);
			$this->entityManager->flush();
		}

		return $this->response($entry);
	}

	private function cutV(string $tag): string {
		return Util::startsWith($tag, "v") ? substr($tag, 1, strlen($tag) - 1) : $tag;
	}
}