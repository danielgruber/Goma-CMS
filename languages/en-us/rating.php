<?php
$rating_l = array(
	"vote"              => "vote",
	"votes"             => "votes",
	"rated"             => "You've already voted!",
	"thanks_for_voting" => "Thanks for your vote",
	"perms_delete"		=> "Reset votes"
);
foreach($rating_l as $key => $value)
{
	$GLOBALS['lang']['rating.'.$key] = $value;
}