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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function json_encode;
use const JSON_FORCE_OBJECT;

class TranslationController extends qpostController {
	/**
	 * @Route("/translation.json")
	 * @return Response
	 * @author Mehdi Baaboura <mbaaboura@gigadrivegroup.com>
	 */
	public function indexAction(): Response {
		return JsonResponse::fromJsonString(json_encode($this->i18n->getAllLocaleStrings(), JSON_FORCE_OBJECT));
	}
}