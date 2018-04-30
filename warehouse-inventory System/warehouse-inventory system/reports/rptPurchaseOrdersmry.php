<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start();
?>
<?php include_once "rcfg11.php" ?>
<?php include_once ((EW_USE_ADODB) ? "adodb5/adodb.inc.php" : "phprptinc/ewmysql.php") ?>
<?php include_once "rphpfn11.php" ?>
<?php include_once "rusrfn11.php" ?>
<?php include_once "rptPurchaseOrdersmryinfo.php" ?>
<?php

//
// Page class
//

$rptPurchaseOrder_summary = NULL; // Initialize page object first

class crrptPurchaseOrder_summary extends crrptPurchaseOrder {

	// Page ID
	var $PageID = 'summary';

	// Project ID
	var $ProjectID = "{5E0D34F4-CF4F-4BFE-BA2E-9971073C46CC}";

	// Page object name
	var $PageObjName = 'rptPurchaseOrder_summary';

	// Page headings
	var $Heading = '';
	var $Subheading = '';

	// Page heading
	function PageHeading() {
		global $ReportLanguage;
		if ($this->Heading <> "")
			return $this->Heading;
		if (method_exists($this, "TableCaption"))
			return $this->TableCaption();
		return "";
	}

	// Page subheading
	function PageSubheading() {
		global $ReportLanguage;
		if ($this->Subheading <> "")
			return $this->Subheading;
		return "";
	}

	// Page name
	function PageName() {
		return ewr_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ewr_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Export URLs
	var $ExportPrintUrl;
	var $ExportExcelUrl;
	var $ExportWordUrl;
	var $ExportPdfUrl;
	var $ReportTableClass;
	var $ReportTableStyle = "";

	// Custom export
	var $ExportPrintCustom = FALSE;
	var $ExportExcelCustom = FALSE;
	var $ExportWordCustom = FALSE;
	var $ExportPdfCustom = FALSE;
	var $ExportEmailCustom = FALSE;

	// Message
	function getMessage() {
		return @$_SESSION[EWR_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EWR_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EWR_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EWR_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ewr_AddMessage($_SESSION[EWR_SESSION_WARNING_MESSAGE], $v);
	}

		// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-info ewInfo\">" . $sMessage . "</div>";
			$_SESSION[EWR_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EWR_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EWR_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-danger ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EWR_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") // Header exists, display
			echo $sHeader;
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") // Fotoer exists, display
			echo $sFooter;
	}

	// Validate page request
	function IsPageRequest() {
		if ($this->UseTokenInUrl) {
			if (ewr_IsHttpPost())
				return ($this->TableVar == @$_POST("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == @$_GET["t"]);
		} else {
			return TRUE;
		}
	}
	var $Token = "";
	var $CheckToken = EWR_CHECK_TOKEN;
	var $CheckTokenFn = "ewr_CheckToken";
	var $CreateTokenFn = "ewr_CreateToken";

	// Valid Post
	function ValidPost() {
		if (!$this->CheckToken || !ewr_IsHttpPost())
			return TRUE;
		if (!isset($_POST[EWR_TOKEN_NAME]))
			return FALSE;
		$fn = $this->CheckTokenFn;
		if (is_callable($fn))
			return $fn($_POST[EWR_TOKEN_NAME]);
		return FALSE;
	}

	// Create Token
	function CreateToken() {
		global $grToken;
		if ($this->CheckToken) {
			$fn = $this->CreateTokenFn;
			if ($this->Token == "" && is_callable($fn)) // Create token
				$this->Token = $fn();
			$grToken = $this->Token; // Save to global variable
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $ReportLanguage;

		// Language object
		$ReportLanguage = new crLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (rptPurchaseOrder)
		if (!isset($GLOBALS["rptPurchaseOrder"])) {
			$GLOBALS["rptPurchaseOrder"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["rptPurchaseOrder"];
		}

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";

		// Page ID
		if (!defined("EWR_PAGE_ID"))
			define("EWR_PAGE_ID", 'summary', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EWR_TABLE_NAME"))
			define("EWR_TABLE_NAME", 'rptPurchaseOrder', TRUE);

		// Start timer
		if (!isset($GLOBALS["grTimer"]))
			$GLOBALS["grTimer"] = new crTimer();

		// Debug message
		ewr_LoadDebugMsg();

		// Open connection
		if (!isset($conn)) $conn = ewr_Connect($this->DBID);

		// Export options
		$this->ExportOptions = new crListOptions();
		$this->ExportOptions->Tag = "div";
		$this->ExportOptions->TagClassName = "ewExportOption";

		// Search options
		$this->SearchOptions = new crListOptions();
		$this->SearchOptions->Tag = "div";
		$this->SearchOptions->TagClassName = "ewSearchOption";

		// Filter options
		$this->FilterOptions = new crListOptions();
		$this->FilterOptions->Tag = "div";
		$this->FilterOptions->TagClassName = "ewFilterOption frptPurchaseOrdersummary";

		// Generate report options
		$this->GenerateOptions = new crListOptions();
		$this->GenerateOptions->Tag = "div";
		$this->GenerateOptions->TagClassName = "ewGenerateOption";
	}

	//
	// Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $gsEmailContentType, $ReportLanguage, $Security, $UserProfile;
		global $gsCustomExport;

		// Get export parameters
		if (@$_GET["export"] <> "")
			$this->Export = strtolower($_GET["export"]);
		elseif (@$_POST["export"] <> "")
			$this->Export = strtolower($_POST["export"]);
		$gsExport = $this->Export; // Get export parameter, used in header
		$gsExportFile = $this->TableVar; // Get export file, used in header
		$gsEmailContentType = @$_POST["contenttype"]; // Get email content type

		// Setup placeholder
		$this->PoNo->PlaceHolder = $this->PoNo->FldCaption();

		// Setup export options
		$this->SetupExportOptions();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();

		// Check token
		if (!$this->ValidPost()) {
			echo $ReportLanguage->Phrase("InvalidPostRequest");
			$this->Page_Terminate();
			exit();
		}

		// Create Token
		$this->CreateToken();
	}

	// Set up export options
	function SetupExportOptions() {
		global $Security, $ReportLanguage, $ReportOptions;
		$exportid = session_id();
		$ReportTypes = array();

		// Printer friendly
		$item = &$this->ExportOptions->Add("print");
		$item->Body = "<a class=\"ewrExportLink ewPrint\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("PrinterFriendly", TRUE)) . "\" href=\"" . $this->ExportPrintUrl . "\">" . $ReportLanguage->Phrase("PrinterFriendly") . "</a>";
		$item->Visible = TRUE;
		$ReportTypes["print"] = $item->Visible ? $ReportLanguage->Phrase("ReportFormPrint") : "";

		// Export to Excel
		$item = &$this->ExportOptions->Add("excel");
		$item->Body = "<a class=\"ewrExportLink ewExcel\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToExcel", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToExcel", TRUE)) . "\" href=\"" . $this->ExportExcelUrl . "\">" . $ReportLanguage->Phrase("ExportToExcel") . "</a>";
		$item->Visible = TRUE;
		$ReportTypes["excel"] = $item->Visible ? $ReportLanguage->Phrase("ReportFormExcel") : "";

		// Export to Word
		$item = &$this->ExportOptions->Add("word");
		$item->Body = "<a class=\"ewrExportLink ewWord\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToWord", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToWord", TRUE)) . "\" href=\"" . $this->ExportWordUrl . "\">" . $ReportLanguage->Phrase("ExportToWord") . "</a>";
		$item->Visible = TRUE;
		$ReportTypes["word"] = $item->Visible ? $ReportLanguage->Phrase("ReportFormWord") : "";

		// Export to Pdf
		$item = &$this->ExportOptions->Add("pdf");
		$item->Body = "<a class=\"ewrExportLink ewPdf\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" href=\"" . $this->ExportPdfUrl . "\">" . $ReportLanguage->Phrase("ExportToPDF") . "</a>";
		$item->Visible = FALSE;

		// Uncomment codes below to show export to Pdf link
//		$item->Visible = TRUE;

		$ReportTypes["pdf"] = $item->Visible ? $ReportLanguage->Phrase("ReportFormPdf") : "";

		// Export to Email
		$item = &$this->ExportOptions->Add("email");
		$url = $this->PageUrl() . "export=email";
		$item->Body = "<a class=\"ewrExportLink ewEmail\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToEmail", TRUE)) . "\" id=\"emf_rptPurchaseOrder\" href=\"javascript:void(0);\" onclick=\"ewr_EmailDialogShow({lnk:'emf_rptPurchaseOrder',hdr:ewLanguage.Phrase('ExportToEmail'),url:'$url',exportid:'$exportid',el:this});\">" . $ReportLanguage->Phrase("ExportToEmail") . "</a>";
		$item->Visible = TRUE;
		$ReportTypes["email"] = $item->Visible ? $ReportLanguage->Phrase("ReportFormEmail") : "";
		$ReportOptions["ReportTypes"] = $ReportTypes;

		// Drop down button for export
		$this->ExportOptions->UseDropDownButton = TRUE;
		$this->ExportOptions->UseButtonGroup = TRUE;
		$this->ExportOptions->UseImageAndText = $this->ExportOptions->UseDropDownButton;
		$this->ExportOptions->DropDownButtonPhrase = $ReportLanguage->Phrase("ButtonExport");

		// Add group option item
		$item = &$this->ExportOptions->Add($this->ExportOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Filter button
		$item = &$this->FilterOptions->Add("savecurrentfilter");
		$item->Body = "<a class=\"ewSaveFilter\" data-form=\"frptPurchaseOrdersummary\" href=\"#\">" . $ReportLanguage->Phrase("SaveCurrentFilter") . "</a>";
		$item->Visible = TRUE;
		$item = &$this->FilterOptions->Add("deletefilter");
		$item->Body = "<a class=\"ewDeleteFilter\" data-form=\"frptPurchaseOrdersummary\" href=\"#\">" . $ReportLanguage->Phrase("DeleteFilter") . "</a>";
		$item->Visible = TRUE;
		$this->FilterOptions->UseDropDownButton = TRUE;
		$this->FilterOptions->UseButtonGroup = !$this->FilterOptions->UseDropDownButton; // v8
		$this->FilterOptions->DropDownButtonPhrase = $ReportLanguage->Phrase("Filters");

		// Add group option item
		$item = &$this->FilterOptions->Add($this->FilterOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Set up options (extended)
		$this->SetupExportOptionsExt();

		// Hide options for export
		if ($this->Export <> "") {
			$this->ExportOptions->HideAllOptions();
			$this->FilterOptions->HideAllOptions();
		}

		// Set up table class
		if ($this->Export == "word" || $this->Export == "excel" || $this->Export == "pdf")
			$this->ReportTableClass = "ewTable";
		else
			$this->ReportTableClass = "table ewTable";
	}

	// Set up search options
	function SetupSearchOptions() {
		global $ReportLanguage;

		// Filter panel button
		$item = &$this->SearchOptions->Add("searchtoggle");
		$SearchToggleClass = $this->FilterApplied ? " active" : " active";
		$item->Body = "<button type=\"button\" class=\"btn btn-default ewSearchToggle" . $SearchToggleClass . "\" title=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-caption=\"" . $ReportLanguage->Phrase("SearchBtn", TRUE) . "\" data-toggle=\"button\" data-form=\"frptPurchaseOrdersummary\">" . $ReportLanguage->Phrase("SearchBtn") . "</button>";
		$item->Visible = TRUE;

		// Reset filter
		$item = &$this->SearchOptions->Add("resetfilter");
		$item->Body = "<button type=\"button\" class=\"btn btn-default\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ResetAllFilter", TRUE)) . "\" onclick=\"location='" . ewr_CurrentPage() . "?cmd=reset'\">" . $ReportLanguage->Phrase("ResetAllFilter") . "</button>";
		$item->Visible = TRUE && $this->FilterApplied;

		// Button group for reset filter
		$this->SearchOptions->UseButtonGroup = TRUE;

		// Add group option item
		$item = &$this->SearchOptions->Add($this->SearchOptions->GroupOptionName);
		$item->Body = "";
		$item->Visible = FALSE;

		// Hide options for export
		if ($this->Export <> "")
			$this->SearchOptions->HideAllOptions();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $ReportLanguage, $EWR_EXPORT, $gsExportFile;
		global $grDashboardReport;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Export
		if ($this->Export <> "" && array_key_exists($this->Export, $EWR_EXPORT)) {
			$sContent = ob_get_contents();
			if (ob_get_length())
				ob_end_clean();

			// Remove all <div data-tagid="..." id="orig..." class="hide">...</div> (for customviewtag export, except "googlemaps")
			if (preg_match_all('/<div\s+data-tagid=[\'"]([\s\S]*?)[\'"]\s+id=[\'"]orig([\s\S]*?)[\'"]\s+class\s*=\s*[\'"]hide[\'"]>([\s\S]*?)<\/div\s*>/i', $sContent, $divmatches, PREG_SET_ORDER)) {
				foreach ($divmatches as $divmatch) {
					if ($divmatch[1] <> "googlemaps")
						$sContent = str_replace($divmatch[0], '', $sContent);
				}
			}
			$fn = $EWR_EXPORT[$this->Export];
			if ($this->Export == "email") { // Email
				if (@$this->GenOptions["reporttype"] == "email") {
					$saveResponse = $this->$fn($sContent, $this->GenOptions);
					$this->WriteGenResponse($saveResponse);
				} else {
					echo $this->$fn($sContent, array());
				}
				$url = ""; // Avoid redirect
			} else {
				$saveToFile = $this->$fn($sContent, $this->GenOptions);
				if (@$this->GenOptions["reporttype"] <> "") {
					$saveUrl = ($saveToFile <> "") ? ewr_FullUrl($saveToFile, "genurl") : $ReportLanguage->Phrase("GenerateSuccess");
					$this->WriteGenResponse($saveUrl);
					$url = ""; // Avoid redirect
				}
			}
		}

		// Close connection if not in dashboard
		if (!$grDashboardReport)
			ewr_CloseConn();

		// Go to URL if specified
		if ($url <> "") {
			if (!EWR_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			ewr_SaveDebugMsg();
			header("Location: " . $url);
		}
		if (!$grDashboardReport)
			exit();
	}

	// Initialize common variables
	var $ExportOptions; // Export options
	var $SearchOptions; // Search options
	var $FilterOptions; // Filter options

	// Paging variables
	var $RecIndex = 0; // Record index
	var $RecCount = 0; // Record count
	var $StartGrp = 0; // Start group
	var $StopGrp = 0; // Stop group
	var $TotalGrps = 0; // Total groups
	var $GrpCount = 0; // Group count
	var $GrpCounter = array(); // Group counter
	var $DisplayGrps = 25; // Groups per page
	var $GrpRange = 10;
	var $Sort = "";
	var $Filter = "";
	var $PageFirstGroupFilter = "";
	var $UserIDFilter = "";
	var $DrillDown = FALSE;
	var $DrillDownInPanel = FALSE;
	var $DrillDownList = "";

	// Clear field for ext filter
	var $ClearExtFilter = "";
	var $PopupName = "";
	var $PopupValue = "";
	var $FilterApplied;
	var $SearchCommand = FALSE;
	var $ShowHeader;
	var $GrpColumnCount = 0;
	var $SubGrpColumnCount = 0;
	var $DtlColumnCount = 0;
	var $Cnt, $Col, $Val, $Smry, $Mn, $Mx, $GrandCnt, $GrandSmry, $GrandMn, $GrandMx;
	var $TotCount;
	var $GrandSummarySetup = FALSE;
	var $GrpIdx;
	var $DetailRows = array();
	var $TopContentClass = "col-sm-12 ewTop";
	var $LeftContentClass = "ewLeft";
	var $CenterContentClass = "col-sm-12 ewCenter";
	var $RightContentClass = "ewRight";
	var $BottomContentClass = "col-sm-12 ewBottom";

	//
	// Page main
	//
	function Page_Main() {
		global $rs;
		global $rsgrp;
		global $Security;
		global $grFormError;
		global $grDrillDownInPanel;
		global $ReportBreadcrumb;
		global $ReportLanguage;
		global $grDashboardReport;

		// Set field visibility for detail fields
		$this->ProductCode->SetVisibility();
		$this->ProductDesc->SetVisibility();
		$this->CostPrice->SetVisibility();
		$this->Qty->SetVisibility();
		$this->Amount->SetVisibility();

		// Aggregate variables
		// 1st dimension = no of groups (level 0 used for grand total)
		// 2nd dimension = no of fields

		$nDtls = 6;
		$nGrps = 5;
		$this->Val = &ewr_InitArray($nDtls, 0);
		$this->Cnt = &ewr_Init2DArray($nGrps, $nDtls, 0);
		$this->Smry = &ewr_Init2DArray($nGrps, $nDtls, 0);
		$this->Mn = &ewr_Init2DArray($nGrps, $nDtls, NULL);
		$this->Mx = &ewr_Init2DArray($nGrps, $nDtls, NULL);
		$this->GrandCnt = &ewr_InitArray($nDtls, 0);
		$this->GrandSmry = &ewr_InitArray($nDtls, 0);
		$this->GrandMn = &ewr_InitArray($nDtls, NULL);
		$this->GrandMx = &ewr_InitArray($nDtls, NULL);

		// Set up array if accumulation required: array(Accum, SkipNullOrZero)
		$this->Col = array(array(FALSE, FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(FALSE,FALSE), array(TRUE,FALSE));

		// Set up groups per page dynamically
		$this->SetUpDisplayGrps();

		// Set up Breadcrumb
		if ($this->Export == "")
			$this->SetupBreadcrumb();

		// Check if search command
		$this->SearchCommand = (@$_GET["cmd"] == "search");

		// Load default filter values
		$this->LoadDefaultFilters();

		// Load custom filters
		$this->Page_FilterLoad();

		// Set up popup filter
		$this->SetupPopup();

		// Load group db values if necessary
		$this->LoadGroupDbValues();

		// Handle Ajax popup
		$this->ProcessAjaxPopup();

		// Extended filter
		$sExtendedFilter = "";

		// Restore filter list
		$this->RestoreFilterList();

		// Build extended filter
		$sExtendedFilter = $this->GetExtendedFilter();
		ewr_AddFilter($this->Filter, $sExtendedFilter);

		// Build popup filter
		$sPopupFilter = $this->GetPopupFilter();

		//ewr_SetDebugMsg("popup filter: " . $sPopupFilter);
		ewr_AddFilter($this->Filter, $sPopupFilter);

		// Check if filter applied
		$this->FilterApplied = $this->CheckFilter();

		// Call Page Selecting event
		$this->Page_Selecting($this->Filter);

		// Requires search criteria
		if (($this->Filter == $this->UserIDFilter || $grFormError != "") && !$this->DrillDown)
			$this->Filter = "0=101";

		// Search options
		$this->SetupSearchOptions();

		// Get sort
		$this->Sort = $this->GetSort($this->GenOptions);

		// Get total group count
		$sGrpSort = ewr_UpdateSortFields($this->getSqlOrderByGroup(), $this->Sort, 2); // Get grouping field only
		$sSql = ewr_BuildReportSql($this->getSqlSelectGroup(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), $this->getSqlOrderByGroup(), $this->Filter, $sGrpSort);
		$this->TotalGrps = $this->GetGrpCnt($sSql);
		if ($this->DisplayGrps <= 0 || $this->DrillDown || $grDashboardReport) // Display all groups
			$this->DisplayGrps = $this->TotalGrps;
		$this->StartGrp = 1;

		// Show header
		$this->ShowHeader = ($this->TotalGrps > 0);

		// Set up start position if not export all
		if ($this->ExportAll && $this->Export <> "")
			$this->DisplayGrps = $this->TotalGrps;
		else
			$this->SetUpStartGroup($this->GenOptions);

		// Set no record found message
		if ($this->TotalGrps == 0) {
				if ($this->Filter == "0=101") {
					$this->setWarningMessage($ReportLanguage->Phrase("EnterSearchCriteria"));
				} else {
					$this->setWarningMessage($ReportLanguage->Phrase("NoRecord"));
				}
		}

		// Hide export options if export/dashboard report
		if ($this->Export <> "" || $grDashboardReport)
			$this->ExportOptions->HideAllOptions();

		// Hide search/filter options if export/drilldown/dashboard report
		if ($this->Export <> "" || $this->DrillDown || $grDashboardReport) {
			$this->SearchOptions->HideAllOptions();
			$this->FilterOptions->HideAllOptions();
			$this->GenerateOptions->HideAllOptions();
		}

		// Get current page groups
		$rsgrp = $this->GetGrpRs($sSql, $this->StartGrp, $this->DisplayGrps);

		// Init detail recordset
		$rs = NULL;
		$this->SetupFieldCount();
	}

	// Get summary count
	function GetSummaryCount($lvl, $curValue = TRUE) {
		$cnt = 0;
		foreach ($this->DetailRows as $row) {
			$wrkPoNo = $row["PoNo"];
			$wrkPRNo = $row["PRNo"];
			$wrkPoDate = $row["PoDate"];
			$wrkSupplierName = $row["SupplierName"];
			if ($lvl >= 1) {
				$val = $curValue ? $this->PoNo->CurrentValue : $this->PoNo->OldValue;
				$grpval = $curValue ? $this->PoNo->GroupValue() : $this->PoNo->GroupOldValue();
				if (is_null($val) && !is_null($wrkPoNo) || !is_null($val) && is_null($wrkPoNo) ||
					$grpval <> $this->PoNo->getGroupValueBase($wrkPoNo))
				continue;
			}
			if ($lvl >= 2) {
				$val = $curValue ? $this->PRNo->CurrentValue : $this->PRNo->OldValue;
				$grpval = $curValue ? $this->PRNo->GroupValue() : $this->PRNo->GroupOldValue();
				if (is_null($val) && !is_null($wrkPRNo) || !is_null($val) && is_null($wrkPRNo) ||
					$grpval <> $this->PRNo->getGroupValueBase($wrkPRNo))
				continue;
			}
			if ($lvl >= 3) {
				$val = $curValue ? $this->PoDate->CurrentValue : $this->PoDate->OldValue;
				$grpval = $curValue ? $this->PoDate->GroupValue() : $this->PoDate->GroupOldValue();
				if (is_null($val) && !is_null($wrkPoDate) || !is_null($val) && is_null($wrkPoDate) ||
					$grpval <> $this->PoDate->getGroupValueBase($wrkPoDate))
				continue;
			}
			if ($lvl >= 4) {
				$val = $curValue ? $this->SupplierName->CurrentValue : $this->SupplierName->OldValue;
				$grpval = $curValue ? $this->SupplierName->GroupValue() : $this->SupplierName->GroupOldValue();
				if (is_null($val) && !is_null($wrkSupplierName) || !is_null($val) && is_null($wrkSupplierName) ||
					$grpval <> $this->SupplierName->getGroupValueBase($wrkSupplierName))
				continue;
			}
			$cnt++;
		}
		return $cnt;
	}

	// Check level break
	function ChkLvlBreak($lvl) {
		switch ($lvl) {
			case 1:
				return (is_null($this->PoNo->CurrentValue) && !is_null($this->PoNo->OldValue)) ||
					(!is_null($this->PoNo->CurrentValue) && is_null($this->PoNo->OldValue)) ||
					($this->PoNo->GroupValue() <> $this->PoNo->GroupOldValue());
			case 2:
				return (is_null($this->PRNo->CurrentValue) && !is_null($this->PRNo->OldValue)) ||
					(!is_null($this->PRNo->CurrentValue) && is_null($this->PRNo->OldValue)) ||
					($this->PRNo->GroupValue() <> $this->PRNo->GroupOldValue()) || $this->ChkLvlBreak(1); // Recurse upper level
			case 3:
				return (is_null($this->PoDate->CurrentValue) && !is_null($this->PoDate->OldValue)) ||
					(!is_null($this->PoDate->CurrentValue) && is_null($this->PoDate->OldValue)) ||
					($this->PoDate->GroupValue() <> $this->PoDate->GroupOldValue()) || $this->ChkLvlBreak(2); // Recurse upper level
			case 4:
				return (is_null($this->SupplierName->CurrentValue) && !is_null($this->SupplierName->OldValue)) ||
					(!is_null($this->SupplierName->CurrentValue) && is_null($this->SupplierName->OldValue)) ||
					($this->SupplierName->GroupValue() <> $this->SupplierName->GroupOldValue()) || $this->ChkLvlBreak(3); // Recurse upper level
		}
	}

	// Accummulate summary
	function AccumulateSummary() {
		$cntx = count($this->Smry);
		for ($ix = 0; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				if ($this->Col[$iy][0]) { // Accumulate required
					$valwrk = $this->Val[$iy];
					if (is_null($valwrk)) {
						if (!$this->Col[$iy][1])
							$this->Cnt[$ix][$iy]++;
					} else {
						$accum = (!$this->Col[$iy][1] || !is_numeric($valwrk) || $valwrk <> 0);
						if ($accum) {
							$this->Cnt[$ix][$iy]++;
							if (is_numeric($valwrk)) {
								$this->Smry[$ix][$iy] += $valwrk;
								if (is_null($this->Mn[$ix][$iy])) {
									$this->Mn[$ix][$iy] = $valwrk;
									$this->Mx[$ix][$iy] = $valwrk;
								} else {
									if ($this->Mn[$ix][$iy] > $valwrk) $this->Mn[$ix][$iy] = $valwrk;
									if ($this->Mx[$ix][$iy] < $valwrk) $this->Mx[$ix][$iy] = $valwrk;
								}
							}
						}
					}
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = 0; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0]++;
		}
	}

	// Reset level summary
	function ResetLevelSummary($lvl) {

		// Clear summary values
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$cnty = count($this->Smry[$ix]);
			for ($iy = 1; $iy < $cnty; $iy++) {
				$this->Cnt[$ix][$iy] = 0;
				if ($this->Col[$iy][0]) {
					$this->Smry[$ix][$iy] = 0;
					$this->Mn[$ix][$iy] = NULL;
					$this->Mx[$ix][$iy] = NULL;
				}
			}
		}
		$cntx = count($this->Smry);
		for ($ix = $lvl; $ix < $cntx; $ix++) {
			$this->Cnt[$ix][0] = 0;
		}

		// Reset record count
		$this->RecCount = 0;
	}

	// Accummulate grand summary
	function AccumulateGrandSummary() {
		$this->TotCount++;
		$cntgs = count($this->GrandSmry);
		for ($iy = 1; $iy < $cntgs; $iy++) {
			if ($this->Col[$iy][0]) {
				$valwrk = $this->Val[$iy];
				if (is_null($valwrk) || !is_numeric($valwrk)) {
					if (!$this->Col[$iy][1])
						$this->GrandCnt[$iy]++;
				} else {
					if (!$this->Col[$iy][1] || $valwrk <> 0) {
						$this->GrandCnt[$iy]++;
						$this->GrandSmry[$iy] += $valwrk;
						if (is_null($this->GrandMn[$iy])) {
							$this->GrandMn[$iy] = $valwrk;
							$this->GrandMx[$iy] = $valwrk;
						} else {
							if ($this->GrandMn[$iy] > $valwrk) $this->GrandMn[$iy] = $valwrk;
							if ($this->GrandMx[$iy] < $valwrk) $this->GrandMx[$iy] = $valwrk;
						}
					}
				}
			}
		}
	}

	// Get group count
	function GetGrpCnt($sql) {
		$conn = &$this->Connection();
		$rsgrpcnt = $conn->Execute($sql);
		$grpcnt = ($rsgrpcnt) ? $rsgrpcnt->RecordCount() : 0;
		if ($rsgrpcnt) $rsgrpcnt->Close();
		return $grpcnt;
	}

	// Get group recordset
	function GetGrpRs($wrksql, $start = -1, $grps = -1) {
		$conn = &$this->Connection();
		$conn->raiseErrorFn = $GLOBALS["EWR_ERROR_FN"];
		$rswrk = $conn->SelectLimit($wrksql, $grps, $start - 1);
		$conn->raiseErrorFn = '';
		return $rswrk;
	}

	// Get group row values
	function GetGrpRow($opt) {
		global $rsgrp;
		if (!$rsgrp)
			return;
		if ($opt == 1) { // Get first group

			//$rsgrp->MoveFirst(); // NOTE: no need to move position
			$this->PoNo->setDbValue(""); // Init first value
		} else { // Get next group
			$rsgrp->MoveNext();
		}
		if (!$rsgrp->EOF)
			$this->PoNo->setDbValue($rsgrp->fields[0]);
		if ($rsgrp->EOF) {
			$this->PoNo->setDbValue("");
		}
	}

	// Get detail recordset
	function GetDetailRs($wrksql) {
		$conn = &$this->Connection();
		$conn->raiseErrorFn = $GLOBALS["EWR_ERROR_FN"];
		$rswrk = $conn->Execute($wrksql);
		$dbtype = ewr_GetConnectionType($this->DBID);
		if ($dbtype == "MYSQL" || $dbtype == "POSTGRESQL") {
			$this->DetailRows = ($rswrk) ? $rswrk->GetRows() : array();
		} else { // Cannot MoveFirst, use another recordset
			$rstmp = $conn->Execute($wrksql);
			$this->DetailRows = ($rstmp) ? $rstmp->GetRows() : array();
			$rstmp->Close();
		}
		$conn->raiseErrorFn = "";
		return $rswrk;
	}

	// Get row values
	function GetRow($opt) {
		global $rs;
		if (!$rs)
			return;
		if ($opt == 1) { // Get first row
			$rs->MoveFirst(); // Move first
			if ($this->GrpCount == 1) {
				$this->FirstRowData = array();
				$this->FirstRowData['PoNo'] = ewr_Conv($rs->fields('PoNo'), 200);
				$this->FirstRowData['PRNo'] = ewr_Conv($rs->fields('PRNo'), 200);
				$this->FirstRowData['PoDate'] = ewr_Conv($rs->fields('PoDate'), 133);
				$this->FirstRowData['SupplierCode'] = ewr_Conv($rs->fields('SupplierCode'), 200);
				$this->FirstRowData['SupplierName'] = ewr_Conv($rs->fields('SupplierName'), 200);
				$this->FirstRowData['Remarks'] = ewr_Conv($rs->fields('Remarks'), 200);
				$this->FirstRowData['ProductCode'] = ewr_Conv($rs->fields('ProductCode'), 200);
				$this->FirstRowData['ProductDesc'] = ewr_Conv($rs->fields('ProductDesc'), 200);
				$this->FirstRowData['CostPrice'] = ewr_Conv($rs->fields('CostPrice'), 131);
				$this->FirstRowData['SellingPrice'] = ewr_Conv($rs->fields('SellingPrice'), 131);
				$this->FirstRowData['Qty'] = ewr_Conv($rs->fields('Qty'), 3);
				$this->FirstRowData['Amount'] = ewr_Conv($rs->fields('Amount'), 131);
				$this->FirstRowData['StatusFlg'] = ewr_Conv($rs->fields('StatusFlg'), 3);
			}
		} else { // Get next row
			$rs->MoveNext();
		}
		if (!$rs->EOF) {
			if ($opt <> 1) {
				if (is_array($this->PoNo->GroupDbValues))
					$this->PoNo->setDbValue(@$this->PoNo->GroupDbValues[$rs->fields('PoNo')]);
				else
					$this->PoNo->setDbValue(ewr_GroupValue($this->PoNo, $rs->fields('PoNo')));
			}
			$this->PRNo->setDbValue($rs->fields('PRNo'));
			$this->PoDate->setDbValue($rs->fields('PoDate'));
			$this->SupplierCode->setDbValue($rs->fields('SupplierCode'));
			$this->SupplierName->setDbValue($rs->fields('SupplierName'));
			$this->Remarks->setDbValue($rs->fields('Remarks'));
			$this->ProductCode->setDbValue($rs->fields('ProductCode'));
			$this->ProductDesc->setDbValue($rs->fields('ProductDesc'));
			$this->CostPrice->setDbValue($rs->fields('CostPrice'));
			$this->SellingPrice->setDbValue($rs->fields('SellingPrice'));
			$this->Qty->setDbValue($rs->fields('Qty'));
			$this->Amount->setDbValue($rs->fields('Amount'));
			$this->StatusFlg->setDbValue($rs->fields('StatusFlg'));
			$this->Val[1] = $this->ProductCode->CurrentValue;
			$this->Val[2] = $this->ProductDesc->CurrentValue;
			$this->Val[3] = $this->CostPrice->CurrentValue;
			$this->Val[4] = $this->Qty->CurrentValue;
			$this->Val[5] = $this->Amount->CurrentValue;
		} else {
			$this->PoNo->setDbValue("");
			$this->PRNo->setDbValue("");
			$this->PoDate->setDbValue("");
			$this->SupplierCode->setDbValue("");
			$this->SupplierName->setDbValue("");
			$this->Remarks->setDbValue("");
			$this->ProductCode->setDbValue("");
			$this->ProductDesc->setDbValue("");
			$this->CostPrice->setDbValue("");
			$this->SellingPrice->setDbValue("");
			$this->Qty->setDbValue("");
			$this->Amount->setDbValue("");
			$this->StatusFlg->setDbValue("");
		}
	}

	// Set up starting group
	function SetUpStartGroup($options = array()) {

		// Exit if no groups
		if ($this->DisplayGrps == 0)
			return;
		$startGrp = (@$options["start"] <> "") ? $options["start"] : @$_GET[EWR_TABLE_START_GROUP];
		$pageNo = (@$options["pageno"] <> "") ? $options["pageno"] : @$_GET["pageno"];

		// Check for a 'start' parameter
		if ($startGrp != "") {
			$this->StartGrp = $startGrp;
			$this->setStartGroup($this->StartGrp);
		} elseif ($pageNo != "") {
			$nPageNo = $pageNo;
			if (is_numeric($nPageNo)) {
				$this->StartGrp = ($nPageNo-1)*$this->DisplayGrps+1;
				if ($this->StartGrp <= 0) {
					$this->StartGrp = 1;
				} elseif ($this->StartGrp >= intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1) {
					$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps)*$this->DisplayGrps+1;
				}
				$this->setStartGroup($this->StartGrp);
			} else {
				$this->StartGrp = $this->getStartGroup();
			}
		} else {
			$this->StartGrp = $this->getStartGroup();
		}

		// Check if correct start group counter
		if (!is_numeric($this->StartGrp) || $this->StartGrp == "") { // Avoid invalid start group counter
			$this->StartGrp = 1; // Reset start group counter
			$this->setStartGroup($this->StartGrp);
		} elseif (intval($this->StartGrp) > intval($this->TotalGrps)) { // Avoid starting group > total groups
			$this->StartGrp = intval(($this->TotalGrps-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to last page first group
			$this->setStartGroup($this->StartGrp);
		} elseif (($this->StartGrp-1) % $this->DisplayGrps <> 0) {
			$this->StartGrp = intval(($this->StartGrp-1)/$this->DisplayGrps) * $this->DisplayGrps + 1; // Point to page boundary
			$this->setStartGroup($this->StartGrp);
		}
	}

	// Load group db values if necessary
	function LoadGroupDbValues() {
		$conn = &$this->Connection();
	}

	// Process Ajax popup
	function ProcessAjaxPopup() {
		global $ReportLanguage;
		$conn = &$this->Connection();
		$fld = NULL;
		if (@$_GET["popup"] <> "") {
			$popupname = $_GET["popup"];

			// Check popup name
			// Output data as Json

			if (!is_null($fld)) {
				$jsdb = ewr_GetJsDb($fld, $fld->FldType);
				if (ob_get_length())
					ob_end_clean();
				echo $jsdb;
				exit();
			}
		}
	}

	// Set up popup
	function SetupPopup() {
		global $ReportLanguage;
		$conn = &$this->Connection();
		if ($this->DrillDown)
			return;

		// Process post back form
		if (ewr_IsHttpPost()) {
			$sName = @$_POST["popup"]; // Get popup form name
			if ($sName <> "") {
				$cntValues = (is_array(@$_POST["sel_$sName"])) ? count($_POST["sel_$sName"]) : 0;
				if ($cntValues > 0) {
					$arValues = $_POST["sel_$sName"];
					if (trim($arValues[0]) == "") // Select all
						$arValues = EWR_INIT_VALUE;
					$this->PopupName = $sName;
					if (ewr_IsAdvancedFilterValue($arValues) || $arValues == EWR_INIT_VALUE)
						$this->PopupValue = $arValues;
					if (!ewr_MatchedArray($arValues, $_SESSION["sel_$sName"])) {
						if ($this->HasSessionFilterValues($sName))
							$this->ClearExtFilter = $sName; // Clear extended filter for this field
					}
					$_SESSION["sel_$sName"] = $arValues;
					$_SESSION["rf_$sName"] = @$_POST["rf_$sName"];
					$_SESSION["rt_$sName"] = @$_POST["rt_$sName"];
					$this->ResetPager();
				}
			}

		// Get 'reset' command
		} elseif (@$_GET["cmd"] <> "") {
			$sCmd = $_GET["cmd"];
			if (strtolower($sCmd) == "reset") {
				$this->ResetPager();
			}
		}

		// Load selection criteria to array
	}

	// Reset pager
	function ResetPager() {

		// Reset start position (reset command)
		$this->StartGrp = 1;
		$this->setStartGroup($this->StartGrp);
	}

	// Set up number of groups displayed per page
	function SetUpDisplayGrps() {
		$sWrk = @$_GET[EWR_TABLE_GROUP_PER_PAGE];
		if ($sWrk <> "") {
			if (is_numeric($sWrk)) {
				$this->DisplayGrps = intval($sWrk);
			} else {
				if (strtoupper($sWrk) == "ALL") { // Display all groups
					$this->DisplayGrps = -1;
				} else {
					$this->DisplayGrps = 25; // Non-numeric, load default
				}
			}
			$this->setGroupPerPage($this->DisplayGrps); // Save to session

			// Reset start position (reset command)
			$this->StartGrp = 1;
			$this->setStartGroup($this->StartGrp);
		} else {
			if ($this->getGroupPerPage() <> "") {
				$this->DisplayGrps = $this->getGroupPerPage(); // Restore from session
			} else {
				$this->DisplayGrps = 25; // Load default
			}
		}
	}

	// Render row
	function RenderRow() {
		global $rs, $Security, $ReportLanguage;
		$conn = &$this->Connection();
		if (!$this->GrandSummarySetup) { // Get Grand total
			$bGotCount = FALSE;
			$bGotSummary = FALSE;

			// Get total count from sql directly
			$sSql = ewr_BuildReportSql($this->getSqlSelectCount(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), "", $this->Filter, "");
			$rstot = $conn->Execute($sSql);
			if ($rstot) {
				$this->TotCount = ($rstot->RecordCount()>1) ? $rstot->RecordCount() : $rstot->fields[0];
				$rstot->Close();
				$bGotCount = TRUE;
			} else {
				$this->TotCount = 0;
			}

			// Get total from sql directly
			$sSql = ewr_BuildReportSql($this->getSqlSelectAgg(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), "", $this->Filter, "");
			$sSql = $this->getSqlAggPfx() . $sSql . $this->getSqlAggSfx();
			$rsagg = $conn->Execute($sSql);
			if ($rsagg) {
				$this->GrandCnt[1] = $this->TotCount;
				$this->GrandCnt[2] = $this->TotCount;
				$this->GrandCnt[3] = $this->TotCount;
				$this->GrandCnt[4] = $this->TotCount;
				$this->GrandCnt[5] = $this->TotCount;
				$this->GrandSmry[5] = $rsagg->fields("sum_amount");
				$rsagg->Close();
				$bGotSummary = TRUE;
			}

			// Accumulate grand summary from detail records
			if (!$bGotCount || !$bGotSummary) {
				$sSql = ewr_BuildReportSql($this->getSqlSelect(), $this->getSqlWhere(), $this->getSqlGroupBy(), $this->getSqlHaving(), "", $this->Filter, "");
				$rs = $conn->Execute($sSql);
				if ($rs) {
					$this->GetRow(1);
					while (!$rs->EOF) {
						$this->AccumulateGrandSummary();
						$this->GetRow(2);
					}
					$rs->Close();
				}
			}
			$this->GrandSummarySetup = TRUE; // No need to set up again
		}

		// Call Row_Rendering event
		$this->Row_Rendering();

		//
		// Render view codes
		//

		if ($this->RowType == EWR_ROWTYPE_TOTAL && !($this->RowTotalType == EWR_ROWTOTAL_GROUP && $this->RowTotalSubType == EWR_ROWTOTAL_HEADER)) { // Summary row
			ewr_PrependClass($this->RowAttrs["class"], ($this->RowTotalType == EWR_ROWTOTAL_PAGE || $this->RowTotalType == EWR_ROWTOTAL_GRAND) ? "ewRptGrpAggregate" : ""); // Set up row class
			if ($this->RowTotalType == EWR_ROWTOTAL_GROUP) $this->RowAttrs["data-group"] = $this->PoNo->GroupOldValue(); // Set up group attribute
			if ($this->RowTotalType == EWR_ROWTOTAL_GROUP && $this->RowGroupLevel >= 2) $this->RowAttrs["data-group-2"] = $this->PRNo->GroupOldValue(); // Set up group attribute 2
			if ($this->RowTotalType == EWR_ROWTOTAL_GROUP && $this->RowGroupLevel >= 3) $this->RowAttrs["data-group-3"] = $this->PoDate->GroupOldValue(); // Set up group attribute 3
			if ($this->RowTotalType == EWR_ROWTOTAL_GROUP && $this->RowGroupLevel >= 4) $this->RowAttrs["data-group-4"] = $this->SupplierName->GroupOldValue(); // Set up group attribute 4

			// PoNo
			$this->PoNo->GroupViewValue = $this->PoNo->GroupOldValue();
			$this->PoNo->CellAttrs["class"] = ($this->RowGroupLevel == 1) ? "ewRptGrpSummary1" : "ewRptGrpField1";
			$this->PoNo->GroupViewValue = ewr_DisplayGroupValue($this->PoNo, $this->PoNo->GroupViewValue);
			$this->PoNo->GroupSummaryOldValue = $this->PoNo->GroupSummaryValue;
			$this->PoNo->GroupSummaryValue = $this->PoNo->GroupViewValue;
			$this->PoNo->GroupSummaryViewValue = ($this->PoNo->GroupSummaryOldValue <> $this->PoNo->GroupSummaryValue) ? $this->PoNo->GroupSummaryValue : "&nbsp;";

			// PRNo
			$this->PRNo->GroupViewValue = $this->PRNo->GroupOldValue();
			$this->PRNo->CellAttrs["class"] = ($this->RowGroupLevel == 2) ? "ewRptGrpSummary2" : "ewRptGrpField2";
			$this->PRNo->GroupViewValue = ewr_DisplayGroupValue($this->PRNo, $this->PRNo->GroupViewValue);
			$this->PRNo->GroupSummaryOldValue = $this->PRNo->GroupSummaryValue;
			$this->PRNo->GroupSummaryValue = $this->PRNo->GroupViewValue;
			$this->PRNo->GroupSummaryViewValue = ($this->PRNo->GroupSummaryOldValue <> $this->PRNo->GroupSummaryValue) ? $this->PRNo->GroupSummaryValue : "&nbsp;";

			// PoDate
			$this->PoDate->GroupViewValue = $this->PoDate->GroupOldValue();
			$this->PoDate->GroupViewValue = ewr_FormatDateTime($this->PoDate->GroupViewValue, 0);
			$this->PoDate->CellAttrs["class"] = ($this->RowGroupLevel == 3) ? "ewRptGrpSummary3" : "ewRptGrpField3";
			$this->PoDate->GroupViewValue = ewr_DisplayGroupValue($this->PoDate, $this->PoDate->GroupViewValue);
			$this->PoDate->GroupSummaryOldValue = $this->PoDate->GroupSummaryValue;
			$this->PoDate->GroupSummaryValue = $this->PoDate->GroupViewValue;
			$this->PoDate->GroupSummaryViewValue = ($this->PoDate->GroupSummaryOldValue <> $this->PoDate->GroupSummaryValue) ? $this->PoDate->GroupSummaryValue : "&nbsp;";

			// SupplierName
			$this->SupplierName->GroupViewValue = $this->SupplierName->GroupOldValue();
			$this->SupplierName->CellAttrs["class"] = ($this->RowGroupLevel == 4) ? "ewRptGrpSummary4" : "ewRptGrpField4";
			$this->SupplierName->GroupViewValue = ewr_DisplayGroupValue($this->SupplierName, $this->SupplierName->GroupViewValue);
			$this->SupplierName->GroupSummaryOldValue = $this->SupplierName->GroupSummaryValue;
			$this->SupplierName->GroupSummaryValue = $this->SupplierName->GroupViewValue;
			$this->SupplierName->GroupSummaryViewValue = ($this->SupplierName->GroupSummaryOldValue <> $this->SupplierName->GroupSummaryValue) ? $this->SupplierName->GroupSummaryValue : "&nbsp;";

			// Amount
			$this->Amount->SumViewValue = $this->Amount->SumValue;
			$this->Amount->CellAttrs["class"] = ($this->RowTotalType == EWR_ROWTOTAL_PAGE || $this->RowTotalType == EWR_ROWTOTAL_GRAND) ? "ewRptGrpAggregate" : "ewRptGrpSummary" . $this->RowGroupLevel;

			// PoNo
			$this->PoNo->HrefValue = "";

			// PRNo
			$this->PRNo->HrefValue = "";

			// PoDate
			if ($this->SupplierCode->OldValue <> "") {
				$this->PoDate->HrefValue = $this->SupplierCode->OldValue; // Add prefix/suffix
				$this->PoDate->LinkAttrs["target"] = ""; // Add target
				if ($this->Export <> "") $this->PoDate->HrefValue = ewr_FullUrl($this->PoDate->HrefValue, "href");
			} else {
				$this->PoDate->HrefValue = "";
			}

			// SupplierName
			$this->SupplierName->HrefValue = "";

			// ProductCode
			$this->ProductCode->HrefValue = "";

			// ProductDesc
			$this->ProductDesc->HrefValue = "";

			// CostPrice
			$this->CostPrice->HrefValue = "";

			// Qty
			$this->Qty->HrefValue = "";

			// Amount
			$this->Amount->HrefValue = "";
		} else {
			if ($this->RowTotalType == EWR_ROWTOTAL_GROUP && $this->RowTotalSubType == EWR_ROWTOTAL_HEADER) {
			$this->RowAttrs["data-group"] = $this->PoNo->GroupValue(); // Set up group attribute
			if ($this->RowGroupLevel >= 2) $this->RowAttrs["data-group-2"] = $this->PRNo->GroupValue(); // Set up group attribute 2
			if ($this->RowGroupLevel >= 3) $this->RowAttrs["data-group-3"] = $this->PoDate->GroupValue(); // Set up group attribute 3
			if ($this->RowGroupLevel >= 4) $this->RowAttrs["data-group-4"] = $this->SupplierName->GroupValue(); // Set up group attribute 4
			} else {
			$this->RowAttrs["data-group"] = $this->PoNo->GroupValue(); // Set up group attribute
			$this->RowAttrs["data-group-2"] = $this->PRNo->GroupValue(); // Set up group attribute 2
			$this->RowAttrs["data-group-3"] = $this->PoDate->GroupValue(); // Set up group attribute 3
			$this->RowAttrs["data-group-4"] = $this->SupplierName->GroupValue(); // Set up group attribute 4
			}

			// PoNo
			$this->PoNo->GroupViewValue = $this->PoNo->GroupValue();
			$this->PoNo->CellAttrs["class"] = "ewRptGrpField1";
			$this->PoNo->GroupViewValue = ewr_DisplayGroupValue($this->PoNo, $this->PoNo->GroupViewValue);
			if ($this->PoNo->GroupValue() == $this->PoNo->GroupOldValue() && !$this->ChkLvlBreak(1))
				$this->PoNo->GroupViewValue = "&nbsp;";

			// PRNo
			$this->PRNo->GroupViewValue = $this->PRNo->GroupValue();
			$this->PRNo->CellAttrs["class"] = "ewRptGrpField2";
			$this->PRNo->GroupViewValue = ewr_DisplayGroupValue($this->PRNo, $this->PRNo->GroupViewValue);
			if ($this->PRNo->GroupValue() == $this->PRNo->GroupOldValue() && !$this->ChkLvlBreak(2))
				$this->PRNo->GroupViewValue = "&nbsp;";

			// PoDate
			$this->PoDate->GroupViewValue = $this->PoDate->GroupValue();
			$this->PoDate->GroupViewValue = ewr_FormatDateTime($this->PoDate->GroupViewValue, 0);
			$this->PoDate->CellAttrs["class"] = "ewRptGrpField3";
			$this->PoDate->GroupViewValue = ewr_DisplayGroupValue($this->PoDate, $this->PoDate->GroupViewValue);
			if ($this->PoDate->GroupValue() == $this->PoDate->GroupOldValue() && !$this->ChkLvlBreak(3))
				$this->PoDate->GroupViewValue = "&nbsp;";

			// SupplierName
			$this->SupplierName->GroupViewValue = $this->SupplierName->GroupValue();
			$this->SupplierName->CellAttrs["class"] = "ewRptGrpField4";
			$this->SupplierName->GroupViewValue = ewr_DisplayGroupValue($this->SupplierName, $this->SupplierName->GroupViewValue);
			if ($this->SupplierName->GroupValue() == $this->SupplierName->GroupOldValue() && !$this->ChkLvlBreak(4))
				$this->SupplierName->GroupViewValue = "&nbsp;";

			// ProductCode
			$this->ProductCode->ViewValue = $this->ProductCode->CurrentValue;
			$this->ProductCode->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// ProductDesc
			$this->ProductDesc->ViewValue = $this->ProductDesc->CurrentValue;
			$this->ProductDesc->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";
			$this->ProductDesc->CellAttrs["style"] = "width: 250px;";

			// CostPrice
			$this->CostPrice->ViewValue = $this->CostPrice->CurrentValue;
			$this->CostPrice->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Qty
			$this->Qty->ViewValue = $this->Qty->CurrentValue;
			$this->Qty->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// Amount
			$this->Amount->ViewValue = $this->Amount->CurrentValue;
			$this->Amount->CellAttrs["class"] = ($this->RecCount % 2 <> 1) ? "ewTableAltRow" : "ewTableRow";

			// PoNo
			$this->PoNo->HrefValue = "";

			// PRNo
			$this->PRNo->HrefValue = "";

			// PoDate
			if ($this->SupplierCode->CurrentValue <> "") {
				$this->PoDate->HrefValue = $this->SupplierCode->CurrentValue; // Add prefix/suffix
				$this->PoDate->LinkAttrs["target"] = ""; // Add target
				if ($this->Export <> "") $this->PoDate->HrefValue = ewr_FullUrl($this->PoDate->HrefValue, "href");
			} else {
				$this->PoDate->HrefValue = "";
			}

			// SupplierName
			$this->SupplierName->HrefValue = "";

			// ProductCode
			$this->ProductCode->HrefValue = "";

			// ProductDesc
			$this->ProductDesc->HrefValue = "";

			// CostPrice
			$this->CostPrice->HrefValue = "";

			// Qty
			$this->Qty->HrefValue = "";

			// Amount
			$this->Amount->HrefValue = "";
		}

		// Call Cell_Rendered event
		if ($this->RowType == EWR_ROWTYPE_TOTAL) { // Summary row

			// PoNo
			$CurrentValue = $this->PoNo->GroupViewValue;
			$ViewValue = &$this->PoNo->GroupViewValue;
			$ViewAttrs = &$this->PoNo->ViewAttrs;
			$CellAttrs = &$this->PoNo->CellAttrs;
			$HrefValue = &$this->PoNo->HrefValue;
			$LinkAttrs = &$this->PoNo->LinkAttrs;
			$this->Cell_Rendered($this->PoNo, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// PRNo
			$CurrentValue = $this->PRNo->GroupViewValue;
			$ViewValue = &$this->PRNo->GroupViewValue;
			$ViewAttrs = &$this->PRNo->ViewAttrs;
			$CellAttrs = &$this->PRNo->CellAttrs;
			$HrefValue = &$this->PRNo->HrefValue;
			$LinkAttrs = &$this->PRNo->LinkAttrs;
			$this->Cell_Rendered($this->PRNo, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// PoDate
			$CurrentValue = $this->PoDate->GroupViewValue;
			$ViewValue = &$this->PoDate->GroupViewValue;
			$ViewAttrs = &$this->PoDate->ViewAttrs;
			$CellAttrs = &$this->PoDate->CellAttrs;
			$HrefValue = &$this->PoDate->HrefValue;
			$LinkAttrs = &$this->PoDate->LinkAttrs;
			$this->Cell_Rendered($this->PoDate, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// SupplierName
			$CurrentValue = $this->SupplierName->GroupViewValue;
			$ViewValue = &$this->SupplierName->GroupViewValue;
			$ViewAttrs = &$this->SupplierName->ViewAttrs;
			$CellAttrs = &$this->SupplierName->CellAttrs;
			$HrefValue = &$this->SupplierName->HrefValue;
			$LinkAttrs = &$this->SupplierName->LinkAttrs;
			$this->Cell_Rendered($this->SupplierName, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Amount
			$CurrentValue = $this->Amount->SumValue;
			$ViewValue = &$this->Amount->SumViewValue;
			$ViewAttrs = &$this->Amount->ViewAttrs;
			$CellAttrs = &$this->Amount->CellAttrs;
			$HrefValue = &$this->Amount->HrefValue;
			$LinkAttrs = &$this->Amount->LinkAttrs;
			$this->Cell_Rendered($this->Amount, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);
		} else {

			// PoNo
			$CurrentValue = $this->PoNo->GroupValue();
			$ViewValue = &$this->PoNo->GroupViewValue;
			$ViewAttrs = &$this->PoNo->ViewAttrs;
			$CellAttrs = &$this->PoNo->CellAttrs;
			$HrefValue = &$this->PoNo->HrefValue;
			$LinkAttrs = &$this->PoNo->LinkAttrs;
			$this->Cell_Rendered($this->PoNo, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// PRNo
			$CurrentValue = $this->PRNo->GroupValue();
			$ViewValue = &$this->PRNo->GroupViewValue;
			$ViewAttrs = &$this->PRNo->ViewAttrs;
			$CellAttrs = &$this->PRNo->CellAttrs;
			$HrefValue = &$this->PRNo->HrefValue;
			$LinkAttrs = &$this->PRNo->LinkAttrs;
			$this->Cell_Rendered($this->PRNo, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// PoDate
			$CurrentValue = $this->PoDate->GroupValue();
			$ViewValue = &$this->PoDate->GroupViewValue;
			$ViewAttrs = &$this->PoDate->ViewAttrs;
			$CellAttrs = &$this->PoDate->CellAttrs;
			$HrefValue = &$this->PoDate->HrefValue;
			$LinkAttrs = &$this->PoDate->LinkAttrs;
			$this->Cell_Rendered($this->PoDate, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// SupplierName
			$CurrentValue = $this->SupplierName->GroupValue();
			$ViewValue = &$this->SupplierName->GroupViewValue;
			$ViewAttrs = &$this->SupplierName->ViewAttrs;
			$CellAttrs = &$this->SupplierName->CellAttrs;
			$HrefValue = &$this->SupplierName->HrefValue;
			$LinkAttrs = &$this->SupplierName->LinkAttrs;
			$this->Cell_Rendered($this->SupplierName, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// ProductCode
			$CurrentValue = $this->ProductCode->CurrentValue;
			$ViewValue = &$this->ProductCode->ViewValue;
			$ViewAttrs = &$this->ProductCode->ViewAttrs;
			$CellAttrs = &$this->ProductCode->CellAttrs;
			$HrefValue = &$this->ProductCode->HrefValue;
			$LinkAttrs = &$this->ProductCode->LinkAttrs;
			$this->Cell_Rendered($this->ProductCode, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// ProductDesc
			$CurrentValue = $this->ProductDesc->CurrentValue;
			$ViewValue = &$this->ProductDesc->ViewValue;
			$ViewAttrs = &$this->ProductDesc->ViewAttrs;
			$CellAttrs = &$this->ProductDesc->CellAttrs;
			$HrefValue = &$this->ProductDesc->HrefValue;
			$LinkAttrs = &$this->ProductDesc->LinkAttrs;
			$this->Cell_Rendered($this->ProductDesc, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// CostPrice
			$CurrentValue = $this->CostPrice->CurrentValue;
			$ViewValue = &$this->CostPrice->ViewValue;
			$ViewAttrs = &$this->CostPrice->ViewAttrs;
			$CellAttrs = &$this->CostPrice->CellAttrs;
			$HrefValue = &$this->CostPrice->HrefValue;
			$LinkAttrs = &$this->CostPrice->LinkAttrs;
			$this->Cell_Rendered($this->CostPrice, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Qty
			$CurrentValue = $this->Qty->CurrentValue;
			$ViewValue = &$this->Qty->ViewValue;
			$ViewAttrs = &$this->Qty->ViewAttrs;
			$CellAttrs = &$this->Qty->CellAttrs;
			$HrefValue = &$this->Qty->HrefValue;
			$LinkAttrs = &$this->Qty->LinkAttrs;
			$this->Cell_Rendered($this->Qty, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);

			// Amount
			$CurrentValue = $this->Amount->CurrentValue;
			$ViewValue = &$this->Amount->ViewValue;
			$ViewAttrs = &$this->Amount->ViewAttrs;
			$CellAttrs = &$this->Amount->CellAttrs;
			$HrefValue = &$this->Amount->HrefValue;
			$LinkAttrs = &$this->Amount->LinkAttrs;
			$this->Cell_Rendered($this->Amount, $CurrentValue, $ViewValue, $ViewAttrs, $CellAttrs, $HrefValue, $LinkAttrs);
		}

		// Call Row_Rendered event
		$this->Row_Rendered();
		$this->SetupFieldCount();
	}

	// Setup field count
	function SetupFieldCount() {
		$this->GrpColumnCount = 0;
		$this->SubGrpColumnCount = 0;
		$this->DtlColumnCount = 0;
		if ($this->PoNo->Visible) $this->GrpColumnCount += 1;
		if ($this->PRNo->Visible) { $this->GrpColumnCount += 1; $this->SubGrpColumnCount += 1; }
		if ($this->PoDate->Visible) { $this->GrpColumnCount += 1; $this->SubGrpColumnCount += 1; }
		if ($this->SupplierName->Visible) { $this->GrpColumnCount += 1; $this->SubGrpColumnCount += 1; }
		if ($this->ProductCode->Visible) $this->DtlColumnCount += 1;
		if ($this->ProductDesc->Visible) $this->DtlColumnCount += 1;
		if ($this->CostPrice->Visible) $this->DtlColumnCount += 1;
		if ($this->Qty->Visible) $this->DtlColumnCount += 1;
		if ($this->Amount->Visible) $this->DtlColumnCount += 1;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $ReportBreadcrumb;
		$ReportBreadcrumb = new crBreadcrumb();
		$url = substr(ewr_CurrentUrl(), strrpos(ewr_CurrentUrl(), "/")+1);
		$url = preg_replace('/\?cmd=reset(all){0,1}$/i', '', $url); // Remove cmd=reset / cmd=resetall
		$ReportBreadcrumb->Add("summary", $this->TableVar, $url, "", $this->TableVar, TRUE);
	}

	function SetupExportOptionsExt() {
		global $ReportLanguage, $ReportOptions;
		$ReportTypes = $ReportOptions["ReportTypes"];
		$item =& $this->ExportOptions->GetItem("pdf");
		$item->Visible = TRUE;
		if ($item->Visible)
			$ReportTypes["pdf"] = $ReportLanguage->Phrase("ReportFormPdf");
		$exportid = session_id();
		$url = $this->ExportPdfUrl;
		$item->Body = "<a class=\"ewrExportLink ewPdf\" title=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" data-caption=\"" . ewr_HtmlEncode($ReportLanguage->Phrase("ExportToPDF", TRUE)) . "\" href=\"javascript:void(0);\" onclick=\"ewr_ExportCharts(this, '" . $url . "', '" . $exportid . "');\">" . $ReportLanguage->Phrase("ExportToPDF") . "</a>";
		$ReportOptions["ReportTypes"] = $ReportTypes;
	}

	// Return extended filter
	function GetExtendedFilter() {
		global $grFormError;
		$sFilter = "";
		if ($this->DrillDown)
			return "";
		$bPostBack = ewr_IsHttpPost();
		$bRestoreSession = TRUE;
		$bSetupFilter = FALSE;

		// Reset extended filter if filter changed
		if ($bPostBack) {

		// Reset search command
		} elseif (@$_GET["cmd"] == "reset") {

			// Load default values
			$this->SetSessionFilterValues($this->PoNo->SearchValue, $this->PoNo->SearchOperator, $this->PoNo->SearchCondition, $this->PoNo->SearchValue2, $this->PoNo->SearchOperator2, 'PoNo'); // Field PoNo

			//$bSetupFilter = TRUE; // No need to set up, just use default
		} else {
			$bRestoreSession = !$this->SearchCommand;

			// Field PoNo
			if ($this->GetFilterValues($this->PoNo)) {
				$bSetupFilter = TRUE;
			}
			if (!$this->ValidateForm()) {
				$this->setFailureMessage($grFormError);
				return $sFilter;
			}
		}

		// Restore session
		if ($bRestoreSession) {
			$this->GetSessionFilterValues($this->PoNo); // Field PoNo
		}

		// Call page filter validated event
		$this->Page_FilterValidated();

		// Build SQL
		$this->BuildExtendedFilter($this->PoNo, $sFilter, FALSE, TRUE); // Field PoNo

		// Save parms to session
		$this->SetSessionFilterValues($this->PoNo->SearchValue, $this->PoNo->SearchOperator, $this->PoNo->SearchCondition, $this->PoNo->SearchValue2, $this->PoNo->SearchOperator2, 'PoNo'); // Field PoNo

		// Setup filter
		if ($bSetupFilter) {
		}
		return $sFilter;
	}

	// Build dropdown filter
	function BuildDropDownFilter(&$fld, &$FilterClause, $FldOpr, $Default = FALSE, $SaveFilter = FALSE) {
		$FldVal = ($Default) ? $fld->DefaultDropDownValue : $fld->DropDownValue;
		$sSql = "";
		if (is_array($FldVal)) {
			foreach ($FldVal as $val) {
				$sWrk = $this->GetDropDownFilter($fld, $val, $FldOpr);

				// Call Page Filtering event
				if (substr($val, 0, 2) <> "@@")
					$this->Page_Filtering($fld, $sWrk, "dropdown", $FldOpr, $val);
				if ($sWrk <> "") {
					if ($sSql <> "")
						$sSql .= " OR " . $sWrk;
					else
						$sSql = $sWrk;
				}
			}
		} else {
			$sSql = $this->GetDropDownFilter($fld, $FldVal, $FldOpr);

			// Call Page Filtering event
			if (substr($FldVal, 0, 2) <> "@@")
				$this->Page_Filtering($fld, $sSql, "dropdown", $FldOpr, $FldVal);
		}
		if ($sSql <> "") {
			ewr_AddFilter($FilterClause, $sSql);
			if ($SaveFilter) $fld->CurrentFilter = $sSql;
		}
	}

	function GetDropDownFilter(&$fld, $FldVal, $FldOpr) {
		$FldName = $fld->FldName;
		$FldExpression = $fld->FldExpression;
		$FldDataType = $fld->FldDataType;
		$FldDelimiter = $fld->FldDelimiter;
		$FldVal = strval($FldVal);
		if ($FldOpr == "") $FldOpr = "=";
		$sWrk = "";
		if (ewr_SameStr($FldVal, EWR_NULL_VALUE)) {
			$sWrk = $FldExpression . " IS NULL";
		} elseif (ewr_SameStr($FldVal, EWR_NOT_NULL_VALUE)) {
			$sWrk = $FldExpression . " IS NOT NULL";
		} elseif (ewr_SameStr($FldVal, EWR_EMPTY_VALUE)) {
			$sWrk = $FldExpression . " = ''";
		} elseif (ewr_SameStr($FldVal, EWR_ALL_VALUE)) {
			$sWrk = "1 = 1";
		} else {
			if (substr($FldVal, 0, 2) == "@@") {
				$sWrk = $this->GetCustomFilter($fld, $FldVal, $this->DBID);
			} elseif ($FldDelimiter <> "" && trim($FldVal) <> "" && ($FldDataType == EWR_DATATYPE_STRING || $FldDataType == EWR_DATATYPE_MEMO)) {
				$sWrk = ewr_GetMultiSearchSql($FldExpression, trim($FldVal), $this->DBID);
			} else {
				if ($FldVal <> "" && $FldVal <> EWR_INIT_VALUE) {
					if ($FldDataType == EWR_DATATYPE_DATE && $FldOpr <> "") {
						$sWrk = ewr_DateFilterString($FldExpression, $FldOpr, $FldVal, $FldDataType, $this->DBID);
					} else {
						$sWrk = ewr_FilterString($FldOpr, $FldVal, $FldDataType, $this->DBID);
						if ($sWrk <> "") $sWrk = $FldExpression . $sWrk;
					}
				}
			}
		}
		return $sWrk;
	}

	// Get custom filter
	function GetCustomFilter(&$fld, $FldVal, $dbid = 0) {
		$sWrk = "";
		if (is_array($fld->AdvancedFilters)) {
			foreach ($fld->AdvancedFilters as $filter) {
				if ($filter->ID == $FldVal && $filter->Enabled) {
					$sFld = $fld->FldExpression;
					$sFn = $filter->FunctionName;
					$wrkid = (substr($filter->ID, 0, 2) == "@@") ? substr($filter->ID,2) : $filter->ID;
					if ($sFn <> "")
						$sWrk = $sFn($sFld, $dbid);
					else
						$sWrk = "";
					$this->Page_Filtering($fld, $sWrk, "custom", $wrkid);
					break;
				}
			}
		}
		return $sWrk;
	}

	// Build extended filter
	function BuildExtendedFilter(&$fld, &$FilterClause, $Default = FALSE, $SaveFilter = FALSE) {
		$sWrk = ewr_GetExtendedFilter($fld, $Default, $this->DBID);
		if (!$Default)
			$this->Page_Filtering($fld, $sWrk, "extended", $fld->SearchOperator, $fld->SearchValue, $fld->SearchCondition, $fld->SearchOperator2, $fld->SearchValue2);
		if ($sWrk <> "") {
			ewr_AddFilter($FilterClause, $sWrk);
			if ($SaveFilter) $fld->CurrentFilter = $sWrk;
		}
	}

	// Get drop down value from querystring
	function GetDropDownValue(&$fld) {
		$parm = substr($fld->FldVar, 2);
		if (ewr_IsHttpPost())
			return FALSE; // Skip post back
		if (isset($_GET["so_$parm"]))
			$fld->SearchOperator = @$_GET["so_$parm"];
		if (isset($_GET["sv_$parm"])) {
			$fld->DropDownValue = @$_GET["sv_$parm"];
			return TRUE;
		}
		return FALSE;
	}

	// Get filter values from querystring
	function GetFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		if (ewr_IsHttpPost())
			return; // Skip post back
		$got = FALSE;
		if (isset($_GET["sv_$parm"])) {
			$fld->SearchValue = @$_GET["sv_$parm"];
			$got = TRUE;
		}
		if (isset($_GET["so_$parm"])) {
			$fld->SearchOperator = @$_GET["so_$parm"];
			$got = TRUE;
		}
		if (isset($_GET["sc_$parm"])) {
			$fld->SearchCondition = @$_GET["sc_$parm"];
			$got = TRUE;
		}
		if (isset($_GET["sv2_$parm"])) {
			$fld->SearchValue2 = @$_GET["sv2_$parm"];
			$got = TRUE;
		}
		if (isset($_GET["so2_$parm"])) {
			$fld->SearchOperator2 = $_GET["so2_$parm"];
			$got = TRUE;
		}
		return $got;
	}

	// Set default ext filter
	function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2) {
		$fld->DefaultSearchValue = $sv1; // Default ext filter value 1
		$fld->DefaultSearchValue2 = $sv2; // Default ext filter value 2 (if operator 2 is enabled)
		$fld->DefaultSearchOperator = $so1; // Default search operator 1
		$fld->DefaultSearchOperator2 = $so2; // Default search operator 2 (if operator 2 is enabled)
		$fld->DefaultSearchCondition = $sc; // Default search condition (if operator 2 is enabled)
	}

	// Apply default ext filter
	function ApplyDefaultExtFilter(&$fld) {
		$fld->SearchValue = $fld->DefaultSearchValue;
		$fld->SearchValue2 = $fld->DefaultSearchValue2;
		$fld->SearchOperator = $fld->DefaultSearchOperator;
		$fld->SearchOperator2 = $fld->DefaultSearchOperator2;
		$fld->SearchCondition = $fld->DefaultSearchCondition;
	}

	// Check if Text Filter applied
	function TextFilterApplied(&$fld) {
		return (strval($fld->SearchValue) <> strval($fld->DefaultSearchValue) ||
			strval($fld->SearchValue2) <> strval($fld->DefaultSearchValue2) ||
			(strval($fld->SearchValue) <> "" &&
				strval($fld->SearchOperator) <> strval($fld->DefaultSearchOperator)) ||
			(strval($fld->SearchValue2) <> "" &&
				strval($fld->SearchOperator2) <> strval($fld->DefaultSearchOperator2)) ||
			strval($fld->SearchCondition) <> strval($fld->DefaultSearchCondition));
	}

	// Check if Non-Text Filter applied
	function NonTextFilterApplied(&$fld) {
		if (is_array($fld->DropDownValue)) {
			if (is_array($fld->DefaultDropDownValue)) {
				if (count($fld->DefaultDropDownValue) <> count($fld->DropDownValue))
					return TRUE;
				else
					return (count(array_diff($fld->DefaultDropDownValue, $fld->DropDownValue)) <> 0);
			} else {
				return TRUE;
			}
		} else {
			if (is_array($fld->DefaultDropDownValue))
				return TRUE;
			else
				$v1 = strval($fld->DefaultDropDownValue);
			if ($v1 == EWR_INIT_VALUE)
				$v1 = "";
			$v2 = strval($fld->DropDownValue);
			if ($v2 == EWR_INIT_VALUE || $v2 == EWR_ALL_VALUE)
				$v2 = "";
			return ($v1 <> $v2);
		}
	}

	// Get dropdown value from session
	function GetSessionDropDownValue(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->DropDownValue, 'sv_rptPurchaseOrder_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_rptPurchaseOrder_' . $parm);
	}

	// Get filter values from session
	function GetSessionFilterValues(&$fld) {
		$parm = substr($fld->FldVar, 2);
		$this->GetSessionValue($fld->SearchValue, 'sv_rptPurchaseOrder_' . $parm);
		$this->GetSessionValue($fld->SearchOperator, 'so_rptPurchaseOrder_' . $parm);
		$this->GetSessionValue($fld->SearchCondition, 'sc_rptPurchaseOrder_' . $parm);
		$this->GetSessionValue($fld->SearchValue2, 'sv2_rptPurchaseOrder_' . $parm);
		$this->GetSessionValue($fld->SearchOperator2, 'so2_rptPurchaseOrder_' . $parm);
	}

	// Get value from session
	function GetSessionValue(&$sv, $sn) {
		if (array_key_exists($sn, $_SESSION))
			$sv = $_SESSION[$sn];
	}

	// Set dropdown value to session
	function SetSessionDropDownValue($sv, $so, $parm) {
		$_SESSION['sv_rptPurchaseOrder_' . $parm] = $sv;
		$_SESSION['so_rptPurchaseOrder_' . $parm] = $so;
	}

	// Set filter values to session
	function SetSessionFilterValues($sv1, $so1, $sc, $sv2, $so2, $parm) {
		$_SESSION['sv_rptPurchaseOrder_' . $parm] = $sv1;
		$_SESSION['so_rptPurchaseOrder_' . $parm] = $so1;
		$_SESSION['sc_rptPurchaseOrder_' . $parm] = $sc;
		$_SESSION['sv2_rptPurchaseOrder_' . $parm] = $sv2;
		$_SESSION['so2_rptPurchaseOrder_' . $parm] = $so2;
	}

	// Check if has Session filter values
	function HasSessionFilterValues($parm) {
		return ((@$_SESSION['sv_' . $parm] <> "" && @$_SESSION['sv_' . $parm] <> EWR_INIT_VALUE) ||
			(@$_SESSION['sv_' . $parm] <> "" && @$_SESSION['sv_' . $parm] <> EWR_INIT_VALUE) ||
			(@$_SESSION['sv2_' . $parm] <> "" && @$_SESSION['sv2_' . $parm] <> EWR_INIT_VALUE));
	}

	// Dropdown filter exist
	function DropDownFilterExist(&$fld, $FldOpr) {
		$sWrk = "";
		$this->BuildDropDownFilter($fld, $sWrk, $FldOpr);
		return ($sWrk <> "");
	}

	// Extended filter exist
	function ExtendedFilterExist(&$fld) {
		$sExtWrk = "";
		$this->BuildExtendedFilter($fld, $sExtWrk);
		return ($sExtWrk <> "");
	}

	// Validate form
	function ValidateForm() {
		global $ReportLanguage, $grFormError;

		// Initialize form error message
		$grFormError = "";

		// Check if validation required
		if (!EWR_SERVER_VALIDATE)
			return ($grFormError == "");

		// Return validate result
		$ValidateForm = ($grFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			$grFormError .= ($grFormError <> "") ? "<p>&nbsp;</p>" : "";
			$grFormError .= $sFormCustomError;
		}
		return $ValidateForm;
	}

	// Clear selection stored in session
	function ClearSessionSelection($parm) {
		$_SESSION["sel_rptPurchaseOrder_$parm"] = "";
		$_SESSION["rf_rptPurchaseOrder_$parm"] = "";
		$_SESSION["rt_rptPurchaseOrder_$parm"] = "";
	}

	// Load selection from session
	function LoadSelectionFromSession($parm) {
		$fld = &$this->FieldByParm($parm);
		$fld->SelectionList = @$_SESSION["sel_rptPurchaseOrder_$parm"];
		$fld->RangeFrom = @$_SESSION["rf_rptPurchaseOrder_$parm"];
		$fld->RangeTo = @$_SESSION["rt_rptPurchaseOrder_$parm"];
	}

	// Load default value for filters
	function LoadDefaultFilters() {
		/**
		* Set up default values for non Text filters
		*/
		/**
		* Set up default values for extended filters
		* function SetDefaultExtFilter(&$fld, $so1, $sv1, $sc, $so2, $sv2)
		* Parameters:
		* $fld - Field object
		* $so1 - Default search operator 1
		* $sv1 - Default ext filter value 1
		* $sc - Default search condition (if operator 2 is enabled)
		* $so2 - Default search operator 2 (if operator 2 is enabled)
		* $sv2 - Default ext filter value 2 (if operator 2 is enabled)
		*/

		// Field PoNo
		$this->SetDefaultExtFilter($this->PoNo, "=", NULL, 'AND', "=", NULL);
		if (!$this->SearchCommand) $this->ApplyDefaultExtFilter($this->PoNo);
		/**
		* Set up default values for popup filters
		*/
	}

	// Check if filter applied
	function CheckFilter() {

		// Check PoNo text filter
		if ($this->TextFilterApplied($this->PoNo))
			return TRUE;
		return FALSE;
	}

	// Show list of filters
	function ShowFilterList($showDate = FALSE) {
		global $ReportLanguage;

		// Initialize
		$sFilterList = "";

		// Field PoNo
		$sExtWrk = "";
		$sWrk = "";
		$this->BuildExtendedFilter($this->PoNo, $sExtWrk);
		$sFilter = "";
		if ($sExtWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sExtWrk</span>";
		elseif ($sWrk <> "")
			$sFilter .= "<span class=\"ewFilterValue\">$sWrk</span>";
		if ($sFilter <> "")
			$sFilterList .= "<div><span class=\"ewFilterCaption\">" . $this->PoNo->FldCaption() . "</span>" . $sFilter . "</div>";
		$divstyle = "";
		$divdataclass = "";

		// Show Filters
		if ($sFilterList <> "" || $showDate) {
			$sMessage = "<div" . $divstyle . $divdataclass . "><div id=\"ewrFilterList\" class=\"alert alert-info\">";
			if ($showDate)
				$sMessage .= "<div id=\"ewrCurrentDate\">" . $ReportLanguage->Phrase("ReportGeneratedDate") . ewr_FormatDateTime(date("Y-m-d H:i:s"), 1) . "</div>";
			if ($sFilterList <> "")
				$sMessage .= "<div id=\"ewrCurrentFilters\">" . $ReportLanguage->Phrase("CurrentFilters") . "</div>" . $sFilterList;
			$sMessage .= "</div></div>";
			$this->Message_Showing($sMessage, "");
			echo $sMessage;
		}
	}

	// Get list of filters
	function GetFilterList() {

		// Initialize
		$sFilterList = "";

		// Field PoNo
		$sWrk = "";
		if ($this->PoNo->SearchValue <> "" || $this->PoNo->SearchValue2 <> "") {
			$sWrk = "\"sv_PoNo\":\"" . ewr_JsEncode2($this->PoNo->SearchValue) . "\"," .
				"\"so_PoNo\":\"" . ewr_JsEncode2($this->PoNo->SearchOperator) . "\"," .
				"\"sc_PoNo\":\"" . ewr_JsEncode2($this->PoNo->SearchCondition) . "\"," .
				"\"sv2_PoNo\":\"" . ewr_JsEncode2($this->PoNo->SearchValue2) . "\"," .
				"\"so2_PoNo\":\"" . ewr_JsEncode2($this->PoNo->SearchOperator2) . "\"";
		}
		if ($sWrk <> "") {
			if ($sFilterList <> "") $sFilterList .= ",";
			$sFilterList .= $sWrk;
		}

		// Return filter list in json
		if ($sFilterList <> "")
			return "{" . $sFilterList . "}";
		else
			return "null";
	}

	// Restore list of filters
	function RestoreFilterList() {

		// Return if not reset filter
		if (@$_POST["cmd"] <> "resetfilter")
			return FALSE;
		$filter = json_decode(@$_POST["filter"], TRUE);
		return $this->SetupFilterList($filter);
	}

	// Setup list of filters
	function SetupFilterList($filter) {
		if (!is_array($filter))
			return FALSE;

		// Field PoNo
		$bRestoreFilter = FALSE;
		if (array_key_exists("sv_PoNo", $filter) || array_key_exists("so_PoNo", $filter) ||
			array_key_exists("sc_PoNo", $filter) ||
			array_key_exists("sv2_PoNo", $filter) || array_key_exists("so2_PoNo", $filter)) {
			$this->SetSessionFilterValues(@$filter["sv_PoNo"], @$filter["so_PoNo"], @$filter["sc_PoNo"], @$filter["sv2_PoNo"], @$filter["so2_PoNo"], "PoNo");
			$bRestoreFilter = TRUE;
		}
		if (!$bRestoreFilter) { // Clear filter
			$this->SetSessionFilterValues("", "=", "AND", "", "=", "PoNo");
		}
		return TRUE;
	}

	// Return popup filter
	function GetPopupFilter() {
		$sWrk = "";
		if ($this->DrillDown)
			return "";
		return $sWrk;
	}

	// Get sort parameters based on sort links clicked
	function GetSort($options = array()) {
		if ($this->DrillDown)
			return "`ProductCode` ASC, `CostPrice` ASC, `Qty` ASC, `Amount` ASC";
		$bResetSort = @$options["resetsort"] == "1" || @$_GET["cmd"] == "resetsort";
		$orderBy = (@$options["order"] <> "") ? @$options["order"] : @$_GET["order"];
		$orderType = (@$options["ordertype"] <> "") ? @$options["ordertype"] : @$_GET["ordertype"];

		// Check for Ctrl pressed
		$bCtrl = (@$_GET["ctrl"] <> "");

		// Check for a resetsort command
		if ($bResetSort) {
			$this->setOrderBy("");
			$this->setStartGroup(1);
			$this->PoNo->setSort("");
			$this->PRNo->setSort("");
			$this->PoDate->setSort("");
			$this->SupplierName->setSort("");
			$this->ProductCode->setSort("");
			$this->ProductDesc->setSort("");
			$this->CostPrice->setSort("");
			$this->Qty->setSort("");
			$this->Amount->setSort("");

		// Check for an Order parameter
		} elseif ($orderBy <> "") {
			$this->CurrentOrder = $orderBy;
			$this->CurrentOrderType = $orderType;
			$this->UpdateSort($this->PoNo, $bCtrl); // PoNo
			$this->UpdateSort($this->PRNo, $bCtrl); // PRNo
			$this->UpdateSort($this->PoDate, $bCtrl); // PoDate
			$this->UpdateSort($this->SupplierName, $bCtrl); // SupplierName
			$this->UpdateSort($this->ProductCode, $bCtrl); // ProductCode
			$this->UpdateSort($this->ProductDesc, $bCtrl); // ProductDesc
			$this->UpdateSort($this->CostPrice, $bCtrl); // CostPrice
			$this->UpdateSort($this->Qty, $bCtrl); // Qty
			$this->UpdateSort($this->Amount, $bCtrl); // Amount
			$sSortSql = $this->SortSql();
			$this->setOrderBy($sSortSql);
			$this->setStartGroup(1);
		}

		// Set up default sort
		if ($this->getOrderBy() == "") {
			$this->setOrderBy("`ProductCode` ASC, `CostPrice` ASC, `Qty` ASC, `Amount` ASC");
			$this->ProductCode->setSort("ASC");
			$this->CostPrice->setSort("ASC");
			$this->Qty->setSort("ASC");
			$this->Amount->setSort("ASC");
		}
		return $this->getOrderBy();
	}

	// Export email
	function ExportEmail($EmailContent, $options = array()) {
		global $grTmpImages, $ReportLanguage;
		$bGenRequest = @$options["reporttype"] == "email";
		$sFailRespPfx = $bGenRequest ? "" : "<p class=\"text-error\">";
		$sSuccessRespPfx = $bGenRequest ? "" : "<p class=\"text-success\">";
		$sRespPfx = $bGenRequest ? "" : "</p>";
		$sContentType = (@$options["contenttype"] <> "") ? $options["contenttype"] : @$_POST["contenttype"];
		$sSender = (@$options["sender"] <> "") ? $options["sender"] : @$_POST["sender"];
		$sRecipient = (@$options["recipient"] <> "") ? $options["recipient"] : @$_POST["recipient"];
		$sCc = (@$options["cc"] <> "") ? $options["cc"] : @$_POST["cc"];
		$sBcc = (@$options["bcc"] <> "") ? $options["bcc"] : @$_POST["bcc"];

		// Subject
		$sEmailSubject = (@$options["subject"] <> "") ? $options["subject"] : @$_POST["subject"];

		// Message
		$sEmailMessage = (@$options["message"] <> "") ? $options["message"] : @$_POST["message"];

		// Check sender
		if ($sSender == "")
			return $sFailRespPfx . $ReportLanguage->Phrase("EnterSenderEmail") . $sRespPfx;
		if (!ewr_CheckEmail($sSender))
			return $sFailRespPfx . $ReportLanguage->Phrase("EnterProperSenderEmail") . $sRespPfx;

		// Check recipient
		if (!ewr_CheckEmailList($sRecipient, EWR_MAX_EMAIL_RECIPIENT))
			return $sFailRespPfx . $ReportLanguage->Phrase("EnterProperRecipientEmail") . $sRespPfx;

		// Check cc
		if (!ewr_CheckEmailList($sCc, EWR_MAX_EMAIL_RECIPIENT))
			return $sFailRespPfx . $ReportLanguage->Phrase("EnterProperCcEmail") . $sRespPfx;

		// Check bcc
		if (!ewr_CheckEmailList($sBcc, EWR_MAX_EMAIL_RECIPIENT))
			return $sFailRespPfx . $ReportLanguage->Phrase("EnterProperBccEmail") . $sRespPfx;

		// Check email sent count
		$emailcount = $bGenRequest ? 0 : ewr_LoadEmailCount();
		if (intval($emailcount) >= EWR_MAX_EMAIL_SENT_COUNT)
			return $sFailRespPfx . $ReportLanguage->Phrase("ExceedMaxEmailExport") . $sRespPfx;
		if ($sEmailMessage <> "") {
			if (EWR_REMOVE_XSS) $sEmailMessage = ewr_RemoveXSS($sEmailMessage);
			$sEmailMessage .= ($sContentType == "url") ? "\r\n\r\n" : "<br><br>";
		}
		$sAttachmentContent = ewr_AdjustEmailContent($EmailContent);
		$sAppPath = ewr_FullUrl();
		$sAppPath = substr($sAppPath, 0, strrpos($sAppPath, "/")+1);
		if (strpos($sAttachmentContent, "<head>") !== FALSE)
			$sAttachmentContent = str_replace("<head>", "<head><base href=\"" . $sAppPath . "\">", $sAttachmentContent); // Add <base href> statement inside the header
		else
			$sAttachmentContent = "<base href=\"" . $sAppPath . "\">" . $sAttachmentContent; // Add <base href> statement as the first statement

		//$sAttachmentFile = $this->TableVar . "_" . Date("YmdHis") . ".html";
		$sAttachmentFile = $this->TableVar . "_" . Date("YmdHis") . "_" . ewr_Random() . ".html";
		if ($sContentType == "url") {
			ewr_SaveFile(EWR_UPLOAD_DEST_PATH, $sAttachmentFile, $sAttachmentContent);
			$sAttachmentFile = EWR_UPLOAD_DEST_PATH . $sAttachmentFile;
			$sUrl = $sAppPath . $sAttachmentFile;
			$sEmailMessage .= $sUrl; // Send URL only
			$sAttachmentFile = "";
			$sAttachmentContent = "";
		} else {
			$sEmailMessage .= $sAttachmentContent;
			$sAttachmentFile = "";
			$sAttachmentContent = "";
		}

		// Send email
		$Email = new crEmail();
		$Email->Sender = $sSender; // Sender
		$Email->Recipient = $sRecipient; // Recipient
		$Email->Cc = $sCc; // Cc
		$Email->Bcc = $sBcc; // Bcc
		$Email->Subject = $sEmailSubject; // Subject
		$Email->Content = $sEmailMessage; // Content
		if ($sAttachmentFile <> "")
			$Email->AddAttachment($sAttachmentFile, $sAttachmentContent);
		if ($sContentType <> "url") {
			foreach ($grTmpImages as $tmpimage)
				$Email->AddEmbeddedImage($tmpimage);
		}
		$Email->Format = ($sContentType == "url") ? "text" : "html";
		$Email->Charset = EWR_EMAIL_CHARSET;
		$EventArgs = array();
		$bEmailSent = FALSE;
		if ($this->Email_Sending($Email, $EventArgs))
			$bEmailSent = $Email->Send();
		ewr_DeleteTmpImages($EmailContent);

		// Check email sent status
		if ($bEmailSent) {

			// Update email sent count and write log
			ewr_AddEmailLog($sSender, $sRecipient, $sEmailSubject, $sEmailMessage);

			// Sent email success
			return $sSuccessRespPfx . $ReportLanguage->Phrase("SendEmailSuccess") . $sRespPfx; // Set up success message
		} else {

			// Sent email failure
			return $sFailRespPfx . $Email->SendErrDescription . $sRespPfx;
		}
	}

	// Export to HTML
	function ExportHtml($html, $options = array()) {

		//global $gsExportFile;
		//header('Content-Type: text/html' . (EWR_CHARSET <> '' ? ';charset=' . EWR_CHARSET : ''));
		//header('Content-Disposition: attachment; filename=' . $gsExportFile . '.html');

		$folder = @$this->GenOptions["folder"];
		$fileName = @$this->GenOptions["filename"];
		$responseType = @$options["responsetype"];
		$saveToFile = "";

		// Save generate file for print
		if ($folder <> "" && $fileName <> "" && ($responseType == "json" || $responseType == "file" && EWR_REPORT_SAVE_OUTPUT_ON_SERVER)) {
			$baseTag = "<base href=\"" . ewr_BaseUrl() . "\">";
			$html = preg_replace('/<head>/', '<head>' . $baseTag, $html);
			ewr_SaveFile($folder, $fileName, $html);
			$saveToFile = ewr_UploadPathEx(FALSE, $folder) . $fileName;
		}
		if ($saveToFile == "" || $responseType == "file")
			echo $html;
		return $saveToFile;
	}

	// Export to WORD
	function ExportWord($html, $options = array()) {
		global $gsExportFile;
		$folder = @$options["folder"];
		$fileName = @$options["filename"];
		$responseType = @$options["responsetype"];
		$saveToFile = "";
		if ($folder <> "" && $fileName <> "" && ($responseType == "json" || $responseType == "file" && EWR_REPORT_SAVE_OUTPUT_ON_SERVER)) {
		 	ewr_SaveFile(ewr_PathCombine(ewr_AppRoot(), $folder, TRUE), $fileName, $html);
			$saveToFile = ewr_UploadPathEx(FALSE, $folder) . $fileName;
		}
		if ($saveToFile == "" || $responseType == "file") {
			header('Set-Cookie: fileDownload=true; path=/');
			header('Content-Type: application/vnd.ms-word' . (EWR_CHARSET <> '' ? ';charset=' . EWR_CHARSET : ''));
			header('Content-Disposition: attachment; filename=' . $gsExportFile . '.doc');
			echo $html;
		}
		return $saveToFile;
	}

	// Export to EXCEL
	function ExportExcel($html, $options = array()) {
		global $gsExportFile;
		$folder = @$options["folder"];
		$fileName = @$options["filename"];
		$responseType = @$options["responsetype"];
		$saveToFile = "";
		if ($folder <> "" && $fileName <> "" && ($responseType == "json" || $responseType == "file" && EWR_REPORT_SAVE_OUTPUT_ON_SERVER)) {
		 	ewr_SaveFile(ewr_PathCombine(ewr_AppRoot(), $folder, TRUE), $fileName, $html);
			$saveToFile = ewr_UploadPathEx(FALSE, $folder) . $fileName;
		}
		if ($saveToFile == "" || $responseType == "file") {
			header('Set-Cookie: fileDownload=true; path=/');
			header('Content-Type: application/vnd.ms-excel' . (EWR_CHARSET <> '' ? ';charset=' . EWR_CHARSET : ''));
			header('Content-Disposition: attachment; filename=' . $gsExportFile . '.xls');
			echo $html;
		}
		return $saveToFile;
	}

	// Export PDF
	function ExportPdf($html, $options = array()) {
		global $gsExportFile;
		@ini_set("memory_limit", EWR_PDF_MEMORY_LIMIT);
		set_time_limit(EWR_PDF_TIME_LIMIT);
		if (EWR_DEBUG_ENABLED) // Add debug message
			$html = str_replace("</body>", ewr_DebugMsg() . "</body>", $html);
		$dompdf = new \Dompdf\Dompdf(array("pdf_backend" => "Cpdf"));
		$doc = new DOMDocument();
		@$doc->loadHTML('<?xml encoding="uft-8">' . ewr_ConvertToUtf8($html)); // Convert to utf-8
		$spans = $doc->getElementsByTagName("span");
		foreach ($spans as $span) {
			if ($span->getAttribute("class") == "ewFilterCaption")
				$span->parentNode->insertBefore($doc->createElement("span", ":&nbsp;"), $span->nextSibling);
		}
		$images = $doc->getElementsByTagName("img");
		$pageSize = "a4";
		$pageOrientation = "portrait";
		foreach ($images as $image) {
			$imagefn = $image->getAttribute("src");
			if (file_exists($imagefn)) {
				$imagefn = realpath($imagefn);
				$size = getimagesize($imagefn); // Get image size
				if ($size[0] <> 0) {
					if (ewr_SameText($pageSize, "letter")) { // Letter paper (8.5 in. by 11 in.)
						$w = ewr_SameText($pageOrientation, "portrait") ? 216 : 279;
					} elseif (ewr_SameText($pageSize, "legal")) { // Legal paper (8.5 in. by 14 in.)
						$w = ewr_SameText($pageOrientation, "portrait") ? 216 : 356;
					} else {
						$w = ewr_SameText($pageOrientation, "portrait") ? 210 : 297; // A4 paper (210 mm by 297 mm)
					}
					$w = min($size[0], ($w - 20 * 2) / 25.4 * 72); // Resize image, adjust the multiplying factor if necessary
					$h = $w / $size[0] * $size[1];
					$image->setAttribute("width", $w);
					$image->setAttribute("height", $h);
				}
			}
		}
		$html = $doc->saveHTML();
		$html = ewr_ConvertFromUtf8($html);
		$dompdf->load_html($html);
		$dompdf->set_paper($pageSize, $pageOrientation);
		$dompdf->render();
		$folder = @$options["folder"];
		$fileName = @$options["filename"];
		$responseType = @$options["responsetype"];
		$saveToFile = "";
		if ($folder <> "" && $fileName <> "" && ($responseType == "json" || $responseType == "file" && EWR_REPORT_SAVE_OUTPUT_ON_SERVER)) {
			ewr_SaveFile(ewr_PathCombine(ewr_AppRoot(), $folder, TRUE), $fileName, $dompdf->output());
			$saveToFile = ewr_UploadPathEx(FALSE, $folder) . $fileName;
		}
		if ($saveToFile == "" || $responseType == "file") {
			header('Set-Cookie: fileDownload=true; path=/');
			$sExportFile = strtolower(substr($gsExportFile, -4)) == ".pdf" ? $gsExportFile : $gsExportFile . ".pdf";
			$dompdf->stream($sExportFile, array("Attachment" => 1)); // 0 to open in browser, 1 to download
		}
		ewr_DeleteTmpImages($html);
		return $saveToFile;
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php

// Create page object
if (!isset($rptPurchaseOrder_summary)) $rptPurchaseOrder_summary = new crrptPurchaseOrder_summary();
if (isset($Page)) $OldPage = $Page;
$Page = &$rptPurchaseOrder_summary;

// Page init
$Page->Page_Init();

// Page main
$Page->Page_Main();
if (!$grDashboardReport)
	ewr_Header(FALSE);

// Global Page Rendering event (in ewrusrfn*.php)
Page_Rendering();

// Page Rendering event
$Page->Page_Render();
?>
<?php if (!$grDashboardReport) { ?>
<?php include_once "phprptinc/header.php" ?>
<?php } ?>
<?php if ($Page->Export == "" || $Page->Export == "print" || $Page->Export == "email" && @$gsEmailContentType == "url") { ?>
<script type="text/javascript">

// Create page object
var rptPurchaseOrder_summary = new ewr_Page("rptPurchaseOrder_summary");

// Page properties
rptPurchaseOrder_summary.PageID = "summary"; // Page ID
var EWR_PAGE_ID = rptPurchaseOrder_summary.PageID;
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown && !$grDashboardReport) { ?>
<script type="text/javascript">

// Form object
var CurrentForm = frptPurchaseOrdersummary = new ewr_Form("frptPurchaseOrdersummary");

// Validate method
frptPurchaseOrdersummary.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);

	// Call Form Custom Validate event
	if (!this.Form_CustomValidate(fobj))
		return false;
	return true;
}

// Form_CustomValidate method
frptPurchaseOrdersummary.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid.
 	return true;
 }
<?php if (EWR_CLIENT_VALIDATE) { ?>
frptPurchaseOrdersummary.ValidateRequired = true; // Uses JavaScript validation
<?php } else { ?>
frptPurchaseOrdersummary.ValidateRequired = false; // No JavaScript validation
<?php } ?>

// Use Ajax
</script>
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown && !$grDashboardReport) { ?>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<a id="top"></a>
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
<!-- Content Container -->
<div id="ewContainer" class="container-fluid ewContainer">
<?php } ?>
<?php if (@$Page->GenOptions["showfilter"] == "1") { ?>
<?php $Page->ShowFilterList(TRUE) ?>
<?php } ?>
<div class="ewToolbar">
<?php
if (!$Page->DrillDownInPanel) {
	$Page->ExportOptions->Render("body");
	$Page->SearchOptions->Render("body");
	$Page->FilterOptions->Render("body");
	$Page->GenerateOptions->Render("body");
}
?>
</div>
<?php $Page->ShowPageHeader(); ?>
<?php $Page->ShowMessage(); ?>
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
<div class="row">
<?php } ?>
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
<!-- Center Container - Report -->
<div id="ewCenter" class="col-sm-12 ewCenter">
<?php } ?>
<!-- Summary Report begins -->
<?php if ($Page->Export <> "pdf") { ?>
<div id="report_summary">
<?php } ?>
<?php if ($Page->Export == "" && !$Page->DrillDown && !$grDashboardReport) { ?>
<!-- Search form (begin) -->
<form name="frptPurchaseOrdersummary" id="frptPurchaseOrdersummary" class="form-inline ewForm ewExtFilterForm" action="<?php echo ewr_CurrentPage() ?>">
<?php $SearchPanelClass = ($Page->Filter <> "") ? " in" : " in"; ?>
<div id="frptPurchaseOrdersummary_SearchPanel" class="ewSearchPanel collapse<?php echo $SearchPanelClass ?>">
<input type="hidden" name="cmd" value="search">
<div id="r_1" class="ewRow">
<div id="c_PoNo" class="ewCell form-group">
	<label for="sv_PoNo" class="ewSearchCaption ewLabel"><?php echo $Page->PoNo->FldCaption() ?></label>
	<span class="ewSearchOperator"><?php echo $ReportLanguage->Phrase("="); ?><input type="hidden" name="so_PoNo" id="so_PoNo" value="="></span>
	<span class="control-group ewSearchField">
<?php ewr_PrependClass($Page->PoNo->EditAttrs["class"], "form-control"); // PR8 ?>
<input type="text" data-table="rptPurchaseOrder" data-field="x_PoNo" id="sv_PoNo" name="sv_PoNo" size="30" maxlength="50" placeholder="<?php echo $Page->PoNo->PlaceHolder ?>" value="<?php echo ewr_HtmlEncode($Page->PoNo->SearchValue) ?>"<?php echo $Page->PoNo->EditAttributes() ?>>
</span>
</div>
</div>
<div class="ewRow"><input type="submit" name="btnsubmit" id="btnsubmit" class="btn btn-primary" value="<?php echo $ReportLanguage->Phrase("Search") ?>">
<input type="reset" name="btnreset" id="btnreset" class="btn hide" value="<?php echo $ReportLanguage->Phrase("Reset") ?>"></div>
</div>
</form>
<script type="text/javascript">
frptPurchaseOrdersummary.Init();
frptPurchaseOrdersummary.FilterList = <?php echo $Page->GetFilterList() ?>;
</script>
<!-- Search form (end) -->
<?php } ?>
<?php if ($Page->ShowCurrentFilter) { ?>
<?php $Page->ShowFilterList() ?>
<?php } ?>
<?php

// Set the last group to display if not export all
if ($Page->ExportAll && $Page->Export <> "") {
	$Page->StopGrp = $Page->TotalGrps;
} else {
	$Page->StopGrp = $Page->StartGrp + $Page->DisplayGrps - 1;
}

// Stop group <= total number of groups
if (intval($Page->StopGrp) > intval($Page->TotalGrps))
	$Page->StopGrp = $Page->TotalGrps;
$Page->RecCount = 0;
$Page->RecIndex = 0;

// Get first row
if ($Page->TotalGrps > 0) {
	$Page->GetGrpRow(1);
	$Page->GrpCounter[0] = 1;
	$Page->GrpCounter[1] = 1;
	$Page->GrpCounter[2] = 1;
	$Page->GrpCount = 1;
}
$Page->GrpIdx = ewr_InitArray($Page->StopGrp - $Page->StartGrp + 1, -1);
while ($rsgrp && !$rsgrp->EOF && $Page->GrpCount <= $Page->DisplayGrps || $Page->ShowHeader) {

	// Show dummy header for custom template
	// Show header

	if ($Page->ShowHeader) {
?>
<?php if ($Page->GrpCount > 1) { ?>
</tbody>
</table>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php if ($Page->Export == "" && !($Page->DrillDown && $Page->TotalGrps > 0)) { ?>
<div class="box-footer ewGridLowerPanel">
<?php include "rptPurchaseOrdersmrypager.php" ?>
<div class="clearfix"></div>
</div>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<span data-class="tpb<?php echo $Page->GrpCount-1 ?>_rptPurchaseOrder"><?php echo $Page->PageBreakContent ?></span>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
<?php if ($Page->Export == "word" || $Page->Export == "excel") { ?>
<div class="ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } else { ?>
<div class="box ewBox ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } ?>
<?php } ?>
<!-- Report grid (begin) -->
<?php if ($Page->Export <> "pdf") { ?>
<div id="gmp_rptPurchaseOrder" class="<?php if (ewr_IsResponsiveLayout()) { echo "table-responsive "; } ?>ewGridMiddlePanel">
<?php } ?>
<table class="<?php echo $Page->ReportTableClass ?>">
<thead>
	<!-- Table header -->
	<tr class="ewTableHeader">
<?php if ($Page->PoNo->Visible) { ?>
	<?php if ($Page->PoNo->ShowGroupHeaderAsRow) { ?>
	<td data-field="PoNo">&nbsp;</td>
	<?php } else { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="PoNo"><div class="rptPurchaseOrder_PoNo"><span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="PoNo">
<?php if ($Page->SortUrl($Page->PoNo) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_PoNo">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_PoNo" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PoNo) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PoNo->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PoNo->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
	<?php } ?>
<?php } ?>
<?php if ($Page->PRNo->Visible) { ?>
	<?php if ($Page->PRNo->ShowGroupHeaderAsRow) { ?>
	<td data-field="PRNo">&nbsp;</td>
	<?php } else { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="PRNo"><div class="rptPurchaseOrder_PRNo"><span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="PRNo">
<?php if ($Page->SortUrl($Page->PRNo) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_PRNo">
			<span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_PRNo" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PRNo) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PRNo->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PRNo->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
	<?php } ?>
<?php } ?>
<?php if ($Page->PoDate->Visible) { ?>
	<?php if ($Page->PoDate->ShowGroupHeaderAsRow) { ?>
	<td data-field="PoDate">&nbsp;</td>
	<?php } else { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="PoDate"><div class="rptPurchaseOrder_PoDate"><span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="PoDate">
<?php if ($Page->SortUrl($Page->PoDate) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_PoDate">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_PoDate" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PoDate) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PoDate->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PoDate->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
	<?php } ?>
<?php } ?>
<?php if ($Page->SupplierName->Visible) { ?>
	<?php if ($Page->SupplierName->ShowGroupHeaderAsRow) { ?>
	<td data-field="SupplierName">&nbsp;</td>
	<?php } else { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="SupplierName"><div class="rptPurchaseOrder_SupplierName"><span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="SupplierName">
<?php if ($Page->SortUrl($Page->SupplierName) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_SupplierName">
			<span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_SupplierName" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->SupplierName) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->SupplierName->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->SupplierName->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
	<?php } ?>
<?php } ?>
<?php if ($Page->ProductCode->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="ProductCode"><div class="rptPurchaseOrder_ProductCode"><span class="ewTableHeaderCaption"><?php echo $Page->ProductCode->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="ProductCode">
<?php if ($Page->SortUrl($Page->ProductCode) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_ProductCode">
			<span class="ewTableHeaderCaption"><?php echo $Page->ProductCode->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_ProductCode" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->ProductCode) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->ProductCode->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->ProductCode->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->ProductCode->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->ProductDesc->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="ProductDesc"><div class="rptPurchaseOrder_ProductDesc" style="width: 250px;"><span class="ewTableHeaderCaption"><?php echo $Page->ProductDesc->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="ProductDesc">
<?php if ($Page->SortUrl($Page->ProductDesc) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_ProductDesc" style="width: 250px;">
			<span class="ewTableHeaderCaption"><?php echo $Page->ProductDesc->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_ProductDesc" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->ProductDesc) ?>',2);" style="width: 250px;">
			<span class="ewTableHeaderCaption"><?php echo $Page->ProductDesc->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->ProductDesc->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->ProductDesc->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->CostPrice->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="CostPrice"><div class="rptPurchaseOrder_CostPrice"><span class="ewTableHeaderCaption"><?php echo $Page->CostPrice->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="CostPrice">
<?php if ($Page->SortUrl($Page->CostPrice) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_CostPrice">
			<span class="ewTableHeaderCaption"><?php echo $Page->CostPrice->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_CostPrice" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->CostPrice) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->CostPrice->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->CostPrice->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->CostPrice->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Qty->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Qty"><div class="rptPurchaseOrder_Qty"><span class="ewTableHeaderCaption"><?php echo $Page->Qty->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Qty">
<?php if ($Page->SortUrl($Page->Qty) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_Qty">
			<span class="ewTableHeaderCaption"><?php echo $Page->Qty->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_Qty" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Qty) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Qty->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Qty->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Qty->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
<?php if ($Page->Amount->Visible) { ?>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
	<td data-field="Amount"><div class="rptPurchaseOrder_Amount"><span class="ewTableHeaderCaption"><?php echo $Page->Amount->FldCaption() ?></span></div></td>
<?php } else { ?>
	<td data-field="Amount">
<?php if ($Page->SortUrl($Page->Amount) == "") { ?>
		<div class="ewTableHeaderBtn rptPurchaseOrder_Amount">
			<span class="ewTableHeaderCaption"><?php echo $Page->Amount->FldCaption() ?></span>
		</div>
<?php } else { ?>
		<div class="ewTableHeaderBtn ewPointer rptPurchaseOrder_Amount" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->Amount) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->Amount->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->Amount->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->Amount->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</div>
<?php } ?>
	</td>
<?php } ?>
<?php } ?>
	</tr>
</thead>
<tbody>
<?php
		if ($Page->TotalGrps == 0) break; // Show header only
		$Page->ShowHeader = FALSE;
	}

	// Build detail SQL
	$sWhere = ewr_DetailFilterSql($Page->PoNo, $Page->getSqlFirstGroupField(), $Page->PoNo->GroupValue(), $Page->DBID);
	if ($Page->PageFirstGroupFilter <> "") $Page->PageFirstGroupFilter .= " OR ";
	$Page->PageFirstGroupFilter .= $sWhere;
	if ($Page->Filter != "")
		$sWhere = "($Page->Filter) AND ($sWhere)";
	$sSql = ewr_BuildReportSql($Page->getSqlSelect(), $Page->getSqlWhere(), $Page->getSqlGroupBy(), $Page->getSqlHaving(), $Page->getSqlOrderBy(), $sWhere, $Page->Sort);
	$rs = $Page->GetDetailRs($sSql);
	$rsdtlcnt = ($rs) ? $rs->RecordCount() : 0;
	if ($rsdtlcnt > 0)
		$Page->GetRow(1);
	$Page->GrpIdx[$Page->GrpCount] = array(-1);
	$Page->GrpIdx[$Page->GrpCount][] = array(-1);
	$Page->GrpIdx[$Page->GrpCount][][] = array(-1);
	while ($rs && !$rs->EOF) { // Loop detail records
		$Page->RecCount++;
		$Page->RecIndex++;
?>
<?php if ($Page->PoNo->Visible && $Page->ChkLvlBreak(1) && $Page->PoNo->ShowGroupHeaderAsRow) { ?>
<?php

		// Render header row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_TOTAL;
		$Page->RowTotalType = EWR_ROWTOTAL_GROUP;
		$Page->RowTotalSubType = EWR_ROWTOTAL_HEADER;
		$Page->RowGroupLevel = 1;
		$Page->PoNo->Count = $Page->GetSummaryCount(1);
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->PoNo->Visible) { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>><span class="ewGroupToggle icon-collapse"></span></td>
<?php } ?>
		<td data-field="PoNo" colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount - 1) ?>"<?php echo $Page->PoNo->CellAttributes() ?>>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PoNo"><span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span></span>
<?php } else { ?>
	<?php if ($Page->SortUrl($Page->PoNo) == "") { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PoNo">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span>
		</span>
	<?php } else { ?>
		<span class="ewTableHeaderBtn ewPointer ewSummaryCaption rptPurchaseOrder_PoNo" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PoNo) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoNo->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PoNo->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PoNo->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</span>
	<?php } ?>
<?php } ?>
		<?php echo $ReportLanguage->Phrase("SummaryColon") ?>
<span data-class="tpx<?php echo $Page->GrpCount ?>_rptPurchaseOrder_PoNo"<?php echo $Page->PoNo->ViewAttributes() ?>><?php echo $Page->PoNo->GroupViewValue ?></span>
		<span class="ewSummaryCount">(<span class="ewAggregateCaption"><?php echo $ReportLanguage->Phrase("RptCnt") ?></span><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span class="ewAggregateValue"><?php echo ewr_FormatNumber($Page->PoNo->Count,0,-2,-2,-2) ?></span>)</span>
		</td>
	</tr>
<?php } ?>
<?php if ($Page->PRNo->Visible && $Page->ChkLvlBreak(2) && $Page->PRNo->ShowGroupHeaderAsRow) { ?>
<?php

		// Render header row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_TOTAL;
		$Page->RowTotalType = EWR_ROWTOTAL_GROUP;
		$Page->RowTotalSubType = EWR_ROWTOTAL_HEADER;
		$Page->RowGroupLevel = 2;
		$Page->PRNo->Count = $Page->GetSummaryCount(2);
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->PoNo->Visible) { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->PRNo->Visible) { ?>
		<td data-field="PRNo"<?php echo $Page->PRNo->CellAttributes(); ?>><span class="ewGroupToggle icon-collapse"></span></td>
<?php } ?>
		<td data-field="PRNo" colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount - 2) ?>"<?php echo $Page->PRNo->CellAttributes() ?>>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PRNo"><span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span></span>
<?php } else { ?>
	<?php if ($Page->SortUrl($Page->PRNo) == "") { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PRNo">
			<span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span>
		</span>
	<?php } else { ?>
		<span class="ewTableHeaderBtn ewPointer ewSummaryCaption rptPurchaseOrder_PRNo" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PRNo) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PRNo->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PRNo->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PRNo->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</span>
	<?php } ?>
<?php } ?>
		<?php echo $ReportLanguage->Phrase("SummaryColon") ?>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_rptPurchaseOrder_PRNo"<?php echo $Page->PRNo->ViewAttributes() ?>><?php echo $Page->PRNo->GroupViewValue ?></span>
		<span class="ewSummaryCount">(<span class="ewAggregateCaption"><?php echo $ReportLanguage->Phrase("RptCnt") ?></span><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span class="ewAggregateValue"><?php echo ewr_FormatNumber($Page->PRNo->Count,0,-2,-2,-2) ?></span>)</span>
		</td>
	</tr>
<?php } ?>
<?php if ($Page->PoDate->Visible && $Page->ChkLvlBreak(3) && $Page->PoDate->ShowGroupHeaderAsRow) { ?>
<?php

		// Render header row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_TOTAL;
		$Page->RowTotalType = EWR_ROWTOTAL_GROUP;
		$Page->RowTotalSubType = EWR_ROWTOTAL_HEADER;
		$Page->RowGroupLevel = 3;
		$Page->PoDate->Count = $Page->GetSummaryCount(3);
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->PoNo->Visible) { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->PRNo->Visible) { ?>
		<td data-field="PRNo"<?php echo $Page->PRNo->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->PoDate->Visible) { ?>
		<td data-field="PoDate"<?php echo $Page->PoDate->CellAttributes(); ?>><span class="ewGroupToggle icon-collapse"></span></td>
<?php } ?>
		<td data-field="PoDate" colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount - 3) ?>"<?php echo $Page->PoDate->CellAttributes() ?>>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PoDate"><span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span></span>
<?php } else { ?>
	<?php if ($Page->SortUrl($Page->PoDate) == "") { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_PoDate">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span>
		</span>
	<?php } else { ?>
		<span class="ewTableHeaderBtn ewPointer ewSummaryCaption rptPurchaseOrder_PoDate" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->PoDate) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->PoDate->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->PoDate->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->PoDate->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</span>
	<?php } ?>
<?php } ?>
		<?php echo $ReportLanguage->Phrase("SummaryColon") ?>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_<?php echo $Page->GrpCounter[1] ?>_rptPurchaseOrder_PoDate"<?php echo $Page->PoDate->ViewAttributes() ?>>
<?php if ($Page->PoDate->HrefValue <> "" || @$Page->PoDate->LinkAttrs["onclick"] <> "") { ?>
<?php if ($Page->PoDate->GroupViewValue <> "" && $Page->PoDate->GroupViewValue <> "&nbsp;") { ?>
<a<?php echo $Page->PoDate->LinkAttributes() ?>><?php echo $Page->PoDate->GroupViewValue ?></a>
<?php } else { echo "&nbsp;"; } ?>
<?php } else { ?>
<?php if ($Page->PoDate->GroupViewValue <> "" && $Page->PoDate->GroupViewValue <> "&nbsp;") { ?>
<?php echo $Page->PoDate->GroupViewValue ?>
<?php } else { echo "&nbsp;"; } ?>
<?php } ?>
</span>
		<span class="ewSummaryCount">(<span class="ewAggregateCaption"><?php echo $ReportLanguage->Phrase("RptCnt") ?></span><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span class="ewAggregateValue"><?php echo ewr_FormatNumber($Page->PoDate->Count,0,-2,-2,-2) ?></span>)</span>
		</td>
	</tr>
<?php } ?>
<?php if ($Page->SupplierName->Visible && $Page->ChkLvlBreak(4) && $Page->SupplierName->ShowGroupHeaderAsRow) { ?>
<?php

		// Render header row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_TOTAL;
		$Page->RowTotalType = EWR_ROWTOTAL_GROUP;
		$Page->RowTotalSubType = EWR_ROWTOTAL_HEADER;
		$Page->RowGroupLevel = 4;
		$Page->SupplierName->Count = $Page->GetSummaryCount(4);
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->PoNo->Visible) { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->PRNo->Visible) { ?>
		<td data-field="PRNo"<?php echo $Page->PRNo->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->PoDate->Visible) { ?>
		<td data-field="PoDate"<?php echo $Page->PoDate->CellAttributes(); ?>></td>
<?php } ?>
<?php if ($Page->SupplierName->Visible) { ?>
		<td data-field="SupplierName"<?php echo $Page->SupplierName->CellAttributes(); ?>><span class="ewGroupToggle icon-collapse"></span></td>
<?php } ?>
		<td data-field="SupplierName" colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount - 4) ?>"<?php echo $Page->SupplierName->CellAttributes() ?>>
<?php if ($Page->Export <> "" || $Page->DrillDown) { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_SupplierName"><span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span></span>
<?php } else { ?>
	<?php if ($Page->SortUrl($Page->SupplierName) == "") { ?>
		<span class="ewSummaryCaption rptPurchaseOrder_SupplierName">
			<span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span>
		</span>
	<?php } else { ?>
		<span class="ewTableHeaderBtn ewPointer ewSummaryCaption rptPurchaseOrder_SupplierName" onclick="ewr_Sort(event,'<?php echo $Page->SortUrl($Page->SupplierName) ?>',2);">
			<span class="ewTableHeaderCaption"><?php echo $Page->SupplierName->FldCaption() ?></span>
			<span class="ewTableHeaderSort"><?php if ($Page->SupplierName->getSort() == "ASC") { ?><span class="caret ewSortUp"></span><?php } elseif ($Page->SupplierName->getSort() == "DESC") { ?><span class="caret"></span><?php } ?></span>
		</span>
	<?php } ?>
<?php } ?>
		<?php echo $ReportLanguage->Phrase("SummaryColon") ?>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_<?php echo $Page->GrpCounter[1] ?>_<?php echo $Page->GrpCounter[2] ?>_rptPurchaseOrder_SupplierName"<?php echo $Page->SupplierName->ViewAttributes() ?>><?php echo $Page->SupplierName->GroupViewValue ?></span>
		<span class="ewSummaryCount">(<span class="ewAggregateCaption"><?php echo $ReportLanguage->Phrase("RptCnt") ?></span><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span class="ewAggregateValue"><?php echo ewr_FormatNumber($Page->SupplierName->Count,0,-2,-2,-2) ?></span>)</span>
		</td>
	</tr>
<?php } ?>
<?php

		// Render detail row
		$Page->ResetAttrs();
		$Page->RowType = EWR_ROWTYPE_DETAIL;
		$Page->RenderRow();
?>
	<tr<?php echo $Page->RowAttributes(); ?>>
<?php if ($Page->PoNo->Visible) { ?>
	<?php if ($Page->PoNo->ShowGroupHeaderAsRow) { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>>&nbsp;</td>
	<?php } else { ?>
		<td data-field="PoNo"<?php echo $Page->PoNo->CellAttributes(); ?>>
<span data-class="tpx<?php echo $Page->GrpCount ?>_rptPurchaseOrder_PoNo"<?php echo $Page->PoNo->ViewAttributes() ?>><?php echo $Page->PoNo->GroupViewValue ?></span></td>
	<?php } ?>
<?php } ?>
<?php if ($Page->PRNo->Visible) { ?>
	<?php if ($Page->PRNo->ShowGroupHeaderAsRow) { ?>
		<td data-field="PRNo"<?php echo $Page->PRNo->CellAttributes(); ?>>&nbsp;</td>
	<?php } else { ?>
		<td data-field="PRNo"<?php echo $Page->PRNo->CellAttributes(); ?>>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_rptPurchaseOrder_PRNo"<?php echo $Page->PRNo->ViewAttributes() ?>><?php echo $Page->PRNo->GroupViewValue ?></span></td>
	<?php } ?>
<?php } ?>
<?php if ($Page->PoDate->Visible) { ?>
	<?php if ($Page->PoDate->ShowGroupHeaderAsRow) { ?>
		<td data-field="PoDate"<?php echo $Page->PoDate->CellAttributes(); ?>>&nbsp;</td>
	<?php } else { ?>
		<td data-field="PoDate"<?php echo $Page->PoDate->CellAttributes(); ?>>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_<?php echo $Page->GrpCounter[1] ?>_rptPurchaseOrder_PoDate"<?php echo $Page->PoDate->ViewAttributes() ?>>
<?php if ($Page->PoDate->HrefValue <> "" || @$Page->PoDate->LinkAttrs["onclick"] <> "") { ?>
<?php if ($Page->PoDate->GroupViewValue <> "" && $Page->PoDate->GroupViewValue <> "&nbsp;") { ?>
<a<?php echo $Page->PoDate->LinkAttributes() ?>><?php echo $Page->PoDate->GroupViewValue ?></a>
<?php } else { echo "&nbsp;"; } ?>
<?php } else { ?>
<?php if ($Page->PoDate->GroupViewValue <> "" && $Page->PoDate->GroupViewValue <> "&nbsp;") { ?>
<?php echo $Page->PoDate->GroupViewValue ?>
<?php } else { echo "&nbsp;"; } ?>
<?php } ?>
</span></td>
	<?php } ?>
<?php } ?>
<?php if ($Page->SupplierName->Visible) { ?>
	<?php if ($Page->SupplierName->ShowGroupHeaderAsRow) { ?>
		<td data-field="SupplierName"<?php echo $Page->SupplierName->CellAttributes(); ?>>&nbsp;</td>
	<?php } else { ?>
		<td data-field="SupplierName"<?php echo $Page->SupplierName->CellAttributes(); ?>>
<span data-class="tpx<?php echo $Page->GrpCount ?>_<?php echo $Page->GrpCounter[0] ?>_<?php echo $Page->GrpCounter[1] ?>_<?php echo $Page->GrpCounter[2] ?>_rptPurchaseOrder_SupplierName"<?php echo $Page->SupplierName->ViewAttributes() ?>><?php echo $Page->SupplierName->GroupViewValue ?></span></td>
	<?php } ?>
<?php } ?>
<?php if ($Page->ProductCode->Visible) { ?>
		<td data-field="ProductCode"<?php echo $Page->ProductCode->CellAttributes() ?>>
<span<?php echo $Page->ProductCode->ViewAttributes() ?>><?php echo $Page->ProductCode->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->ProductDesc->Visible) { ?>
		<td data-field="ProductDesc"<?php echo $Page->ProductDesc->CellAttributes() ?>>
<span<?php echo $Page->ProductDesc->ViewAttributes() ?>><?php echo $Page->ProductDesc->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->CostPrice->Visible) { ?>
		<td data-field="CostPrice"<?php echo $Page->CostPrice->CellAttributes() ?>>
<span<?php echo $Page->CostPrice->ViewAttributes() ?>><?php echo $Page->CostPrice->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Qty->Visible) { ?>
		<td data-field="Qty"<?php echo $Page->Qty->CellAttributes() ?>>
<span<?php echo $Page->Qty->ViewAttributes() ?>><?php echo $Page->Qty->ListViewValue() ?></span></td>
<?php } ?>
<?php if ($Page->Amount->Visible) { ?>
		<td data-field="Amount"<?php echo $Page->Amount->CellAttributes() ?>>
<span<?php echo $Page->Amount->ViewAttributes() ?>><?php echo $Page->Amount->ListViewValue() ?></span></td>
<?php } ?>
	</tr>
<?php

		// Accumulate page summary
		$Page->AccumulateSummary();

		// Get next record
		$Page->GetRow(2);

		// Show Footers
?>
<?php
	} // End detail records loop
?>
<?php

	// Next group
	$Page->GetGrpRow(2);

	// Show header if page break
	if ($Page->Export <> "")
		$Page->ShowHeader = ($Page->ExportPageBreakCount == 0) ? FALSE : ($Page->GrpCount % $Page->ExportPageBreakCount == 0);

	// Page_Breaking server event
	if ($Page->ShowHeader)
		$Page->Page_Breaking($Page->ShowHeader, $Page->PageBreakContent);
	$Page->GrpCount++;
	$Page->GrpCounter[2] = 1;
	$Page->GrpCounter[1] = 1;
	$Page->GrpCounter[0] = 1;

	// Handle EOF
	if (!$rsgrp || $rsgrp->EOF)
		$Page->ShowHeader = FALSE;
} // End while
?>
<?php if ($Page->TotalGrps > 0) { ?>
</tbody>
<tfoot>
<?php
	$Page->Amount->Count = $Page->GrandCnt[5];
	$Page->Amount->SumValue = $Page->GrandSmry[5]; // Load SUM
	$Page->ResetAttrs();
	$Page->RowType = EWR_ROWTYPE_TOTAL;
	$Page->RowTotalType = EWR_ROWTOTAL_GRAND;
	$Page->RowTotalSubType = EWR_ROWTOTAL_FOOTER;
	$Page->RowAttrs["class"] = "ewRptGrandSummary";
	$Page->RenderRow();
?>
<?php if ($Page->SupplierName->ShowCompactSummaryFooter) { ?>
	<tr<?php echo $Page->RowAttributes() ?>><td colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount) ?>"><?php echo $ReportLanguage->Phrase("RptGrandSummary") ?> (<span class="ewAggregateCaption"><?php echo $ReportLanguage->Phrase("RptCnt") ?></span><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span class="ewAggregateValue"><?php echo ewr_FormatNumber($Page->TotCount,0,-2,-2,-2) ?></span>)</td></tr>
	<tr<?php echo $Page->RowAttributes() ?>>
<?php if ($Page->GrpColumnCount > 0) { ?>
		<td colspan="<?php echo $Page->GrpColumnCount ?>" class="ewRptGrpAggregate">&nbsp;</td>
<?php } ?>
<?php if ($Page->ProductCode->Visible) { ?>
		<td data-field="ProductCode"<?php echo $Page->ProductCode->CellAttributes() ?>></td>
<?php } ?>
<?php if ($Page->ProductDesc->Visible) { ?>
		<td data-field="ProductDesc"<?php echo $Page->ProductDesc->CellAttributes() ?>></td>
<?php } ?>
<?php if ($Page->CostPrice->Visible) { ?>
		<td data-field="CostPrice"<?php echo $Page->CostPrice->CellAttributes() ?>></td>
<?php } ?>
<?php if ($Page->Qty->Visible) { ?>
		<td data-field="Qty"<?php echo $Page->Qty->CellAttributes() ?>></td>
<?php } ?>
<?php if ($Page->Amount->Visible) { ?>
		<td data-field="Amount"<?php echo $Page->Amount->CellAttributes() ?>><?php echo $ReportLanguage->Phrase("RptSum") ?><?php echo $ReportLanguage->Phrase("AggregateEqual") ?><span<?php echo $Page->Amount->ViewAttributes() ?>><?php echo $Page->Amount->SumViewValue ?></span></td>
<?php } ?>
	</tr>
<?php } else { ?>
	<tr<?php echo $Page->RowAttributes() ?>><td colspan="<?php echo ($Page->GrpColumnCount + $Page->DtlColumnCount) ?>"><?php echo $ReportLanguage->Phrase("RptGrandSummary") ?> <span class="ewDirLtr">(<?php echo ewr_FormatNumber($Page->TotCount,0,-2,-2,-2); ?><?php echo $ReportLanguage->Phrase("RptDtlRec") ?>)</span></td></tr>
	<tr<?php echo $Page->RowAttributes() ?>>
<?php if ($Page->GrpColumnCount > 0) { ?>
		<td colspan="<?php echo $Page->GrpColumnCount ?>" class="ewRptGrpAggregate"><?php echo $ReportLanguage->Phrase("RptSum") ?></td>
<?php } ?>
<?php if ($Page->ProductCode->Visible) { ?>
		<td data-field="ProductCode"<?php echo $Page->ProductCode->CellAttributes() ?>>&nbsp;</td>
<?php } ?>
<?php if ($Page->ProductDesc->Visible) { ?>
		<td data-field="ProductDesc"<?php echo $Page->ProductDesc->CellAttributes() ?>>&nbsp;</td>
<?php } ?>
<?php if ($Page->CostPrice->Visible) { ?>
		<td data-field="CostPrice"<?php echo $Page->CostPrice->CellAttributes() ?>>&nbsp;</td>
<?php } ?>
<?php if ($Page->Qty->Visible) { ?>
		<td data-field="Qty"<?php echo $Page->Qty->CellAttributes() ?>>&nbsp;</td>
<?php } ?>
<?php if ($Page->Amount->Visible) { ?>
		<td data-field="Amount"<?php echo $Page->Amount->CellAttributes() ?>>
<span<?php echo $Page->Amount->ViewAttributes() ?>><?php echo $Page->Amount->SumViewValue ?></span></td>
<?php } ?>
	</tr>
<?php } ?>
	</tfoot>
<?php } elseif (!$Page->ShowHeader && FALSE) { // No header displayed ?>
<?php if ($Page->Export <> "pdf") { ?>
<?php if ($Page->Export == "word" || $Page->Export == "excel") { ?>
<div class="ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } else { ?>
<div class="box ewBox ewGrid"<?php echo $Page->ReportTableStyle ?>>
<?php } ?>
<?php } ?>
<!-- Report grid (begin) -->
<?php if ($Page->Export <> "pdf") { ?>
<div id="gmp_rptPurchaseOrder" class="<?php if (ewr_IsResponsiveLayout()) { echo "table-responsive "; } ?>ewGridMiddlePanel">
<?php } ?>
<table class="<?php echo $Page->ReportTableClass ?>">
<?php } ?>
<?php if ($Page->TotalGrps > 0 || FALSE) { // Show footer ?>
</table>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php if ($Page->Export == "" && !($Page->DrillDown && $Page->TotalGrps > 0)) { ?>
<div class="box-footer ewGridLowerPanel">
<?php include "rptPurchaseOrdersmrypager.php" ?>
<div class="clearfix"></div>
</div>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<?php } ?>
<?php if ($Page->Export <> "pdf") { ?>
</div>
<?php } ?>
<!-- Summary Report Ends -->
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
</div>
<!-- /#ewCenter -->
<?php } ?>
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
</div>
<!-- /.row -->
<?php } ?>
<?php if ($Page->Export == "" && !$grDashboardReport) { ?>
</div>
<!-- /.ewContainer -->
<?php } ?>
<?php
$Page->ShowPageFooter();
if (EWR_DEBUG_ENABLED)
	echo ewr_DebugMsg();
?>
<?php

// Close recordsets
if ($rsgrp) $rsgrp->Close();
if ($rs) $rs->Close();
?>
<?php if ($Page->Export == "" && !$Page->DrillDown && !$grDashboardReport) { ?>
<script type="text/javascript">

// Write your table-specific startup script here
// console.log("page loaded");

</script>
<?php } ?>
<?php if (!$grDashboardReport) { ?>
<?php include_once "phprptinc/footer.php" ?>
<?php } ?>
<?php
$Page->Page_Terminate();
if (isset($OldPage)) $Page = $OldPage;
?>
