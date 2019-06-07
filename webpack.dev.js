/*
 * Copyright (c) 2019 Gigadrive Group - All rights reserved.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 * https://gigadrivegroup.com/dev/technologies
 */

const merge = require("webpack-merge");
const common = require("./webpack.common");

module.exports = merge(common, {
	mode: "development",
	watch: true
});