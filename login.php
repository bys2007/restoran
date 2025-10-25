<?php require_once('Connections/koneksi.php'); ?>
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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['user'])) {
  $loginUsername=$_POST['user'];
  $password=$_POST['pass'];
  $MM_fldUserAuthorization = "id_level";
  $MM_redirectLoginSuccess = "admin/dashboard.php";
  $MM_redirectLoginFailed = "login.php?error=1";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_koneksi, $koneksi);
  
  $LoginRS__query=sprintf("SELECT username, password, id_level FROM `user` WHERE username=%s AND password=%s",
  GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $koneksi) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
    $loginStrGroup  = mysql_result($LoginRS,0,'id_level');
    if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;
    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BYS Restoran</title>
<link rel="stylesheet" href="assets/style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="icon" type="image/png" href="assets/favicon.png" />
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&amp;family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
</head>
<body>
  <div class="top-bar">239G+XCM Bumiayu, Kabupaten Kendal, Jawa Tengah<span> || </span>Indonesia<span> || </span>6°58'48.2"S 110°04'33.8"E</div>
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
          <a href="index.php">Home</a>
          <a class="home" aria-current="page" href="login.php">Login</a>
          <a href="menu.php">Menu</a>
          <a href="kontak.php">Kontak</a>
        </nav>
        <div class="social-icons">
          <a aria-label="Facebook" href="#"><i class="fab fa-facebook-f"></i></a>
          <a aria-label="Tiktok" href="#"><i class="fab fa-tiktok"></i></a>
          <a aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </aside>
      <section class="main-panels">
        <article class="panel">
          <h2>Login</h2>
          <form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>" style="margin-top:18px;">
            <div class="input-group">
              <i class='bx bx-user'></i>
              <input type="text" name="user" id="user" placeholder="Username" required>
            </div>
            <div class="input-group">
              <i class='bx bx-lock-alt'></i>
              <input type="password" name="pass" id="pass" placeholder="Password" required>
            </div>
            <input type="submit" name="Blogin" id="Blogin" value="Login" class="btn">
          </form>
        </article>
      </section>
    </div>
  </div>
  <script src="assets/script.js"></script>
</body>

<!-- Modal Login Gagal -->
<div id="modalLoginError" class="modal-update" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="closeModalLoginError()">&times;</span>
    <div style="color:#e74c3c;font-size:18px;font-weight:600;margin-bottom:12px;">Login Gagal</div>
    <div style="color:#31344b;">Username atau password salah. Silakan coba lagi.</div>
  </div>
</div>

</html>