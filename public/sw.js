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

const CACHE_NAME = "qpost-sw-precache";
const LOG_PREFIX = "[QPOST-SW] ";

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