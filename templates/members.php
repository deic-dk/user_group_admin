<?php

$group = $_['group'];
$owner = $_['owner'];
$members = $_['members'];
$numMembers = count($members);

echo "<div>".$l->t("Description")."</div>";

echo "<li><span class='left'>".
		"<textarea class='description' readonly='readonly' rows='3' cols='92'>".$_['description']."</textarea>".
		"</span></li>";

echo "<div class='owner'>".$l->t("Owner")."</div>";

$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><span class='left'>$name </span></li>";

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
		$uid = $member["uid"];
		$verified = $member["verified"];
		$verifiedStr = "";
		if($verified==OC_User_Group_Admin_Util::$GROUP_INVITATION_OPEN){
			$verifiedStr = '<i class="group_pending">'.$l->t("Pending").'...</i>';
		}
		elseif ($verified===OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED){
			if($showMembers){
				$verifiedStr = '<i class="group_declined">'.$l->t("User declined the invitation").'...</i>';
			}
			else{
				continue;
			}
		}
		$name = OC_User_Group_Admin_Util::prepareUser($uid);
		
		echo "<li data-member=$uid><span class='left'>$name </span>
			<span class='normaltext'><i>($uid)</i></span>
			<span class=\"member-actions\" id='spanaction'>
			<a href=# class='removemember' original-title=" . $l->t('Remove') . ">
			<i class=\"icon icon-cancel-circled\"></i></a></span>
			<span class='group_status'>$verifiedStr</span>
			</li>" ;
	}
}
