<?php
    # Obtain the JSON payload from an myspeedtest app POSTed via HTTP
    # and insert into database table.

    header("Content-type: application/json");

    $payload = file_get_contents("php://input");
    $data =  @json_decode($payload, true);

	print_r($data);
	// foreach ($data as $item) {
	//	print_r($item['mbps']);
	//	echo "\n";
	//	print_r($item['rtt_ms']);
	//	echo "\n";
	//	print_r($item['date']);
	//	echo "\n";
	// }

	// print_r($data[0]);
	// print_r($data[0]['mbps']);
	// echo "\n";
	// print_r($data[1]);
	// print_r($data[1]['mbps']);
	// echo "\n";
	// exit(0);

	// if (isset($data['error'])) {
	//	print_r($data);
	//	echo $data['error'];
	//	echo "\n";
	// 	exit(1);
	// }

	// echo "Mega bit per second : ";
	// echo $data['mbps'];
	// echo "\n";
	// echo "Date : ";
	// print_r($data['date']);
	// echo "\n";


	// $mbps = $data['mbps'];
	// $rtt_ms = $data['rtt_ms'];
	// $date = $data['date'];
	// $epoch = $data['epoch'];
	// $local_host = $data['local_host'];
	// $dl	= $data['dl'];


	if (isset($data[0]['mbps'])) {
		$mysqli = new mysqli("127.0.0.1", "root", "chang%", "mynut");
		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_errno());
			exit(1);
		}

		$sql = "INSERT INTO `mynut`.`speedtest` (`mbps`, `rtt_ms`, `date`, `epoch`, `local_host`, `dl`) VALUE (?,?,?,?,?,?)";
		if ($stmt = $mysqli->prepare($sql)) {
			// if($stmt->bind_param('ddsdss', $mbps,$rtt_ms,$date,$epoch,$local_host,$dl)) {
		    if($stmt->bind_param('ddsdss', $mbps,$rtt_ms,$date,$epoch,$local_host,$dl)) {
				echo "Bind Parameters Success";
				print "\n";
			}
			else {
				echo "Bind Parameters Failure"; 
				print "\n";
				printf("Error: %s.\n", $stmt->error);
			}
			$mysqli->query("START TRANSACTION");
			// print_r($data);
			foreach ($data as $one) {
				// print_r($one);
				$mbps = $one['mbps'];
				$rtt_ms = $one['rtt_ms'];
				$date = $one['date'];
				$epoch = $one['epoch'];
				$local_host = $one['local_host'];
				$dl = $one['dl'];
				if ($stmt->execute()) {
					echo "Execute Success";
					print "\n";
				}
				else {
					echo "Execute Failure";
					print "\n";
					printf("Error: %s.\n", $stmt->error);
				}
			}
			$stmt->close();
			$mysqli->query("COMMIT");

			// echo "Mega bit per second";
        		// echo $data['mbps'];
        		// echo "\n";
        		// echo "Date : ";
        		// print_r($data['date']);
        		// echo "\n";
		}
		$mysqli->close();
	}
?>
