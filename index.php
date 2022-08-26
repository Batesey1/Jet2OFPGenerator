<?php
//INITIALISE URL AND FORMAT RESULTS IN JSON
$simbriefURL = "https://www.simbrief.com/api/xml.fetcher.php?username=" . $_GET['simbriefUsername'];
$rawSimbrief = simplexml_load_file($simbriefURL);
$encodeSimbrief = json_encode($rawSimbrief);
$decodeSimbrief = json_decode($encodeSimbrief, true);

//SET VARIABLES
$flight_number = "EXS" . $decodeSimbrief['general']['flight_number'];
$reg = $decodeSimbrief['aircraft']['reg'];
$release = $decodeSimbrief['general']['release'];
$dof = strtoupper(gmdate("j/M/y", $decodeSimbrief['api_params']['date']));
$callsign = $decodeSimbrief['atc']['callsign'];
$ete = gmdate('H:i',$decodeSimbrief['times']['est_time_enroute']);
$aircraft = $decodeSimbrief['aircraft']['icaocode'];
$departure = $decodeSimbrief['origin']['icao_code'];
$departure_iata = $decodeSimbrief['origin']['iata_code'];
$destination = $decodeSimbrief['destination']['icao_code'];
$destination_iata = $decodeSimbrief['destination']['iata_code'];
$generatedtime = strtoupper(gmdate("jMy H:i", $decodeSimbrief['params']['time_generated']));
$validto = strtoupper(gmdate("jMy H:i", ($decodeSimbrief['params']['time_generated'])+43200));
$sked = gmdate("H:i",$decodeSimbrief['times']['sched_out']);
$eta = gmdate("H:i",$decodeSimbrief['times']['sched_on']);
$route = $departure . " " . $decodeSimbrief['api_params']['route']. " "  . $destination;
$costindex = $decodeSimbrief['general']['costindex'];
$landingelevation = $decodeSimbrief['destination']['elevation'] . "FT";
if($decodeSimbrief['aircraft']['fuelfact'] > 1){
    $fuelbias = "P".substr($decodeSimbrief['aircraft']['fuelfact'],1) * 100 . ".0";
}
if($decodeSimbrief['aircraft']['fuelfact'] == 1){
    $fuelbias = "P".substr($decodeSimbrief['aircraft']['fuelfact'],1) . ".0";
}
$pax = $decodeSimbrief['weights']['pax_count'];
$zfw = $decodeSimbrief['weights']['est_zfw'];
$mzfw = $decodeSimbrief['weights']['max_zfw'];
$pld = $decodeSimbrief['weights']['payload'];
$tom = $decodeSimbrief['weights']['est_tow'];
$mtom = $decodeSimbrief['weights']['max_tow'];
$ldm = $decodeSimbrief['weights']['est_ldw'];
$mldm = $decodeSimbrief['weights']['max_ldw'];
$tripfuel = $decodeSimbrief['fuel']['enroute_burn'];
$cont = $decodeSimbrief['fuel']['contingency'];
$conttime = gmdate('H:i',$decodeSimbrief['times']['contfuel_time']);
$contrule = $decodeSimbrief['general']['cont_rule'];
$alternatefuel = $decodeSimbrief['fuel']['alternate_burn'];
$reservefuel = $decodeSimbrief['fuel']['reserve'];
$etopsfuel = $decodeSimbrief['fuel']['etops'];
$taxifuel = $decodeSimbrief['fuel']['taxi'];
$blockfuel = $decodeSimbrief['fuel']['plan_ramp'];
$extrafuel = $decodeSimbrief['fuel']['extra'];
$distance = $decodeSimbrief['general']['air_distance'];
$remainingdis = $distance;
$reservetime = gmdate("H:i", $decodeSimbrief['times']['reserve_time']);
$etopsfueltime = gmdate("H:i",$decodeSimbrief['times']['etopsfuel_time']);
$extrafueltime = gmdate("H:i",$extrafuel);
$blockfueltime = gmdate("H:i",$decodeSimbrief['times']['endurance']);
$alternatetimes = array();
$altdis = array();
try{
foreach ($decodeSimbrief['alternate'] as $alternate){
    array_push($alternatetimes,gmdate("H:i",$alternate['ete']));
    $alttime = $alternatetimes[0];
    array_push($altdis,$alternate['air_distance']);
    $altdistance = $altdis[0];
}} catch (Throwable $t) {
    $alttime = $decodeSimbrief['alternate']['ete'];
    $altdistance = $decodeSimbrief['alternate']['air_distance'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $reg . "_" . $callsign ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        #marginedbox{
            margin-left: 70px;
            margin-right: 70px;
        }
        .route {
            margin-left: 2%;
        }
        .pd-t-b{
            margin-top:0.5%;
            margin-bottom: 0.5%;
        }
        body{
            width:1000px;
            margin:0 auto;
            font-family: 'Courier Prime', monospace;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: separate;
            border-spacing: 0px;
            margin-right: -1px;
        }
        table.fixed {table-layout:fixed; width:80px;}/*Setting the table width is important!*/
        table.fixed td {overflow:hidden;}/*Hide text outside the cell.*/
        table.fixed td:nth-of-type(1) {width:80px;}/*Setting the width of column 1.*/
        table.fixed td:nth-of-type(2) {width:80px;}/*Setting the width of column 2.*/
        table.fixed td:nth-of-type(3) {width:80px;}/*Setting the width of column 3.*/
        table.fixed td:nth-of-type(4) {width:80px;}/*Setting the width of column 3.*/

        table.fixed2 {table-layout:fixed; width:80px;}/*Setting the table width is important!*/
        table.fixed2 td {overflow:hidden;}/*Hide text outside the cell.*/
        table.fixed2 td:nth-of-type(1) {width:150px;}/*Setting the width of column 1.*/
        table.fixed2 td:nth-of-type(2) {width:80px;}/*Setting the width of column 2.*/
        table.fixed2 td:nth-of-type(3) {width:80px;}/*Setting the width of column 3.*/
        table.fixed2 td:nth-of-type(4) {width:80px;}/*Setting the width of column 3.*/
    </style>
</head>
<body>
<div class="container"></div>
<div id="marginedbox" class="row">
    <div class="col-md-3">FLT NO: <?php echo $flight_number ?></div>
    <div class="col-md-6 text-center">RELEASE NBR <?php echo $release ."/0 ". gmdate("ymd G:i:s",$decodeSimbrief['params']['time_generated'])."Z"?></div>
    <div class="col-md-3 "><div class="float-end">DOF: <?php echo $dof?></div></div>
</div>
<div id="marginedbox" class="row border border-dark">
    <div class="col-md-4">
    <strong><?php echo $callsign?></strong>
        <br>
    <strong><?php echo $dof?></strong>
        <br>
    <strong>ACFT-<?php echo $reg. " (" . $aircraft . ")"?></strong>
        <br>
        <br>
        <br>
        ROUTE: <span class="route"><?php echo $departure . "-" . $destination ?></span>
        <br>
        <?php echo "VARIANT:" ?>
    </div>
    <div class="col-md-2">
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <div class="float-end">TANKER: NO</div>
    </div>
    <div class="col-md-3">
        <div class="float-end">
        CMPTD:
        <br>
        VALID TO:
        <br>
        WIND DATA REF:
        <br>
        <br>
        SKED:
        <br>
        ETA:
        <br>
        R/ETA:
        </div>
    </div>
    <div class="col-md-3">
        <div class=" float-end">
        <?php echo $generatedtime;
        echo "<br>";
        echo $validto;
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo $sked . "Z";
        echo "<br>";
        echo $eta . "Z";
?>
        </div>
    </div>
</div>
<div id="marginedbox" class="row border border-dark">
    <span class="pd-t-b">MEL/CDL</span>
</div>
<div id="marginedbox" class="row border border-dark">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
</div>
<div id="marginedbox" class="row border border-dark">
    <strong>RTNG</strong>
    <?php echo $route?>
    <br>
    <br>
</div>
<div id="marginedbox" class="row border border-dark">
    <strong>ATIS</strong>
    <br>
    <br>
    <br>
    <br>
</div>
<div id="marginedbox" class="row">
    <div class="col-md-5  border border-dark">
        <br>
        <?php echo $departure."/".$destination;
        echo "<br>";
        echo "CI M/".$costindex;
        echo "<br>";
        echo "FUEL BIAS ". $fuelbias;

        ?>
    </div>
    <div class="col-md-7  border border-dark">
        <table style="width: 100%">
            <tr>
                <td style="width: 130px"><?php echo $destination . "/" . $destination_iata ?></td>
                <td style="float: left; width: 160px;">ON</td>
                <td style="float: left; width: 185px;">LAND</td>
            </tr>
            <tr>
                <td style="width: 130px"><?php echo $departure . "/" . $departure_iata ?></td>
                <td style="float: left; width: 160px;">OFF</td>
                <td  style="float: left; width: 185px;">AIR</td>
            </tr>
            <tr>
                <td><br></td>
                <td style="float: left; width: 160px; height: 50px">BLK</td>
                <td  style="float: left; width: 185px;height: 50px">FLT</td>
            </tr>
        </table>
    </div>
</div>
<div id="marginedbox" class="row">
        <div class="col-md-6 border border-dark">
            <strong>DISPATCH LOAD</strong>
            <table class="fixed"style="border: black 0px solid !important;">
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">PAX</td>
                    <td style="border: black 0px solid !important;"><?php echo $pax?></td>
                    <td style="border: black 0px solid !important;"></td>
                </tr>
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">EZFM</td>
                    <td style="border: black 0px solid !important;"><?php echo $zfw?></td>
                    <td style="border: black 0px solid !important;"><?php echo $mzfw?></td>
                </tr>
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">PLD <!-- PAYLOAD --></td>
                    <td style="border: black 0px solid !important;"><?php echo $pld?></td>
                </tr>
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">TOM</td>
                    <td style="border: black 0px solid !important;"><?php echo $tom?></td>
                    <td style="border: black 0px solid !important;"><?php echo $mtom?></td>
                </tr>
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">DOM</td>
                    <td style="border: black 0px solid !important;"><?php echo "00000"?></td>
                </tr>
                <tr style="border: black 0px solid !important;">
                    <td style="border: black 0px solid !important;">LDM</td>
                    <td style="border: black 0px solid !important;"><?php echo $ldm?></td>
                    <td style="border: black 0px solid !important;"><?php echo $mldm?></td>
                </tr>
            </table>
        </div>
    <div class="col-md-6 border border-dark">
        <strong>ATC CLEARANCE</strong>
    </div>
    </div>
<div id="marginedbox" class="row">
    <div class="col-md-6 border border-dark">
        FUEL PENALTIES
        <br>
        <br>
        PER 100KG =
        <br>
        <br>
    </div>
    <div class="col-md-6 border border-dark"></div>
    </div>
<div id="marginedbox" class="row">
    <div class="col-md-6 border border-dark">
        <table class="fixed"style="border: black 0px solid !important;">
            <tr style="border: black 0px solid !important;">
                <td style="border: black 0px solid !important;"><?php echo $destination_iata?></td>
                <td style="border: black 0px solid !important;">FUEL</td>
                <td style="border: black 0px solid !important;">TIME</td>
                <td style="border: black 0px solid !important;">DIST</td>
            </tr>
            <tr>
                <td style="border: black 0px solid !important;">TRIP</td>
                <td style="border: black 0px solid !important;"><?php echo $tripfuel?></td>
                <td style="border: black 0px solid !important;"><?php echo $ete?></td>
                <td style="border: black 0px solid !important;"><?php echo $distance?></td>
            </tr>
            <tr>
                <td style="border: black 0px solid !important;">CONT<?php echo $contrule; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $cont; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $conttime; ?></td>
            </tr>
            <tr>
                <td style="border: black 0px solid !important;">ALT</td>
                <td style="border: black 0px solid !important;"><?php echo $alternatefuel; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $alttime; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $altdistance; ?></td>
            </tr>
            <tr>
                <td style="border: black 0px solid !important;">FRF</td>
                <td style="border: black 0px solid !important;"><?php echo $reservefuel; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $reservetime; ?></td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">LLV</td>
            <td style="border: black 0px solid !important;">0</td>
            <td style="border: black 0px solid !important;">00:00</td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">ETOPS</td>
                <td style="border: black 0px solid !important;"><?php echo $etopsfuel; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $etopsfueltime; ?></td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">MEL/CDL</td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">TAXI</td>
                <td style="border: black 0px solid !important;"><?php echo $taxifuel; ?></td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">EXTRA</td>
                <td style="border: black 0px solid !important;"><?php echo $extrafuel; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $extrafueltime; ?></td>
            </tr>
            <tr>
            <td style="border: black 0px solid !important;">BLOCK</td>
                <td style="border: black 0px solid !important;"><?php echo $blockfuel; ?></td>
                <td style="border: black 0px solid !important;"><?php echo $blockfueltime; ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6 border border-dark">
        <div class="text-center"><span align="center"><?php echo "FOR INFO ONLY" ?></span></div>
        <table class="text-center" style="border: black 0px solid !important; width:100%">
            <tr style="border: black 0px solid !important;">
                <td style="border: black 0px solid !important;">ALTN</td>
                <td  style="border: black 0px solid !important;">DIST</td>
                <td style="border: black 0px solid !important;">FUEL</td>
                <td style="border: black 0px solid !important;">TIME</td>
                <td style="border: black 0px solid !important;">MDIV</td>
            </tr>
            <?php try{ foreach ($decodeSimbrief['alternate'] as $altn){?>
            <tr>
                <td  style="border: black 0px solid !important;"><?php echo $altn['icao_code']?></td>
                <td  style="border: black 0px solid !important;"><?php echo $altn['air_distance']?></td>
                <td  style="border: black 0px solid !important;"><?php echo $altn['burn']?></td>
                <td  style="border: black 0px solid !important;"><?php echo gmdate("H:i",$altn['ete']);?></td>
                <td  style="border: black 0px solid !important;"><?php echo $reservefuel + $altn['burn']?></td>
            </tr>
            <?php }} catch (Throwable $t) {?>
            <tr>
                <td style="border: black 0px solid !important;"><?php echo $decodeSimbrief['alternate']['icao_code'];?></td>
                <td style="border: black 0px solid !important;"><?php echo $decodeSimbrief['alternate']['air_distance'];?></td>
                <td style="border: black 0px solid !important;"><?php echo $decodeSimbrief['alternate']['burn'];?></td>
                <td style="border: black 0px solid !important;"><?php echo gmdate("H:i",$decodeSimbrief['alternate']['ete']);?></td>
                <td  style="border: black 0px solid !important;"><?php echo $reservefuel + $decodeSimbrief['alternate']['burn']?></td>
            </tr>
            <?php }?>
        </table>
    </div>
</div>
<div id="marginedbox" class="row">
    <div class="col-md-3 border border-dark">
        BLK:
        <?php echo "<br>";?>
    </div>
    <div class="col-md-4 border border-dark">
        REM:
        <?php echo "<br>";?>
    </div>
    <div class="col-md-5 border border-dark">
        PLANNED BURN: <?php echo $tripfuel ?>
        <br>
        BURN:
    </div>
</body>
</html>
