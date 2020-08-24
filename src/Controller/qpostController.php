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

namespace qpost\Controller;

use Gigadrive\Bundle\SymfonyExtensionsBundle\Controller\GigadriveController;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Service\Database\Pagination\PaginationService;
use Gigadrive\Bundle\SymfonyExtensionsBundle\Service\GigadriveGeneralService;
use qpost\Service\TokenService;
use qpost\Service\TranslationService;

class qpostController extends GigadriveController {
	/**
	 * @var TranslationService $i18n
	 */
	protected $i18n;

	/**
	 * @var TokenService $tokenService
	 */
	protected $tokenService;

	public function __construct(
		GigadriveGeneralService $generalService,
		PaginationService $pagination,
		TranslationService $i18n,
		TokenService $tokenService
	) {
		parent::__construct($generalService, $pagination);

		$this->i18n = $i18n;
		$this->tokenService = $tokenService;
	}
}