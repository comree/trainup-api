
<?php
    require_once '../includes/Db_operations.php';
    $response = array();
    if($_SERVER['REQUEST_METHOD']=='POST')
        {
            if(isset($_POST['email']) and isset($_POST['password']))
            {
                $db = new Db_operations();
                if($db->userLogin($_POST['email'],$_POST['password']))
                    {
                       $user = $db->getUserByEmail($_POST['email']);
                        $response['error'] = false;
                        $response['id'] = $user['id'];
                        $response['username'] = $user['username'];
                        $response['email'] = $user['email'];
                        $response['weight'] = $user['weight'];
                    }
                else
                    {
                        $response['error'] = true;
                        $response['message'] = "Invalid email or password";
                    }
            }
            else
            {
                $response['error'] = true;
                $response['message'] = "Fill in all fields";
               
            }
        }
         echo json_encode($response);
?>