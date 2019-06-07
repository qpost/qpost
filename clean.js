/*
 * Copyright (c) 2019 Gigadrive Group - All rights reserved.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 * https://gigadrivegroup.com/dev/technologies
 */

const fs = require("fs");

function deleteFolderRecursive(path) {
	if (fs.existsSync(path) && fs.lstatSync(path).isDirectory()) {
		fs.readdirSync(path).forEach(function (file, index) {
			var curPath = path + "/" + file;

			if (fs.lstatSync(curPath).isDirectory()) { // recurse
				deleteFolderRecursive(curPath);
			} else { // delete file
				fs.unlinkSync(curPath);
			}
		});

		console.log(`Deleting directory "${path}"...`);
		fs.rmdirSync(path);
	}
}

console.log("Cleaning working tree...");

deleteFolderRecursive("./public/build");

console.log("Successfully cleaned working tree!");
