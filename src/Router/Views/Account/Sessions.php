<?php

use qpost\Account\IPInformation;
use qpost\Account\Token;
use qpost\Database\Database;
use qpost\Util\Util;

$mysqli = Database::Instance()->get();

if(isset($_POST["action"]) && !Util::isEmpty($_POST["action"])){
    $action = $_POST["action"];

    if($action == "logOutSession"){
        if(isset($_POST["sesstoken"]) && !Util::isEmpty($_POST["sesstoken"])){
			$token = Token::getTokenById($_POST["sesstoken"]);
			
			if(!is_null($token) && $token->getUserId() == Util::getCurrentUser()->getId()){
				$token->expire();
                ?>
<div class="alert alert-success" role="alert">
    The session has been killed.
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
				<th>Location</th>
				<th>Last access</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
        <tbody>
        <?php

            $uID = Util::getCurrentUser()->getId();

            $stmt = $mysqli->prepare("SELECT `id` FROM `tokens` WHERE `expiry` > NOW() AND `user` = ? ORDER BY `lastAccessTime` DESC");
			$stmt->bind_param("i",$uID);
			if($stmt->execute()){
			    $result = $stmt->get_result();
			    if($result->num_rows){
			        while($row = $result->fetch_assoc()){
						$token = Token::getTokenById($row["id"]);
						if(is_null($token)) continue;

			            $userAgent = parse_user_agent($token->getUserAgent());
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
                        if($_COOKIE["sesstoken"] == $token->getId()) $isCurrent = true;

                        $ipInfo = IPInformation::getInformationFromIP($token->getIP());
                        
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
					    echo '<span data-toggle="tooltip" title="IP: ' . $token->getIP() . '">N/A</span>';
					}

					?>
                </td>
                <td><?= Util::timeago($token->getLastAccessTime()) ?></td>
                <td>
                    <?php

                        if($isCurrent){
                            echo '&nbsp';
                        } else {
                            ?>
                        <form action="/account/sessions" method="post">
                            <?= Util::insertCSRFToken(); ?>
                            <input type="hidden" name="action" value="logOutSession" />
                            <input type="hidden" name="sesstoken" value="<?= $token->getId(); ?>"/>
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