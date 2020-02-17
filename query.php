<?php
include("dbConnect.php");
function getAll($dimdateID){
    $sql = "SELECT  DISTINCT `log-watering`.`DIMownerID`,`log-watering`.`DIMfarmID`,`log-watering`.`DIMsubFID` FROM `log-watering` WHERE `log-watering`.`isDelete` = 0 AND `log-watering`.`DIMdateID` = $dimdateID UNION
    SELECT  DISTINCT `log-raining`.`DIMownerID`,`log-raining`.`DIMfarmID`,`log-raining`.`DIMsubFID` FROM `log-raining` WHERE `log-raining`.`isDelete` = 0 AND `log-raining`.`DIMdateID` = $dimdateID";
    return selectData($sql);
}
function getLW($ownerId,$farmId,$subfarmId,$dimdateID){
    $sql = "SELECT sum(`log-watering`.`Period`) as 'period' FROM `log-watering` WHERE `log-watering`.`isDelete` = 0 AND `log-watering`.`DIMfarmID` = $farmId  AND `log-watering`.`DIMsubFID` = $subfarmId AND `DIMownerID` = $ownerId   AND `log-watering`.`DIMdateID` = $dimdateID";
    return selectData($sql);
}
function getLR($ownerId,$farmId,$subfarmId,$dimdateID){
    $sql = "SELECT sum(`log-raining`.`Period`) as 'period' FROM `log-raining` WHERE `log-raining`.`isDelete` = 0 AND `log-raining`.`DIMfarmID` = $farmId  AND `log-raining`.`DIMsubFID` = $subfarmId AND `DIMownerID` = $ownerId   AND `log-raining`.`DIMdateID` = $dimdateID";
    return selectData($sql);
}
function getDrying($ownerId,$farmId,$subfarmId){
    $sql = "SELECT * FROM `fact-drying` WHERE `fact-drying`.`DIMownerID` = $ownerId AND `fact-drying`.`DIMfarmID`  = $farmId AND `fact-drying`.`DIMsubFID` = $subfarmId AND `fact-drying`.`DIMstopDID` is null";
    // echo $sql;
    return selectData($sql);
}
function insertFW($data){
    $time = time();
    $sql = "INSERT INTO `fact-watering`(`LOGloginID`,`DIMdateID`,`DIMownerID`,`DIMfarmID`,`DIMsubFID`,`WaterPeriod`,`RainPeriod`,`TotalPeriod`,`Modify`) VALUES ({$data['loglogin']},{$data['dimdate']},{$data['ownerid']},{$data['farmid']},{$data['subfarmid']},{$data['waterperiod']},{$data['rainperiod']},{$data['totalperiod']},$time)";
    // echo "$sql";
    return addinsertData($sql);
}
function insertFD($data){
    $time = time();
    $sql = "INSERT INTO `fact-drying`(`LOGloginID`, `DIMstartDID`, `DIMownerID`, `DIMfarmID`, `DIMsubFID`,`Period`,`Modify`) VALUES ({$data['loglogin']},{$data['dimstart']},{$data['ownerid']},{$data['farmid']},{$data['subfarmid']},1,$time)";
    
    return addinsertData($sql);
}
function updateFD($data){
    $time = time();
    $sql = "UPDATE `fact-drying` SET `Period` = {$data['period']},`DIMstopDID` = {$data['dimstop']},`Modify` = $time WHERE `ID` = {$data['id']}";
    // echo $sql;
    return updateData($sql);
}
function getAllPalm(){
    $sql = "SELECT `db-subfarm`.`Name` as `SUBNAME`,`db-subfarm`.`Alias` as `SUBALIAS`,`db-farm`.`FMID`,`db-subfarm`.`FSID`,`db-farmer`.`UFID` as `UID` FROM `db-farm` join `db-subfarm` on `db-farm`.`FMID` = `db-subfarm`.`FMID` join `db-farmer` on `db-farm`.`UFID` = `db-farmer`.`UFID`";
    return selectData($sql);
}
function getDimFarmID($dbID){
    $sql = "SELECT DISTINCT `dim-farm`.`ID` FROM `dim-farm` join `log-farm` on `log-farm`.`DIMfarmID` = `dim-farm`.`ID` WHERE `dim-farm`.`IsFarm`  = 1 AND `dim-farm`.`dbID` = $dbID AND `log-farm`.`EndID` is null";
    return selectData($sql);
}
function getDimSubFarm($dbID,$name,$alias){
    $sql = "SELECT DISTINCT `dim-farm`.`ID` FROM `dim-farm`  WHERE `dim-farm`.`IsFarm`  = 0 AND `dim-farm`.`dbID` = $dbID AND `dim-farm`.`Alias` = '$alias' AND `dim-farm`.`Name` = '$name' ";
    return selectData($sql);
}
function getDimFarmer($dbID){
    $sql = "SELECT DISTINCT `dim-user`.`ID` FROM `dim-user` join `log-farmer` on `log-farmer`.`DIMuserID` = `dim-user`.`ID` WHERE `log-farmer`.`EndID` is null AND `dim-user`.`Type` = 'F' AND `dim-user`.`dbID` = $dbID";
    return selectData($sql);
}
function getAllDim(){
    $DBAll = getAllPalm();
    // print_r($DBAll);
    $DIMALL[0]['num'] = $DBAll[0]['num'];
    if($DBAll[0]['num']>0){
        foreach($DBAll as $key=>$val){
            if($key>0){
                $ID = getDimFarmer($val['UID']);
                $IDOWNER = $ID[1]['ID'];
                $ID = getDimSubFarm($val['FSID'],$val['SUBNAME'],$val['SUBALIAS']);
                $IDSUBFARM = $ID[1]['ID'];
                $ID = getDimFarmID($val['FMID']);
                // print_r($ID);
                $IDFARM = $ID[1]['ID'];
                $DIMALL[$key]['dimfarmID'] = $IDFARM;
                $DIMALL[$key]['dimsubfarmID'] = $IDSUBFARM;
                $DIMALL[$key]['dimfarmerID'] = $IDOWNER;
            }
        }
    }
    return $DIMALL;
    
}