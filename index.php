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
$alticao = array();
$altiata = array();
try{
foreach ($decodeSimbrief['alternate'] as $alternate){
    array_push($alternatetimes,gmdate("H:i",$alternate['ete']));
    $alttime = $alternatetimes[0];
    array_push($altdis,$alternate['air_distance']);
    $altdistance = $altdis[0];
    array_push($alticao,$alternate['icao_code']);
    $alternateICAO = $alticao[0];
    array_push($altiata,$alternate['iata_code']);
    $alternateIATA = $altiata[0];
}} catch (Throwable $t) {
    $alttime = $decodeSimbrief['alternate']['ete'];
    $altdistance = $decodeSimbrief['alternate']['air_distance'];
    $alternateICAO = array_push($alticao,$decodeSimbrief['alternate']['icao_code']);;
    $alternateIATA = array_push($altiata,$decodeSimbrief['alternate']['iata_code']);
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

        table.fixed {table-layout:fixed; width:80px;}/*Setting the table width is important!*/
        table.fixed td {overflow:hidden;}/*Hide text outside the cell.*/
        table.fixed td:nth-of-type(1) {width:80px; border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 1.*/
        table.fixed td:nth-of-type(2) {width:80px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 2.*/
        table.fixed td:nth-of-type(3) {width:80px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed td:nth-of-type(4) {width:80px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed td:nth-of-type(5) {width:80px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/


        table.fixed2 {table-layout:fixed; width:80px;}/*Setting the table width is important!*/
        table.fixed2 td {overflow:hidden;}/*Hide text outside the cell.*/
        table.fixed2 td:nth-of-type(1) {width:80px;}/*Setting the width of column 1.*/
        table.fixed2 td:nth-of-type(2) {width:80px;}/*Setting the width of column 2.*/
        table.fixed2 td:nth-of-type(3) {width:180px;}/*Setting the width of column 3.*/
        table.fixed2 td:nth-of-type(4) {width:80px;}/*Setting the width of column 3.*/
        table.fixed2 td:nth-of-type(5) {width:80px;}/*Setting the width of column 3.*/

        table.fixed3 {table-layout:fixed; width:80px;}/*Setting the table width is important!*/
        table.fixed3 td {overflow:hidden;}/*Hide text outside the cell.*/
        table.fixed3 td:nth-of-type(1) {width:70px; border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 1.*/
        table.fixed3 td:nth-of-type(2) {width:120px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 2.*/
        table.fixed3 td:nth-of-type(3) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(4) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(5) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(6) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(7) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(8) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(9) {width:40px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(10) {width:110px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
        table.fixed3 td:nth-of-type(11) {width:80px;border-left: black 1px solid;border-bottom: black 1px solid;border-right: black 1px solid; border-top: black 1px solid;}/*Setting the width of column 3.*/
    </style>
</head>
<body>
<div class="container">
<div id="marginedbox" class="row">
    <div class="col-md-3">FLT NO: <?php echo $flight_number ?></div>
    <div class="col-md-6 text-center">RELEASE NBR <?php echo $release ."/0 ". gmdate("ymd G:i:s",$decodeSimbrief['params']['time_generated'])."Z"?></div>
    <div class="col-md-3 "><div class="float-end">DOF: <?php echo $dof?></div></div>
</div>
<div id="marginedbox" class="row border border-dark g-0">
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
<div id="marginedbox" class="row g-0">
    <div class="col-md-5" style="border-left: black 1px solid;">
        <br>
        <?php echo $departure."/".$destination;
        echo "<br>";
        echo "CI M/".$costindex;
        echo "<br>";
        echo "FUEL BIAS ". $fuelbias;

        ?>
    </div>
    <div class="col-md-7 box1" style="border-right: black 1px solid">
        <table style="width: 100%;border-right: 0px black solid !important;" class="fixed">
            <tr>
                <td style="width: 50px"><?php echo $destination."/".$destination_iata ?></td>
                <td>ON</td>
                <td>LAND</td>
            </tr>
            <tr>
                <td><?php echo $departure."/".$departure_iata ?></td>
                <td>OFF</td>
                <td>AIR</td>
            </tr>
            <tr style="">
                <td></td>
                <td style="height:50px;vertical-align:top;">BLK</td>
                <td style="height: 50px;vertical-align:top;">FLT <br></td>
            </tr>
        </table>
    </div>
</div>
<div id="marginedbox" class="row">
        <div class="col-md-6 border border-dark">
            <strong>DISPATCH LOAD</strong>
            <table class="fixed2"style="border: black 0px solid !important;">
                <tr>
                    <td>PAX</td>
                    <td><?php echo $pax?></td>
                    <td></td>
                </tr>
                <tr>
                    <td>EZFM</td>
                    <td><?php echo $zfw?></td>
                    <td><?php echo "MZFM " .$mzfw?></td>
                </tr>
                <tr>
                    <td>PLD <!-- PAYLOAD --></td>
                    <td><?php echo $pld?></td>
                </tr>
                <tr>
                    <td>TOM</td>
                    <td><?php echo $tom?></td>
                    <td><?php echo "DMTOM ".  $mtom?></td>
                </tr>
                <tr>
                    <td>DOM</td>
                    <td><?php echo "00000"?></td>
                </tr>
                <tr>
                    <td>LDM</td>
                    <td><?php echo $ldm?></td>
                    <td><?php echo "MLDM " . $mldm?></td>
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
<div id="marginedbox" class="row g-0">
    <div class="col-md-6 border border-dark">
        <table class="fixed"style="width 100%">
            <tr>
                <td style="width: 110px"><?php echo $destination_iata?></td>
                <td style="width: 90px">FUEL</td>
                <td style="width: 80px">TIME</td>
                <td style="width: 137px">DIST</td>
            </tr>
            <tr>
                <td>TRIP</td>
                <td><?php echo $tripfuel?></td>
                <td><?php echo $ete?></td>
                <td><?php echo $distance?></td>
            </tr>
            <tr>
                <td>CONT<?php echo $contrule; ?></td>
                <td><?php echo $cont; ?></td>
                <td><?php echo $conttime; ?></td>
                <td></td>
            </tr>
            <tr>
                <td>ALT</td>
                <td><?php echo $alternatefuel; ?></td>
                <td><?php echo $alttime; ?></td>
                <td><?php echo $altdistance; ?></td>
                <td></td>
            </tr>
            <tr>
                <td>FRF</td>
                <td><?php echo $reservefuel; ?></td>
                <td><?php echo $reservetime; ?></td>
                <td></td>
            </tr>
            <tr>
            <td>LLV</td>
            <td>0</td>
            <td>00:00</td>
                <td></td>
            </tr>
            <tr>
            <td>ETOPS</td>
                <td><?php echo $etopsfuel; ?></td>
                <td><?php echo $etopsfueltime; ?></td>
                <td></td>
            </tr>
            <tr>
            <td>MEL/CDL</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
            <td>TAXI</td>
                <td><?php echo $taxifuel; ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
            <td>EXTRA</td>
                <td><?php echo $extrafuel; ?></td>
                <td><?php echo $extrafueltime; ?></td>
                <td></td>
            </tr>
            <tr>
            <td>BLOCK</td>
                <td><?php echo $blockfuel; ?></td>
                <td><?php echo $blockfueltime; ?></td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6 border border-dark">
        <div class="text-center"><?php echo "FOR INFO ONLY" ?></div>
        <table class="text-center fixed" style="width:100%">
            <tr>
                <td>ALTN</td>
                <td >DIST</td>
                <td>FUEL</td>
                <td>TIME</td>
                <td>MDIV</td>
            </tr>
            <?php try{ foreach ($decodeSimbrief['alternate'] as $altn){?>
            <tr>
                <td ><?php echo $altn['icao_code']?></td>
                <td ><?php echo $altn['air_distance']?></td>
                <td ><?php echo $altn['burn']?></td>
                <td ><?php echo gmdate("H:i",$altn['ete']);?></td>
                <td ><?php echo $reservefuel + $altn['burn']?></td>
            </tr>
            <?php }} catch (Throwable $t) {?>
            <tr>
                <td><?php echo $decodeSimbrief['alternate']['icao_code'];?></td>
                <td><?php echo $decodeSimbrief['alternate']['air_distance'];?></td>
                <td><?php echo $decodeSimbrief['alternate']['burn'];?></td>
                <td><?php echo gmdate("H:i",$decodeSimbrief['alternate']['ete']);?></td>
                <td ><?php echo $reservefuel + $decodeSimbrief['alternate']['burn']?></td>
            </tr>
            <?php }?>
        </table>
    </div>
</div>
<div id="marginedbox" class="row g-0">
    <div class="col-md-3 border border-dark">
        BLK:
        <?php echo "<br>";?>
    </div>
    <div class="col-md-4 border border-dark">
        REM:
        <?php echo "<br>";?>
    </div>
    <div class="col-md-5 border border-dark">
        PLANNED BURN: <?php echo $tripfuel; ?>
        <br>
        BURN:
    </div>
</div>
<br>
<div id="marginedbox" class="row">
    <div class="col-md-3">FLT NO: <?php echo $flight_number ?></div>
    <div class="col-md-6 text-center">RELEASE NBR <?php echo $release ."/0 ". gmdate("ymd G:i:s",$decodeSimbrief['params']['time_generated'])."Z"?></div>
    <div class="col-md-3 "><div class="float-end">DOF: <?php echo $dof?></div></div>
</div>
    <div id="marginedbox" class="row border border-dark g-0 ">
        <div class="col-md-12 text-center" style="margin-top: 3%; margin-bottom: 4%;"><?php echo $flight_number . "    " . $dof . " RTNG " . $departure."-".$destination?></div>
    </div>
    <div id="marginedbox" class="row border border-dark g-0 ">
        <div class="col-md-12">
        <table class="fixed3" style="width: 100%!important;">
            <tr class="text-center">
                <td>AWY1234 <br> MORA</td>
                <td>PSN</td>
                <td>DST</td>
                <td>TT <br>MT</td>
                <td>TM <br>CT</td>
                <td>E</td>
                <td>R</td>
                <td>A</td>
                <td style="vertical-align: top; text-align: right; margin-right: 1%">WS <br>FL</td>
                <td>WIND-MACH<br>TMP-TAS/GS</td>
                <td>EFB - AFB<br>MFB - ...</td>
            </tr>
            <?php foreach ($decodeSimbrief['navlog']['fix'] as $fix){ $remainingdis = ($remainingdis-$fix['distance'])
            ?>
            <tr>
                <td><?php echo $fix['via_airway'] . "<br>" . "0" . substr($fix['mora'], 0,2 )?></td>
                <td><?php echo $fix['ident'] . "<br>" . $fix['pos_lat'] . "<br>" . $fix['pos_long']?></td>
                <td><?php echo $fix['distance'] . "<br>" . $remainingdis ?></td>
                <td><!--TT MT--></td>
                <td class="text-center"><?php echo gmdate("Hi",$fix['time_leg']) . "<br>" . gmdate("Hi",$fix['time_total'])  ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?php echo "<br>" ?><?php if($fix['altitude_feet']>=10000) {echo substr($fix['altitude_feet'],0,3);} else {echo "0" . substr($fix['altitude_feet'],0,2);}?></td>
                <td><?php echo $fix['wind_dir'] . "/". $fix['wind_spd'] . " ". " ". ($fix['mach']*1000) . "<br>" ?><?php if($fix['oat']< 0) {echo "M".substr($fix['oat'],1,2);} else {echo "P".substr($fix['oat'],0,2);}?><?php echo " " . " " . $fix['true_airspeed'] . "/" . $fix['groundspeed'];?></td>
                <td><?php echo substr($fix['fuel_plan_onboard'],0,2) ."." . substr($fix['fuel_plan_onboard'],2,2)  . " ...." . "<br>" . substr($fix['fuel_min_onboard'],0,2) ."." . substr($fix['fuel_min_onboard'],2,2) . " ...."; ?></td>
            </tr>
            <?php };?>
        </table>
    </div></div>
    <div id="marginedbox" class="row">
        <div class="col-md-3">FLT NO: <?php echo $flight_number ?></div>
        <div class="col-md-6 text-center">RELEASE NBR <?php echo $release ."/0 ". gmdate("ymd G:i:s",$decodeSimbrief['params']['time_generated'])."Z"?></div>
        <div class="col-md-3 "><div class="float-end">DOF: <?php echo $dof?></div></div>
</div>
    <div id="marginedbox" class="row border border-dark g-0 ">
        <div class="col-md-12" style="">DECENT WINDS
            <br><br>
        <?php foreach ($decodeSimbrief['navlog']['fix'] as $fix)
            if($fix['ident'] == "TOD") {
                foreach($fix['wind_data']['level'] as $winds) {
                if($winds['altitude'] >=10000 && $winds['altitude'] <= 30000){echo  "FL". substr($winds['altitude'],0,3) . " ". $winds['wind_dir']."/".$winds['wind_spd'] . " " . " ";}
                elseif ($winds['altitude'] == 5000) {
                    echo  "FL". substr($winds['altitude'],0,2) . " ". $winds['wind_dir']."/".$winds['wind_spd'] . " " . " ";}
            }
        }
        ?></div>
    </div>
    <div id="marginedbox" class="row border border-dark g-0 ">
        <table class="" style="width: 100%!important;">
            <tr>
                <td>POINT</td>
                <td>FL100</td>
                <td>FL180</td>
                <td>FL240</td>
                <td>FL300</td>
                <td>FL340</td>
                <td>FL390</td>
            </tr>
            <tr>
                <td></td>
               <td>W/V  TMP</td>
               <td>W/V  TMP</td>
               <td>W/V  TMP</td>
               <td>W/V  TMP</td>
               <td>W/V  TMP</td>
               <td>W/V  TMP</td>
            </tr>
            <?php foreach ($decodeSimbrief['navlog']['fix'] as $fix){?>
                <tr>
                    <td><?php echo $fix['ident']?></td>
                <?php foreach($fix['wind_data']['level'] as $winds) { ?>
                    <?php
                    if($winds['altitude'] == 10000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php
                    if($winds['altitude'] == 18000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php
                    if($winds['altitude'] == 24000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php
                    if($winds['altitude'] == 30000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php
                    if($winds['altitude'] == 34000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php
                    if($winds['altitude'] == 39000) {
                        echo "<td>" . $winds['wind_dir'] . "/" . $winds['wind_spd'] . "" . $winds['oat'] . "</td>";
                    } ?>
                    <?php }}?>
        </table>
    </div>
    <div id="marginedbox" class="row border border-dark g-0 ">
        <div class="text-center" style="margin-top: 2%; margin-bottom: 2%"><span>ICAO FLIGHT PLAN</span></div>
        <?php echo $decodeSimbrief['atc']['flightplan_text']; ?>
    </div>
</body>
</html>
