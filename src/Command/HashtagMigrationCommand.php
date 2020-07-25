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

namespace qpost\Command;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use qpost\Entity\FeedEntry;
use qpost\Entity\Hashtag;
use qpost\Util\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HashtagMigrationCommand extends Command {
	protected static $defaultName = "qpost:hashtag-migration";

	/**
	 * @var LoggerInterface $logger
	 */
	private $logger;

	/**
	 * @var EntityManagerInterface $entityManager
	 */
	private $entityManager;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
	}

	protected function configure() {
		$this
			->setDescription("Goes through the database and adds Hashtag entities for all FeedEntry entities with unlogged hashtags in them.")
			->setHelp("Goes through the database and adds Hashtag entities for all FeedEntry entities with unlogged hashtags in them.");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$feedEntryRepository = $this->entityManager->getRepository(FeedEntry::class);
		$hashtagRepository = $this->entityManager->getRepository(Hashtag::class);

		/**
		 * @var FeedEntry[] $feedEntries
		 */
		$feedEntries = $feedEntryRepository->createQueryBuilder("f")
			->where("f.text IS NOT NULL")
			->andWhere("f.text LIKE :query")
			->setParameter("query", "%#%", Type::STRING)
			->getQuery()
			->getResult();

		$output->writeln("Results: " . count($feedEntries));

		foreach ($feedEntries as $feedEntry) {
			$output->writeln("#" . $feedEntry->getId() . " - " . $feedEntry->getText());

			$tags = Util::extractHashtags($feedEntry->getText());

			if ($tags && count($tags) > 0) {
				foreach ($tags as $tag) {
					$hashtag = $hashtagRepository->findHashtag($tag);
					if (!$hashtag) {
						$hashtag = (new Hashtag())
							->setId($tag)
							->setCreator($feedEntry->getUser())
							->setCreatingEntry($feedEntry)
							->setTime(new DateTime("now"));
					}

					$hashtag->addFeedEntry($feedEntry);

					$this->entityManager->persist($hashtag);
					$this->entityManager->flush();
				}
			}
		}

		$output->writeln("Done.");

		return 0;
	}
}