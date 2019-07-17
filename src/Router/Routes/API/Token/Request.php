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

namespace qpost\Router\API\Token;

/*use Doctrine\DBAL\Types\Type;
use qpost\Account\Token;
use qpost\Account\User;
use qpost\Database\EntityManager;
use function qpost\Router\API\api_prepare_object;
use qpost\Util\Util;
use function qpost\Router\API\api_method_check;
use function qpost\Router\API\api_request_data;
use function qpost\Router\create_route;*/

// Temporarily retired because of inability to authorize Gigadrive users

//create_route("/api/token/request", function () {
//	if(api_method_check($this,"POST",false)){
//		$requestData = api_request_data($this);
//
//		if(isset($requestData["email"])){
//			if(!Util::isEmpty($requestData["email"])){
//				$email = $requestData["email"];
//
//				if(isset($requestData["password"])){
//					if(!Util::isEmpty($requestData["password"])){
//						$entityManager = EntityManager::instance();
//						$password = $requestData["password"];
//
//						/**
//						 * @var User $user
//						 */
//						$user = $entityManager->getRepository(User::class)->createQueryBuilder("u")
//							->where("upper(u.email) = upper(:query)")
//							->setParameter("query", $email, Type::STRING)
//							->orWhere("upper(u.username) = upper(:query)")
//							->setParameter("query", $email, Type::STRING)
//							->getQuery()
//							->getResult();
//
//						if(!is_null($user)){
//							if($user->isEmailActivated()){
//								if($user->isGigadriveLinked()){
//									$content = @file_get_contents("https://gigadrivegroup.com/api/v1/login/?apiKey=" . urlencode(GIGADRIVE_API_LEGACY_KEY) . "&username=" . urlencode($email) . "&password=" . urlencode($password));
//
//									if($content && Util::isValidJSON($content)){
//										$result = json_decode($content,true);
//
//										if($result){
//											if(!isset($result["success"])){
//												return json_encode(["error" => isset($result["error"]) ? $result["error"] : "An error occurred"]);
//											}
//										} else {
//											return json_encode(["error" => "An error occurred"]);
//										}
//									} else {
//										return json_encode(["error" => "An error occurred"]);
//									}
//								} else {
//									if(password_verify($password,$user->getPassword())){
//										if($user->isSuspended()){
//											return json_encode(["error" => "Your account has been suspended."]);
//										}
//									} else {
//										return json_encode(["error" => "Invalid email/password combination"]);
//									}
//								}
//
//								// successfully authenticated
//
//								$token = Token::createToken($user,isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : null,Util::getIP());
//
//								if(!is_null($token)){
//									return json_encode(api_prepare_object($token));
//								} else {
//									return json_encode(["error" => "An error occurred"]);
//								}
//							} else {
//								return json_encode(["error" => "Please activate your email."]);
//							}
//						} else {
//							return json_encode(["error" => "Invalid email/password combination"]);
//						}
//					} else {
//						return json_encode(["error" => "Invalid password"]);
//					}
//				} else {
//					return json_encode(["error" => "Invalid password"]);
//				}
//			} else {
//				return json_encode(["error" => "Invalid email"]);
//			}
//		} else {
//			return json_encode(["error" => "Invalid email"]);
//		}
//	} else {
//		return "";
//	}
//});