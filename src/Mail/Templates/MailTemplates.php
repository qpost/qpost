<?php

namespace Gigadrive\MailTemplates;

/**
 * Class used to load email templates from HTML with variables in them
 * 
*  @package MailTemplates
 * @author Gigadrive (support@gigadrivegroup.com)
 * @copyright 2016-2018 Gigadrive
 * @link https://gigadrivegroup.com/dev/technologies
 */
class MailTemplates {
	/**
	 * Returns HTML code for an email loaded from a template, replaced with variables
	 * 
	 * @access public
	 * @param string $name The name of the template folder
	 * @param array $variables An array of strings that will replace the variable placeholders in the template
	 * @return string HTML code for the email, returns null if the template could not be loaded
	 */
	public static function readTemplate($name,$variables = null){
		$folder = __DIR__ . "/" . $name . "/";

		if(file_exists($folder) && is_dir($folder)){
			$htmlLocation = $folder . "mail.html";

			if(file_exists($htmlLocation)){
				$content = file_get_contents($htmlLocation);

				if(!is_null($content)){
					if(!is_null($variables) && is_array($variables) && count($variables) > 0){
						for($i = 0; $i < count($variables); $i++){
							$var = $variables[$i];

							$content = str_replace("{" . $i . "}",$var,$content);
						}
					}

					return $content;
				} else {
					return null;
				}
			} else {
				return null;
			}
		} else {
			return null;
		}
	}
}