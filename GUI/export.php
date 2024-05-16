$conn = new mysqli("localhost","Agung","16Agunghp","sensordb");

if($conn->connect_error) {
 die("Koneksi database gagal: " . $conn->connect_error);
 }

$sql = "SELECT * FROM Sensor Raspi";
$result = $conn->query($sql);

$filename = "data.csv";
$fp = fopen('php://output', 'w');

$header = array("Waktu", "Temp", "Level", "Density");
fputcsv($fp,$header);

while($row = $result->fetch_assoc()) {
  fputcsv($fp,$row);
  }

fclose($fp);

$conn->close();
