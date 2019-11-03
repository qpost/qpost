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

/**
 * Notification JS
 * Shims up the Notification API
 *
 * @author Andrew Dodson
 * @website http://adodson.com/notification.js/
 */

!function(i,t){var e,n="granted",o="denied",c="unknown",s=[],a=0;function r(){0!==s.length&&("external"in i&&"msSiteModeClearIconOverlay"in i.external&&i.external.msSiteModeClearIconOverlay(),clearInterval(e),e=!1,t.title=s[0],s=[])}function l(){return i.Notification.permission="external"in i&&"msIsSiteMode"in i.external?i.external.msIsSiteMode()?n:c:"webkitNotifications"in i?0===i.webkitNotifications.checkPermission()?n:o:"mozNotification"in i.navigator?n:c,i.Notification.permission}Object(i.Notification).permission||(!function i(t,e,n){if(e.match(" "))for(var o=e.split(" "),c=0;c<o.length;c++)i(t,o[c],n);t.addEventListener?(t.removeEventListener(e,n,!1),t.addEventListener(e,n,!1)):(t.detachEvent("on"+e,n),t.attachEvent("on"+e,n))}(i,"focus scroll click",r),i.Notification=function(n,o){if(!(this instanceof i.Notification))return new i.Notification(n,o);var c,l,f=this;if(o=o||{},this.body=o.body||"",this.icon=o.icon||"",this.lang=o.lang||"",this.tag=o.tag||"",this.close=function(){r(),Object(c).close&&c.close(),f.onclose()},this.onclick=function(){},this.onclose=function(){},l=n,0===s.length&&(s=[t.title]),s.push(l),e||(e=setInterval(function(){-1===s.indexOf(t.title)&&(s[0]=t.title),t.title=s[++a%s.length]},1e3)),"external"in i&&"msIsSiteMode"in i.external)i.external.msIsSiteMode()&&(i.external.msSiteModeActivate(),this.icon&&i.external.msSiteModeSetIconOverlay(this.icon,n));else if("webkitNotifications"in i)0===i.webkitNotifications.checkPermission()&&((c=i.webkitNotifications.createNotification(this.icon,n,this.body)).show(),c.onclick=function(){f.onclick(),i.focus(),setTimeout(function(){c.cancel()},1e3)});else if("mozNotification"in i.navigator){i.navigator.mozNotification.createNotification(n,this.body,this.icon).show()}},i.Notification.requestPermission=function(t){if(t=t||function(){},"external"in i&&"msIsSiteMode"in i.external){try{i.external.msIsSiteMode()||(i.external.msAddSiteMode(),t(c))}catch(i){}t(l())}else"webkitNotifications"in i?i.webkitNotifications.requestPermission(function(){t(l())}):t(l())},l())}(window,document);