<?php
$servername = "localhost";
$username = "Agung";
$password = "16Agunghp";
$dbname = "sensordb";

$conn = new mysqli("servername", "username","password","dbname");

if($conn ->connect_error) {
 die("Koneksi database gagal: " .$conn->connect_error);
 }

//ambil data
$Level = $_POST['level'];
$temp = $_POST['temp_c'];
$datetime = $_POST['d'];
$Density = $_POST['density'];

$sql = "INSERT INTO Sensor Raspi (Level, temp, datetime, Density) VALUES ('$level', '$temp_c', '$d', '$density')";

if ($conn->query($sql) ==TRUE) {
  echo "Data berhasil masuk";
  }
else {
  echo "Error: " .$sql . "<br> .$conn->error;
  }
  
$conn->close();

?>
