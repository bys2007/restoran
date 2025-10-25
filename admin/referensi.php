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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdateMasakan")) {
  $id_masakan = $_POST['id_masakan'];
  // Ambil nama file lama dari database
  $query_foto = mysql_query("SELECT foto_masakan FROM masakan WHERE id_masakan=" . intval($id_masakan));
  $row_foto = mysql_fetch_assoc($query_foto);
  $nama_file_lama = $row_foto['foto_masakan'];
  $nama_file_update = $nama_file_lama; // Default: pakai nama lama

  // Jika ada file baru diupload
  if (isset($_FILES['foto_update']) && $_FILES['foto_update']['error'] == 0 && $_FILES['foto_update']['name'] != "") {
    // Hapus file lama jika ada dan file-nya ada di folder
    $path_lama = '../foto_masakan/' . $nama_file_lama;
    if ($nama_file_lama && file_exists($path_lama)) {
      unlink($path_lama);
    }
    // Upload file baru
    $nama_file_update = time() . "_" . basename($_FILES['foto_update']['name']);
    move_uploaded_file($_FILES['foto_update']['tmp_name'], '../foto_masakan/' . $nama_file_update);
  }

  $updateSQL = sprintf("UPDATE masakan SET nama_masakan=%s, harga=%s, status_masakan=%s, foto_masakan=%s WHERE id_masakan=%s",
                       GetSQLValueString($_POST['nama_masakan'], "text"),
                       GetSQLValueString($_POST['harga'], "int"),
                       GetSQLValueString($_POST['status_masakan'], "text"),
                       GetSQLValueString($nama_file_update, "text"),
                       GetSQLValueString($id_masakan, "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  $updateGoTo = "referensi.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $nama_file = '';
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $nama_file = time() . "_" . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../foto_masakan/' . $nama_file);
  }
  $insertSQL = sprintf("INSERT INTO masakan (id_masakan, nama_masakan, harga, status_masakan, foto_masakan) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['id_masakan'], "int"),
                       GetSQLValueString($_POST['nama_masakan'], "text"),
                       GetSQLValueString($_POST['harga'], "int"),
                       GetSQLValueString($_POST['status_masakan'], "text"),
                       GetSQLValueString($nama_file, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());
}

if ((isset($_GET['id_masakan'])) && ($_GET['id_masakan'] != "")) {
  $deleteSQL = sprintf("DELETE FROM masakan WHERE id_masakan=%s",
                       GetSQLValueString($_GET['id_masakan'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($deleteSQL, $koneksi) or die(mysql_error());

  // Redirect tanpa query string
  header("Location: referensi.php");
  exit;
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rreferensi = "SELECT * FROM masakan";
$Rreferensi = mysql_query($query_Rreferensi, $koneksi) or die(mysql_error());
$row_Rreferensi = mysql_fetch_assoc($Rreferensi);
$totalRows_Rreferensi = mysql_num_rows($Rreferensi);

$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM `user` WHERE username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <title>Referensi Menu | Restoran bys</title>
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
          <a href="registrasi.php">Registrasi</a>
          <a class="home" aria-current="page" href="referensi.php">Entri Referensi</a>
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
        <!-- Panel Form Tambah Data Masakan -->
        <article class="panel" style="width:100%;max-width:800px; margin:24px auto;">
          <h2>Tambah Data Masakan</h2>
          <form method="post" name="form1" action="<?php echo $editFormAction; ?>" class="form-register" style="margin-top:18px;" enctype="multipart/form-data">
            <div class="input-group">
              <i class='bx bx-food-menu'></i>
              <input type="text" name="nama_masakan" placeholder="Nama Masakan" required>
            </div>
            <div class="input-group">
              <i class='bx bx-money'></i>
              <input type="text" name="harga" placeholder="Harga" required>
            </div>
            <div class="input-group">
              <i class='bx bx-check-circle'></i>
              <select name="status_masakan" style="cursor: pointer;" required>
                <option value="tersedia">Tersedia</option>
                <option value="habis">Habis</option>
              </select>
            </div>
            <div class="input-group" style="position:relative;">
              <i class="bx bx-upload"></i>
              <span>Pilih Foto</span>
              <input type="file" name="foto" id="foto" accept="image/*" 
                style="opacity:0;position:absolute;left:0;top:0;width:100%;height:100%;cursor:pointer;z-index:2;"
                onChange="document.getElementById('file-name').textContent = this.files[0]?.name || '';">
              <span id="file-name" class="text-gray-500 text-sm" style="margin-left:8px;"></span>
            </div>
            <input type="hidden" name="id_masakan" value="">
            <input type="hidden" name="MM_insert" value="form1">
            <button type="submit" class="btn" style="width:100%;margin-top:10px;">Submit</button>
          </form>
        </article>
        <article class="panel" style="width:100%;max-width:800px;">
          <h2>Daftar Referensi Menu</h2>
          <table class="table-user" style="margin-top:18px;">
            <thead>
              <tr>
                <th>ID Masakan</th>
                <th>Nama Masakan</th>
                <th>Harga</th>
                <th>Status Masakan</th>
                <th>Foto</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php do { ?>
              <tr>
                <td><?php echo $row_Rreferensi['id_masakan']; ?></td>
                <td><?php echo $row_Rreferensi['nama_masakan']; ?></td>
                <td>Rp<?php echo number_format($row_Rreferensi['harga'], 0, ',', '.'); ?></td>
                <td><?php echo $row_Rreferensi['status_masakan']; ?></td>
                <td>
                  <?php if ($row_Rreferensi['foto_masakan']) { ?>
                    <img src="../foto_masakan/<?php echo $row_Rreferensi['foto_masakan']; ?>" alt="<?php echo $row_Rreferensi['nama_masakan']; ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;"/>
                  <?php } else { ?>
                    <span style="color:#888;">No Image</span>
                  <?php } ?>
                </td>
                <td>
                  <button type="button" class="btn" style="background:#3498db; color:#fff;"
                    onclick="openModalUpdate('<?php echo $row_Rreferensi['id_masakan']; ?>','<?php echo htmlspecialchars($row_Rreferensi['nama_masakan'], ENT_QUOTES); ?>','<?php echo $row_Rreferensi['harga']; ?>','<?php echo $row_Rreferensi['status_masakan']; ?>')">
                    <i class='bx bx-edit'></i> Update
                  </button>
                  <a href="referensi.php?id_masakan=<?php echo $row_Rreferensi['id_masakan']; ?>"
                     onclick="return confirm('Yakin ingin menghapus data ini?')"
                     class="btn" style="background:#e74c3c; color:#fff;">
                     <i class='bx bx-trash'></i> Delete
                  </a>
                </td>
              </tr>
            <?php } while ($row_Rreferensi = mysql_fetch_assoc($Rreferensi)); ?>
            </tbody>
          </table>
        </article>
      </section>
    </div>
    <!-- Modal Update -->
    <div id="modalUpdate" class="modal-update" style="display:none;">
      <div class="modal-content">
        <span class="close-modal" onclick="closeModalUpdate()">&times;</span>
        <form action="<?php echo $editFormAction; ?>" method="post" id="formUpdateMasakan" class="form-register" enctype="multipart/form-data">
          <div class="title" style="margin-bottom:18px;">Update Data Masakan</div>
          <div class="input-group">
            <i class='bx bx-food-menu'></i>
            <input type="text" name="nama_masakan" id="nama_masakan" placeholder="Nama Masakan" required />
          </div>
          <div class="input-group">
            <i class='bx bx-money'></i>
            <input type="text" name="harga" id="harga" placeholder="Harga" required />
          </div>
          <div class="input-group">
            <i class='bx bx-check-circle'></i>
            <select name="status_masakan" id="status_masakan" style="cursor: pointer;" required>
              <option value="tersedia">Tersedia</option>
              <option value="habis">Habis</option>
            </select>
          </div>
          <div class="input-group" style="position:relative;">
            <i class="bx bx-upload"></i>
            <span>Ganti Foto</span>
            <input type="file" name="foto_update" id="foto_update" accept="image/*" class="hidden"
            style="opacity:0;position:absolute;left:0;top:0;width:100%;height:100%;cursor:pointer;z-index:2;"
            onChange="document.getElementById('file-name-update').textContent = this.files[0]?.name || '';">
            <span id="file-name-update" class="text-gray-500 text-sm"></span>
          </div>
          <input type="hidden" name="id_masakan" id="id_masakan" />
          <input type="hidden" name="MM_update" value="formUpdateMasakan">
          <button type="submit" class="btn">Update</button>
        </form>
      </div>
    </div>
    <script src="../assets/script.js"></script>
  </div>
</body>
</html>
<?php
mysql_free_result($Rreferensi);

mysql_free_result($Ruser);
?>