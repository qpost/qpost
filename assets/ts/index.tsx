import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "../scss/index.scss";
import $ from "jquery";
import "bootstrap";

window["jQuery"] = $;
window["$"] = $;

App.init();

ReactDOM.render(<App/>, document.getElementById("root"));
