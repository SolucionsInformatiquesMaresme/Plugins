#!/usr/bin/php
<?php
#$dbhost = '89.0.0.54\BKUPEXEC';
#$dbuname = 'nagios';
#$dbpass = 'nagiosxi';
#$dbname = 'BEDB';

$job = "";

foreach ($argv as $arg) {
	if (strpos($arg, "H=") !== false) { $dbhost = str_replace("H=","",$arg); }
	if (strpos($arg, "u=") !== false) { $dbuname = str_replace("u=","",$arg); }
	if (strpos($arg, "p=") !== false) { $dbpass = str_replace("p=","",$arg); }
	if (strpos($arg, "d=") !== false) { $dbname = str_replace("d=","",$arg); }
	if (strpos($arg, "j=") !== false) { $job = str_replace("j=","",$arg); }
	if (strpos($arg, "w=") !== false) { $warning = str_replace("w=","",$arg); }
	if (strpos($arg, "c=") !== false) { $critical = str_replace("c=","",$arg); }
}

$dbhandle = mssql_connect($dbhost, $dbuname, $dbpass) or die("Couldn't connect to SQL Server on $dbhost");
$db = mssql_select_db($dbname, $dbhandle) or die("Couldn't open database $myDB");

$count_jobs = 0;
$count_success = 0;
$count_error = 0;
$count_jobs_active = 0;

$lista_errors = "";
$lista_active = "";
$lista_success = "";

$sql_extension = "";
if ($job !== "") { $sql_extension = "and JobName ='".$job."'"; }

        $sql = "select * from jobs where TaskTypeID=200 ".$sql_extension." order by JobName";
        $result = mssql_query($sql);
        while ($row = mssql_fetch_array($result)){
				#$error = 1; servei per saber si s'ha fet o no correctament.
				$error = 1;
                $sqlhistoric = "SELECT TOP 1 JobID,JobName,FinalJobStatus,TotalDataSizeBytes,TotalRateMBMin,TotalNumberOfFiles,TotalNumberOfDirectories,TotalSkippedFiles,TotalCorruptFiles,TotalInUseFiles,ActualStartTime,EndTime FROM JobHistorySummary WHERE JobName = '".$row["JobName"]."' ORDER BY EndTime DESC";
                $resulthistoric = mssql_query($sqlhistoric);
                $rowhistoric = mssql_fetch_array($resulthistoric);
#                echo $row["JobName"].",".$rowhistoric["FinalJobStatus"]."\n";
                $count_jobs++;
                if ($rowhistoric["FinalJobStatus"] == 19) { $count_success++; $error = 0; $lista_success .= $row["JobName"]." (no errors)\n";}
                if ($rowhistoric["FinalJobStatus"] == "") { $count_success++; $error = 0; $lista_success .= $row["JobName"]." (scheduled)\n";}
                if ($rowhistoric["FinalJobStatus"] == 3) { $count_success++; $error = 0; $lista_success .= $row["JobName"]." (exceptions)\n";}
                if ($rowhistoric["FinalJobStatus"] == 16) { $count_jobs_active++; $error = 0; $lista_active .= $row["JobName"]."\n";}
				if ($error == 1)  { $count_error++; $lista_errors .= $row["JobName"]."\n";}
        }
#$count_error = $count_jobs - $count_success - $count_jobs_active;

$estado = "OK";
$salida = 0;
if ($count_error >= $warning ) { $estado = "WARNING"; $salida=1; }
if ($count_error >= $critical ) { $estado = "CRITICAL"; $salida=2; }
echo $estado." - Copias=".$count_jobs."\n***Activas=".$count_jobs_active."\n".$lista_active."\n***Correctas=".$count_success."\n".$lista_success."\n***Fallidas=".$count_error."\n".$lista_errors." | Copias=".$count_jobs." Activas=".$count_jobs_active." Correctas=".$count_success." Fallidas=".$count_error;

exit($salida);

?>

