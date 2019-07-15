<?php

namespace qpost\Router\API\Status;

use qpost\Database\EntityManager;
use qpost\Feed\FeedEntry;
use function qpost\Router\API\api_get_token;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_prepare_object;
use function qpost\Router\API\api_request_data;
use function qpost\Router\create_route_get;

create_route_get("/api/status", function () {
	if (api_method_check($this, "GET", false)) {
		$token = api_get_token();
		$currentUser = !is_null($token) ? $token->getUser() : null;
		$requestData = api_request_data($this);

		if (isset($requestData["id"])) {
			$id = $requestData["id"];

			if (is_numeric($id)) {
				$entityManager = EntityManager::instance();

				/**
				 * @var FeedEntry $feedEntry
				 */
				$feedEntry = $entityManager->getRepository(FeedEntry::class)->findOneBy([
					"id" => $id
				]);

				if (!is_null($feedEntry) && $feedEntry->mayView($currentUser)) {
					return json_encode(["result" => api_prepare_object($feedEntry)]);
				} else {
					$this->response->status = "404";
					return json_encode(["error" => "The requested resource could not be found."]);
				}
			} else {
				$this->response->status = "400";
				return json_encode(["error" => "'id' has to be an integer."]);
			}
		} else {
			$this->response->status = "400";
			return json_encode(["error" => "'id' is required."]);
		}
	} else {
		return "";
	}
});