<?php
require_once('includes/load.php');

function getSubCategoryFromCategory($category)
{
    global $db;

    return find_by_sql("call spSelectSubCategoryFromCategory('{$category}');");
}


if (isset($_POST['_category'])) {
    $SelectedSubCtegory = getSubCategoryFromCategory($_POST['_category']);

    $value = "<option>Select Sub Category</option>";
    foreach ($SelectedSubCtegory as $scat):
        $value = $value."<option value=".$scat['SubcategoryCode'].">".$scat['SubcategoryDesc']."</option>";
    endforeach;

    echo $value;
}

function EqualValue()


?>