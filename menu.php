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
mysql_select_db($database_koneksi, $koneksi);
$query_Rmenu = "SELECT * FROM masakan";
$Rmenu = mysql_query($query_Rmenu, $koneksi) or die(mysql_error());
$row_Rmenu = mysql_fetch_assoc($Rmenu);
$totalRows_Rmenu = mysql_num_rows($Rmenu);
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
          <a href="login.php">Login</a>
          <a class="home" aria-current="page" href="menu.php">Menu</a>
          <a href="kontak.php">Kontak</a>
        </nav>
        <div class="social-icons">
          <a aria-label="Facebook" href="#"><i class="fab fa-facebook-f"></i></a>
          <a aria-label="Tiktok" href="#"><i class="fab fa-tiktok"></i></a>
          <a aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </aside>
      <section class="main-panels">
        <article class="panel" style="width:100%;max-width:800px;">
          <h2>Daftar Menu</h2>
          <table class="table-user" style="margin-top:18px; width:100%;">
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nama Masakan</th>
                <th>Harga</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php do { ?>
              <tr>
                <td><img src="foto_masakan/<?php echo $row_Rmenu['foto_masakan']; ?>" alt="<?php echo $row_Rmenu['nama_masakan']; ?>" style="width:100px;height:100px;object-fit:cover;border-radius:5px;"/></td>
                <td><?php echo $row_Rmenu['nama_masakan']; ?></td>
                <td>Rp<?php echo number_format($row_Rmenu['harga'], 0, ',', '.'); ?></td>
                <td><?php echo $row_Rmenu['status_masakan']; ?></td>
              </tr>
            <?php } while ($row_Rmenu = mysql_fetch_assoc($Rmenu)); ?>
            </tbody>
          </table>
        </article>
      </section>
    </div>
  </div>
</body>
</html>
<?php
mysql_free_result($Rmenu);
?>
