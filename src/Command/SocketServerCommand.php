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

namespace qpost\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\Socket\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SocketServerCommand extends Command {
	protected static $defaultName = "qpost:socket-server";

	private $logger;
	private $entityManager;

	public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
		parent::__construct(null);

		$this->logger = $logger;
		$this->entityManager = $entityManager;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$port = !empty($_ENV["GATEWAY_LOCAL_PORT"]) ? $_ENV["GATEWAY_LOCAL_PORT"] : 8080;

		$loop = Factory::create();
		$server = new Server("127.0.0.1:" . $port, $loop);

		$restartCount = 0;
		$restartTimer = $loop->addPeriodicTimer(1, function () use ($server, &$restartCount, $loop) {
			$restartCount++;

			if($restartCount >= 620){
				$this->logger->info("Force Restarting.");

				$server->close();
				$loop->stop();

				return;
			}

			if ($restartCount >= 600) {
				$this->logger->info("Restarting.");
				$server->close();
			}
		});

		$this->logger->info("Running on port " . $port);

		$loop->run();
	}
}