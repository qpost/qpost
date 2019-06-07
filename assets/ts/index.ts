import "../scss/index.scss";
import $ from "jquery";
import "./JSX";
import "jquery-ui/ui/widgets/datepicker";
import "popper.js";
import "bootstrap";
import Listener from "./Listener/Listener";
import Component from "./Component/Component";
import Base from "./Component/Base";
import "../js/jquery.timeago";

window["$"] = $;
window["jQuery"] = $;

(() => {
	Base.init();
	$(document).on("ready", Base.init);
	setTimeout(Base.init, 500);

	// initialize component
	Component.init();

	// initialize listeners
	Listener.init();

	window["Dropzone"].autoDiscover = false;

	window["LOADED_SHARES"] = [];
	window["LOADED_FAVORITES"] = [];
})();
