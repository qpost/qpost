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

return [
	Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
	Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
	Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
	Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class => ['all' => true],
	Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
	Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
	Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
	Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
	Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
	Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
	Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
	Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
	Symfony\Bundle\WebServerBundle\WebServerBundle::class => ['dev' => true],
	Sentry\SentryBundle\SentryBundle::class => ['prod' => true]
];
