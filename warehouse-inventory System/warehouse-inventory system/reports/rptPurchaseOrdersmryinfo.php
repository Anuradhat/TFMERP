<?php

// Global variable for table object
$rptPurchaseOrder = NULL;

//
// Table class for rptPurchaseOrder
//
class crrptPurchaseOrder extends crTableBase {
	var $ShowGroupHeaderAsRow = FALSE;
	var $ShowCompactSummaryFooter = TRUE;
	var $PoNo;
	var $PRNo;
	var $PoDate;
	var $SupplierCode;
	var $SupplierName;
	var $Remarks;
	var $ProductCode;
	var $ProductDesc;
	var $CostPrice;
	var $SellingPrice;
	var $Qty;
	var $Amount;
	var $StatusFlg;

	//
	// Table class constructor
	//
	function __construct() {
		global $ReportLanguage, $grLanguage;
		$this->TableVar = 'rptPurchaseOrder';
		$this->TableName = 'rptPurchaseOrder';
		$this->TableType = 'REPORT';
		$this->TableReportType = 'summary';
		$this->SourcTableIsCustomView = FALSE;
		$this->DBID = 'DB';
		$this->ExportAll = TRUE;
		$this->ExportPageBreakCount = 0;

		// PoNo
		$this->PoNo = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_PoNo', 'PoNo', '`PoNo`', 200, EWR_DATATYPE_STRING, -1);
		$this->PoNo->Sortable = TRUE; // Allow sort
		$this->PoNo->GroupingFieldId = 1;
		$this->PoNo->ShowGroupHeaderAsRow = $this->ShowGroupHeaderAsRow;
		$this->PoNo->ShowCompactSummaryFooter = $this->ShowCompactSummaryFooter;
		$this->PoNo->DateFilter = "";
		$this->PoNo->SqlSelect = "";
		$this->PoNo->SqlOrderBy = "";
		$this->PoNo->FldGroupByType = "";
		$this->PoNo->FldGroupInt = "0";
		$this->PoNo->FldGroupSql = "";
		$this->fields['PoNo'] = &$this->PoNo;

		// PRNo
		$this->PRNo = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_PRNo', 'PRNo', '`PRNo`', 200, EWR_DATATYPE_STRING, -1);
		$this->PRNo->Sortable = TRUE; // Allow sort
		$this->PRNo->GroupingFieldId = 2;
		$this->PRNo->ShowGroupHeaderAsRow = $this->ShowGroupHeaderAsRow;
		$this->PRNo->ShowCompactSummaryFooter = $this->ShowCompactSummaryFooter;
		$this->PRNo->DateFilter = "";
		$this->PRNo->SqlSelect = "";
		$this->PRNo->SqlOrderBy = "";
		$this->PRNo->FldGroupByType = "";
		$this->PRNo->FldGroupInt = "0";
		$this->PRNo->FldGroupSql = "";
		$this->fields['PRNo'] = &$this->PRNo;

		// PoDate
		$this->PoDate = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_PoDate', 'PoDate', '`PoDate`', 133, EWR_DATATYPE_DATE, 0);
		$this->PoDate->Sortable = TRUE; // Allow sort
		$this->PoDate->GroupingFieldId = 3;
		$this->PoDate->ShowGroupHeaderAsRow = $this->ShowGroupHeaderAsRow;
		$this->PoDate->ShowCompactSummaryFooter = $this->ShowCompactSummaryFooter;
		$this->PoDate->FldDefaultErrMsg = str_replace("%s", $GLOBALS["EWR_DATE_FORMAT"], $ReportLanguage->Phrase("IncorrectDate"));
		$this->PoDate->DateFilter = "";
		$this->PoDate->SqlSelect = "";
		$this->PoDate->SqlOrderBy = "";
		$this->PoDate->FldGroupByType = "";
		$this->PoDate->FldGroupInt = "0";
		$this->PoDate->FldGroupSql = "";
		$this->fields['PoDate'] = &$this->PoDate;

		// SupplierCode
		$this->SupplierCode = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_SupplierCode', 'SupplierCode', '`SupplierCode`', 200, EWR_DATATYPE_STRING, -1);
		$this->SupplierCode->Sortable = TRUE; // Allow sort
		$this->SupplierCode->DateFilter = "";
		$this->SupplierCode->SqlSelect = "";
		$this->SupplierCode->SqlOrderBy = "";
		$this->fields['SupplierCode'] = &$this->SupplierCode;

		// SupplierName
		$this->SupplierName = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_SupplierName', 'SupplierName', '`SupplierName`', 200, EWR_DATATYPE_STRING, -1);
		$this->SupplierName->Sortable = TRUE; // Allow sort
		$this->SupplierName->GroupingFieldId = 4;
		$this->SupplierName->ShowGroupHeaderAsRow = $this->ShowGroupHeaderAsRow;
		$this->SupplierName->ShowCompactSummaryFooter = $this->ShowCompactSummaryFooter;
		$this->SupplierName->DateFilter = "";
		$this->SupplierName->SqlSelect = "";
		$this->SupplierName->SqlOrderBy = "";
		$this->SupplierName->FldGroupByType = "";
		$this->SupplierName->FldGroupInt = "0";
		$this->SupplierName->FldGroupSql = "";
		$this->fields['SupplierName'] = &$this->SupplierName;

		// Remarks
		$this->Remarks = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_Remarks', 'Remarks', '`Remarks`', 200, EWR_DATATYPE_STRING, -1);
		$this->Remarks->Sortable = TRUE; // Allow sort
		$this->Remarks->DateFilter = "";
		$this->Remarks->SqlSelect = "";
		$this->Remarks->SqlOrderBy = "";
		$this->fields['Remarks'] = &$this->Remarks;

		// ProductCode
		$this->ProductCode = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_ProductCode', 'ProductCode', '`ProductCode`', 200, EWR_DATATYPE_STRING, -1);
		$this->ProductCode->Sortable = TRUE; // Allow sort
		$this->ProductCode->DateFilter = "";
		$this->ProductCode->SqlSelect = "";
		$this->ProductCode->SqlOrderBy = "";
		$this->fields['ProductCode'] = &$this->ProductCode;

		// ProductDesc
		$this->ProductDesc = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_ProductDesc', 'ProductDesc', '`ProductDesc`', 200, EWR_DATATYPE_STRING, -1);
		$this->ProductDesc->Sortable = TRUE; // Allow sort
		$this->ProductDesc->DateFilter = "";
		$this->ProductDesc->SqlSelect = "";
		$this->ProductDesc->SqlOrderBy = "";
		$this->fields['ProductDesc'] = &$this->ProductDesc;

		// CostPrice
		$this->CostPrice = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_CostPrice', 'CostPrice', '`CostPrice`', 131, EWR_DATATYPE_NUMBER, -1);
		$this->CostPrice->Sortable = TRUE; // Allow sort
		$this->CostPrice->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectFloat");
		$this->CostPrice->DateFilter = "";
		$this->CostPrice->SqlSelect = "";
		$this->CostPrice->SqlOrderBy = "";
		$this->fields['CostPrice'] = &$this->CostPrice;

		// SellingPrice
		$this->SellingPrice = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_SellingPrice', 'SellingPrice', '`SellingPrice`', 131, EWR_DATATYPE_NUMBER, -1);
		$this->SellingPrice->Sortable = TRUE; // Allow sort
		$this->SellingPrice->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectFloat");
		$this->SellingPrice->DateFilter = "";
		$this->SellingPrice->SqlSelect = "";
		$this->SellingPrice->SqlOrderBy = "";
		$this->fields['SellingPrice'] = &$this->SellingPrice;

		// Qty
		$this->Qty = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_Qty', 'Qty', '`Qty`', 3, EWR_DATATYPE_NUMBER, -1);
		$this->Qty->Sortable = TRUE; // Allow sort
		$this->Qty->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->Qty->DateFilter = "";
		$this->Qty->SqlSelect = "";
		$this->Qty->SqlOrderBy = "";
		$this->fields['Qty'] = &$this->Qty;

		// Amount
		$this->Amount = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_Amount', 'Amount', '`Amount`', 131, EWR_DATATYPE_NUMBER, -1);
		$this->Amount->Sortable = TRUE; // Allow sort
		$this->Amount->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectFloat");
		$this->Amount->DateFilter = "";
		$this->Amount->SqlSelect = "";
		$this->Amount->SqlOrderBy = "";
		$this->fields['Amount'] = &$this->Amount;

		// StatusFlg
		$this->StatusFlg = new crField('rptPurchaseOrder', 'rptPurchaseOrder', 'x_StatusFlg', 'StatusFlg', '`StatusFlg`', 3, EWR_DATATYPE_NUMBER, -1);
		$this->StatusFlg->Sortable = TRUE; // Allow sort
		$this->StatusFlg->FldDefaultErrMsg = $ReportLanguage->Phrase("IncorrectInteger");
		$this->StatusFlg->DateFilter = "";
		$this->StatusFlg->SqlSelect = "";
		$this->StatusFlg->SqlOrderBy = "";
		$this->fields['StatusFlg'] = &$this->StatusFlg;
	}

	// Set Field Visibility
	function SetFieldVisibility($fldparm) {
		global $Security;
		return $this->$fldparm->Visible; // Returns original value
	}

	// Multiple column sort
	function UpdateSort(&$ofld, $ctrl) {
		if ($this->CurrentOrder == $ofld->FldName) {
			$sSortField = $ofld->FldExpression;
			$sLastSort = $ofld->getSort();
			if ($this->CurrentOrderType == "ASC" || $this->CurrentOrderType == "DESC") {
				$sThisSort = $this->CurrentOrderType;
			} else {
				$sThisSort = ($sLastSort == "ASC") ? "DESC" : "ASC";
			}
			$ofld->setSort($sThisSort);
			if ($ofld->GroupingFieldId == 0) {
				if ($ctrl) {
					$sOrderBy = $this->getDetailOrderBy();
					if (strpos($sOrderBy, $sSortField . " " . $sLastSort) !== FALSE) {
						$sOrderBy = str_replace($sSortField . " " . $sLastSort, $sSortField . " " . $sThisSort, $sOrderBy);
					} else {
						if ($sOrderBy <> "") $sOrderBy .= ", ";
						$sOrderBy .= $sSortField . " " . $sThisSort;
					}
					$this->setDetailOrderBy($sOrderBy); // Save to Session
				} else {
					$this->setDetailOrderBy($sSortField . " " . $sThisSort); // Save to Session
				}
			}
		} else {
			if ($ofld->GroupingFieldId == 0 && !$ctrl) $ofld->setSort("");
		}
	}

	// Get Sort SQL
	function SortSql() {
		$sDtlSortSql = $this->getDetailOrderBy(); // Get ORDER BY for detail fields from session
		$argrps = array();
		foreach ($this->fields as $fld) {
			if ($fld->getSort() <> "") {
				$fldsql = $fld->FldExpression;
				if ($fld->GroupingFieldId > 0) {
					if ($fld->FldGroupSql <> "")
						$argrps[$fld->GroupingFieldId] = str_replace("%s", $fldsql, $fld->FldGroupSql) . " " . $fld->getSort();
					else
						$argrps[$fld->GroupingFieldId] = $fldsql . " " . $fld->getSort();
				}
			}
		}
		$sSortSql = "";
		foreach ($argrps as $grp) {
			if ($sSortSql <> "") $sSortSql .= ", ";
			$sSortSql .= $grp;
		}
		if ($sDtlSortSql <> "") {
			if ($sSortSql <> "") $sSortSql .= ", ";
			$sSortSql .= $sDtlSortSql;
		}
		return $sSortSql;
	}

	// Table level SQL
	// From

	var $_SqlFrom = "";

	function getSqlFrom() {
		return ($this->_SqlFrom <> "") ? $this->_SqlFrom : "`vwPurchaseOrder`";
	}

	function SqlFrom() { // For backward compatibility
		return $this->getSqlFrom();
	}

	function setSqlFrom($v) {
		$this->_SqlFrom = $v;
	}

	// Select
	var $_SqlSelect = "";

	function getSqlSelect() {
		return ($this->_SqlSelect <> "") ? $this->_SqlSelect : "SELECT * FROM " . $this->getSqlFrom();
	}

	function SqlSelect() { // For backward compatibility
		return $this->getSqlSelect();
	}

	function setSqlSelect($v) {
		$this->_SqlSelect = $v;
	}

	// Where
	var $_SqlWhere = "";

	function getSqlWhere() {
		$sWhere = ($this->_SqlWhere <> "") ? $this->_SqlWhere : "";
		return $sWhere;
	}

	function SqlWhere() { // For backward compatibility
		return $this->getSqlWhere();
	}

	function setSqlWhere($v) {
		$this->_SqlWhere = $v;
	}

	// Group By
	var $_SqlGroupBy = "";

	function getSqlGroupBy() {
		return ($this->_SqlGroupBy <> "") ? $this->_SqlGroupBy : "";
	}

	function SqlGroupBy() { // For backward compatibility
		return $this->getSqlGroupBy();
	}

	function setSqlGroupBy($v) {
		$this->_SqlGroupBy = $v;
	}

	// Having
	var $_SqlHaving = "";

	function getSqlHaving() {
		return ($this->_SqlHaving <> "") ? $this->_SqlHaving : "";
	}

	function SqlHaving() { // For backward compatibility
		return $this->getSqlHaving();
	}

	function setSqlHaving($v) {
		$this->_SqlHaving = $v;
	}

	// Order By
	var $_SqlOrderBy = "";

	function getSqlOrderBy() {
		return ($this->_SqlOrderBy <> "") ? $this->_SqlOrderBy : "`PoNo` ASC, `PRNo` ASC, `PoDate` ASC, `SupplierName` ASC";
	}

	function SqlOrderBy() { // For backward compatibility
		return $this->getSqlOrderBy();
	}

	function setSqlOrderBy($v) {
		$this->_SqlOrderBy = $v;
	}

	// Table Level Group SQL
	// First Group Field

	var $_SqlFirstGroupField = "";

	function getSqlFirstGroupField() {
		return ($this->_SqlFirstGroupField <> "") ? $this->_SqlFirstGroupField : "`PoNo`";
	}

	function SqlFirstGroupField() { // For backward compatibility
		return $this->getSqlFirstGroupField();
	}

	function setSqlFirstGroupField($v) {
		$this->_SqlFirstGroupField = $v;
	}

	// Select Group
	var $_SqlSelectGroup = "";

	function getSqlSelectGroup() {
		return ($this->_SqlSelectGroup <> "") ? $this->_SqlSelectGroup : "SELECT DISTINCT " . $this->getSqlFirstGroupField() . " FROM " . $this->getSqlFrom();
	}

	function SqlSelectGroup() { // For backward compatibility
		return $this->getSqlSelectGroup();
	}

	function setSqlSelectGroup($v) {
		$this->_SqlSelectGroup = $v;
	}

	// Order By Group
	var $_SqlOrderByGroup = "";

	function getSqlOrderByGroup() {
		return ($this->_SqlOrderByGroup <> "") ? $this->_SqlOrderByGroup : "`PoNo` ASC";
	}

	function SqlOrderByGroup() { // For backward compatibility
		return $this->getSqlOrderByGroup();
	}

	function setSqlOrderByGroup($v) {
		$this->_SqlOrderByGroup = $v;
	}

	// Select Aggregate
	var $_SqlSelectAgg = "";

	function getSqlSelectAgg() {
		return ($this->_SqlSelectAgg <> "") ? $this->_SqlSelectAgg : "SELECT SUM(`Amount`) AS `sum_amount` FROM " . $this->getSqlFrom();
	}

	function SqlSelectAgg() { // For backward compatibility
		return $this->getSqlSelectAgg();
	}

	function setSqlSelectAgg($v) {
		$this->_SqlSelectAgg = $v;
	}

	// Aggregate Prefix
	var $_SqlAggPfx = "";

	function getSqlAggPfx() {
		return ($this->_SqlAggPfx <> "") ? $this->_SqlAggPfx : "";
	}

	function SqlAggPfx() { // For backward compatibility
		return $this->getSqlAggPfx();
	}

	function setSqlAggPfx($v) {
		$this->_SqlAggPfx = $v;
	}

	// Aggregate Suffix
	var $_SqlAggSfx = "";

	function getSqlAggSfx() {
		return ($this->_SqlAggSfx <> "") ? $this->_SqlAggSfx : "";
	}

	function SqlAggSfx() { // For backward compatibility
		return $this->getSqlAggSfx();
	}

	function setSqlAggSfx($v) {
		$this->_SqlAggSfx = $v;
	}

	// Select Count
	var $_SqlSelectCount = "";

	function getSqlSelectCount() {
		return ($this->_SqlSelectCount <> "") ? $this->_SqlSelectCount : "SELECT COUNT(*) FROM " . $this->getSqlFrom();
	}

	function SqlSelectCount() { // For backward compatibility
		return $this->getSqlSelectCount();
	}

	function setSqlSelectCount($v) {
		$this->_SqlSelectCount = $v;
	}

	// Sort URL
	function SortUrl(&$fld) {
		global $grDashboardReport;
		if ($this->Export <> "" || $grDashboardReport ||
			in_array($fld->FldType, array(128, 204, 205))) { // Unsortable data type
				return "";
		} elseif ($fld->Sortable) {

			//$sUrlParm = "order=" . urlencode($fld->FldName) . "&ordertype=" . $fld->ReverseSort();
			$sUrlParm = "order=" . urlencode($fld->FldName) . "&amp;ordertype=" . $fld->ReverseSort();
			return ewr_CurrentPage() . "?" . $sUrlParm;
		} else {
			return "";
		}
	}

	// Setup lookup filters of a field
	function SetupLookupFilters($fld) {
		global $grLanguage;
		switch ($fld->FldVar) {
		}
	}

	// Setup AutoSuggest filters of a field
	function SetupAutoSuggestFilters($fld) {
		global $grLanguage;
		switch ($fld->FldVar) {
		}
	}

	// Table level events
	// Page Selecting event
	function Page_Selecting(&$filter) {

		// Enter your code here
	}

	// Page Breaking event
	function Page_Breaking(&$break, &$content) {

		// Example:
		//$break = FALSE; // Skip page break, or
		//$content = "<div style=\"page-break-after:always;\">&nbsp;</div>"; // Modify page break content

	}

	// Row Rendering event
	function Row_Rendering() {

		// Enter your code here
	}

	// Cell Rendered event
	function Cell_Rendered(&$Field, $CurrentValue, &$ViewValue, &$ViewAttrs, &$CellAttrs, &$HrefValue, &$LinkAttrs) {

		//$ViewValue = "xxx";
		//$ViewAttrs["style"] = "xxx";

	}

	// Row Rendered event
	function Row_Rendered() {

		// To view properties of field class, use:
		//var_dump($this-><FieldName>);

	}

	// User ID Filtering event
	function UserID_Filtering(&$filter) {

		// Enter your code here
	}

	// Load Filters event
	function Page_FilterLoad() {

		// Enter your code here
		// Example: Register/Unregister Custom Extended Filter
		//ewr_RegisterFilter($this-><Field>, 'StartsWithA', 'Starts With A', 'GetStartsWithAFilter'); // With function, or
		//ewr_RegisterFilter($this-><Field>, 'StartsWithA', 'Starts With A'); // No function, use Page_Filtering event
		//ewr_UnregisterFilter($this-><Field>, 'StartsWithA');

	}

	// Page Filter Validated event
	function Page_FilterValidated() {

		// Example:
		//$this->MyField1->SearchValue = "your search criteria"; // Search value

	}

	// Page Filtering event
	function Page_Filtering(&$fld, &$filter, $typ, $opr = "", $val = "", $cond = "", $opr2 = "", $val2 = "") {

		// Note: ALWAYS CHECK THE FILTER TYPE ($typ)! Example:
		//if ($typ == "dropdown" && $fld->FldName == "MyField") // Dropdown filter
		//	$filter = "..."; // Modify the filter
		//if ($typ == "extended" && $fld->FldName == "MyField") // Extended filter
		//	$filter = "..."; // Modify the filter
		//if ($typ == "popup" && $fld->FldName == "MyField") // Popup filter
		//	$filter = "..."; // Modify the filter
		//if ($typ == "custom" && $opr == "..." && $fld->FldName == "MyField") // Custom filter, $opr is the custom filter ID
		//	$filter = "..."; // Modify the filter

	}

	// Email Sending event
	function Email_Sending(&$Email, &$Args) {

		//var_dump($Email); var_dump($Args); exit();
		return TRUE;
	}

	// Lookup Selecting event
	function Lookup_Selecting($fld, &$filter) {

		// Enter your code here
	}
}
?>
