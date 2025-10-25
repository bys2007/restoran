<?php require_once('../Connections/koneksi.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
  function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
    if (PHP_VERSION < 6) {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
    }

    $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

    switch ($theType) {
      case "text":
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;
      case "long":
      case "int":
        $theValue = ($theValue != "") ? intval($theValue) : "NULL";
        break;
      case "double":
        $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
        break;
      case "date":
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;
      case "defined":
        $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
        break;
    }
    return $theValue;
  }
}

$redirectStatus = "notfound";

if ((isset($_GET['id_transaksi'])) && ($_GET['id_transaksi'] !== "")) {
  $deleteSQL = sprintf("DELETE FROM transaksi WHERE id_transaksi=%s",
                       GetSQLValueString($_GET['id_transaksi'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result = mysql_query($deleteSQL, $koneksi) or die(mysql_error());

  if (mysql_affected_rows($koneksi) > 0) {
    $redirectStatus = "deleted";
  }
}

header("Location: transaksi.php?status=" . $redirectStatus);
exit;
?>
