<?php
session_start();
include 'connect.php';
global $con;
if (isset($_SESSION['ID'])) {
    $ID = $_SESSION['ID'];
    $idx = $_GET['id'];
    $idlist = $_GET['name']; 
    if ($idlist == 'general') {
        $stmtx = $con->prepare("SELECT lista_g FROM " . DB_PREFIX . "user_details  WHERE id = ? ");
        $stmtx->execute(array($ID));
        $row1 = $stmtx->fetch();
        $subject = $row1['lista_g'] ;
        $array = explode(',', $subject);
        $index = array_search($idx, $array);
        
        if ($index == 0 ) {
            if( count($array)==1){
                $search = $idx ;    
            }else{
                $search = $idx.',' ;    
            }
        } else {
            $search = ','.$idx ;
        }        
        $trimmed = str_replace($search, '', $subject) ;
        $stmt = $con->prepare("UPDATE " . DB_PREFIX . "user_details 
        SET  
        lista_g =  ?
        WHERE ID = ? ");
        $stmt->execute(array($trimmed, $ID));
    } elseif ($idlist == 'specializari') {
        $stmtx = $con->prepare("SELECT lista_s FROM " . DB_PREFIX . "user_details  WHERE id = ? ");
        $stmtx->execute(array($ID));
        $row1 = $stmtx->fetch();
        $subject = $row1['lista_s'] ;
        $array = explode(',', $subject);
        $index = array_search($idx, $array);
        if ($index == 0 ) {
            if( count($array)==1){
                $search = $idx ;
            }else{
                $search = $idx.',' ;    
            }
        } else {
            $search = ','.$idx ;
        }        
        $trimmed = str_replace($search, '', $subject) ;
        $stmt = $con->prepare("UPDATE " . DB_PREFIX . "user_details 
        SET  
        lista_s = ?
        WHERE ID = ? ");
        $stmt->execute(array($trimmed, $ID));
     
    }
}
?>