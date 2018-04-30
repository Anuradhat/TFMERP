<?php

// Menu
$RootMenu = new crMenu("RootMenu", TRUE);
$RootMenu->AddMenuItem(58, "mi_rptPurchaseOrder", $ReportLanguage->Phrase("DetailSummaryReportMenuItemPrefix") . $ReportLanguage->MenuPhrase("58", "MenuText") . $ReportLanguage->Phrase("DetailSummaryReportMenuItemSuffix"), "rptPurchaseOrdersmry.php", -1, "", TRUE, FALSE, FALSE, "");
echo $RootMenu->ToScript();
?>
<div class="ewVertical" id="ewMenu"></div>
