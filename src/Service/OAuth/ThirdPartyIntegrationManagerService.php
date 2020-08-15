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

namespace qpost\Service\OAuth;

use function strtoupper;

class ThirdPartyIntegrationManagerService {
	/**
	 * @var ThirdPartyIntegration[] $integrationServices
	 */
	private $integrationServices;

	/**
	 * ThirdPartyIntegrationManagerService constructor.
	 * @param DiscordIntegration $discordIntegration
	 * @param TwitchIntegration $twitchIntegration
	 * @param TwitterIntegration $twitterIntegration
	 * @param MastodonIntegration $mastodonIntegration
	 * @param LastFmIntegration $lastFmIntegration
	 * @param SpotifyIntegration $spotifyIntegration
	 * @param InstagramIntegration $instagramIntegration
	 * @param RedditIntegration $redditIntegration
	 */
	public function __construct(
		DiscordIntegration $discordIntegration,
		TwitchIntegration $twitchIntegration,
		TwitterIntegration $twitterIntegration,
		MastodonIntegration $mastodonIntegration,
		LastFmIntegration $lastFmIntegration,
		SpotifyIntegration $spotifyIntegration,
		InstagramIntegration $instagramIntegration,
		RedditIntegration $redditIntegration
	) {
		$this->integrationServices = [
			$discordIntegration,
			$twitchIntegration,
			$twitterIntegration,
			$mastodonIntegration,
			$lastFmIntegration,
			$spotifyIntegration,
			$instagramIntegration,
			$redditIntegration
		];
	}

	/**
	 * @return ThirdPartyIntegration[]
	 */
	public function getIntegrationServices(): array {
		return $this->integrationServices;
	}

	/**
	 * Get an integration service from it's identifier.
	 * @param string $identifier
	 * @return ThirdPartyIntegration|null
	 */
	public function getIntegrationService(string $identifier): ?ThirdPartyIntegration {
		foreach ($this->integrationServices as $service) {
			$serviceIdentifier = $service->getServiceIdentifier();

			if (!is_null($serviceIdentifier) && strtoupper($serviceIdentifier) === strtoupper($identifier)) {
				return $service;
			}
		}

		return null;
	}
}