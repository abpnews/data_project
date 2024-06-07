<?php
include "conn.php";
$college_state_url = array("https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Andaman%20and%20Nicobar%20Islands%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Andhra%20Pradesh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Arunachal%20Pradesh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Assam%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Bihar%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Chandigarh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Chhattisgarh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Dadra%20and%20Nagar%20Haveli%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Daman%20and%20Diu%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Delhi%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Goa%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Gujarat%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Haryana%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Himachal%20Pradesh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Jammu%20and%20Kashmir%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Jharkhand%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Karnataka%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Kerala%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Madhya%20Pradesh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Maharashtra%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Manipur%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Meghalaya%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Mizoram%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Nagaland%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Odisha%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Orissa%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Puducherry%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Punjab%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Rajasthan%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Sikkim%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Tamil%20Nadu%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Telangana%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Tripura%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Uttar%20Pradesh%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=Uttarakhand%20&course=1",
"https://facilities.aicte-india.org/dashboard/pages/php/approvedinstituteserver.php?method=fetchdata&year=2023-2024&program=1&level=1&institutiontype=1&Women=1&Minority=1&state=West%20Bengal%20&course=1");
$state = array("Andaman and Nicobar Islands", "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chandigarh", "Chhattisgarh", "Dadra and Nagar Haveli", "Dadra and Nagar Haveli", "Daman and Diu", "Delhi", "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jammu and Kashmir", "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Orissa", "Puducherry", "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal");

for($x=0;$x<count($college_state_url);$x++)
{
  $api_url = $college_state_url[$x];
  $json_data = file_get_contents($api_url);
  $response_data = json_decode($json_data);
  if($response_data != '' OR count($response_data)>0){    
    for($i=0;$i<count($response_data);$i++){
      $aicte_id = $response_data[$i][0];
      $college_name = addslashes($response_data[$i][1]);
      $college_address = addslashes($response_data[$i][2]);
      $college_state =  $state[$x];
      $college_district =  addslashes($response_data[$i][3]);
      $institution_type =  addslashes($response_data[$i][4]);
      $women = $response_data[$i][5];
      $minority = $response_data[$i][6];
      $sqlselect = "SELECT id FROM college_info Where aicte_id = '".$aicte_id."'";
      $resultselect = $conn->query($sqlselect);
      if ($resultselect->num_rows < 1) 
      {
        $sqlinsert = "INSERT INTO  college_info(aicte_id, college_name, college_address, college_state,
                    college_district, institution_type, women, minority,  created_at)
                    VALUES ('".$aicte_id."', '".$college_name."', '".$college_address."', '".$college_state."',
                    '".$college_district."', '".$institution_type."', '".$women."', '".$minority."',
                    NOW())";
                    if ($conn->query($sqlinsert) === TRUE)
                    {
                      $last_id = $conn->insert_id;
                      $aicte_id_c = "aicteid=/".$response_data[$i][0]."/";
                      $api_url_course = "https://facilities.aicte-india.org/dashboard/pages/php/approvedcourse.php?method=fetchdata&".$aicte_id_c."&course=/1/&year=/2023-2024/";
                      $json_data_course = file_get_contents($api_url_course);
                      $response_data_course = json_decode($json_data_course);
                      
                      echo "<pre>";
                      print_r($response_data_course);
                      
                      if($response_data_course != '' OR count($response_data_course)>0){
                        for($j=0;$j<count($response_data_course);$j++)
                        {
                          $college_id = $last_id;
                          $aicte_id = addslashes($response_data_course[$j][0]);
                          $college_name = addslashes($response_data_course[$j][1]);
                          $college_state =  addslashes($response_data_course[$j][2]);
                          $programme =  addslashes($response_data_course[$j][3]);
                          $university =  addslashes($response_data_course[$j][4]);
                          $course_level = addslashes($response_data_course[$j][5]);
                          $course = addslashes($response_data_course[$j][6]);
                          $intake = $response_data_course[$j][7];
                         echo $sqlinsert_course = "INSERT INTO  college_course_info(college_id, aicte_id, college_name, 
                          college_state, programme, university, course_level, course, intake, created_at)
                          VALUES ('".$college_id."', '".$aicte_id."', '".$college_name."', '".$college_state."', 
                          '".$programme."', '".$university."', '".$course_level."', '".$course."', 
                          '".$intake."', NOW())";
                          $conn->query($sqlinsert_course);
                        }
                      } 
                      //sleep(1);
                      $aicte_id_f = "aicteid=/".$response_data[$i][0]."/";
                      $api_url_faculty = "https://facilities.aicte-india.org/dashboard/pages/php/faculty.php?method=fetchdata&".$aicte_id_f."&pid=1-468009933&year=2023-2024";
                      $json_data_faculty = file_get_contents($api_url_faculty);
                      $response_data_faculty = json_decode($json_data_faculty);
                      if($response_data_faculty != '' OR count($response_data_faculty)>0){
                        for($k=0;$k<count($response_data_faculty);$k++){
                          $college_id = $last_id;
                          $faculty_id = $response_data_faculty[$k][0];
                          $faculty_name = addslashes($response_data_faculty[$k][1]);
                          $gender =  addslashes($response_data_faculty[$k][2]);
                          $designation =  addslashes($response_data_faculty[$k][3]);
                          $dob =  addslashes($response_data_faculty[$k][4]);
                          $area_specialisation = addslashes($response_data_faculty[$k][5]);
                          $appointment_type = addslashes($response_data_faculty[$k][6]);
                          $college_name = addslashes($response_data_faculty[$k][7]);
                          $sqlinsert_faculty = "INSERT INTO  college_faculty_info(college_id, faculty_id, faculty_name, 
                          gender, designation, dob, area_specialisation, appointment_type, college_name, created_at)
                          VALUES ('".$college_id."', '".$faculty_id."', '".$faculty_name."', '".$gender."', 
                          '".$designation."', '".$dob."', '".$area_specialisation."', '".$appointment_type."', 
                          '".$college_name."', NOW())";
                          $conn->query($sqlinsert_faculty);
                        }
                      }
                      //sleep(1);
                    }else{
                        $fail = $fail+1;
                    }
      }else{
        $rowc = $resultselect->fetch_assoc();
        $college_id1 = $rowc['id'];
        $updateSql = "UPDATE college_info SET aicte_id = '".$aicte_id."', college_name = '".$college_name."',
                      college_address = '".$college_address."', college_state = '".$college_state."', college_district = 
                      '".$college_district."', institution_type = '".$institution_type."', women = '".$women."',
                      minority = '".$minority."', created_at = NOW() WHERE id = '".$college_id1."'";
        if ($conn->query($updateSql) === TRUE) { 
          $delsql =  "DELETE FROM college_course_info WHERE college_id ='".$college_id1."'";
            if ($conn->query($delsql) === TRUE) { 
                      $aicte_id_c = "aicteid=/".$response_data[$i][0]."/";
                      $api_url_course = "https://facilities.aicte-india.org/dashboard/pages/php/approvedcourse.php?method=fetchdata&".$aicte_id_c."&course=/1/&year=/2023-2024/";
                      $json_data_course = file_get_contents($api_url_course);
                      $response_data_course = json_decode($json_data_course);
                      if($response_data_course != '' OR count($response_data_course)>0){
                        for($j=0;$j<count($response_data_course);$j++){
                          $college_id = $college_id1;
                          $aicte_id = $response_data_course[$j][0];
                          $college_name = addslashes($response_data_course[$j][1]);
                          $college_state =  addslashes($response_data_course[$j][2]);
                          $programme =  addslashes($response_data_course[$j][3]);
                          $university =  addslashes($response_data_course[$j][4]);
                          $course_level = addslashes($response_data_course[$j][5]);
                          $course = addslashes($response_data_course[$j][6]);
                          $intake = addslashes($response_data_course[$j][7]);
                          $sqlinsert_course = "INSERT INTO  college_course_info(college_id, aicte_id, college_name, 
                          college_state, programme, university, course_level, course, intake, created_at)
                          VALUES ('".$college_id."', '".$aicte_id."', '".$college_name."', '".$college_state."', 
                          '".$programme."', '".$university."', '".$course_level."', '".$course."', 
                          '".$intake."', NOW())";
                          $conn->query($sqlinsert_course);
                        }
                      } 
            }
             //sleep(1);
            $delsqlf =  "DELETE FROM college_faculty_info WHERE college_id ='".$college_id1."'";
            if ($conn->query($delsqlf) === TRUE) {
                     
                      $aicte_id_f = "aicteid=/".$response_data[$i][0]."/";
                      $api_url_faculty = "https://facilities.aicte-india.org/dashboard/pages/php/faculty.php?method=fetchdata&".$aicte_id_f."&pid=1-468009933&year=2023-2024";
                      $json_data_faculty = file_get_contents($api_url_faculty);
                      $response_data_faculty = json_decode($json_data_faculty);
                      if($response_data_faculty != '' OR count($response_data_faculty)>0){
                         
                        for($k=0;$k<count($response_data_faculty);$k++){
                             echo "<pre>";
                          print_r($response_data_faculty[$k]);
                          $college_id = $college_id1;
                          $faculty_id = $response_data_faculty[$k][0];
                          $faculty_name = addslashes($response_data_faculty[$k][1]);
                          $gender =  $response_data_faculty[$k][2];
                          $designation =  addslashes($response_data_faculty[$k][3]);
                          $dob =  $response_data_faculty[$k][4];
                          $area_specialisation = addslashes($response_data_faculty[$k][5]);
                          $appointment_type = $response_data_faculty[$k][6];
                          $college_name = addslashes($response_data_faculty[$k][7]);
                         echo $sqlinsert_faculty = "INSERT INTO  college_faculty_info(college_id, faculty_id, faculty_name, 
                          gender, designation, dob, area_specialisation, appointment_type, college_name, created_at)
                          VALUES ('".$college_id."', '".$faculty_id."', '".$faculty_name."', '".$gender."', 
                          '".$designation."', '".$dob."', '".$area_specialisation."', '".$appointment_type."', 
                          '".$college_name."', NOW())";
                          $conn->query($sqlinsert_faculty);
                        }
                      }
            } 
            //sleep(1);         
        }             
      }
    }
  }else{
    echo "not data";
  }
}  
//}
?>