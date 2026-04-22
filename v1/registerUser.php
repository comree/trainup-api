<?php
    require_once '../includes/Db_operations.php';
    $response = array();
    if($_SERVER['REQUEST_METHOD']=='POST')
        {
           


            if(isset($_POST['username']) and isset($_POST['email'])and isset($_POST['gender']) 
                and isset($_POST['password'])and isset($_POST['confirm_password'])and isset($_POST['weight']))
            {
                //checke if passwords match
                 $userpassword = $_POST['password'];
                 $confirmPassword = $_POST['confirm_password'];
                 if ($userpassword !== $confirmPassword) {
                 $response['error'] = true;
                 $response['message'] = "Passwords do not match";
                 echo json_encode($response);
                 exit();
            }
               $db = new Db_operations();
               $result = $db->createUser($_POST['username'], $_POST['email'],$_POST['gender'], $_POST['password'],$_POST['weight']);
              if($result == 0)
                {
                $response['error'] = false;
                $response['message'] = "User registered successfully";
                echo json_encode($response);
                }
                elseif($result == 2)
                {
                    $response['error'] = true;
                    $response['message'] = "Some error occurred please try again";
                    echo json_encode($response);
                }
                elseif($result == 1)
                {
                    $response['error'] = true;
                    $response['message'] = "User already registered, please choose another username or email";
                    echo json_encode($response);
                }
            }
            else
            {
                $response['error'] = true;
                $response['message'] = "Required fields are missing";
                echo json_encode($response);
            }

        }
        else
        {
        $response['error'] = true;
        $response['message'] = "Invalid Request";
        echo json_encode($response);
        exit(); 
        }
?>