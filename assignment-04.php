<?php

/*

ASSIGNMENT #4 - BY: Charles Byrne (Student Number: 97700266)
  FOR CS230[A] - Web Information Processing
  5th April 2021 -
  - Tested on: - Chrome and Firefox, Edge and Vivaldi 3.6 in Windows 10, XAMPP 

  - Back end - PHP - MARIADB

*/

// DEFINE GLOBAL VARIABLES ------------------------------

$SERVER = 'localhost';
$USER_NAME = 'testuser123';
$PASSWORD = 'testuser123';
$DATABASE_NAME = 'USERS';

$FULL_TABLE_SQL = "SELECT customers.customerID, customers.Title, customers.Firstnames,
customers.Surname, customers.Mobile, customers.Email, address.addressID,
address.Line1, address.Line2, address.Town, address.County, address.Eircode, address.addressType
FROM customers LEFT JOIN customeraddress ON (customers.customerID = customeraddress.CustomerID)
LEFT JOIN address ON customeraddress.addressID = address.addressID
ORDER BY customers.customerID";

// -------------------------------------------------------

// can return html or JSON

// Check to see if we are in the console or web browser:

if (php_sapi_name() === 'cli') {
    $EOL = "\n\n";
    $IN_CONSOLE = true;
} else {
    $EOL = "<br><br>";
    $IN_CONSOLE = false;
}


function printCLO($str) {               // only prints if using command line:
    global $IN_CONSOLE;
    if ($IN_CONSOLE) {
        echo $str;
    }
}

function createRandomUser() {
       //     $user = ['Mr', 'Mick', 'McGoo', '086 123 4567', 'mick@mcgoo.com'];
       $user = [];
       $titles = array(['Mx','Ms','Mrs','Miss','Sr'],['Mr','Dr','Fr']); // Titles
       $cNames = array(['Philomena','Sandra','John','Mary','Anne','Catherine','Therese','Brighid','Clare'],
       ['Paul','Francis','James','Sean','Joseph','Frank','Anthony','Gerry','Pat','Louis','Leo','Mick','Conor'] ) ;


        $sNames = ['Murphy','McGoo','Doe','Byrne','Gaynor','McDonald','Woods','Clarke','Molloy',
        'McDonald','Brogan','Barry','Quinn','McGuffin','Grendon','Malone','McGuire'];
        $domainNames = ['gmail.com','hotmail.com','email.com','mumail.ie'];

 
        $sex = rand(0,1);
        $user[] = $titles[$sex][rand(0, sizeof($titles[$sex])-1)];
        $cName = $cNames[$sex][rand(0, sizeof($cNames[$sex])-1)];
        $user[] = $cName;
        $user[] = $sNames[rand(0, sizeof($sNames)-1)];
        $user[] = "08".rand(0,9)." ".rand(100,999)." ".rand(1000,9999);
        $user[] = strtolower($cName).'@'.$domainNames[rand(0, sizeof($domainNames)-1)];
        return $user;
}

function createRandomAddress() {

    $streetNamesA = ['College','Melody','Hawthorn','Sycamore','Cottage','Lake','Greenfields','Mountain','Stream','Lake'];
    $streetNamesB = ['Rise','Drive','Avenue','Gardens','Green','Park','Heights','Valley','View','Manor','Terrace'];
    $RoadNamesA = ['Dublin','Main','Wide','Narrow','Scenic','Winding','Straight','Side'];
    $RoadNamesB = ['Road','Road','Way'];
    $towns = [
        'Tallaght, Dublin 24',
        'Howth, Co Dublin',
        'Dun Laoghaire, Co Dublin',
        'Dalkey, Dublin',
        'Killiney​, Co Dublin',
        'Skerries, Co Dublin',
        'Santry, Dublin 9',
        'Ballymun, Dublin 9',
        'Drumcondra, Dublin 9',
        'Glasnevin, Dublin 9',
        'Clonsilla, Dublin 15',
        'Cork City, Co Cork',
        'Galway City, Co Galway',
        'Drogheda, Co Louth',
        'Swords, Fingal',
        'Dundalk, Louth',
        'Bray, Wicklow',
        'Navan, Meath',
        'Kilkenny, Kilkenny',
        'Ennis, Clare',
        'Carlow, Carlow',
        'Tralee, Kerry',
        'Newbridge, Kildare',
        'Portlaoise, Laois',
        'Balbriggan, Fingal',
        'Naas, Kildare',
        'Athlone, Westmeath',
        'Mullingar, Westmeath',
        'Celbridge, Kildare',
        'Wexford, Wexford',
        'Letterkenny, Donegal',
        'Sligo, Sligo'
    ];

    $address = [];
    $houseNumber = rand(0,400);
    if ($houseNumber>0)
        $line1 = "$houseNumber ";
    else
        $line1 = "";
    $line1 .= $streetNamesA[rand(0, sizeof($streetNamesA)-1)] . ' ' . $streetNamesB[rand(0, sizeof($streetNamesB)-1)];
    if (rand(0,2)>0) {
        $line2 = $RoadNamesA[rand(0, sizeof($RoadNamesA)-1)] . ' ' . $RoadNamesB[rand(0, sizeof($RoadNamesB)-1)];
    } else {
        $line2 = "";
    }
    $townAndCounty = explode(', ', $towns[rand(0, sizeof($towns)-1)]);
    $eircode = chr(rand(65,90)).rand(0,9).rand(0,9).' '.chr(rand(65,90)).chr(rand(65,90)).rand(0,9).rand(0,9);

    $address[] = $line1;
    $address[] = $line2;
    $address[] = $townAndCounty[0];
    $address[] = $townAndCounty[1];
    $address[] = $eircode;
    return $address;

} // END: function createRandomAddress()

function safeGet($param, $pos, $emptyReturn) {  // get parameters either from prompt or get

global $argc, $argv;
    
    if (php_sapi_name() === 'cli') { // from command line arguments

            if ($argc > $pos) {
                return $argv[$pos]; 
            } else {
                return $emptyReturn;
            }
        } else { // from GET

            if(isset($_GET[$param])) {
                return $_GET[$param];
            } else {
                return $emptyReturn;
            }
    }

} // END: function safeGet()


function createDB($databaseName) {             //          #1 - CREATE THE DATABASE

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;
    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD);

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();

    } else {
        $sql = "CREATE DATABASE $databaseName";
        if (mysqli_query($databaseConnection, $sql)) {
            $returnData = "Databae $databaseName created";
        } else {
            $returnData = mysqli_error($databaseConnection);
        }
        mysqli_close($databaseConnection);
    }
    return $returnData;
}


function goSQL($sql) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
            if (mysqli_multi_query ($databaseConnection, $sql)) {
                $returnData = "Success - affected rows: " . $databaseConnection -> affected_rows;
            } else {
                $returnData = mysqli_error($databaseConnection);
            }

            while ($databaseConnection->next_result()) {;} // NB !!! : flush multi_queries

            mysqli_close($databaseConnection);
    }
    return $returnData;

}

function goSQL_INIT($sql) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD);

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
            if (mysqli_multi_query ($databaseConnection, $sql)) {
                $returnData = 'OK';
            } else {
                $returnData = mysqli_error($databaseConnection);
            }

            while ($databaseConnection->next_result()) {;} // NB !!! : flush multi_queries

            mysqli_close($databaseConnection);
    }
    return $returnData;

}


function queryDB2($sql) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    $returnData = "";
//echo $sql;
    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        if (mysqli_query($databaseConnection, $sql)) {
            $last_id = mysqli_insert_id($databaseConnection);
          } else {
            echo mysqli_error($databaseConnection);
            $last_id = -1;
          }          
        
        mysqli_close($databaseConnection);
    }
    return $last_id;
}

function queryDB($sql) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    $returnData = "";

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        $result = mysqli_query($databaseConnection, $sql);
        
        if (mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
            $returnData = $row;
          }
        } else {
            $returnData = "";
        }

        mysqli_close($databaseConnection);
    }
    return $returnData;
}

function searchName($strict, $namesToGet) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $returnData = "";
    $sql = "SELECT * FROM CUSTOMERS where Firstnames";
    if ($strict) {
        $sql .= "='$namesToGet[0]'";
    } else {
        $sql .= " LIKE '%$namesToGet[0]%'";
    }
    if (count($namesToGet)>1) {
        if ($strict) {
            $sql .= " AND Surname='$namesToGet[1]'";
        } else {
            $sql .= " AND Surname LIKE '%$namesToGet[1]%'";
        }
    }

//    echo "SQL: '$sql'\n\n";

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        $result = mysqli_query($databaseConnection, $sql);
        
        if (mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {

            foreach($row as $_column) {
                $returnData .= "{$_column} ";
            }
                $returnData .= "\n";


          }
          $recCount = $databaseConnection -> affected_rows;
          $returnData = "Records found: $recCount\n".$returnData;
         } else {
            $returnData = "No records.\n";
        }

        mysqli_close($databaseConnection);
    }
    return $returnData;

} // END: searchName()

function showUsers($sql, $wantJSON) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    if ($wantJSON) {
        $returnData = "{";
    } else {
        $returnData = "<table>";
    }

        //        echo "{\"result\":true, \"count\":42}";

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        $result = mysqli_query($databaseConnection, $sql);
        
        if (mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {

            $returnData .= "<tr>";
            foreach($row as $_column) {
                if ($wantJSON) {
                    $returnData .= "{$_column},";
                } else {
                    $returnData .= "<td>{$_column}</td>";
                }

            }
            if ($wantJSON) {
//                $returnData .= "</tr>";
            } else {
                $returnData .= "</tr>";
            }

            //$returnData .= $row['customerID']." ".$row['Firstnames']." ".$row['Surname']."<br/>";
          }
          if ($wantJSON) {
            $returnData .= "}";
          } else {
            $returnData .= "</table>";
          }
         } else {
            $returnData = "";
        }

        mysqli_close($databaseConnection);
    }
    return $returnData;
}

function showUsers2($sql, $wantJSON) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    $dbdata = array();

    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        $result = mysqli_query($databaseConnection, $sql);
        
        if (mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
              
           // $newdata =  array (
           //     'adr_1' => 'test',
           //     'adr_1' => 'test',
           //     'adr_3' => 'test'
           //   );
           //   array_push($row, $newdata);

      //        array_push($row, (object)[
      //          'key1' => 'someValue',
      //          'key2' => 'someValue2',
      //          'key3' => 'someValue3',
      //  ]);
      $row['adr'] = (object)[
                  'ad1' => '1 Somewhere,',
                  'ad2' => 'SomeTown',
                  'ad3' => 'SomeCounty',
          ];

            //  array_push($row.["addresses"],$newdata);
            $dbdata[]=$row;

            //$returnData .= $row['customerID']." ".$row['Firstnames']." ".$row['Surname']."<br/>";
          }

          if ($wantJSON) {
            $returnData = json_encode($dbdata);
          } else {
            $returnData = $dbdata;
          }

         } else {
            $returnData = "";
        }

        mysqli_close($databaseConnection);
    }
    return $returnData;
}



function showUsers3($sql) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;
    
    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    $dbdata = array();
    $lastRowID = -1;
    $lastRow = null;
    $lastAddress = [];

    //echo $sql.'<br/>';
    if (!$databaseConnection) {
        $returnData = mysqli_connect_error();
    } else {
        $result = mysqli_query($databaseConnection, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $i=0;
          while($row = mysqli_fetch_assoc($result)) {
              $i++;

              
           // $newdata =  array (
           //     'adr_1' => 'test',
           //     'adr_1' => 'test',
           //     'adr_3' => 'test'
           //   );
           //   array_push($row, $newdata);

      //        array_push($row, (object)[
      //          'key1' => 'someValue',
      //          'key2' => 'someValue2',
      //          'key3' => 'someValue3',
      //  ]);
      //$row['adr'] = (object)[
      //            'ad1' => '1 Somewhere,',
      //            'ad2' => 'SomeTown',
      //            'ad3' => 'SomeCounty',
      //    ];

            //  array_push($row.["addresses"],$newdata);
 //           echo($i. " ". $row['customerID']." ". $lastRowID . "<br/>");
      if ($row['customerID']!==$lastRowID) {
        if ($lastRow!==null) {
            $thisRow = (object)[
                'customerID' => $lastRow -> customerID,
               'Title' => $lastRow -> Title,
               'Firstnames' => $lastRow -> Firstnames,
               'Surname' => $lastRow -> Surname,
               'Mobile' => $lastRow -> Mobile,
               'Email' => $lastRow -> Email,
               'Address' => $lastAddress
            ];
            $dbdata[]=$thisRow;
            $lastAddress = [];
          }
          if ($row['Line1'] == null) {
            $address = null;
        } else {
            $address = (object)[
                'addressID' => $row['addressID'],
                'Line1' => $row['Line1'],
                'Line2' => $row['Line2'],
                'Town' => $row['Town'],
                'County' => $row['County'],  
                'Eircode' => $row['Eircode'],
                'addressType' => $row['addressType']   
            ];
        }

        $lastRow = (object)[
                   'customerID' => $row['customerID'],
                  'Title' => $row['Title'],
                  'Firstnames' => $row['Firstnames'],
                  'Surname' => $row['Surname'],
                  'Mobile' => $row['Mobile'],
                  'Email' => $row['Email']
          ];

        $lastRowID = $row['customerID'];
        if ($address!==null)
            $lastAddress[] = $address;
      } else {
        if ($row['Line1'] == null) {
            $address = null;
        } else {
            $address = (object)[
                'addressID' => $row['addressID'],
                'Line1' => $row['Line1'],
                'Line2' => $row['Line2'],
                'Town' => $row['Town'],
                'County' => $row['County'],  
                'Eircode' => $row['Eircode'],
                'addressType' => $row['addressType']
            ];
        }

        $lastAddress[] = $address;
//        $lastRow['Address'] = $address;

      }

            //$returnData .= $row['customerID']." ".$row['Firstnames']." ".$row['Surname']."<br/>";
          } // WHILE LOOP

          if ($lastRow!==null) {
            $thisRow = (object)[
                'customerID' => $lastRow -> customerID,
               'Title' => $lastRow -> Title,
               'Firstnames' => $lastRow -> Firstnames,
               'Surname' => $lastRow -> Surname,
               'Mobile' => $lastRow -> Mobile,
               'Email' => $lastRow -> Email,
               'Address' => $lastAddress
            ];
            $dbdata[]=$thisRow;
            $lastAddress = [];
          }

            $returnData = json_encode($dbdata);
         } else {
            $returnData = "";
        }

        mysqli_close($databaseConnection);
    }
    return $returnData;
}



//===========================================================================

function updateShippingAddresses($pos) {
            // UPDATE OTHER RECORDS EFFECTED BY SHIPING TYPE CHANGE

    $shippingTypeChanges = [];
    for ($t=0; $t<4; $t++) {
        $shippingTypeChanges[] = safeGet("st$t", ($pos+$t), -1);
     //   echo("ST $t = $shippingTypeChanges[$t]...<br/>");
    }
    if ($shippingTypeChanges[0]>-1) {
        if ($shippingTypeChanges[1]>-1) {
            $sql = "UPDATE address SET addressType = '$shippingTypeChanges[1]' WHERE addressID = '$shippingTypeChanges[0]'; ";
        //    echo $sql.'<br/>';
            goSql($sql);
        }
    }
    if ($shippingTypeChanges[2]>-1) {
        if ($shippingTypeChanges[3]>-1) {
        //    echo $sql.'<br/>';
            $sql = "UPDATE address SET addressType = '$shippingTypeChanges[3]' WHERE addressID = '$shippingTypeChanges[2]'; ";
            goSql($sql);
        }
    }
}

function showFullTable() {
    global $FULL_TABLE_SQL;
    if (php_sapi_name() !== 'cli') {
        echo showUsers3($FULL_TABLE_SQL);
    }
}

function addressAddOrUpdate($isNew) {

    $id = safeGet('id',3, ''); // this is the customer ID if new address, or the address id if a change

/*
        $id = safeGet('id',3,'');
        $col = safeGet('col',4,'');
        $data = safeGet('data',5,'-'); 
        
        if ($id<0) {            
            $newID = customerAdd([$col, $data]);
            printCLO("New customer record; ID: ");
            echo $newID;
        } else {
            customerEdit([$id, $col, $data]);
        }

*/

    $line1 = safeGet('line1', 4, '');
    $line2 = safeGet('line2', 5, '');
    $town = safeGet('town', 6, '');
    $county = safeGet('county', 7, '');
    $eircode = safeGet('eircode', 8, '');
    $addressType = safeGet('addressType', 9, '');

    if ($isNew) {
                // ######################################################## INSERT
                $sql = "INSERT INTO ADDRESS (Line1, Line2, Town, County, Eircode, addressType)
                VALUES ('$line1', '$line2', '$town', '$county', '$eircode', '$addressType' );";

                printCLO($sql."\n\n");
                $newAddr = queryDB2($sql);

                $sql = "INSERT INTO CUSTOMERADDRESS (CustomerID, addressID) VALUES ($id, $newAddr);";      
                printCLO($sql."\n\n");
                goSql($sql);
        
    } else {
                // ######################################################## UPDATE
                $sql = "UPDATE ADDRESS SET Line1='$line1', Line2='$line2',
                Town='$town', County='$county', Eircode='$eircode',
                addressType='$addressType'
                WHERE addressID=$id;";
                goSql($sql);
                printCLO($sql);
               
    }

        updateShippingAddresses(10);
        showFullTable();

} // END: function adddressAddOrUpdate()

function addressAOU($isNew, $params) { // ADDRESS - ADD OR UPDATE (NB: SAFEGUARDS AGAINST SQL INJECTION)

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;
    global $eol;

    // 1st record is: either (new Address): customerID or (edit): addressID
    $id = array_shift($params);
    $paramCount = count($params);

    //    PARAMS: [$ID, $line1, $line2, $town, $county, $eircode, $addressType];

    if ($paramCount!==6) {                 // There must be 6 Cols
        echo "You need 7 parameters (you have (".($paramCount+1)."). Enclose strings with spaces in quotes.\n";
        if ($isNew) {
            $idType = "CustomerID";
        } else {
            $idType = "addressID";            
        }
        echo "Required parameters: [$idType, line1, line2, town, county, eircode, addressType (1=Shipping and Billing; 2=Shipping; 3=Billing, 0=other)]\n\n";
         //fish
        die('');
    }


    // new Address - need a customer ID
    // edit Address - need the ID of the address

    if (!$isNew) {                        
        $params[] = $id; // place ID at end of array
    }

        //    (addressID - PK

    $cols = array('Line1', 'Line2', 'Town', 'County', 'Eircode','addressType');

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);
    
    if ($databaseConnection->connect_error) {
         die($databaseConnection->connect_error);
    }

    //--------------------------------------------------

    $colTxt = '';

    $len = sizeof($cols);
    for ($i=0; $i<$len; $i++) {
        if ($i>0) $colTxt .=', ';
        if ($isNew) {
            $colTxt .= $cols[$i];
        } else {
            $colTxt .= $cols[$i].'=?';
        }

    } // for loop

    //------------------------------------------------------

    // prepare and bind

    if ($isNew) {
        $sql = "INSERT INTO address ($colTxt) VALUES (?, ?, ?, ?, ?, ?)";
        $preparedStatement = $databaseConnection->prepare($sql);
        $preparedStatement->bind_param("ssssss", ...$params);
    } else {
        $sql = "UPDATE address SET $colTxt WHERE addressID = ?;";
        $preparedStatement = $databaseConnection->prepare($sql);
        $preparedStatement->bind_param("sssssii", ...$params);
    }

    $preparedStatement->execute();

    $newID = $databaseConnection->insert_id;         // NB: GET NEW RECORD ID

    $preparedStatement->close();

    if ($isNew) {            // if new Address we need to create a relation to the customer
        $sql = "INSERT INTO CUSTOMERADDRESS (CustomerID, addressID) VALUES (?, $newID);";
        $preparedStatement = $databaseConnection->prepare($sql);
        $preparedStatement->bind_param("i", $id);
        $preparedStatement->execute();
        $preparedStatement->close();

    }

    $databaseConnection->close();

    if ($isNew) {
        return $newID;
    }

} // END: function addressAdd()



function customerAdd($params) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $paramCount = count($params);

    if ($paramCount>=5) {                 // if there are 5 cols add all these

    } else if ($paramCount == 2) {
        $col = $params[0]; 
        $data = $params[1];
        if ($data=='' || $data==0) {
            $data = '-';
        }
    } else {
        die('You need 2 or 5 parameters: [ColumnName Data] or [Title Firstnames Surname Mobile Email]. Enclose strings in double quotes');
    }
    $cols = array('Title', 'Firstnames', 'Surname', 'Mobile', 'Email');

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);
    
    if ($databaseConnection->connect_error) {
         die($databaseConnection->connect_error);
    }

    //--------------------------------------------------

    $colTxt = '';
    $valArray = [];
    $len = sizeof($cols);
    for ($i=0; $i<$len; $i++) {
        if ($i>0) {
            $colTxt .=', ';
        }
        $colTxt .= $cols[$i];
        
        // Different if 5 params or 2:

        if ($paramCount==2) {
            if ($cols[$i]==$col) {
                $valArray[] = $data;
            } else {
                $valArray[] = "-";
            }
        } else {
            $valArray[] = $params[$i];
        }
        

    } // for loop

    //------------------------------------------------------

    // prepare and bind

    $preparedStatement = $databaseConnection->prepare("INSERT INTO customers ($colTxt) VALUES (?, ?, ?, ?, ?)");
    $preparedStatement->bind_param("sssss", ...$valArray);

    $preparedStatement->execute();

    $newID = $databaseConnection->insert_id;         // NB: GET NEW RECORD ID

    $preparedStatement->close();
    $databaseConnection->close();

    return $newID;

} // END: function customerAdd()


function customerEdit($params) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;

    $paramCount = count($params);

    if ($paramCount>=6) {                 // if there are 6 cols add all these
        $sql="UPDATE customers SET Title=?, Firstnames=?,Surname=?, Mobile=?,Email=? WHERE customerID = ?;";
    } else if ($paramCount == 3) {
        $cols = array('Title', 'Firstnames', 'Surname', 'Mobile', 'Email');
        $id = $params[0];
        $col = $params[1]; 
        $data = $params[2];
        if ($data=='' || $data==0) {
            $data = '-';
        }

        if (!in_array($col, $cols)) {           // Safeguard against injection
            die("Invalid column name");
        }
        $sql="UPDATE customers SET $col=? WHERE customerID = ?;";

    } else {
        //echo ($paramCount . " ");
        die('You need 3 or 6 parameters: [ColumnName Data] or [Title Firstnames Surname Mobile Email]. Enclose strings in double quotes');
    }


    $cols = array('Title', 'Firstnames', 'Surname', 'Mobile', 'Email');

    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    if ($databaseConnection->connect_error) {
            die($databaseConnection->connect_error);
    }

    //echo "col=$col, data=$data<br/>";
    $preparedStatement = $databaseConnection->prepare($sql);
    //echo "SQL: $sql \n\n";

    if ($paramCount == 3) {
        $preparedStatement->bind_param("si", $data ,$id);
    } else {
        $preparedStatement->bind_param("sssssi", $params[1], $params[2], $params[3], $params[4], $params[5], $params[0]);
    }


    $preparedStatement->execute();

    $preparedStatement->close();
    $databaseConnection->close();

} // END: function customerEdit()

function getRemainingParams($fromThis) {
    global $argc, $argv;
    
    $params = [];
    if (php_sapi_name() === 'cli') { // from command line arguments

        $i = $fromThis;

        while ($i < $argc) {
            $params[] = $argv[$i]; 
            $i++;
        }
    }
    return $params;
    
}

function createAddressTable() {

    goSQL(
    "DROP TABLE IF EXISTS ADDRESS;
    CREATE TABLE ADDRESS 
        (addressID INT NOT NULL AUTO_INCREMENT,
        Line1 VARCHAR(30) NOT NULL,
        Line2 VARCHAR(30),
        Town VARCHAR(30) NOT NULL, 
        County VARCHAR(30) NOT NULL,
        Eircode VARCHAR(10),
        addressType INT NOT NULL DEFAULT 0,
        CONSTRAINT address_PKEY PRIMARY KEY (addressID)
        );"
    );
} // END: function createAddressTable()

function createCustomerAddressRelationTable() {
    goSql(
        "DROP TABLE IF EXISTS CUSTOMERADDRESS;
        CREATE TABLE CUSTOMERADDRESS
        (customerID INT NOT NULL,
        addressID INT NOT NULL,
        TimeCreated timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customerID) REFERENCES customers (customerID) ON DELETE CASCADE,
        FOREIGN KEY (addressID) REFERENCES address (addressID) ON DELETE CASCADE
        );"     
    );        

} // END: createCustomerAddressRelationTable()


function createRandomRecord() {
    $params = createRandomUser();
    $newID = customerAdd($params);
    printCLO("New customer record created, ID: $newID\n");
    if (rand(0,2)>0) {
        $newAddressID = addressAOU(true, [$newID,...createRandomAddress(),2]);
        $newAddressID2 = addressAOU(true, [$newID,...createRandomAddress(),3]);
        printCLO("New customer address records created, (Shipping) ID: $newAddressID, (Billing) ID: $newAddressID2\n");
    } else {
        $newAddressID = addressAOU(true, [$newID,...createRandomAddress(),1]);
        printCLO("New customer address record created, ID: $newAddressID\n");
    }

} // END: function createRandomRecord()


function dumpTable($tableName) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;
    
    $databaseConnection = mysqli_connect($SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME);

    $data = $databaseConnection->query("SELECT * FROM `$tableName`;");
    $fieldCount = $data->field_count;
    $rowCount = $databaseConnection->affected_rows;
    $showCreateTable = $databaseConnection->query("SHOW CREATE TABLE `$tableName`;")->fetch_row()[1]; 
    $returnSQL = "DROP TABLE IF EXISTS `$tableName`; " . $showCreateTable.";\n\n";

    while($row = $data->fetch_row())	{
          $returnSQL .= "INSERT INTO ".$tableName." VALUES (";
            
            for($j=0; $j<$fieldCount; $j++) {
                $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                if (isset($row[$j])){$returnSQL .= '"'.$row[$j].'"' ;
                }  else {$returnSQL .= '""';}
         	   if ($j<($fieldCount-1)) {
                    $returnSQL.= ',';
                }
            }
            $returnSQL .=");\n";
    }
mysqli_close($databaseConnection);
return $returnSQL;
}

function createCustomerTable() {
    goSql(
        "DROP TABLE IF EXISTS CUSTOMERS;
       CREATE TABLE CUSTOMERS 
       (customerID INT NOT NULL AUTO_INCREMENT,
       Title VARCHAR(10),
       Firstnames VARCHAR(30) NOT NULL,
       Surname VARCHAR(30) NOT NULL,
       Mobile VARCHAR(20) NOT NULL,
       Email VARCHAR(50) NOT NULL,
       CONSTRAINT customer_PKEY PRIMARY KEY (customerID) )"
       );
}

function deleteUserMatching3or4($params) {


    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME;


    $paramCount = count($params);
    if ($paramCount<3 || $paramCount>4) {
        die("You need 3 or 4 parameters for this command (you have $paramCount). E.g.: Delete mail Phone FirstName Surname\nUse double quotes to hold strings containing spaces and special charachters.");
    }
    
    $email = $params[0];
    $mobile = $params[1];
    $firstNames = $params[2];
    if ($paramCount==4) {
        $sc1 = " AND customers.Surname='$params[3]'";
        $sc2 = " AND Surname='$params[3]'";
    } else {
        $sc1 = "";
        $sc2 = "";    
    }
    
    $sql = "DELETE address, customeraddress FROM address
    INNER JOIN customeraddress ON (address.addressID=customeraddress.addressID)
    INNER JOIN customers ON (customeraddress.customerID=customers.customerID)
    WHERE customers.Email='$email' AND customers.Mobile='$mobile' AND customers.Firstnames='$firstNames'$sc1;
    DELETE FROM customers WHERE Email='$email' AND Mobile='$mobile' AND Firstnames='$firstNames'$sc2;";
    echo "$sql\n\n";
    $rVal = goSql($sql);
    echo $rVal;
}



function goFn($fn, $fn2, $searchColumn, $searchWord) {

    global $SERVER, $USER_NAME, $PASSWORD, $DATABASE_NAME, $IN_CONSOLE, $FULL_TABLE_SQL;

    global $argc, $argv, $eol;

    if ($fn=='start') {                 // CREATE THE DB & REQUIRED TABLES
        createDB($DATABASE_NAME);
        createCustomerTable();
        createAddressTable();
        createCustomerAddressRelationTable();
    } else if ($fn=='delete') {                 // (iv) - Delete matching email phone and name
        
        $params = getRemainingParams(2);
        deleteUserMatching3or4($params);

    } else if ($fn=='retrieve' || $fn=='search') {                 // (ii) - retrieve users matching a name
            $params = getRemainingParams(2);
            $paramCount = count($params);
            if ($paramCount<1) {
                die("You need at least one parameter, EG: 'retrieve Bob'\n");
            }
            if ($params[0]=='like' && $paramCount>1) {
                array_shift($params);
                echo searchName(false,$params);
            } else {
//                echo showUsers("SELECT * FROM CUSTOMERS where Firstnames='$params[0]';",true);
                echo searchName(true,$params);
            }
    } else if ($fn=='drop' && ($fn2 == 'database')) {                 // CREATE THE DB & REQUIRED TABLES
        goSQL("DROP DATABASE IF EXISTS `USERS`;");
        echo "Entire 'users' database deleted!";
    } else if ( ($fn=='4' || $fn=='create')                          
             || ($fn=='crud' && (($fn2 == 'c') || ($fn2 == 'create'))) ) { 
    
    if ($fn2=='customer') {                        // create a customer [Title Firstnames Surnames Mobile Email]
           $params = getRemainingParams(3);
            $paramCount = count($params);
            // must have 5 parameters

            if ($paramCount!==5) {
                echo("You need 5 parameters after 'create customer' (you have $paramCount).\n");
                echo("EG: 'create customer Title Firstnames Surnames Mobile Email'\n");
                echo("Enclose strings with spaces in quotes.\n");
                die("");
            }

           // validate the title is it: Mx, Ms, Mr, Mrs, Miss, Dr or Other (specify).
           // (The spec is abiguous since "Other (specify)" indicates any alternative)
           // In the second part I allow alternatives, here I will validate strictly and ignore the 'other'
           
           if (!in_array(strtolower($params[0]), ["mx","ms","mr","mrs","miss","dr"])) {
               echo "Title field ('$params[0]') must be one of the following: Mx, Ms, Mr, Mrs, Miss or Dr\n";
               echo "NOTE:  The spec is abiguous since 'Other (specify)' indicates any alternative)\n";
               echo "In the second part I allow alternatives, here I will validate strictly and ignore the 'other'";
               die("");
           }
           $tempFields = ['Firstnames', 'Surnames', 'Mobile', 'Email'];
           for ($i=1; $i<5; $i++) {
                if ($params[$i]=="") {
                    echo("Cannot leave field [".$tempFields[$i-1]."] blank. This is a required field.");
                    die('');
                }
           }
            $newID = customerAdd($params);
            printCLO("New customer record created, ID: $newID\n");
        
    } else if ($fn2=='address') {                        // create an address
                //        [CustomerID, line1, line2, town, county, eircode, addressType (1=Shipping and Billing; 2=Shipping; 3=Billing, 0=other)];
        $params = getRemainingParams(3);
        $paramCount = count($params);
        // must have 5 parameters

        if ($paramCount!==7) {
            echo("You need 7 parameters after 'create customer' (you have $paramCount).\n");
            echo("EG: 'create address CustomerID line1 line2 Town County Eircode addressType' (1=Shipping and Billing; 2=Shipping; 3=Billing, 0=other)'\n");
            echo("Enclose strings with spaces in quotes.\n");
            die("");
        }


        $tempFields = ['CustomerID', 'line1', 'line2', 'Town', 'County', 'Eircode', 'addressType'];
        $req =        [true        , true   , false  , true  , true    , false    , true];
        for ($i=0; $i<5; $i++) {
             if ($req[$i] && $params[$i]=="") {
                 echo("Cannot leave field [".$tempFields[$i]."] blank. This is a required field.");
                 die('');
             }
        }
        if ($params[6]<0 || $params[6]>3) {
            die("AddresType must be a number 0-3. 0- 1=Shipping & Billing, 2=Shipping, 3=Billing, 0=Other\n");
        }
        $newAddressID = addressAOU(true, $params);
        printCLO("New customer address records created, (Shipping) ID: $newAddressID.\n");

//            printCLO("New u")


         } else {                                            // (ia) - CREATE A RANDOM RECORD
            if ($fn2>0) {
                if ($fn2>1000) {
                    die("Enter a number (1-1000). Max 1000 records at a time.");
                }
                $n = $fn2; 
            } else {
                $n = 1;
            }
            for ($i=0; $i<$n; $i++) {
                createRandomRecord();
                printCLO("-------------\n");
            }
        }

    if (!$IN_CONSOLE) {
        showFullTable();
    }

}  else if ($fn=='update') {  //                           UPDATE USER
    // ################################################################################################
    $params = getRemainingParams(2);
    $paramCount = count($params);

    if ($paramCount == 1) { // all random
        $randUser = createRandomUser();
        $randAddress = createRandomAddress();

        $sql = "UPDATE Customers SET Mobile = '$randUser[3]', Email='$randUser[4]', Title='$randUser[0]' WHERE customerID = $params[0]; ";
        echo "SQL: ".$sql."\n";
        echo goSql($sql);

        $sql = "UPDATE address
                    INNER JOIN customeraddress ON address.addressID = customeraddress.addressID
                    INNER JOIN customers ON (customeraddress.customerID=customers.customerID)
                    SET Line1='$randAddress[0]', Line2='$randAddress[1]', Town='$randAddress[2]',
                         County='$randAddress[3]', Eircode='$randAddress[4]'
                    WHERE customers.customerID=$params[0] LIMIT 1;";

           echo("\n------------------------\n");
           echo($sql."\n");
           echo goSql($sql);
        
    } else {
        echo "You need one parameter - the Customer ID. EG: update 299\n";
    }

}
 // #############################################################################################
else if ($fn=='crud' && (($fn2 == 'r') || ($fn2 == 'retrieve') || ($fn2=='search'))) { 
        echo showUsers('SELECT * FROM CUSTOMERS',true);
//        echo $data;                                                        // #2 - RETRIEVE A RECORD
    } else if ($fn=='crud' && (($fn2 == 'u') || ($fn2 == 'update'))) { // #3 - UPDATE A RECORD

    } else if ($fn=='crud' && (($fn2 == 'd') || ($fn2 == 'delete'))) { // #4 - DELETE A RECORD

    } else if ($fn=='1' || $fn=='create db') {
        $dbName = safeGet('db');                // create DATABASE
        if ($dbName!='')
            createDB($dbName);
    } else if ($fn=='crud' && (($fn2 == 'ca'))) {   // create address - needs paramater
        if ($argc<4) {
            die('need to specify the id of the customer whose address you want to generate');
        } else {
            $customerID = safeGet('id',3,-1);  //CHANGE 178
            $newAddressID = addressAOU(true, [$customerID, ...createRandomAddress(),0]);
            echo "Address $newAddressID created for cusomer $customerID\n\n";
        }

    } else if ($fn=='crud' && (($fn2 == 'ua'))) {
        addressAOU(false, [120,'905 College Rise','Newfoundwell Highway','Droghamabad','Co Loo','MTO 123',1]);
    } else if ($fn=='3') {
        createCustomerTable();
    } else if ($fn=='5') {
            echo showUsers("SELECT * from Customers;",false);
    } else if ($fn=='6') {
        $col='Surname';
        $word='';
        if ($searchColumn!='') {
            $whereCondition = "WHERE ".$searchColumn." LIKE '%".$searchWord."%'";
        } else {
            $whereCondition = "";
        }


        //        echo showUsers2("SELECT * from Customers;",true);
        $sql = "SELECT customers.customerID, customers.Title, customers.Firstnames,
        customers.Surname, customers.Mobile, customers.Email, address.addressID,
        address.Line1, address.Line2, address.Town, address.County, address.Eircode, address.addressType
        FROM customers LEFT JOIN customeraddress ON (customers.customerID = customeraddress.CustomerID)
        LEFT JOIN address ON customeraddress.addressID = address.addressID
        $whereCondition ORDER BY customers.customerID";

        echo showUsers3($sql);

    } else if ($fn=='7' || ($fn=='delete' && ($fn2 == 'customer'))) {
        $id = safeGet('id',3,'');
        $sql = "DELETE address, customeraddress FROM address
        INNER JOIN customeraddress ON (address.addressID=customeraddress.addressID)
        INNER JOIN customers ON (customeraddress.customerID=customers.customerID)
        WHERE customers.customerID=$id;
        DELETE FROM customers WHERE customerID=$id;";
        printCLO($sql);
        goSql($sql);

        showFullTable();

    } else if ($fn=='8') {
        //$params = [safeGet('f0')];
        goSql("INSERT INTO customers 
            (Title,
             Firstnames, 
             Surname,
             Mobile,
             Email
            ) VALUES ('" . safeGet('f0') . "', '" . safeGet('f1') . "', '" . safeGet('f2') . "', '" . safeGet('f3') . "', '" . safeGet('f4') . "');
        ");
        echo showUsers2("SELECT * from Customers;",true);
    } else if ($fn=='9') {
        $id = safeGet('id',3,'');
        $col = safeGet('col',4,'');
        $data = safeGet('data',5,'-'); 
        
        if ($id<0) {            
            $newID = customerAdd([$col, $data]);
            printCLO("New customer record; ID: ");
            echo $newID;
        } else {
            customerEdit([$id, $col, $data]);
        }
    } else if ($fn=='edit' && ($fn2 == 'customer')) { // if there are only 2 extra params (1 ID, 2 column, 3 data)
        $params = getRemainingParams(3);
        customerEdit($params);
    } else if ($fn=='new' && ($fn2 == 'customer')) {
        $params = getRemainingParams(3);
        $newID = customerAdd($params);
        printCLO("New customer record; ID: ");
        echo $newID;
    } else if ($fn=='9_old') {

        $id = safeGet('id');
        if ($id<0) {
//            echo("ADD RECORD<br/>");
            $col = safeGet('col');
            $data = safeGet('data');
            if ($data=='' || $data==0) {
                $data = '-';
            }
            $cols = array('Title', 'Firstnames', 'Surname', 'Mobile', 'Email');
            $sql = "INSERT INTO customers (";
            $colTxt = '';
            $valTxt = '';
            $len = sizeof($cols);
            for ($i=0; $i<$len; $i++) {
                if ($i>0) {
                    $colTxt .=', ';
                    $valTxt .=', ';
                }
                $colTxt .= $cols[$i];
                if ($cols[$i]==$col) {
                    $valTxt .= "'".$data."'";
                } else {
                    $valTxt .= "'-'";
                }
            }
            $sql .= $colTxt.") VALUES (".$valTxt.");";
            echo("SQL: " . $sql ."<br/>");
            //            $sql = "INSERT INTO customers (".safeGet('col').") VALUES ('".safeGet('data')."');";
 
            $newID = queryDB2($sql);

            echo($newID);
        } else {
            $sql = "UPDATE Customers SET ".safeGet('col')." = '".safeGet('data')."' WHERE customerID = ".safeGet('id')."; ";
            goSql($sql);
            //echo showUsers2("SELECT * from Customers;",true);
        }


    } else if ($fn=='10') {
        
        createAddressTable();

    } else if ($fn=='11') {
        goSql(
            "INSERT INTO ADDRESS (Line1, Line2, Town, County, Eircode)
            VALUES ('Somewhere', 'Someplace', 'SomeTown', 'SomeCounty', 'AB1 3AA'
            );"
        );        
    } else if ($fn=='12') {

        createCustomerAddressRelationTable();
    
    } else if ($fn=='13') {
        goSql(
            "INSERT INTO CUSTOMERADDRESS (customerID, addressID)
            VALUES (22,1
            );"
        );        
    } else if ($fn=='14') {
        goSql(
            "SELECT * FROM customers LEFT JOIN customeraddress
            ON (customers.customerID = customeraddress.CustomerID)
            LEFT JOIN address
            ON customeraddress.addressID = address.addressID;"
        );        
    } else if ($fn=='15' || ($fn=='add' && ($fn2 == 'address'))) {
        addressAddOrUpdate(true);
    } else if ($fn=='17' || ($fn=='update' && ($fn2 == 'address'))) {
        addressAddOrUpdate(false);
    } else if ($fn=='16' || ($fn=='delete' && ($fn2 == 'address') ) ) {
            $id = safeGet('id',3,'');
            $sql = "DELETE FROM ADDRESS WHERE addressID=$id;";
            printCLO($sql."\n\n");
            goSql($sql);
            updateShippingAddresses(4);        
            showFullTable();
            
    } else if ($fn=='22' || $fn=='dump2') { // dump Database

        // must be in this order in order to re-create properly
        $dumpSQL = "DROP DATABASE IF EXISTS `USERS`;\n\n CREATE DATABASE `USERS`;\n\n USE `USERS`;\n\n";
        $dumpSQL .= dumpTable('customers');
        $dumpSQL .= dumpTable('address');
        $dumpSQL .= dumpTable('customeraddress');

        $fileName = 'Assignment-04_'.date('H-i-s').'_'.date('d-m-Y').'.sql';
        ob_get_clean();
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($dumpSQL, '8bit'): strlen($dumpSQL)) );
        header("Content-disposition: attachment; filename=\"".$fileName."\""); 
        echo $dumpSQL; exit;

//        echo $dumpSQL;

} else if ($fn=='dump') { // dump Database - save file

    // must be in this order in order to re-create properly
    $dumpSQL = "DROP DATABASE IF EXISTS `USERS`;\n\n CREATE DATABASE `USERS`;\n\n USE `USERS`;\n\n";
    $dumpSQL .= dumpTable('customers');
    $dumpSQL .= dumpTable('address');
    $dumpSQL .= dumpTable('customeraddress');

    $fileName = 'Assignment-04_'.date('H-i-s').'_'.date('d-m-Y').'.sql';
 //   ob_get_clean();
 //   header('Content-Type: application/octet-stream');
 //   header("Content-Transfer-Encoding: Binary");
 //   header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($dumpSQL, '8bit'): strlen($dumpSQL)) );
 //   header("Content-disposition: attachment; filename=\"".$fileName."\""); 
 //   echo $dumpSQL; exit;

    file_put_contents($fileName, $dumpSQL, FILE_APPEND | LOCK_EX);
    printCLO("Database dumped as '$fileName'.\n\n");

//        echo $dumpSQL;

} else if ($fn=='load') {

    $loadedSQL = file_get_contents('php://input');
    goSql_INIT($loadedSQL);
    showFullTable();

} else if ($fn=='dump4') {
    header("Content-type: text/csv");
    header("Cache-Control: no-store, no-cache");
    header('Content-Disposition: attachment; filename="content.csv"');

    $sql = "SELECT customers.customerID, customers.Title, customers.Firstnames,
    customers.Surname, customers.Mobile, customers.Email, address.addressID,
    address.Line1, address.Line2, address.Town, address.County, address.Eircode, address.addressType
    FROM customers LEFT JOIN customeraddress ON (customers.customerID = customeraddress.CustomerID)
    LEFT JOIN address ON customeraddress.addressID = address.addressID
    ORDER BY customers.customerID";

    echo showUsers3($sql); //fopen('php://output','w');

    } else {
        echo ("WELCOME TO ASSIGNMENT 4: \n\n");
        echo ("Commands: (e.g.: 'php assignment-04.php dump') \n\n");
        echo ("1. 'start' - Sets up the database\n");
        echo ("2. 'drop database' - delete the entire database\n");
        echo ("3. 'dump' - dump the database to an SQL file \n");
        echo ("4. 'create' [number of addresses] - Create random customer and address(es). Optional parameter for multiples (1-1000). \n");
        echo (" - 'create customer [Title Firstnames Surnames Mobile Email \n");
        echo (" - 'create address [CustomerID, line1, line2, town, county, eircode, addressType (1=Shipping and Billing; 2=Shipping; 3=Billing, 0=other)];\n");
        echo ("5. 'retrieve' or 'search' (ii) - Retrieve records of customers with given Firstname (and optional Surname) \n");
        echo (" -- 'retrieve like' or 'search like' - as above, but using LIKE operator. \n");
        echo ("7. 'update' [customerID] (iii) - updates Phone Email and Title and their first address.");
        echo ("-- Parameters: 1 [id] - random values added.\n");
        echo ("6. 'delete' - (iv) Deletes all records matching a combination of Email Phone and Name:\n");
        echo (" --- can have 3 or 4 parameters: Email Phone Firstnames [Surnames]");
        echo "args:\n\n";   

    }


        
    
} // END: function goFn($fn)

global $argc, $argv;


$fn = strtolower(safeGet('fn',1,''));
$fn2 = strtolower(safeGet('table',2,''));

$searchWord = safeGet('searchWord',100,'');
$searchColumn = safeGet('searchColumn',100,'');
//echo "DATA: ".$searchWord . ' ' . $searchColumn . " ; ";

goFn($fn,$fn2, $searchColumn,$searchWord);
//global $returnData;
//$returnData = '{Title,FirstNames,Surname,Mobile,Email,HomeAddress,ShippingAddress}';
//createDB(DB);
/*
goSql(
    "DROP TABLE IF EXISTS TOWN;
    CREATE TABLE TOWN 
        (townID INT NOT NULL AUTO_INCREMENT,
        town VARCHAR(30),
        CONSTRAINT town_PKEY PRIMARY KEY (townID)
        )"
);
goSql(
    "DROP TABLE IF EXISTS COUNTY;
    CREATE TABLE COUNTY 
        (countyID INT NOT NULL AUTO_INCREMENT,
        County VARCHAR(30),
        CONSTRAINT county_PKEY PRIMARY KEY (countyID)
        )"
);
goSql(
    "DROP TABLE IF EXISTS ADDRESS;
    CREATE TABLE ADDRESS 
        (addressID INT NOT NULL AUTO_INCREMENT,
        Line1 VARCHAR(30) NOT NULL,
        Line2 VARCHAR(30),
        Town INT NOT NULL REFERENCES TOWN(townID),
        County INT NOT NULL REFERENCES COUNTY(countyID),
        Eircode VARCHAR(10),
         addressType INT NOT NULL DEFAULT 0,
        CONSTRAINT address_PKEY PRIMARY KEY (addressID)
        )"
);

goSql(
    "DROP TABLE IF EXISTS USERS;
    CREATE TABLE USERS 
        (userid INT NOT NULL AUTO_INCREMENT,
        Title VARCHAR(10),
        Firstnames VARCHAR(30) NOT NULL,
        Surname VARCHAR(30) NOT NULL,
        Mobile VARCHAR(20) NOT NULL,
        Email VARCHAR(50),
        CONSTRAINT customer_PKEY PRIMARY KEY (userid)
        )"
);
*/
// add address


//$cityToFind = safeGet('town');


//goSQL();
//echo("Return Data:- $returnData");



?>