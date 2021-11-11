<?php
include_once "dbconfig.php";

Class USERAPI
{
    //private variables can be used in class functions only
    private $postdata=array();
    private $returnarray=array();
    
    public $dbobj;
    
    function __construct() {
        //$this work for class object
        foreach($_POST as $key=>$value)
        {
            $_POST[$key]=$value;
        }
        $this->returnarray=array();
        $this->performAction();
    }
    
    private function performAction()
    {
        extract($_POST);
        if($a>=1 && $a<=6)
        {
            switch($a)
            {
                case 1:
                    //Send email invitations to signup. Mandatory field :- emailid
                    if(isset($emailid) && $emailid!='')
                    {
                        $emailid=trim($emailid);
                        //Currently we are using php mail function. We can use otherls or REST APIs to send emails
                        $from = 'sender@jodhraj.com'; 
                        $fromName = 'Jodhraj Kumawat'; 
                         
                        $subject = "You are invited to signup account on our platform."; 
                         
                        $htmlContent = ' 
                            <html> 
                            <head> 
                                <title>Welcome to Our Portal</title> 
                            </head> 
                            <body> 
                                <h1>You can create your account by clicking on below link.</h1> 
                                <a href="http://appapis.iamstmartin.com/expinc/laravalapi/signup?em'.base64_encode($emailid).'">http://appapis.iamstmartin.com/expinc/laravalapi/signup.html?em'.base64_encode($emailid).'</a>
                            </body> 
                            </html>'; 
                         
                        // Set content-type header for sending HTML email 
                        $headers = "MIME-Version: 1.0" . "\r\n"; 
                        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
                         
                        // Additional headers 
                        $headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
                         
                        // Send email 
                        if(mail($emailid, $subject, $htmlContent, $headers)){ 
                            $this->returnarray['replyCode']="success";
                            $this->returnarray['replyCode']="Email has sent successfully."; 
                        }else{ 
                            $this->returnarray['replyCode']="error";
                            $this->returnarray['replyCode']="Email sending failed."; 
                        }
                        echo json_encode($this->returnarray);
                    }
                    else
                    {
                        $this->returnarray['replyCode']="error";
                        $this->returnarray['replyMsg']="Please send all parameters.";
                        echo json_encode($this->returnarray);
                    }
                    break;
                case 2:
                    //User Signup  Mandatory fields :-  emailid,  username & password
                    if(isset($emailid) && $emailid!='' && isset($username) && $username!='' && isset($password) && $password!='')
                    {   
                        $dbobj=new DB(DBNAME,HOST,USERNAME,PASS,TYPE,PORTNUMBER);
                        $dbobj->connect();
					       
                        if(!(strlen($username)>=4 && strlen($username)<=20))
                        {
                            $this->returnarray['replyCode']="error";
                            $this->returnarray['replyCode']="User name should contain minimum 4 characters and maximum 20 characters.";
                        }
                        else
                        {
                            $conditionarr=array(":e"=>$emailid,":u"=>$username);
                            $select="select id from apiusers where email=:e or user_name=:u ";
                            $records=$dbobj->fetch_array_query($select,$conditionarr);
                            if(count($records)>0)
                            {
                                $this->returnarray['replyCode']="error";
                                $this->returnarray['replyCode']="User already exists with same username or email id.";
                            }
                            else
                            {
                                $confirmpin=rand(100000,999999);
                                
                                $uhpwd=sha1(md5($password).$password);
                                
                                $insertarr=array();
                                $insertarr['user_name']=$username;
                                $insertarr['email']=$emailid;
                                $insertarr['password']=$uhpwd;
                                $insertarr['user_role']="user"; 
                                $insertarr['isconfirmed']=0;
                                $insertarr['confirmpin']=$confirmpin;
                                $insertarr['created_at']=date("Y-m-d H:i:s");
                                $dbobj->insertData('apiusers',$insertarr);
                                $lastuserid=$dbobj->lastinsertedid;
                                if($lastuserid>0)
                                {
                                    $from = 'sender@jodhraj.com'; 
                                    $fromName = 'Jodhraj Kumawat'; 
                                     
                                    $subject = "Account confirmation Email.."; 
                                     
                                    $htmlContent = ' 
                                        <html> 
                                        <head> 
                                            <title>Welcome to Our Portal</title> 
                                        </head> 
                                        <body> 
                                            <h1>Your account created successfully, Please verify your account by clicking on below link.</h1> 
                                            <a href="http://appapis.iamstmartin.com/expinc/laravalapi/verify?em'.base64_encode($emailid).'&p='.base64_encode($confirmpin).'">http://appapis.iamstmartin.com/expinc/laravalapi/verify?em'.base64_encode($emailid).'&p='.base64_encode($confirmpin).'</a>
                                        </body> 
                                        </html>'; 
                                     
                                    // Set content-type header for sending HTML email 
                                    $headers = "MIME-Version: 1.0" . "\r\n"; 
                                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
                                     
                                    // Additional headers 
                                    $headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
                                     
                                    // Send email 
                                    if(mail($emailid, $subject, $htmlContent, $headers)){ 
                                        $this->returnarray['replyCode']="success";
                                        $this->returnarray['replyCode']="Confirmation Email has been sent successfully. Please verify account by clicking on verification link in email."; 
                                    }else{ 
                                        $dbobj->deleteData("apiusers",array("id"=>$lastuserid));
                                        $this->returnarray['replyCode']="error";
                                        $this->returnarray['replyCode']="There is an issue in signup. Please try again later.";
                                    }
                                }
                                else
                                {
                                    $this->returnarray['replyCode']="error";
                                    $this->returnarray['replyCode']="There is an issue in sign up. Please contact to administrator.";
                                }
                            }
                        }
                        
                        $dbobj->close();
                        $dbobj=null;
                        
                        echo json_encode($this->returnarray);
                        
                    }
                    else
                    {
                        $this->returnarray['replyCode']="error";
                        $this->returnarray['replyMsg']="Please send all parameters.";
                        echo json_encode($this->returnarray);
                    }
                    break;
                case 3:
                    //Verify account. Required fields :- Email id,   PIN
                    if(isset($emailid) && $emailid!='' && isset($pin) && $pin!='')
                    {   
                        $dbobj=new DB(DBNAME,HOST,USERNAME,PASS,TYPE,PORTNUMBER);
                        $dbobj->connect();
					       
                        $conditionarr=array(":e"=>$emailid);
                        $select="select confirmpin,id from apiusers where email=:e";
                        $records=$dbobj->fetch_array_query($select,$conditionarr);
                        if(count($records)>0)
                        {
                            if($records[0]['confirmpin']==$pin)
                            {
                                $updatearr=array();
                                $updatearr['confirmpin']=null;
                                $updatearr['isconfirmed']=1;
                                $updatearr['registered_at']=date("Y-m-d H:i:s");
                                $updatearr['updated_at']=date("Y-m-d H:i:s");
                                
                                $dbobj->updateData("apiusers",$updatearr,array("id"=>$records[0]['id']));
                                
                                $this->returnarray['replyCode']="success";
                                $this->returnarray['replyMsg']="Your account verified successfully.";
                            }
                            else
                            {
                                $this->returnarray['replyCode']="error";
                                $this->returnarray['replyMsg']="Please send valid verification PIN.";
                            }
                            echo json_encode($this->returnarray);
                        }
                        else
                        {
                            $this->returnarray['replyCode']="error";
                            $this->returnarray['replyMsg']="***Account does not exists. Please enter valid email id.";
                            echo json_encode($this->returnarray);
                        }
                        $dbobj->close();
                    }
                    else
                    {
                        $this->returnarray['replyCode']="error";
                        $this->returnarray['replyMsg']="Please send all parameters.";
                        echo json_encode($this->returnarray);
                    }
                    break;
                case 4:
                   //Login  Mandatory fiedls: username, password
                    if(isset($username) && $username!='' && isset($password) && $password!='')
                    {
                        $dbobj=new DB(DBNAME,HOST,USERNAME,PASS,TYPE,PORTNUMBER);
                        $dbobj->connect();
                        
                        $conditionarr=array(":u"=>$username);
                        $select="select * from apiusers where user_name=:u ";
                        $records=$dbobj->fetch_array_query($select,$conditionarr);
                        if(count($records)==0)
                        {
                            $msg="*** Please provide valid username.";
                            $msg_type="error";
                        }
                        else
                        {   
                            $uhpwd=sha1(md5($password).$password);
                            if($records[0]['password']==$uhpwd)
                            {
                                $msg="*** User login successful.";
                                $msg_type="success";
                                $this->returnarray['data']=$records[0];
                            }
                            else
                            {
                                $msg="*** Please provide valid password.";
                                $msg_type="error";
                            }
                        }
                        $this->returnarray['replyMsg']=$msg;
                        $this->returnarray['replyCode']=$msg_type;
                        echo json_encode($this->returnarray);
                        
                        $dbobj->close();
                        $dbobj=null;
                        
                    }
                    else
                    {
                        $this->returnarray['replyCode']="error";
                        $this->returnarray['replyMsg']="Please send all parameters.";
                        echo json_encode($this->returnarray);
                    }
                    break;
                case 5:
                    //update user profile Mandatory fields:-  userid, name, avatar
                    if(isset($userid) && $userid>0 && isset($name) && $name!='')
                    {
                        $dbobj=new DB(DBNAME,HOST,USERNAME,PASS,TYPE,PORTNUMBER);
                        $dbobj->connect();
                        
                        $userdetail=$dbobj->fetch_array_query("SELECT * FROM users where id=:uid",array(":uid"=>$userid));
                       
                        if(count($userdetail)==0)
                        {
                            $this->returnarray['replyMsg']="*** Please provide valid user id parameter.";
                            $this->returnarray['replyCode']="error";
                        }
                        else
                        {
                            $nofile=array();
                            $filename=$userid;
                            if(isset($_FILES['file']['name']) && $_FILES['file']['name']!='')
                            {
                                $lastchars=substr($_FILES['file']['name'],strlen($_FILES['file']['name'])-3,3);
                                if($lastchars=="peg")
                                {
                                    $reqidname=$filename.".jpeg";
                                }
                                else
                                {
                                    $reqidname=$filename.".".$lastchars;    
                                }
                                
                                $returnval="";
                                $dirname="photoimages/";
                                if(!file_exists($dirname))
                                {
                                    mkdir($dirname,0777,true);
                                }
                                
                                $target_file = $dirname . basename($reqidname);    
                                
                                $target_file=str_ireplace(" ","",$target_file);
                                $uploadOk = 1;
                                $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                            
                                // Check if file already exists
                                if (file_exists($target_file)) {
                                    unlink($target_file);
                                    $temp = explode(".", $filearr["name"]);
                                    $target_file = $dirname.$userid.'.'. end($temp);
                                }
                                $sourceProperties = getimagesize($_FILES['file']['tmp_name']);
                                // Check file size
                                if ($sourceProperties[0]!=256 && $sourceProperties[1]!=256 ) {
                                    $returnval= "***Sorry, your file dimension should be 256px * 256px.";
                                    $uploadOk = 0;
                                }
                                $filetypesarr=array('png','PNG','JPG','jpg','JPEG','jpeg','bmp');
                                // Allow certain file formats
                                if(!in_array($imageFileType,$filetypesarr)) {
                                    $returnval= "***Sorry, only ".implode(", ",$filetypesarr)." type of file is allowed.";
                                    $uploadOk = 0;
                                }
                                // Check if $uploadOk is set to 0 by an error
                                if ($uploadOk == 1) {
                                    $msg=move_uploaded_file($filearr["tmp_name"], $target_file);
                                    if ($msg) {
                                        $msg="Profile updated successfully.";
                                        $msgtype='success';
                                        $updatearr=array();
                                        $updatearr['name']=$name;
                                        $updatearr['avatar']=$target_file;
                                        $updatearr['updated_at']=date("Y-m-d H:i:s");
                                        
                                        $dbobj->updateData("apiusers",$updatearr,array("id"=>$userid));
                                    } else {
                                        $uploadOk=0;
                                        $msg="***Sorry, there was an error uploading your file.";
                                        $msgtype='error';
                                    }
                                }
                                else
                                {
                                    $msg=$returnval;
                                    $msgtype='error';
                                }
                                
                            }
                            else
                            {
                                $msg="***Please upload image.";
                                $msgtype='error';
                            }
                            
                            $this->returnarray['replyCode']=$msgtype;
                            $this->returnarray['replyMsg']=$msg;
                        }
                        
                        echo json_encode($this->returnarray);
                        
                        $dbobj->close();
                    }
                    else
                    {
                        $this->returnarray['replyCode']="error";
                        $this->returnarray['replyMsg']="Please send all parameters.";
                        echo json_encode($this->returnarray);
                    }
                    break;
                default:
                    $returnarray['replyCode']="error";
                    $returnarray['replyMsg']="Please check for latest API Document.";
                    echo json_encode($returnarray);
                    
            }
        }
        else
        {
            $returnarray['replyCode']="error";
            $returnarray['replyMsg']="Please check for latest API Document";
            echo json_encode($returnarray);
        }
        if(isset($dbobj))
        {
            $dbobj->close();
            unset($dbobj);
        }
        if(isset($_SESSION['eexm']))
        {
            //setcookie("eexam", json_encode($_SESSION['eexm']), time()+60*60*24);
        } 
        die;
    }
    
   function __destruct()
    {
        if(isset($dbobj))
        {
            $dbobj->close();
            unset($dbobj);
        }
    }
        
}
header("Access-Control-Allow-Origin: *");
if(isset($_SERVER['HTTP_ORIGIN']))
{
    $http_origin = $_SERVER['HTTP_ORIGIN'];

    //header("Access-Control-Allow-Origin: *");
    
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');  
header('content-type: application/json; charset=utf-8');

date_default_timezone_set("US/Eastern");

if(!isset($_SESSION['lastrequesttlog']) || (isset($_SESSION['lastrequesttlog']) && (time()-$_SESSION['lastrequesttlog'])>1))
{
    $_SESSION['lastrequesttlog']=time();
    $userObj= new USERAPI(); 
}
else
{
    $returnarray=array();
    $returnarray['replyCode']="error";
    $returnarray['replyMsg']="Please try after 5 seconds.";
    echo json_encode($returnarray);
} 

?>
