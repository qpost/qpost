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

namespace qpost\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gigadrive\Bundle\SymfonyExtensionsBundle\DependencyInjection\Util;
use Parsedown;
use Psr\Log\LoggerInterface;
use qpost\Entity\ChangelogEntry;
use qpost\Repository\ChangelogEntryRepository;
use qpost\Service\GitLabService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_null;
use function str_replace;

class UpdateChangelogsCommand extends Command {
	protected static $defaultName = "qpost:update-changelogs";

	private $logger;
	private $entityManager;
	private $gitLabService;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, GitLabService $gitLabService) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
		$this->gitLabService = $gitLabService;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/**
		 * @var ChangelogEntryRepository $repository
		 */
		$repository = $this->entityManager->getRepository(ChangelogEntry::class);

		$output->writeln("Fetching releases...");
		$releases = $this->gitLabService->fetchReleases();
		$releasesCount = count($releases);
		$output->writeln("Releases found: " . $releasesCount);

		if ($releasesCount > 0) {
			$parsedown = (new Parsedown)->setSafeMode(true);

			foreach ($releases as $release) {
				if (!isset($release["release"]) || is_null($release["release"])) continue;
				if (!isset($release["release"]["description"]) || Util::isEmpty($release["release"]["description"])) continue;

				$tag = $release["name"];
				$description = $release["release"]["description"];

				$output->writeln("Found release " . $tag);

				$entry = $repository->find($tag);

				if (is_null($entry)) {
					$output->writeln("Creating new entry");

					$entry = (new ChangelogEntry())
						->setTag($tag)
						->setTime(new DateTime("now"));
				} else {
					$output->writeln("Updating existing entry");
				}

				$entry->setDescription(str_replace("<a href", "<a target=\"_blank\" href", $parsedown->text($description)));

				$this->entityManager->persist($entry);

				$output->writeln("Persisting");
			}

			$output->writeln("Flushing");
			$this->entityManager->flush();
		}

		$output->writeln("Done.");

		return 0;
	}
}