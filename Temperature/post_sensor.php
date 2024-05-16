

<?php

$servername = "localhost";

// REPLACE with your Database name
$dbname = "sensordb";
// REPLACE with Database user
$username = "Agung";
// REPLACE with Database user password
$password = "16Agunghp";


// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


$sensor = 0;
$lvl_out = 0;
$lvl_act = 0;
$status = '';
$idpost  = '';
$datetime = '';
$formatText = '';
$minSetPoint = '';
$maxSetPoint = '';
$pesan_update_wm = '';
$nameWl = '';

//prevent empty data
if (!isset($_POST['d']) || !isset($_POST['idpost'])) {
    $response = [
        'pesan' => 'Datetime or ID POST is empty!!!!',
    ];
} else {

    // if (!isset($_POST['lvl_act'])) {
    //     $lvl_in = water_level($_POST["lvl_in"]);
    //     $lvl_out = water_level($_POST["lvl_out"]);
    // } else if (isset($_POST['lvl_act'])) {
    //     $lvl_act = water_level($_POST["lvl_act"]);
    // }
    
    date_default_timezone_set('Asia/Jakarta');

    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('Asia/Jakarta'));
    
    $date_now =  $now->format('Y-m-d H:i:s');

    $idpost  = water_level($_POST["idpost"]);
    $submittedDatetime = water_level($_POST["d"]);
    
    $objDatetime = new DateTime($submittedDatetime, new DateTimeZone('Asia/Jakarta'));
    
    $oneYearAgo = $now->modify('-1 year');
    
    if ($objDatetime < $oneYearAgo) {
        $datetime =  $date_now;
    } else {
        $datetime = water_level($_POST["d"]);
    }

    $lvl_in = water_level($_POST["lvl_in"]);
    $lvl_out = isset($_POST["lvl_out"]) ? water_level($_POST["lvl_out"]) : 0;
    $lvl_act = isset($_POST["lvl_act"]) ? water_level($_POST["lvl_act"]) : 0;

    // echo $idpost;
    // echo $datetime;
    // echo $lvl_in;

    $queryWl  = "SELECT * FROM water_level_list  JOIN water_level ON water_level.idpost=water_level_list.id WHERE water_level_list.id=$idpost";
    $resultQuery = mysqli_query($conn, $queryWl);
    $wldatalist = mysqli_fetch_array($resultQuery);

    $sql = "INSERT INTO water_level (datetime, lvl_in, lvl_out, lvl_act,  idpost)
        VALUES ('" . $datetime . "', '" . $lvl_in . "', '" . $lvl_out . "', '" . $lvl_act . "', '" . $idpost . "')";

    // $lvl_inBeforeEnd = '';
    $sqlDataBeforeEnd       = "SELECT lvl_in FROM water_level where idpost=$idpost  ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $sqlDataBeforeEnd);
    $result = mysqli_fetch_array($result);

    $lvl_inBeforeEnd = (int)$result['lvl_in'];

    $maxSetPoint =  (int)$wldatalist['max'];
    $minSetPoint =  (int)$wldatalist['min'];
    $idGroup =  $wldatalist['id_group']  ?? 0;
    $tokenGroup =  $wldatalist['token'] ?? '5078711925:AAGDIb7GhxRHJRULHiWVX8xoiNCV-VlnX4Q';

    // date_default_timezone_set('Asia/Jakarta');

    // // Current time
    // $now = new DateTime();
    // $now->setTimezone(new DateTimeZone('Asia/Jakarta'));
    
    // $date_now =  $now->format('Y-m-d H:i:s');

    if ($conn->query($sql) === TRUE) {
        // Send Notifcation Firebase
        $sqlCheckData = "SELECT * FROM water_level_idwm WHERE id = '" . $idpost . "'";
        $resultCheckData = mysqli_query($conn, $sqlCheckData);
        if ($resultCheckData) {
            if (mysqli_num_rows($resultCheckData) > 0) {
                conditionOfNotification($conn, $lvl_in, $result['lvl_in'], $idpost, $nameWl);
            }
        }
        // End Send Notifcation Firebase
    
        // Update Last Online IDWM
        $sqlLast = "SELECT * FROM water_level_idwm WHERE id = '" . $idpost . "' LIMIT 1";
        $resultLast = mysqli_query($conn, $sqlLast);
        if ($resultLast) {
            if (mysqli_num_rows($resultLast) > 0) {
                $dataWm = mysqli_fetch_assoc($resultLast);
                $nameWl = $dataWm['est'] . " " . $dataWm['afd'] . " " . $dataWm['blok'] . " ";
                
                $rawDateWm = $dataWm['last_online'];
                $datePost = new DateTime($datetime);
                $dateWm = new DateTime($rawDateWm);
                
                if ($rawDateWm === null || $datePost > $dateWm) {
                    $queryUpdate = "UPDATE water_level_idwm SET level_in = '" . $lvl_in . "', level_out = '" . $lvl_out . "', last_online = '" . $datetime . "' WHERE id = '" . $idpost . "'";
                    $result_update = mysqli_query($conn, $queryUpdate);
                    if ($result_update) {
                        $pesan_update_wm = "New record updated successfully";
                    } else {
                        $pesan_update_wm = 'Gagal update data! e: ' . mysqli_error($conn);
                    }
                } else {
                    $pesan_update_wm = "Data water level sudah terbaru!";
                }
            }
        }
        // End Update Last Online IDWM
        
        $response = [
            'pesan' => "New record created successfully". ",". $date_now. "," ,
            'datetime' =>  $date_now,
            'pesan_update' => $pesan_update_wm
        ];
        
        $date =  $now->format('Y-m-d H:i:s');
        // Update timestamp column in grading_machine table
        $queryUpdate = "UPDATE water_level_list SET last_online = '$date' WHERE id = '$idpost'";
        
        $result_update = mysqli_query($conn, $queryUpdate);
        
        // if ($result_update) {
        //     echo 'Sukses update water_lavel last_online dengan id: ' . $idpost;
        // } else {
        //     echo 'Gagal melakukan update. Error: ' . mysqli_error($conn);
        // }

        if ($lvl_in > $maxSetPoint && $lvl_inBeforeEnd < $maxSetPoint) {
            $formatText             = "Level air di " . $wldatalist['location'] . " telah melebihi batas siaga banjir, level air saat ini " . $lvl_in . " cm.";
        } elseif ($lvl_in < $minSetPoint && $lvl_inBeforeEnd > $minSetPoint) {
            $formatText             = "Level air di " . $wldatalist['location'] . " kurang dari batas surut, level air saat ini " . $lvl_in . " cm.";
        } elseif (($lvl_in < $maxSetPoint && $lvl_in > $minSetPoint) && ($lvl_inBeforeEnd > $maxSetPoint || $lvl_inBeforeEnd < $minSetPoint)) {
            $formatText  = "Level air di " . $wldatalist['location'] . " stabil untuk saat ini dengan ketinggian " . $lvl_in . " cm.";
        }

        if (!empty($formatText)) {

            # ~ Setup Token Telegram Bot


            $defaultToken = '5078711925:AAGDIb7GhxRHJRULHiWVX8xoiNCV-VlnX4Q';
            $token = $tokenGroup ?? $defaultToken;
            $token = $tokenGroup;
            

            $text                   = urlencode($formatText);

            // $grup_id = -775814598;
            // $grup_id = -698002401;
            $grup_id = $idGroup;
            // echo $grup_id;
            // echo $token;
            //$user_id = 1350364584;

            $request_params = [
                'chat_id' => $grup_id,
                'text' => $formatText,
            ];

            $request_url = "https://api.telegram.org/bot" . $token . '/sendMessage?' . http_build_query($request_params);

            file_get_contents($request_url);
        }
    } else {
        $response = [
            'pesan' =>  "Error: " . $sql . "<br>" . $conn->error,
            'pesan_update' => $pesan_update_wm
        ];
    }
    
    $conn->close();
}

echo json_encode($response);

function water_level($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function sendFCMNotificationLegacy($topic, $title, $body, $setNotif) {
    $serverKey = "AAAAhAbClMI:APA91bHhLIW1rW2ubRh5tf0koGisZI9GqhgdQFCT1Jm-9NHSY7BO03yVNcja0am1Ld3Uyhd-UF2d1Rv7M45pBFEmh8Oenis0WDsKNeT2jWWx_j9rEzzQl4lmNE6yiOrKvBKbUI39mZ8C";
    $url = "https://fcm.googleapis.com/fcm/send";

    $data = [
        "to" => "/topics/$topic",
        "notification" => [
            "title" => $title,
            "body" => $body,
        ],
        "data" => [
            "message_id" => uniqid(),
            "set_notif" => $setNotif
        ]
    ];

    $headers = [
        "Authorization: key=$serverKey",
        "Content-Type: application/json",
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_exec($ch);
    curl_close($ch);
}

function conditionOfNotification($connection, $lvlInPost, $lvlInLast, $idpost, $nameOfWl) {
    $topic = "News";
    $title = "SSMS Water Management";
    
    $dataOneHours = filterDataHours($connection, $idpost, 'PT1H');
    $avgOneHours = rawAverageHours($dataOneHours);
    $finalAvgOneHours = finalAverageHours($avgOneHours);
    
    // Kondisi pertama: naik atau turun 5cm setiap 10 menit
    $setDataLvlIn1 = (float)$lvlInPost - (float)$lvlInLast;
    if ($setDataLvlIn1 > 5) {
        $setNotif = $idpost . "_WLN01";
        $body = "Ketinggian air dalam " . $nameOfWl . "mengalami kenaikan lebih dari 5cm!";
        sendFCMNotificationLegacy($topic, $title, $body, $setNotif);
    } else if ($setDataLvlIn1 < -5) {
        $setNotif = $idpost . "_WLN01";
        $body = "Ketinggian air dalam " . $nameOfWl . "mengalami penurunan lebih dari 5cm!";
        sendFCMNotificationLegacy($topic, $title, $body, $setNotif);
    }
    
    // Kondisi kedua: naik atau turun 10 cm dalam 1 jam dari data sekarang
    $setDataLvlIn2 = (float)$lvlInPost - (float)$finalAvgOneHours['lvl_in'];
    if ($setDataLvlIn2 > 10) {
        $setNotif = $idpost . "_WLN02";
        $body = "Ketinggian air dalam " . $nameOfWl . "mengalami kenaikan lebih dari 10cm dalam 1 jam terakhir!";
        sendFCMNotificationLegacy($topic, $title, $body, $setNotif);
    } else if ($setDataLvlIn2 < -10) {
        $setNotif = $idpost . "_WLN02";
        $body = "Ketinggian air dalam " . $nameOfWl . "mengalami penurunan lebih dari 10cm dalam 1 jam terakhir!";
        sendFCMNotificationLegacy($topic, $title, $body, $setNotif);
    }
}

function filterDataHours($connection, $idpost, $interval) {
    $filteredData = [];
    $lastDate = "";
    $dataQr = mysqli_query($connection, "SELECT * FROM water_level WHERE idpost = '$idpost' ORDER BY datetime DESC LIMIT 1");
    while ($row = $dataQr->fetch_assoc()) {
        $lastDate = new DateTime($row['datetime']);
    }
    
    if (empty($lastDate)) {
        $dateNow = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
        $lastDate = new DateTime($dateNow);
    }
    
    $hoursAgo = $lastDate->sub(new DateInterval($interval));
    $hoursAgoFormatted = $hoursAgo->format('Y-m-d H:i:s');
    
    $filteredDataQr = mysqli_query($connection, "SELECT * FROM water_level WHERE datetime >= '$hoursAgoFormatted' AND idpost = '$idpost'");
    while ($filteredRow = $filteredDataQr->fetch_assoc()) {
        $filteredData[] = $filteredRow;
    }
    
    return $filteredData;
}

function rawAverageHours($listData) {
    $hourlyAverages = [];
    if (!empty($listData)) {
        foreach ($listData as $entry) {
            $hour = date('H', strtotime($entry['datetime']));
            if (!isset($hourlyAverages[$hour]['lvl_in'])) {
                $hourlyAverages[$hour]['lvl_in'] = $entry['lvl_in'];
                $hourlyAverages[$hour]['lvl_in_count'] = 1;
            } else {
                $hourlyAverages[$hour]['lvl_in'] += $entry['lvl_in'];
                $hourlyAverages[$hour]['lvl_in_count']++;
            }
        
            if (!isset($hourlyAverages[$hour]['lvl_out'])) {
                $hourlyAverages[$hour]['lvl_out'] = $entry['lvl_out'];
                $hourlyAverages[$hour]['lvl_out_count'] = 1;
            } else {
                $hourlyAverages[$hour]['lvl_out'] += $entry['lvl_out'];
                $hourlyAverages[$hour]['lvl_out_count']++;
            }
        }
        
        foreach ($hourlyAverages as &$hourlyAverage) {
            $hourlyAverage['lvl_in'] /= $hourlyAverage['lvl_in_count'];
            $hourlyAverage['lvl_out'] /= $hourlyAverage['lvl_out_count'];
        }
    }
    
    return $hourlyAverages;
}

function finalAverageHours($listData) {
    $fixCount = 1;
    $fixHourAve = [];
    
    if (count($listData) < $fixCount) {
        $currentHour = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('H');
        
        $result = $fixCount - count($listData);
        $keys = array_keys($listData);
        $firstKey = !empty(reset($keys)) ? reset($keys) : $currentHour;
        $dateAdd = DateTime::createFromFormat('H', $firstKey);
        $hoursBefore = [];
        for ($i = 0; $i < $result; $i++) {
            $dateAdd->sub(new DateInterval('PT1H'));
            $hoursBefore[] = $dateAdd->format('H');
        }
        $hoursBefore = array_reverse($hoursBefore);
        
        foreach ($hoursBefore as $time) {
            $fixHourAve[$time . ':00']['lvl_in'] = "0";
            $fixHourAve[$time . ':00']['lvl_out'] = "0";
        }
    }
    
    foreach ($listData as $key => $value) {
        $fixHourAve[$key . ':00']['lvl_in'] = round($value['lvl_in'], 2);
        $fixHourAve[$key . ':00']['lvl_out'] = round($value['lvl_out'], 2);
    }
    
    $arrFixFinal = ['lvl_in' => 0, 'lvl_out' => 0];
    if (!empty($fixHourAve)) {
        $sum_lvl_in = 0;
        $sum_lvl_out = 0;
        
        foreach ($fixHourAve as $item) {
            $sum_lvl_in += $item['lvl_in'];
            $sum_lvl_out += $item['lvl_out'];
        }
        
        $count = count($fixHourAve) != 0 ? count($fixHourAve) : 1;
        $arrFixFinal['lvl_in'] = $sum_lvl_in / $count;
        $arrFixFinal['lvl_out'] = $sum_lvl_out / $count;
    }
    
    return $arrFixFinal;
}

post-wl-data (1).php
Menampilkan post-wl-data (1).php.