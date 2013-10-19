<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("ADMIN_MODULE_NAME", "beono.forumadvert");
$module_id = 'beono.forumadvert';
$module_path = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/";
global $MESS;
include(GetLangFileName($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/lang/", "/options.php"));
$APPLICATION->SetTitle(GetMessage("BEONO_MODULE_FORUMADVERT_OPTIONS_TAB_1"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(!CModule::IncludeModule('forum')) {
	CAdminMessage::ShowMessage(GetMessage('BEONO_MODULE_FORUMADVERT_ERROR_FORUMNOTFOUND'));	
} else {
		
	if ($APPLICATION->GetGroupRight("forum")>="R") {
				
		if (method_exists("CModule", "IncludeModuleEx") && CModule::IncludeModuleEx("beono.forumadvert") == MODULE_DEMO_EXPIRED) {
			CAdminMessage::ShowMessage(GetMessage('BEONO_MODULE_FORUMADVERT_ERROR_EXPIRED'));
		}
		
		$forum_advert_logfile = $module_path."/log.txt";
		
		$arTemplatePath = array();
		$arTemplateMask = array();
		
		// components
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum.topic.read/templates/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum.topic.read/templates/*/template.php";
		// complex compnents
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum/templates/.default/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum/templates/.default/*/forum.topic.read/*/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum/templates/*/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/components/*/forum/templates/*/*/forum.topic.read/*/template.php";
		// site templates components
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum.topic.read/*/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum.topic.read/*/template.php";
		// site templates complex components
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum/.default/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum/.default/*/forum.topic.read/*/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum/*/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/.default/components/*/forum/*/*/forum.topic.read/*/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum/.default/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum/.default/*/forum.topic.read/*/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum/*/*/forum.topic.read/.default/template.php";
		$arTemplateMask[] = $_SERVER['DOCUMENT_ROOT']."/bitrix/templates/*/components/*/forum/*/*/forum.topic.read/*/template.php";
		
		// crazy, yeah? :)
		foreach ($arTemplateMask as $mask) {
			if($arPaths = glob($mask)) {
				$arTemplatePath = array_merge($arTemplatePath, $arPaths);
			}
		}
		
		if ($_POST['Update'] && check_bitrix_sessid()) {
					
			$advert_result_modifier_path = $module_path."/install/result_modifier.php";
			
			if(preg_match('/[^\d^\,]+/', $_POST['message_positions'])) {
				$error = GetMessage("BEONO_MODULE_FORUMADVERT_OPTIONS_ERROR_POSITIONS_REGEXP");
			} else {
				
				if (!is_readable($advert_result_modifier_path)) {
					$error = "can't read file ".$advert_result_modifier_path;
				} else {
					foreach ($arTemplatePath as $template) {
									
						$dest_result_modifier_path = dirname($template)."/result_modifier.php";	
				
						if ($_POST['status'] == 1 && !COption::GetOptionString($module_id, "status")) {			
							if (!file_exists($dest_result_modifier_path)) {				
						    	copy($advert_result_modifier_path, $dest_result_modifier_path);
						    	file_put_contents($forum_advert_logfile, "copying to\n".$dest_result_modifier_path."\n\n", FILE_APPEND);
						    } else {
						    	$advert_result_modifier_content = file_get_contents($advert_result_modifier_path);
						    	if(strpos(file_get_contents($dest_result_modifier_path), 'beono.forumadvert') === false) {
						    		if(!file_put_contents($dest_result_modifier_path, $advert_result_modifier_content, FILE_APPEND | LOCK_EX)) {
						    			$error = "can't write file ".$dest_result_modifier_path;
						    		} else {
						    			file_put_contents($forum_advert_logfile, "writing to ".$dest_result_modifier_path."\n\n", FILE_APPEND);
						    		}
						    	}
							}	
						} else if ($_POST['status'] == 0 && COption::GetOptionString($module_id, "status") == 1) {
							if (file_exists($dest_result_modifier_path)) {
								$advert_result_modifier_content = file_get_contents($advert_result_modifier_path);									
								$dest_result_modifier_content = file_get_contents($dest_result_modifier_path);	
								file_put_contents($forum_advert_logfile, "checking\n".$dest_result_modifier_path."\n".$dest_result_modifier_content."\n\n", FILE_APPEND);
						    	$dest_result_modifier_content = str_replace($advert_result_modifier_content, '', $dest_result_modifier_content);
						    	file_put_contents($dest_result_modifier_path, $dest_result_modifier_content, LOCK_EX);
						    	if (filesize($dest_result_modifier_path) == 0) {
						    		if(unlink($dest_result_modifier_path)) {
						    			file_put_contents($forum_advert_logfile, "deleted\n\n", FILE_APPEND);
						    		}    		
						    	}					
						    }
						}		
					}
						
					COption::SetOptionString($module_id, "advert_code", $_POST['advert_code']);
					COption::SetOptionString($module_id, "message_positions", trim($_POST['message_positions'], ','));
					COption::SetOptionString($module_id, "message_interval", intval($_POST['message_interval']));
					COption::SetOptionString($module_id, "advert_forums", serialize($_POST['advert_forums']));
					COption::SetOptionString($module_id, "guests_only", intval($_POST['guests_only']));		
					COption::SetOptionString($module_id, "status", $_POST['status']);		
					
					if (!$error) {	
						CAdminMessage::ShowNote(GetMessage('BEONO_MODULE_FORUMADVERT_SAVED'));
						//LocalRedirect($APPLICATION->GetCurPageParam());
					}
				}
			}
		}
		
		if ($error) {
			CAdminMessage::ShowMessage($error);
		}
		
		$arFormField['advert_code']['value'] = COption::GetOptionString($module_id, "advert_code");
		$arFormField['message_positions']['value'] = COption::GetOptionString($module_id, "message_positions", "1,5");
		$arFormField['message_interval']['value'] = COption::GetOptionString($module_id, "message_interval", "10");
		$arFormField['advert_forums']['value'] = unserialize(COption::GetOptionString($module_id, "advert_forums", ""));
		$arFormField['guests_only']['value'] = COption::GetOptionString($module_id, "guests_only", "0");
		$arFormField['status']['value'] = COption::GetOptionString($module_id, "status", "0");
		
		$aTabs = array();
		$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("BEONO_MODULE_FORUMADVERT_OPTIONS_TAB_1"), "ICON" => "blog_settings", "TITLE" => GetMessage("BEONO_MODULE_FORUMADVERT_OPTIONS_TAB_1_TITLE"));
		
		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		?>
		<?
		$tabControl->Begin();
		?>
		<form method="post"	action="<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<?$tabControl->BeginNextTab();?>
		
				<tr>
					<td valign="top" class="field-name"><label for="advert_code"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_CODE');?>:</label>
					</td>
					<td><textarea name="advert_code" id="advert_code" cols="40" rows="7"><?=$arFormField['advert_code']['value'];?></textarea>
					</td>
				</tr>	
				<tr>
					<td class="field-name"><label for="message_positions"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_POSITIONS');?>:</label>
					</td>
					<td>
						<input type="text" name="message_positions" id="message_positions" value="<?=$arFormField['message_positions']['value'];?>" size="20" maxlength="25" />
					</td>
				</tr>	
				<tr>
					<td class="field-name"><label for="message_interval"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_INTERVAL');?>:</label>
					</td>
					<td>
						<input type="text" name="message_interval" id="message_interval" value="<?=$arFormField['message_interval']['value'];?>" size="2" maxlength="2" />
					</td>
				</tr>
				<tr>
					<td valign="top" class="field-name"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_FORUMS');?>:
					</td>
					<td>
						<?
						CModule::IncludeModule('forum');
						$rsForums = CForumNew::GetList();
						while($arForum = $rsForums->GetNext()):?>
							<label>
								<input type="checkbox" name="advert_forums[]" value="<?=$arForum['ID']?>" <?if(is_array($arFormField['advert_forums']['value']) && in_array($arForum['ID'], $arFormField['advert_forums']['value'])):?>checked="checked"<?endif;?>/>
								<?=$arForum['NAME']?>
							</label><br/>	
						<?endwhile;?>
					</td>
				</tr>
				<tr>
					<td valign="top" class="field-name"><label for="guests_only"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_GUESTSONLY');?>:</label>
					</td>
					<td>
						<input type="checkbox" name="guests_only" id="guests_only" value="1" <?if($arFormField['guests_only']['value']):?>checked="checked"<?endif;?>/>
					</td>
				</tr>
				<tr>
					<td valign="top" class="field-name"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_STATUS');?>:
					</td>
					<td>
						<label style="color: green;"><input type="radio" name="status" value="1" <?if($arFormField['status']['value']):?>checked="checked"<?endif;?> /> <?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_ON');?></label><br/>
						<label style="color: red;"><input type="radio" name="status" value="0" <?if(!$arFormField['status']['value']):?>checked="checked"<?endif;?> /> <?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_OFF');?></label><br/><br/>
						<?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_TEMPLATES');?>:<br/><br/>				
						<?if (is_array($arTemplatePath)):?>
							<?foreach ($arTemplatePath as $key=>$filename):?>
							    <nobr><?=++$key;?>) <?=str_replace($_SERVER['DOCUMENT_ROOT'], '', $filename);?></nobr>
							    <?if(!is_writable(dirname($filename))):?><b style="color: red;"><?=GetMessage('BEONO_MODULE_FORUMADVERT_OPTIONS_ERROR_WRITE');?></b><?endif;?><br/><br/>
							<?endforeach;?>
						<?endif;?>
					</td>
				</tr>
		
			<?$tabControl->Buttons();?>
			<input type="submit" name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
			<?$tabControl->End();?>
		</form>
	<?
	}
}
?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>