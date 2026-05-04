<?php
include "con.php";

extract($_REQUEST);


$get = mysqli_query($con,"select * from gametime_manual");
while($xc = mysqli_fetch_array($get))
{ 
   $data['data'][] = $xc;	
}


// $get = mysqli_query($con,"select * from starline_markets");
// while($xc = mysqli_fetch_array($get))
// {
    
    
//   $chts['market'] = $xc['name'];
            
//             $data['data'][] = $chts;
	
// }




// $get = mysqli_query($con,"select * from gametime_delhi");
// while($xc = mysqli_fetch_array($get))
// { 
//   $data['data'][] = $xc;	
// }


echo json_encode($data);