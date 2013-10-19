<?

class BeonoForumAdvert {
	
	static protected $module_id = 'beono.forumadvert';

	public static function modifyMessageList (&$arMessages) {		
	
		if (method_exists("CModule", "IncludeModuleEx") && CModule::IncludeModuleEx(self::$module_id) != MODULE_INSTALLED) {
			$demo_mode = true;
		}
		
		if (!$demo_mode || $GLOBALS['USER']->IsAdmin()) {
				
			if (!is_array($arMessages) || !COption::GetOptionString(self::$module_id, "status")) {
				return true;
			}		
			if ($GLOBALS['USER']->IsAuthorized() && COption::GetOptionString(self::$module_id, "guests_only")) {
				return true;
			}
									
			$advert_code = COption::GetOptionString(self::$module_id, "advert_code");
			$message_positions = COption::GetOptionString(self::$module_id, "message_positions", "");
			$message_interval = COption::GetOptionString(self::$module_id, "message_interval", "4");
			$advert_forums = unserialize(COption::GetOptionString(self::$module_id, "advert_forums", ""));	
					
			if ($message_positions) { 
				$message_positions = explode(',', $message_positions); 
			}

			if(!is_array($advert_forums)) {
				return true;
			}
			
			$i_message = 0;
			$arNewMessages = array();
			$arBannerCache = array();
			foreach ($arMessages as $key=>$arMessage) {				
				$i_message++;
				$arNewMessages[] = $arMessage;

				if ($i_message > 0 && (is_array($message_positions) && in_array($i_message, $message_positions) || ($message_interval && $i_message%$message_interval === 0))) {
					
					if (in_array($arMessage['FORUM_ID'], $advert_forums)) {
						$id = $arMessage['ID']."advert"; 
						$css_id = 'beono_message'.$id;
						$current_post_message_text = '';
						
						// parsing bitrix banners
						if(preg_match_all('/#BXBANNER_([^#]+)#/', $advert_code, $matches)) {
							if (is_array($matches[1]) && !empty($matches[1]) && CModule::IncludeModule('advertising')) {
								$arBannerReplacements = array();
								foreach ($matches[1] as $banner_type) {
									
									if($banner_code = CAdvBanner::Show($banner_type)) {
										$arBannerCache[$banner_type] = $banner_code;
									}									
									$arBannerReplacements[] = $arBannerCache[$banner_type];																
								}
								if (count($arBannerReplacements) > 0) {
									$current_post_message_text = str_replace($matches[0], $arBannerReplacements, $advert_code);
								}					
							}
						} else {
							$current_post_message_text = $advert_code;
						}
						
						if ($current_post_message_text) {
						
							if ($demo_mode) {
								$current_post_message_text .= '<br/><br/><a style="font-weight: bold;" target="_blank" href="http://mp.1c-bitrix.ru/solutions/beono.forumadvert/">1 day demo. Buy full version</a>';
							}
							$current_post_message_text .= '<script type="text/javascript">
							var beono_forum_mess_id = document.getElementById("message_text_'.$id.'");
							if (beono_forum_mess_id) {
								beono_forum_mess_id.parentNode.parentNode.parentNode.parentNode.parentNode.setAttribute("id", "'.$css_id.'");
							}
							</script>
							';
							$current_post_message_text .= '<style type="text/css">#'.$css_id.' .forum-post-date, #'.$css_id.' .forum-cell-contact, #'.$css_id.' .forum-cell-actions, #'.$css_id.' .forum-user-moderate-info {display:none;}</style>';
												
							$arNewMessages[] = array("ID" => $id, "AUTHOR_NAME" => " ", "POST_MESSAGE_TEXT" => $current_post_message_text, "POST_DATE" => "&nbsp;", "APPROVED" => "Y");
						}
					}
				}
			}
			if (!empty($arNewMessages)) {
				$arMessages = $arNewMessages;
			}
		}
		return true;
	}
}

?>