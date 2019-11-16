/*
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

const CACHE_NAME = "qpost-sw-precache";
const PRECACHE_FILES = [];
const LOG_PREFIX = "[QPOST-SW] ";
const OFFLINE_PAGE = "/offline.html";

self.addEventListener("install", (event) => {
	console.log(LOG_PREFIX + "Installing");
	console.log(LOG_PREFIX + "Skip waiting on install");
	self.skipWaiting();

	// Add assets to cache
	event.waitUntil(caches.open(CACHE_NAME).then(cache => {
		console.log(LOG_PREFIX + "Caching pages during install");

		return cache.addAll(PRECACHE_FILES);
	}));
});

self.addEventListener("activate", (event) => {
	console.log(LOG_PREFIX + "Claiming clients for current page");

	event.waitUntil(self.clients.claim());
});


self.addEventListener("fetch", (event) => {
	// Serve from cache if a fetch fails
	const request = event.request;

	// only cache HTTP GET requests
	if (request.method !== "GET") return;

	event.respondWith(fetch(request).then(response => {
		event.waitUntil(updateCache(request, response.clone()));

		return response;
	}).catch(error => {
		if (request.destination !== "document" || request.mode !== "navigate") return;

		console.error(LOG_PREFIX + "Network request failed. Serving offline page", error);
		return caches.open(CACHE_NAME).then(cache => {
			return cache.match(OFFLINE_PAGE)
		});
	}));
});

self.addEventListener("refreshOffline", () => {
	const offlinePageRequest = new Request(OFFLINE_PAGE);

	return fetch(OFFLINE_PAGE).then((response) => {
		return updateCache(offlinePageRequest, response);
	});
});

loadFromCache = request => {
	return caches.open(CACHE_NAME).then(cache => {
		return cache.match(request).then(matching => {
			if (!matching || matching.status === 404) {
				return Promise.reject("No match was found.");
			}

			return matching;
		});
	});
};

updateCache = (request, response) => {
	return caches.open(CACHE_NAME).then(cache => {
		return cache.put(request, response);
	});
};

// https://github.com/bpolaszek/webpush-js/blob/master/src/webpush-sw.js

self.addEventListener('push', event => {
	try {
		const Notification = event.data.json();
		event.waitUntil(
			self.registration.showNotification(Notification.title || '', Notification.options || {})
		);
	} catch (e) {
		try {
			const Notification = event.data.text();
			event.waitUntil(
				self.registration.showNotification('Notification', {body: Notification})
			);
		} catch (e) {
			event.waitUntil(
				self.registration.showNotification('')
			);
		}
	}
});

self.addEventListener('notificationclick', event => {

	event.notification.close();
	const url = event.notification.data.link || '';

	if (url.length > 0) {
		event.waitUntil(
			clients.matchAll({
				type: 'window'
			})
				.then(windowClients => {
					for (const client of windowClients) {
						if (client.url === url && 'focus' in client) {
							return client.focus();
						}
					}

					if (clients.openWindow) {
						return clients.openWindow(url);
					}
				})
		);
	}
});