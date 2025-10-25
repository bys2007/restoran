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

// Insert order
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formTambahOrder")) {
  $insertSQL = sprintf("INSERT INTO `order` (id_order, id_meja, tanggal, id_user, keterangan, status_order) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString(isset($_POST['id_order']) ? $_POST['id_order'] : "", "int"),
                       GetSQLValueString(isset($_POST['id_meja']) ? $_POST['id_meja'] : "", "int"),
                       GetSQLValueString(isset($_POST['tanggal']) ? $_POST['tanggal'] : "", "date"),
                       GetSQLValueString(isset($_POST['id_user']) ? $_POST['id_user'] : "", "int"),
                       GetSQLValueString(isset($_POST['keterangan']) ? $_POST['keterangan'] : "", "text"),
                       GetSQLValueString(isset($_POST['status_order']) ? $_POST['status_order'] : "", "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  header("Location: order.php");
  exit;
}

// Insert detail_order
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "formTambahDetailOrder")) {
  if (!isset($_POST['id_order']) || $_POST['id_order'] === "") {
    header("Location: order.php");
    exit;
  }
  $insertSQL = sprintf("INSERT INTO detail_order (id_detail_order, id_order, id_masakan, keterangan, status_detail_order) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString(isset($_POST['id_detail_order']) ? $_POST['id_detail_order'] : "", "int"),
                       GetSQLValueString($_POST['id_order'], "int"),
                       GetSQLValueString(isset($_POST['id_masakan']) ? $_POST['id_masakan'] : "", "int"),
                       GetSQLValueString(isset($_POST['keterangan']) ? $_POST['keterangan'] : "", "text"),
                       GetSQLValueString(isset($_POST['status_detail_order']) ? $_POST['status_detail_order'] : "", "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  header("Location: order.php");
  exit;
}
// Update order
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdateOrder")) {
  $updateSQL = sprintf("UPDATE `order` SET id_meja=%s, tanggal=%s, id_user=%s, keterangan=%s, status_order=%s WHERE id_order=%s",
                       GetSQLValueString($_POST['id_meja'], "int"),
                       GetSQLValueString($_POST['tanggal'], "date"),
                       GetSQLValueString($_POST['id_user'], "int"),
                       GetSQLValueString($_POST['keterangan'], "text"),
                       GetSQLValueString($_POST['status_order'], "text"),
                       GetSQLValueString($_POST['id_order'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  header("Location: order.php");
  exit;
}

// Update detail_order
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "formUpdateDetailOrder")) {
  $updateSQL = sprintf("UPDATE detail_order SET id_masakan=%s, keterangan=%s, status_detail_order=%s WHERE id_detail_order=%s",
                       GetSQLValueString($_POST['id_masakan'], "int"),
                       GetSQLValueString($_POST['keterangan'], "text"),
                       GetSQLValueString($_POST['status_detail_order'], "text"),
                       GetSQLValueString($_POST['id_detail_order'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  header("Location: order.php");
  exit;
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rorder = "SELECT * FROM `order`";
$Rorder = mysql_query($query_Rorder, $koneksi) or die(mysql_error());
$row_Rorder = mysql_fetch_assoc($Rorder);
$totalRows_Rorder = mysql_num_rows($Rorder);

mysql_select_db($database_koneksi, $koneksi);
$query_Rdetail_order = "SELECT * FROM detail_order";
$Rdetail_order = mysql_query($query_Rdetail_order, $koneksi) or die(mysql_error());
$row_Rdetail_order = mysql_fetch_assoc($Rdetail_order);
$totalRows_Rdetail_order = mysql_num_rows($Rdetail_order);

mysql_select_db($database_koneksi, $koneksi);
$query_Rmeja = "SELECT id_meja, no_meja FROM meja";
$Rmeja = mysql_query($query_Rmeja, $koneksi) or die(mysql_error());
$row_Rmeja = mysql_fetch_assoc($Rmeja);
$totalRows_Rmeja = mysql_num_rows($Rmeja);

mysql_select_db($database_koneksi, $koneksi);
$query_ALLuser = "SELECT id_user, nama_user FROM `user`";
$ALLuser = mysql_query($query_ALLuser, $koneksi) or die(mysql_error());
$row_ALLuser = mysql_fetch_assoc($ALLuser);
$totalRows_ALLuser = mysql_num_rows($ALLuser);

mysql_select_db($database_koneksi, $koneksi);
$query_Rmasakan = "SELECT id_masakan, nama_masakan FROM masakan";
$Rmasakan = mysql_query($query_Rmasakan, $koneksi) or die(mysql_error());
$row_Rmasakan = mysql_fetch_assoc($Rmasakan);
$totalRows_Rmasakan = mysql_num_rows($Rmasakan);
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
  <title>Dashboard Admin | Restoran bys</title>
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
          <a href="referensi.php">Entri Referensi</a>
          <a class="home" aria-current="page" href="order.php">Entri Order</a>
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
        <h2>Data Order</h2>
        <button class="btn" name="tambahOrder" id="tambahOrder" style="background:#27ae60; color:#fff;">
            <i class='bx bx-plus'></i> Tambah Order
          </button>
          <table class="table-user" style="margin-top:18px;">
          <thead>
            <tr>
              <th>ID Order</th>
              <th>ID Meja</th>
              <th>Tanggal</th>
              <th>ID User</th>
              <th>Keterangan</th>
              <th>Status Order</th>
              <th>Aksi</th>
            </tr>
           </thead>
            <?php do { ?>
            <tbody>
              <tr>
                <td><?php echo $row_Rorder['id_order']; ?></td>
                <td><?php echo $row_Rorder['id_meja']; ?></td>
                <td><?php echo $row_Rorder['tanggal']; ?></td>
                <td><?php echo $row_Rorder['id_user']; ?></td>
                <td><?php echo $row_Rorder['keterangan']; ?></td>
                <td><?php echo $row_Rorder['status_order']; ?></td>
                <td>
                  <button type="button" class="btn" style="background:#3498db; color:#fff;"
                    onclick="openModalUpdateOrder('<?php echo $row_Rorder['id_order']; ?>','<?php echo $row_Rorder['id_meja']; ?>','<?php echo $row_Rorder['tanggal']; ?>','<?php echo $row_Rorder['id_user']; ?>','<?php echo htmlspecialchars($row_Rorder['keterangan'], ENT_QUOTES); ?>','<?php echo $row_Rorder['status_order']; ?>')">
                    <i class='bx bx-edit'></i> Update
                  </button>
                  <a href="delete_order.php?id_order=<?php echo $row_Rorder['id_order']; ?>"
                     onclick="return confirm('Yakin ingin menghapus data ini?')"
                     class="btn" style="background:#e74c3c; color:#fff;">
                     <i class='bx bx-trash'></i> Delete
                  </a>
                  <button type="button" class="btn" style="background:#373737; color:#fff;"
                    onclick="openModalDetailOrder('<?php echo $row_Rorder['id_order']; ?>')">
                    <i class="bx bx-file"></i> Detail
                  </button>
                </td>
              </tr>
             </tbody>
              <?php } while ($row_Rorder = mysql_fetch_assoc($Rorder)); ?>
          </table>
        </article>

       <article class="panel" id="panelDetailOrder" style="width:100%;max-width:800px;">
        <div class="modal-detail-order" id="modalDetailOrder">
        <div class="modal-content">
          <span class="close-article" onclick="closeModalDetailOrder()">&times;</span>
          <h2>Detail Order</h2>
          <button class="btn" name="tambahDetailOrder" id="tambahDetailOrder" style="background:#27ae60; color:#fff;">
            <i class='bx bx-plus'></i> Tambah Detail Order
          </button>
        <table class="table-user" style="margin-top:18px;">
          <thead>
          <tr>
            <th>ID Detail Order</th>
            <th>ID Masakan</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
          </thead>
          <tbody id="tbody-detail-order"></tbody>
        </table>
        </div>
      </div>
      </article>
      </section>

      <!-- Modal Tambah Order -->
      <div id="modalTambahOrder" class="modal-update" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" onclick="closeModalTambahOrder()">&times;</span>
          <form method="post" action="<?php echo $editFormAction; ?>" class="form-register" id="formTambahOrder">
            <div class="title" style="margin-bottom:18px;">Tambah Order</div>
            <div class="input-group">
              <i class='bx bx-table'></i>
              <select name="id_meja" id="id_meja_tambah" required>
                <option value="" disabled selected>Pilih Meja</option>
                <?php if ($totalRows_Rmeja > 0) { ?>
                  <?php do { ?>
                    <option value="<?php echo $row_Rmeja['id_meja']; ?>"><?php echo htmlspecialchars($row_Rmeja['no_meja'], ENT_QUOTES); ?></option>
                  <?php } while ($row_Rmeja = mysql_fetch_assoc($Rmeja)); ?>
                  <?php mysql_data_seek($Rmeja, 0); ?>
                  <?php $row_Rmeja = mysql_fetch_assoc($Rmeja); ?>
                <?php } ?>
              </select>
            </div>
            <div class="input-group">
              <i class='bx bx-calendar'></i>
              <input type="date" name="tanggal" id="tanggal_tambah" value="<?php echo date('Y-m-d'); ?>" placeholder="Tanggal" required />
            </div>
            <div class="input-group">
              <i class='bx bx-user'></i>
              <select name="id_user" id="id_user_tambah" required>
                <option value="" disabled selected>Pilih User</option>
                <?php if ($totalRows_ALLuser > 0) { ?>
                  <?php do { ?>
                    <option value="<?php echo $row_ALLuser['id_user']; ?>"><?php echo htmlspecialchars($row_ALLuser['nama_user'], ENT_QUOTES); ?></option>
                  <?php } while ($row_ALLuser = mysql_fetch_assoc($ALLuser)); ?>
                  <?php mysql_data_seek($ALLuser, 0); ?>
                  <?php $row_ALLuser = mysql_fetch_assoc($ALLuser); ?>
                <?php } ?>
              </select>
            </div>
            <div class="input-group">
              <i class='bx bx-note'></i>
              <input type="text" name="keterangan" id="keterangan_tambah" placeholder="Keterangan" />
            </div>
            <div class="input-group">
              <i class='bx bx-info-circle'></i>
              <select name="status_order" id="status_order_tambah" required>
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <input type="hidden" name="id_order" id="id_order_tambah" value="" />
            <input type="hidden" name="MM_insert" value="formTambahOrder" />
            <button type="submit" class="btn">Simpan</button>
          </form>
        </div>
      </div>

        <!-- Modal Update Order -->
      <div id="modalUpdateOrder" class="modal-update" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" onclick="closeModalUpdateOrder()">&times;</span>
          <form method="post" action="<?php echo $editFormAction; ?>" class="form-register">
            <div class="title" style="margin-bottom:18px;">Update Order</div>
            <input type="hidden" name="id_order" id="id_order_update" />
            <div class="input-group">
              <i class='bx bx-table'></i>
              <input type="text" name="id_meja" id="id_meja_update" placeholder="ID Meja" required />
            </div>
            <div class="input-group">
              <i class='bx bx-calendar'></i>
              <input type="text" name="tanggal" id="tanggal_update" placeholder="Tanggal" required />
            </div>
            <div class="input-group">
              <i class='bx bx-user'></i>
              <input type="text" name="id_user" id="id_user_update" placeholder="ID User" required />
            </div>
            <div class="input-group">
              <i class='bx bx-note'></i>
              <input type="text" name="keterangan" id="keterangan_update" placeholder="Keterangan" />
            </div>
            <div class="input-group">
              <i class='bx bx-info-circle'></i>
              <select name="status_order" id="status_order_update" required>
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <input type="hidden" name="MM_update" value="formUpdateOrder" />
            <button type="submit" class="btn">Update</button>
          </form>
        </div>
      </div>

      <!-- Modal Tambah Detail Order -->
      <div id="modalTambahDetailOrder" class="modal-update" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" onclick="closeModalTambahDetailOrder()">&times;</span>
          <form method="post" action="<?php echo $editFormAction; ?>" class="form-register" id="formTambahDetailOrder">
            <div class="title" style="margin-bottom:18px;">Tambah Detail Order</div>
            <div class="input-group">
              <i class='bx bx-receipt'></i>
              <input type="text" id="id_order_detail_display" placeholder="ID Order" readonly />
            </div>
            <input type="hidden" name="id_order" id="id_order_detail_tambah" />
            <div class="input-group">
              <i class='bx bx-dish'></i>
              <select name="id_masakan" id="id_masakan_tambah" required>
                <option value="" disabled selected>Pilih Masakan</option>
                <?php if ($totalRows_Rmasakan > 0) { ?>
                  <?php do { ?>
                    <option value="<?php echo $row_Rmasakan['id_masakan']; ?>"><?php echo htmlspecialchars($row_Rmasakan['nama_masakan'], ENT_QUOTES); ?></option>
                  <?php } while ($row_Rmasakan = mysql_fetch_assoc($Rmasakan)); ?>
                  <?php mysql_data_seek($Rmasakan, 0); ?>
                  <?php $row_Rmasakan = mysql_fetch_assoc($Rmasakan); ?>
                <?php } ?>
              </select>
            </div>
            <div class="input-group">
              <i class='bx bx-note'></i>
              <input type="text" name="keterangan" id="keterangan_detail_tambah" placeholder="Keterangan" />
            </div>
            <div class="input-group">
              <i class='bx bx-info-circle'></i>
              <select name="status_detail_order" id="status_detail_order_tambah" required>
                <option value="dimasak">Dimasak</option>
                <option value="dihidangkan">Dihidangkan</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <input type="hidden" name="id_detail_order" id="id_detail_order_tambah" value="" />
            <input type="hidden" name="MM_insert" value="formTambahDetailOrder" />
            <button type="submit" class="btn">Simpan</button>
          </form>
        </div>
      </div>

      <!-- Modal Update Detail Order -->
      <div id="modalUpdateDetailOrder" class="modal-update" style="display:none;">
        <div class="modal-content">
          <span class="close-modal" onclick="closeModalUpdateDetailOrder()">&times;</span>
          <form method="post" action="<?php echo $editFormAction; ?>" class="form-register">
            <div class="title" style="margin-bottom:18px;">Update Detail Order</div>
            <input type="hidden" name="id_detail_order" id="id_detail_order_update" />
            <div class="input-group">
              <i class='bx bx-dish'></i>
              <input type="text" name="id_masakan" id="id_masakan_update" placeholder="ID Masakan" required />
            </div>
            <div class="input-group">
              <i class='bx bx-note'></i>
              <input type="text" name="keterangan" id="keterangan_detail_update" placeholder="Keterangan" />
            </div>
            <div class="input-group">
              <i class='bx bx-info-circle'></i>
              <select name="status_detail_order" id="status_detail_order_update" required>
                <option value="dimasak">Dimasak</option>
                <option value="dihidangkan">Dihidangkan</option>
                <option value="selesai">Selesai</option>
              </select>
            </div>
            <input type="hidden" name="MM_update" value="formUpdateDetailOrder" />
            <button type="submit" class="btn">Update</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/script.js"></script>
  <script>
var dataDetailOrder = <?php
  // Ambil semua detail_order jadi array JS
  $allDetailOrder = [];
  mysql_data_seek($Rdetail_order, 0);
  while ($row = mysql_fetch_assoc($Rdetail_order)) {
    $allDetailOrder[] = $row;
  }
  echo json_encode($allDetailOrder);
?>;
</script>
</body>
</html>
<?php
mysql_free_result($Rorder);

mysql_free_result($Rdetail_order);

mysql_free_result($Rmeja);

mysql_free_result($ALLuser);

mysql_free_result($Rmasakan);

mysql_free_result($Ruser);
?>
