<!doctype html>
<html lang="en">
 <?php
 include_once __DIR__ . '/../basics/basics.inc';
 include __DIR__ . '/../../tparty/mpdf/mpdf.php';
 if (!empty($_SESSION['default_theme'])) {
  $selected_theme = $_SESSION['default_theme'];
 } else {
  set_default_theme();
  $selected_theme = $_SESSION['default_theme'];
 }

 defined('THEME_DIR') ? null : define('THEME_DIR', HOME_DIR . DS . 'themes' . DS . $selected_theme);
 defined('THEME_URL') ? null : define("THEME_URL", HOME_URL . 'themes/' . $selected_theme);
 $mpdf = new mPDF('c');
 ob_start();
 global $si;
 ?>
 <head>
  <meta charset="utf-8">
  <title>inoERP Document Print</title>
  <meta name="description" content="The HTML5 Herald">
  <meta name="inoerp" content="Print">
    <link href="<?php echo HOME_URL; ?>tparty/bootstrap/css/bootstrap.css" rel="stylesheet">
  <link href="<?php echo HOME_URL; ?>tparty/bootstrap/css/style.css" rel="stylesheet">
  <link href="<?php echo THEME_URL ?>/public.css" media="all" rel="stylesheet" type="text/css" />
  <link href="<?php echo HOME_URL ?>/misc/ecss/pdf_print.css" media="all" rel="stylesheet" type="text/css" />
 </head>
 <body>
  <?php
  if (!empty($_GET['class_name'])) {
   $class = $_GET['class_name'];
  }
  unset($class_names);
  $class_names = [];
  $class_names[] = $class;
  if (property_exists($class, 'dependent_classes')) {
   foreach ($class::$dependent_classes as $key => $dep_class_name) {
    array_push($class_names, $dep_class_name);
   }
  }

  if (class_exists($class)) {
   $$class = new $class;
  } else {
   $continue = false;
   return;
//   pa(get_declared_classes());
//   redirect_to(HOME_URL);
  }

  if (property_exists($class, 'field_a')) {
   if ((empty($$class->field_a))) {
    $$class->field_a = get_dbColumns($class);
   }
   if (!empty($$class->field_a)) {
    foreach ($$class->field_a as $key => $value) {
     $$class->$value = NULL;
    }
   }
  }

  $class_id = property_exists($class, 'primary_column') ? $class::$primary_column : $table_name . '_id';
  if (!empty($_GET[$class_id])) {
   $class_id_value = $_GET[$class_id];
  }
  if (!empty($class_id_value) && (!is_array($class_id_value))) {
   $class_i = $$class->findBy_id($class_id_value);
  }

  if (isset($class_names[1])) {
   $class_second = $class_names[1];
   $$class_second = new $class_second;
   //$$class_second = $$class_names[1];
   $line_obj = $class_second . '_object';
   if (property_exists($class, 'primary_column')) {
    $primar_column_class1 = $class::$primary_column;
    $order_by_field = !empty($class_second::$query_order_by_field) ? $class_second::$query_order_by_field : '';
    $order_by_value = !empty($class_second::$query_order_by_value) ? $class_second::$query_order_by_value : ' ASC ';
    $$line_obj = [];
    array_push($$line_obj, $$class_second);
    if (!empty($$class->$primar_column_class1) && (!empty($class_second::$parent_primary_column))) {
     $$line_obj = $$class_second->findBy_parentId($$class->$primar_column_class1, $order_by_field, $order_by_value);
     if (empty($$line_obj)) {
      $$line_obj = [];
      array_push($$line_obj, $$class_second);
     }
    }
   }
  } else if (strpos($class, '_v') != false) {
   $class_second = $class;
   $$class_second = new $class_second;
   $$line_obj = $class::find_by_ColumnNameVal($class_id, $class_i->$class_id);
  }
  $$class = $class_i;
  if (isset($class_names[2])) {
   $class_third = $class_names[2];
   //$$class_third = $$class_names[2];
  }

  $theme_pdd_tpl_file = THEME_DIR . DS . "pdf_print_templates" . DS . "$class.php";
// echo "1 . theme_pdd_tpl_file is $theme_pdd_tpl_file ";  
  if (file_exists($theme_pdd_tpl_file)) {
   include_once $theme_pdd_tpl_file;
  } else {
   $pdd_tpl_file = __DIR__ . "/../../misc/pdf_print_templates/$class.php";
   if (file_exists($pdd_tpl_file)) {
    include_once $pdd_tpl_file;
   }
  }
  ?>
  <div id="print_footer">
  </div>
  <?php
  $html = ob_get_contents();
  ob_end_clean();
//  echo $html;
// send the captured HTML from the output buffer to the mPDF class for processing
  $mpdf->WriteHTML($html);
  download_send_headers("po_print" . date("Y-m-d") . ".pdf", 'pdf_format');
  $mpdf->Output();
  ?>
 </body>
</html>
<?php exit; ?>