<?php require_once('../Connections/koneksi.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "../login.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "2";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../kasir/dashboard.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO `user` (id_user, username, password, nama_user, id_level) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['id_user'], "int"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['nama_user'], "text"),
                       GetSQLValueString($_POST['id_level'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "registrasi.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM `user` WHERE username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);

mysql_select_db($database_koneksi, $koneksi);
$query_Wuser = "SELECT * FROM `user` WHERE id_level = 5";
$Wuser = mysql_query($query_Wuser, $koneksi) or die(mysql_error());
$row_Wuser = mysql_fetch_assoc($Wuser);
$totalRows_Wuser = mysql_num_rows($Wuser);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Dashboard Admin | Restoran bys</title>
  <link rel="icon" type="image/png" href="../assets/favicon.png" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&amp;family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
</head>
<body>
  <div class="top-bar">
      Selamat datang waiter, <?php echo $row_Ruser['username']; ?><span></span>
  </div>
  <div class="page-wrapper">
    <div aria-hidden="true" class="background-blur">
      <img alt="Blurred background image" src="https://storage.googleapis.com/a1aa/image/53f88344-6f93-4104-a77e-b097980e4a86.jpg" width="1920" height="1080"/>
    </div>
    <div class="content-container" role="main">
      <aside class="sidebar">
        <div class="logo">
          BYS<small>RESTORAN</small>
        </div>
        <nav>
          <a href="dashboard.php">Dashboard</a>
          <a class="home" aria-current="page" href="registrasi.php">Registrasi</a>
          <a href="order.php">Entri Order</a>
          <a href="laporan.php">Generate Laporan</a>
          <a href="<?php echo $logoutAction ?>">Logout</a>
        </nav>
        <div class="social-icons">
          <a aria-label="Facebook" href="#"><i class="fab fa-facebook-f"></i></a>
          <a aria-label="Tiktok" href="#"><i class="fab fa-tiktok"></i></a>
          <a aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </aside>
      <section class="main-panels">
        <article class="panel" style="width:100%;max-width:800px;">
          <form method="post" name="form1" action="<?php echo $editFormAction; ?>" class="form-register" style="margin-top:18px;">
            <h2>Form Registrasi User</h2>
            <div class="input-group">
              <i class='bx bx-user'></i>
              <input type="text" name="username" placeholder="Username" required />
            </div>
            <div class="input-group">
              <i class='bx bx-lock-alt'></i>
              <input type="password" name="password" placeholder="Password" required />
            </div>
            <div class="input-group">
              <i class='bx bx-id-card'></i>
              <input type="text" name="nama_user" placeholder="Nama User" required />
            </div>
            <input type="hidden" name="id_user" value="">
            <input type="hidden" name="id_level" value="5">
            <input type="hidden" name="MM_insert" value="form1">
            <button type="submit" class="btn">Submit</button>
          </form>
        </article>
        <article class="panel" style="width:100%;max-width:800px;">
          <h2>Data User</h2>
          <table class="table-user" style="margin-top:18px;">
            <thead>
              <tr>
                <th>id_user</th>
                <th>username</th>
                <th>password</th>
                <th>nama_user</th>
                <th>id_level</th>
              </tr>
            </thead>
            <tbody>
            <?php do { ?>
              <tr>
                <td><?php echo $row_Wuser['id_user']; ?></td>
                <td><?php echo $row_Wuser['username']; ?></td>
                <td><?php echo $row_Wuser['password']; ?></td>
                <td><?php echo $row_Wuser['nama_user']; ?></td>
                <td><?php echo $row_Wuser['id_level']; ?></td>
              </tr>
              <?php } while ($row_Wuser = mysql_fetch_assoc($Wuser)); ?>
            </tbody>
          </table>
        </article>
      </section>
    </div>
  </div>
  <script src="../assets/script.js"></script>
</body>
</html>
<?php
mysql_free_result($Ruser);

mysql_free_result($Wuser);
?>
