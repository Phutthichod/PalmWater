<?php
include("query.php");
function getDIMDate()
{
    $sql = "SELECT * FROM `dim-time` WHERE Date = '" . date('Y-m-d') . "'";
    $DIMTIME = selectData($sql);
    if ($DIMTIME[0]['num'] == 0) {
        $yearQuarter = ceil(date("n") / 3);
        date_default_timezone_set("Asia/Bangkok");
        $today = date("m-d");
        $summer = date("m-d", strtotime("2019-02-15"));
        $rainy = date("m-d", strtotime("2019-05-15"));
        $winter = date("m-d", strtotime("2019-10-15"));
        switch (true) {
            case $today >= $summer && $today < $rainy:
                $Season = 3;
                break;
            case $today >= $rainy && $today < $winter:
                $Season = 1;
                break;
            default:
                $Season = 2;
        }
        date_default_timezone_set("Asia/Bangkok");
        $yearQuarter = ceil(date("n") / 3);
        $sql = "INSERT INTO `dim-time`(`Date`,`dd`,`Day`,`Week`,`Season`,`Month`,`Quarter`,`Year1`,`Year2`) VALUES ('" . date("Y-m-d") . "','" . date("j") . "','" . date("w") . "','" . date("W") . "','" . $Season . "','" . date("n") . "','" . $yearQuarter . "','" . date("Y") . "','" . (date("Y") + 543) . "')";
        $idinsert = addinsertData($sql);
        $sql = "SELECT * FROM `dim-time` WHERE ID = '" . $idinsert . "'";
        $DIMTIME = selectData($sql);
    }
    return $DIMTIME;
}
function calWerPerDay($dimDate,$LOGloginID){
    $DATAALL = getALL($dimDate);
    // print_r($DATAALL);
    $PERWATER = array();
    $PERRAIN  = array();
    if($DATAALL[0]['num'] > 0){
        foreach($DATAALL as $key => $val){
            if($key!=0){
                $perwater = getLW($val['DIMownerID'],$val['DIMfarmID'],$val['DIMsubFID'],$dimDate);
                $perrain = getLR($val['DIMownerID'],$val['DIMfarmID'],$val['DIMsubFID'],$dimDate);
                // print_r($perrain);
                if($perwater[1]['period'] != null) $perwater = $perwater[1]['period']; else $perwater = 0;
                // echo $perwater;
                if($perrain[1]['period'] != null) $perrain = $perrain[1]['period']; else $perrain = 0;
                // echo $perrain;
                $total = $perwater+$perrain;
                $data = ['loglogin'=>$LOGloginID,'dimdate'=>$dimDate,'ownerid'=>$val['DIMownerID'],'farmid'=>$val['DIMfarmID'],'subfarmid'=>$val['DIMsubFID'],'waterperiod'=>$perwater,'rainperiod'=>$perrain,'totalperiod'=>$total];
                insertFW($data);
            }
        }  
    }
    else{
        return false;
    }
    
}
function checkDrying($dimDate,$LOGloginID){
    $DATAWARTER = getALL($dimDate);
    $DATAPALM = getAllDim();
    // print_r($DATAWARTER);
    // echo "</br>";
    // print_r($DATAPALM);
    foreach($DATAPALM as $keyP=>$valP){
        // print_r($valP);
        if($keyP>0){
            $FACTDRYING = getDrying($valP['dimfarmerID'],$valP['dimfarmID'],$valP['dimsubfarmID']);
            $isDrying = true;
            foreach($DATAWARTER as $key => $val){
                if($key>0){
                    if($valP['dimfarmID'] == $val['DIMfarmID'] && $valP['dimsubfarmID'] == $val['DIMsubFID'] && $valP['dimfarmerID'] == $val['DIMownerID']){
                        $isDrying = false;
                    }
                }
            }
            // echo $isDrying;
            if($isDrying == true){
                // echo $FACTDRYING[0]['num'];
                if($FACTDRYING[0]['num']>0){
                    // echo "sss";
                    $period = $FACTDRYING[1]['Period']+1;
                    $id = $FACTDRYING[1]['ID'];
                    $data = ['period'=>$period,'id'=>$id,'dimstop' => 'null'];
                    updateFD($data);
                }
                else{
                    $data  = ['dimstart'=>$dimDate,'loglogin'=>$LOGloginID,'dimdate'=>$dimDate,'ownerid'=>$valP['dimfarmerID'],'farmid'=>$valP['dimfarmID'],'subfarmid'=>$valP['dimsubfarmID'],'DIMstartDID'=>$dimDate];
                    // print_r($data);
                    insertFD($data);
                }
                
            }else{
                if($FACTDRYING[0]['num']>0){
                    $period = $FACTDRYING[1]['Period'];
                    $id = $FACTDRYING[1]['ID'];
                    $data = ['period'=>$period,'id'=>$id,'dimstop' => $dimDate];
                    updateFD($data);
                }
            }
        }
        
    }
}