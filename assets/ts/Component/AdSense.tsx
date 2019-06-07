import $ from "jquery";
import Util from "../Util";

export default class AdSense {
	public static init(): void {
		this.updateAdBlocks();
	}

	public static leaderboardAd(center?: boolean, classes?: string[]): string {
		let classesString: string = "";
		if (classes) {
			classes.forEach(clazz => {
				classesString = classesString.concat(clazz + " ");
			});

			classesString = classesString.trim();
		}

		return Util.nodeToHTML(<div class={center ? "text-center" : ""}>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-6156128043207415"
				 data-ad-slot="1055807482" data-ad-format="auto"></ins>
			<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		</div>);
	}

	public static verticalAd(center?: boolean, classes?: string[]): string {
		let classesString: string = "";
		if (classes) {
			classes.forEach(clazz => {
				classesString = classesString.concat(clazz + " ");
			});

			classesString = classesString.trim();
		}

		return Util.nodeToHTML(<div class={center ? "text-center" : ""}>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle" style="display:inline-block;width:120px;height:600px"
				 data-ad-client="ca-pub-6156128043207415" data-ad-slot="1788401303"></ins>
			<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		</div>);
	}

	public static blockAd(center?: boolean, classes?: string[]): string {
		let classesString: string = "";
		if (classes) {
			classes.forEach(clazz => {
				classesString = classesString.concat(clazz + " ");
			});

			classesString = classesString.trim();
		}

		return (Util.nodeToHTML(<div class={center ? "text-center" : ""}>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<ins class="adsbygoogle" style="display:inline-block;width:120px;height:600px"
				 data-ad-client="ca-pub-6156128043207415" data-ad-slot="1788401303"></ins>
			<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
		</div>));
	}

	private static updateAdBlocks(): void {
		$(".advertisment.leaderboard").replaceWith(AdSense.leaderboardAd(true));
		$(".advertisment.vertical").replaceWith(AdSense.verticalAd(true));
		$(".advertisment.block").replaceWith(AdSense.blockAd(true));

		setTimeout(this.updateAdBlocks, 500);

		window["adbanner_leaderboard"] = AdSense.leaderboardAd;
		window["adbanner_vertical"] = AdSense.verticalAd;
		window["adbanner_block"] = AdSense.blockAd;
	}
}