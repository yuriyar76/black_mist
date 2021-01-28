<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('pull'))
   return false;


CPullWatch::Add($USER->GetId(), 'PULL_TEST');

?>
<button onclick="sendCommand();">It`s work!</button>

<div id="pull_test"></div>

<script type="text/javascript">

function sendCommand()
{
	BX.ajax({
		url: '/bitrix/components/black_mist/pull.test/ajax.php',
		method: 'POST',
		data: {'SEND' : 'Y', 'sessid': BX.bitrix_sessid()}
	});
}
BX.ready(function(){
	BX.addCustomEvent("onPullEvent", function(module_id,command,params) {
		console.log(module_id,command,params);
		if (module_id == "test" && command == 'check')
		{
			BX('pull_test').innerHTML += params.USERS+'<br>';
			console.log(params.USERS);
		}
	});
	BX.PULL.extendWatch('PULL_TEST');
});
</script>
