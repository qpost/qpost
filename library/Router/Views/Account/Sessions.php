<?php

use Gigadrive\Account\IPInformation;

$mysqli = Database::Instance()->get();

if(isset($_POST["action"]) && !empty($_POST["action"])){
    $action = $_POST["action"];

    if($action == "logOutSession"){
        if(isset($_POST["sessionAID"]) && !empty($_POST["sessionAID"])){
            $aid = $_POST["sessionAID"];
            $s = "%id|i:" . Util::getCurrentUser()->getID() . ";%";

            $b = false;
            $stmt = $mysqli->prepare("UPDATE `sessions` SET `is_active` = 0 WHERE `is_active` = 1 AND `aid` = ? AND `data` LIKE ?");
            $stmt->bind_param("is",$aid,$s);
            if($stmt->execute()) $b = true;
            $stmt->close();

            if($b == true){
                ?>
<div class="alert alert-success" role="alert">
    The session has been killed.
</div>
<?php
            } else {
                ?>
<div class="alert alert-success" role="alert">
    An error occurred.
</div>
<?php
            }
        }
    }
}

?><div class="card">
    <table class="my-0 table">
        <thead>
			<tr>
				<th>&nbsp;</th>
				<th>Browser &amp; Platform</th>
				<th>IP address</th>
				<th>Last access</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
        <tbody>
        <?php

            $s = "%id|i:" . Util::getCurrentUser()->getID() . ";%";

            $stmt = $mysqli->prepare("SELECT * FROM `sessions` WHERE `is_active` = 1 AND `data` LIKE ? ORDER BY `lastAccessTime` DESC");
			$stmt->bind_param("s",$s);
			if($stmt->execute()){
			    $result = $stmt->get_result();
			    if($result->num_rows){
			        while($row = $result->fetch_assoc()){
			            $userAgent = parse_user_agent($row["userAgent"]);
						$platform = $userAgent["platform"];
						$browser = $userAgent["browser"];
						$browserVersion = $userAgent["version"];

						$icon = "fas fa-globe";
						if(strpos($platform,"Xbox") !== false) $icon = "fab fa-xbox";
						if(strpos($platform,"PlayStation") !== false) $icon = "fab fa-playstation";
						if(strpos($platform,"Macintosh") !== false) $icon = "fab fa-apple";
						if(strpos($platform,"iPhone") !== false) $icon = "fab fa-apple";
						if(strpos($platform,"iPad") !== false) $icon = "fab fa-apple";
						if(strpos($platform,"iPod") !== false) $icon = "fab fa-apple";
						if(strpos($browser,"Android") !== false) $icon = "fab fa-android";
						if(strpos($browser,"BlackBerry") !== false) $icon = "fab fa-blackberry";
						if(strpos($browser,"Kindle") !== false) $icon = "fab fa-amazon";
						if(strpos($browser,"Firefox") !== false) $icon = "fab fa-firefox";
						if(strpos($browser,"Safari") !== false) $icon = "fab fa-safari";
						if(strpos($browser,"Internet Explorer") !== false) $icon = "fab fa-internet-explorer";
						if(strpos($browser,"Chrome") !== false) $icon = "fab fa-chrome";
						if(strpos($browser,"Opera") !== false) $icon = "fab fa-opera";
						if(strpos($browser,"Edge") !== false) $icon = "fab fa-edge";

						$isCurrent = false;
                        if(session_id() == $row["id"]) $isCurrent = true;

                        $ipInfo = IPInformation::getInformationFromIP($row["ip"]);
                        
                        ?>
            <tr<?= $isCurrent ? ' class="bg-dark text-white"' : "" ?>>
                <td style="text-align: center; font-size: 36px;"><i class="<?= $icon ?>"></i></td>
                <td>
                    <?= $platform; ?>
					<?php if($isCurrent){ ?>(current)<?php } ?>
					<br/>
					<?= $browser; ?>
					<?= $browserVersion; ?>
                </td>
                <td>
                    <?php 
										
					if($ipInfo != null && $ipInfo->getData() != null && array_key_exists("city",$ipInfo->getData()) && array_key_exists("country_name",$ipInfo->getData())){
					    echo '<span data-toggle="tooltip" title="IP: ' . $ipInfo->getIP() . '">';
					    echo Util::fixUmlaut($ipInfo->getData()["city"]) . ", " . $ipInfo->getData()["country_name"];
					    echo '</span>';
					} else {
					    echo '<span data-toggle="tooltip" title="IP: ' . $row["ip"] . '">N/A</span>';
					}

					?>
                </td>
                <td><?= Util::timeago($row["lastAccessTime"]) ?></td>
                <td>
                    <?php

                        if($isCurrent){
                            echo '&nbsp';
                        } else {
                            ?>
                        <form action="/account/sessions" method="post">
                            <?= Util::insertCSRFToken(); ?>
                            <input type="hidden" name="action" value="logOutSession" />
                            <input type="hidden" name="sessionAID" value="<?= $row["aid"]; ?>"/>
                            <button type="submit" class="btn btn-sm btn-block btn-danger mt-2">Log out</button>
                        </form>
                        <?php
                        }

                    ?>
                </td>
            </tr>
                        <?php
			        }
			    }
			}
			$stmt->close();

        ?>
        </tbody>
    </table>
</div>