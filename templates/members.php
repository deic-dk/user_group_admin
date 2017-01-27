<?php

$group = $_['group'];
$owner = $_['owner'];
$members = $_['members'];
$numMembers = count($members);

echo "<div>Description</div>";

echo "<li><span class='left'>".
		"<textarea class='description' readonly='readonly' rows='3' cols='92'>".$_['description']."</textarea>".
		"</span></li>";

echo "<div class='owner'>Owner</div>";

$name = OC_User_Group_Admin_Util::prepareUser($owner);
echo "<li data-member=$owner><span class='left'>$name </span></li>";

echo "<div class='memberscount info' members='".$numMembers."'>".$numMembers." member".($numMembers==1?"":"s")."</div>";

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
			$verifiedStr = '<i class="group_pending">Pending...</i>';
		}
		elseif ($verified===OC_User_Group_Admin_Util::$GROUP_INVITATION_DECLINED){
			if($showMembers){
				$verifiedStr = '<i class="group_declined">Member declined the invitation';
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
