<?php

$group = $_['group'];
$owner = $_['owner'];
$members = $_['members'];
$numMembers = count($members);
$privategroup = $_['privategroup'];
$opengroup = $_['opengroup'];

echo "<div>".$l->t("Description")."</div>";

echo "<li><span class='left'>".
		"<textarea class='description' readonly='readonly' rows='3' cols='92'>".$_['description']."</textarea>".
		"</span></li>";

echo "<div>".$l->t("Privacy")."</div>";

echo "<li><span class='left'>".
		$l->t("Private").
		"<input id='privategroup' type='checkbox' title='".$l->t("Is group hidden from non-members?").
		"'".($privategroup?" checked='checked'":"")." />".
		"&nbsp;".
		$l->t("Open").
		"<input id='opengroup' type='checkbox' title='".$l->t("Is group open to all users without approval?").
		"'".($opengroup?" checked='checked'":"")." />".
		"</span></li>";

echo "<div class='owner'>".$l->t("Owner")."</div>";

$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><span class='left'>$name</span></li>";

echo "<div class='memberscount info' members='".$numMembers."'>".$l->t("Members").": ".$numMembers."</div>";

$showMembers = false;

if($owner===\OC_User::getUser()){
	$showMembers = true;
}
else{
	foreach($members as $member){
		if($member["uid"]==\OC_User::getUser()){
			if($member["verified"]==OC_User_Group_Admin_Util::$GROUP_INVITATION_ACCEPTED){
				$showMembers = true;
			}
			break;
		}
	}
}

if($showMembers){
	foreach($members as $member){
		$verified = $member["verified"];
		$verifiedStr = "";
		$userStr = "";
		if(empty($member["uid"]) || $member["uid"]==OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER){
			$uid = "";
			$userStr = "";
			$name = "";
		}
		else{
			$uid = $member["uid"];
			$userStr = "<span class='normaltext'><i>($uid)</i></span>";
			$name = OC_User_Group_Admin_Util::prepareUser($uid);
		}
		$invitationEmail = "";
		if($verified==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN){
			if((empty($uid) || $uid==OC_User_Group_Admin_Util::$UNKNOWN_GROUP_MEMBER) &&
					!empty($member['invitation_email'])){
					$invitationEmail = $member['invitation_email'];
					$name = '</i> <span class="normaltext">'.
					$l->t('invitation sent to %s', array($member['invitation_email'])).'</span>';
					$verifiedStr = '<i class="group_pending">'.$l->t("Pending").'... </i>';
			}
			else{
				$verifiedStr = '<i class="group_pending">'.$l->t("Pending").'... </i>';
			}
		}
		elseif($verified===OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED){
			if($showMembers){
				$verifiedStr = '<i class="group_declined">'.$l->t("User declined the invitation");
			}
			else{
				continue;
			}
		}
		print_unescaped("<li data-member=\"$uid\" data-invitation-email=\"$invitationEmail\"><span class='left'>$name</span>" .
			$userStr .
			"<span class=\"member-actions\" id='spanaction'>
			<a href=# class='removemember' original-title=" . $l->t('Remove') . ">
			<i class=\"icon icon-cancel-circled\"></i></a></span>
			<span class='group_status'>$verifiedStr</span>
			</li>");
	}
}
