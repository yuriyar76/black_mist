<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}
if ($arResult['OPEN'])
{
	?>
    <div class="modal-body">
    	<div class="row">
        	<div class="col-md-12" class="text-right">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        </div>    
        <div class="row">
            <div class="col-md-12">
				<?
                $APPLICATION->IncludeComponent(
                        "black_mist:delivery.get_pods", 
                        ".default", 
                        array(
                            "SHOW_FORM" => "N",
                            "CACHE_TYPE" => "A",
                            "CACHE_TIME" => "3600",
                            "SAVE_TO_SITE" => "N",
                            "SHOW_TITLE" => "N",
                            "SET_TITLE" => "N",
                            "TEST_MODE" => "N",
							"COMPONENT_TEMPLATE" => ".default",
							"NO_TEMPLATE" => "N",
							"ONLY_1C_DATA" => "Y",
							"COMPOSITE_FRAME_MODE" => "A",
							"COMPOSITE_FRAME_TYPE" => "AUTO"
                        ),
                        false
                    );
				?>
			</div>
		</div>
	</div>
	<?
}
?>