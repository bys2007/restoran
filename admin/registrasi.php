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
$MM_authorizedUsers = "1";
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

$MM_restrictGoTo = "../waiter/dashboard.php";
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
<?php require_once('../Connections/koneksi.php'); ?>
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

// Proses update user
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdateUser")) {
  $updateSQL = sprintf("UPDATE `user` SET username=%s, password=%s, nama_user=%s, id_level=%s WHERE id_user=%s",
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['nama_user'], "text"),
                       GetSQLValueString($_POST['id_level'], "int"),
                       GetSQLValueString($_POST['id_user'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "registrasi.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rregistrasi = "SELECT * FROM `user`";
$Rregistrasi = mysql_query($query_Rregistrasi, $koneksi) or die(mysql_error());
$row_Rregistrasi = mysql_fetch_assoc($Rregistrasi);
$totalRows_Rregistrasi = mysql_num_rows($Rregistrasi);

$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM `user` WHERE username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);

// Proses delete user
if ((isset($_GET['id_user'])) && ($_GET['id_user'] != "")) {
  $deleteSQL = sprintf("DELETE FROM `user` WHERE id_user=%s",
                       GetSQLValueString($_GET['id_user'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($deleteSQL, $koneksi) or die(mysql_error());

  header("Location: registrasi.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Registrasi User | Restoran bys</title>
  <link rel="icon" type="image/png" href="../assets/favicon.png" />
  <link rel="stylesheet" href="../assets/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@600&amp;family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet"/>
</head>
<body>
  <div class="top-bar">
      Selamat datang admin, <span><?php echo $row_Ruser['username']; ?></span>
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
          <a href="referensi.php">Entri Referensi</a>
          <a href="order.php">Entri Order</a>
          <a href="transaksi.php">Entri Transaksi</a>
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
          <h2>Registrasi User</h2>
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-register" style="margin-top:18px;">
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
            <div class="input-group">
              <i class='bx bx-user-check'></i>
              <select name="id_level" style="cursor: pointer;" required>
                <option value="">-- Pilih Level --</option>
                <option value="1">Admin</option>
                <option value="2">Waiter</option>
                <option value="3">Kasir</option>
                <option value="4">Owner</option>
                <option value="5">Pelanggan</option>
              </select>
            </div>
            <input type="hidden" name="id_user" value="" />
            <input type="hidden" name="MM_insert" value="form1" />
            <button type="submit" class="btn">Submit</button>
          </form>
        </article>
        <article class="panel" style="width:100%;max-width:800px;">
          <h2>Data User</h2>
          <table class="table-user" style="margin-top:18px;">
            <thead>
              <tr>
                <th>ID User</th>
                <th>Username</th>
                <th>Password</th>
                <th>Nama User</th>
                <th>Level</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php do { ?>
                <tr>
                  <td><?php echo $row_Rregistrasi['id_user']; ?></td>
                  <td><?php echo $row_Rregistrasi['username']; ?></td>
                  <td><?php echo $row_Rregistrasi['password']; ?></td>
                  <td><?php echo $row_Rregistrasi['nama_user']; ?></td>
                  <td>
                    <?php
                      switch ($row_Rregistrasi['id_level']) {
                        case 1: echo 'Admin'; break;
                        case 2: echo 'Waiter'; break;
                        case 3: echo 'Kasir'; break;
                        case 4: echo 'Owner'; break;
                        case 5: echo 'Pelanggan'; break;
                        default: echo $row_Rregistrasi['id_level'];
                      }
                    ?>
                  </td>
                  <td>
                    <button type="button" class="btn" style="background:#3498db; color:#fff;"
                      onclick="openModalUpdateUser('<?php echo $row_Rregistrasi['id_user']; ?>','<?php echo htmlspecialchars($row_Rregistrasi['username'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($row_Rregistrasi['password'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($row_Rregistrasi['nama_user'], ENT_QUOTES); ?>','<?php echo $row_Rregistrasi['id_level']; ?>')">
                      <i class='bx bx-edit'></i> Update
                    </button>
                    <a href="registrasi.php?id_user=<?php echo $row_Rregistrasi['id_user']; ?>"
                       onclick="return confirm('Yakin ingin menghapus data ini?')"
                       class="btn" style="background:#e74c3c; color:#fff;">
                       <i class='bx bx-trash'></i> Delete
                    </a>
                  </td>
                </tr>
              <?php } while ($row_Rregistrasi = mysql_fetch_assoc($Rregistrasi)); ?>
            </tbody>
          </table>
        </article>
      </section>
    </div>
  </div>
  <script src="../assets/script.js"></script>
</body>
</body>

<!-- Modal Update User -->
<div id="modalUpdateUser" class="modal-update" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="closeModalUpdateUser()">&times;</span>
    <form action="<?php echo $editFormAction; ?>" method="post" id="formUpdateUser" class="form-register">
      <div class="title" style="margin-bottom:18px;">Update Data User</div>
      <div class="input-group">
        <i class='bx bx-user'></i>
        <input type="text" name="username" id="username_update" placeholder="Username" required />
      </div>
      <div class="input-group">
        <i class='bx bx-lock-alt'></i>
        <input type="password" name="password" id="password_update" placeholder="Password" required />
      </div>
      <div class="input-group">
        <i class='bx bx-id-card'></i>
        <input type="text" name="nama_user" id="nama_user_update" placeholder="Nama User" required />
      </div>
      <div class="input-group">
        <i class='bx bx-user-check'></i>
        <select name="id_level" id="id_level_update" style="cursor: pointer;" required>
          <option value="">-- Pilih Level --</option>
          <option value="1">Admin</option>
          <option value="2">Waiter</option>
          <option value="3">Kasir</option>
          <option value="4">Owner</option>
          <option value="5">Pelanggan</option>
        </select>
      </div>
      <input type="hidden" name="id_user" id="id_user_update" />
      <input type="hidden" name="MM_update" value="formUpdateUser">
      <button type="submit" class="btn">Update</button>
    </form>
  </div>
</div>

</html>
<?php
mysql_free_result($Rregistrasi);

mysql_free_result($Ruser);?>
