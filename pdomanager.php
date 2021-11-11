<?php

function redirecterrpage()
{
    ?>
    <script>
        window.location='http-error-200';
    </script>
    <?php
}

if (!extension_loaded("pdo")) header("location:http-error-200"); // check if extension loaded
class DB {
        public $conn = NULL;
        public $err = NULL;
        public $dbname;
        public $hostname;
        public $dbusername;
        public $dbpassword;
        public $numofrows=0;
        public $affectedrows=0;
        public $portnumber=3306;
        public $dbtype="mysql";
        public $lastinsertedid=0;
    
    # @object, The PDO object
        private $pdo;

        # @object, PDO statement object
        private $sQuery;

        # @array,  The database settings
        private $settings;

        # @bool ,  Connected to the database
        private $bConnected = false;

        # @array, The parameters of the SQL query
        private $parameters;
    
        /**
         * Connects to SQL Server
         * 
         * @return      true/false
         */
   
   function __construct($dbname=DBNAME,$hostname=HOST,$uname=USERNAME,$pwd=PASSWORD,$type=TYPE,$pno=PORTNUMBER){
    
        $this->parameters = array();
        
        $this->dbname=$dbname;
        $this->hostname=$hostname;
        $this->dbusername=$uname;
        $this->dbpassword=$pwd;
        $this->dbtype=$type;
        $this->portnumber=$pno;
   }
     
   public function connect()
        {
            
                // set OPTIONS
            $options = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            
            );
            
        try {
                
                        switch (strtolower($this->dbtype)) {
                                case "mysql":
                                        if (!extension_loaded("pdo_mysql")) header("location:http-error-200"); // check if extension loaded
                                        
                                        
                                        if(empty($this->portnumber)) $this->portnumber=3306;
                                           
                                            $this->conn = new PDO("mysql:host=".$this->hostname.";port=".$this->portnumber.";dbname=".$this->dbname, $this->dbusername, $this->dbpassword,array(PDO::ATTR_PERSISTENT => true,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
                                            //$this->conn = new PDO("mysql:dbname=advanced_crm;port=3306;host=localhost", "root", "root");
                                            //$this->conn->setAttribute( PDO::ATTR_PERSISTENT, true );
                                            $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
                                            $this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false ); 
                                            $this->conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

                                            $this->bConnected = true;
                                        break;
                                case "sqlsrv":
                                        
                                        $this->conn = new PDO("sqlsrv:server=".$this->hostname.";Database=".$this->dbname, $this->dbusername, $this->dbpassword,array('MultipleActiveResultSets'=>true ));
                                        $this->conn->setAttribute( PDO::ATTR_PERSISTENT, true );
                                        $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                                        $this->conn->setAttribute(PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8);
                                           
                                        $this->bConnected = true;
                                        
                                        break;
                                case "dblib":
                                        
                                        $this->conn = new PDO("dblib:host=".$this->hostname.";dbname=".$this->dbname.";charset=UTF-16", $this->dbusername, $this->dbpassword ,array('MultipleActiveResultSets'=>true ));
                                        $this->bConnected = true;
                                        
                                        break;    
                                case "pgsql":
                                        if (!extension_loaded("pdo_pgsql")) die("Missing pdo_pgsql PHP extension."); // check if extension loaded
                                        if(empty($this->portnumber)) $this->portnumber=5432;
                                        $this->conn = new PDO("pgsql:host=".$this->hostname.";port=".$this->portnumber.";dbname=".$this->dbname, $this->dbusername, $this->dbpassword);
                                        $this->bConnected = true;
                                        break;
                                        
                                case "sqlite":
                                        if (!extension_loaded("pdo_sqlite")) header("location:http-error-200"); // check if extension loaded     
                                        $this->rootdir = dirname(__FILE__)."/";
                                        $this->dbfile  = $this->rootdir."database/".$this->dbname.".db";
                                        $this->created = false;
                                        $this->bConnected = true;
                                        if(!file_exists($this->rootdir."database/")) {
                                                // try to create the database folder
                                                @mkdir($this->rootdir."database", 0777, true);                  
                                        }
                                        if(!file_exists($this->dbfile)) {
                                                // try to create the database file
                                                if(@touch($this->dbfile))
                                                        $this->created = true;
                                        }
                                        if(!file_exists($this->rootdir."database/.htaccess")) {
                                                // try to create the htaccess file
                                                @file_put_contents($this->rootdir."database/.htaccess", "Deny from all");
                                        }
                                        if(file_exists($this->dbfile) && is_readable($this->dbfile) && is_writable($this->dbfile)) {
                                                $this->conn = new PDO("sqlite:".$this->dbfile, $this->dbusername, $this->dbpassword);
                                                //if($this->created)
                                                        //$this->conn->exec(file_get_contents($this->rootdir."sql/".$this->conf["server"]."/sqlite.sql"));
                                        } else {
                                                $this->err = "cant create the sqlite database";
                                                $_SESSION['pdoerror']=$this->err;
                                                redirecterrpage();
                                                //return false;
                                        }
                                        break;
                                        
                                default:
                                        $this->err = "not supported database type";
                                        //return false;
                                        $_SESSION['pdoerror']=$this->err;
                                        redirecterrpage();
                                        break;
                        }
                        return true;
                } catch (PDOException $e) {
                    
                        $this->err = $e->getMessage();
                        
                        //$this->ExceptionLog($e->getMessage());
                        $_SESSION['pdoerror']=$e->getMessage();
                        redirecterrpage();
                        exit();
                }
                
                
        }
        
        /**
	* @brief transaction, execute the transactional operations.
	* @param string $type shortcut for trasaction to execute. i.e: B=begin, C=commit & R=rollback. */
	public function transaction($type){
		$this->err = "";
		if($this->conn!=null){
			try{
				if($type=="B")
					$this->conn->beginTransaction();
				elseif($type=="C")
					$this->conn->commit();
				elseif($type=="R")
					$this->conn->rollBack();
				else{
					$this->err = "Error: The passed param is wrong! just allow [B=begin, C=commit or R=rollback]";
					return false;
				}
			}catch(PDOException $e){
				$this->err = "Error: ". $e->getMessage();
                $_SESSION['pdoerror']=$this->err;
                redirecterrpage();
				return false;
			}
		}else{
			$this->err = "Error: Connection to database lost.";
            $_SESSION['pdoerror']=$this->err;
            redirecterrpage();
			return false;
		}
	}

        
        /*
         * close the database connection
         */
        public function close () {
            
                $this->conn = NULL;
        }
    
    /**
        *       Every method which needs to execute a SQL query uses this method.
        *       
        *       1. If not connected, connect to the database.
        *       2. Prepare Query.
        *       3. Parameterize Query.
        *       4. Execute Query.       
        *       5. On exception : Write Exception into the log + SQL query.
        *       6. Reset the Parameters.
        */      
                private function Init($query,$parameters = "")
                {
                # Connect to database
                if(!$this->bConnected) { 
                    
                    $this->Connect(); 
               
                }
                    try {
                        
                                # Prepare query
                                $this->sQuery = $this->conn->prepare($query);
                                
                                if(is_array($parameters) && !empty($parameters))
                                {
                                    $this->sQuery->execute($parameters);
                                }
                                else
                                    $this->sQuery->execute();
                
                    }
                    catch(PDOException $e)
                    {
                        # Write into log and display Exception
                        //$this->ExceptionLog($e->getMessage(), $query );
                        $_SESSION['pdoerror']=$e->getMessage()." in ".$query ;
                        redirecterrpage();
                        exit();
                    }

                    # Reset the parameters
                    $this->parameters = array();
                }
                
       /**
        *       @void 
        *
        *       Add the parameter to the parameter array
        *       @param string $para  
        *       @param string $value 
        */      
                public function bind($para, $value)
                {       
                        $this->parameters[sizeof($this->parameters)] = ":" . $para . "\x7F" . $value;
                }
       /**
        *       @void
        *       
        *       Add more parameters to the parameter array
        *       @param array $parray
        */      
                public function bindMore($parray)
                {
                        if(empty($this->parameters) && is_array($parray)) {
                                $columns = array_keys($parray);
                                foreach($columns as $i => &$column)     {
                                        $this->bind($column, $parray[$column]);
                                }
                        }
                }
        /**
         * Sends a database query to SQL server.
         *
         * @param       string          $this->res              a database query
         * @param       array           $this->bind             
         * @return      integer                                 id of the query result
         */
        public function query($query,$params = array(), $fetchmode = PDO::FETCH_ASSOC)
        {
            try
            {
                $query = trim($query);
                $this->Init($query,$params);
               # The first six letters of the sql statement -> insert, select, etc...
                $statement = strtolower(substr($query, 0 , 6));
                
                $this->affectedrows=$this->sQuery->rowCount();
                
                return $this->sQuery;   
            }
            catch(Exception $e)
            {
                //die($e->getMessage()." unable to execute query");
                $_SESSION['pdoerror']=$e->getMessage();
                redirecterrpage();
            }
            
        }
        
        
        public function PrepareExactQuery($query,$params = array())
        {
            foreach ($params as $key=>$value)
            {
                $query=str_ireplace("=".$key,"='".$value."'",$query);
                //echo "<br>Key => ".$key."  value => ".$value;
            }
            
            return $query;  
            
        }
        
        
        public function PrepareInsertExactQuery($query,$params = array())
        {
            foreach ($params as $key=>$value)
            {
                $query=str_ireplace($key,"'".$value."'",$query);
                //echo "<br>Key => ".$key."  value => ".$value;
            }
            
            return $query;  
            
        }
        /**
         * Gets a row from SQL database query result.
         *
         * @param       string          $this->res              a database query
         * @return                              array           a row from result
         */
        
    public function fetch_array($resultset=null, $fetchmode = PDO::FETCH_ASSOC)
    {
        if($this->affectedrows>0 && $resultset!=null)
        {
            $row= $resultset->fetchAll($fetchmode);
            
            for($i=0;$i<count($row);$i++)
            {
                    foreach($row[$i] as $key=>$value)
                    {
                            $row[$i][$key]=trim(stripcslashes($value));
                    }
            }
        }
        else
        {
            $row=array();
        }
        return $row;
        
    }
    
    public function fetch_array_query($query, $params = array(),$fetchmode = PDO::FETCH_ASSOC)
    {
        try
        {
            $query = trim($query);
            $this->Init($query,$params);
           
            $this->affectedrows=$this->sQuery->rowCount();
            
            if($this->affectedrows>0 && $this->sQuery!=null)
            {
                $row= $this->sQuery->fetchAll($fetchmode);
                
                for($i=0;$i<count($row);$i++)
                {
                        foreach($row[$i] as $key=>$value)
                        {
                                $row[$i][$key]=trim(stripcslashes($value));
                        }
                }
            }
            else
            {
                $row=array();
            }
            return $row;
        }
        catch(Exception $e)
        {
            //die($e->getMessage()." unable to execute query");
            $_SESSION['pdoerror']=$e->getMessage();
            redirecterrpage();
        }
    }

    public function fetch_num_of_rows ($res,$fetchmode = PDO::FETCH_ASSOC) {
                
        try
        {
            # Affected Rows?
                        $this->affectedrows=$res->rowCount(); // 1
                        $row = $res->fetchAll($fetchmode);
                        
                        for($i=0;$i<count($row);$i++)
                        {
                                foreach($row[$i] as $key=>$value)
                                {
                                        $row[$i][$key]=trim(stripcslashes($value));
                                }
                        }
                        return count($row);
         }
         catch (PDOException $e) {
                        $this->err = $e->getMessage();
           //$this->ExceptionLog($e->getMessage());
            $_SESSION['pdoerror']=$e->getMessage();
            redirecterrpage();
                        exit();
                } 
        }
        
        /**
         * return the last insert id
         */
        public function last_id () {
            
                return $this->conn->lastInsertId();
                
                //SELECT @@IDENTITY as newinsertedid;
        
        }
        
        /**
         * Returns SQL error number for last error.
         *
         * @return      integer         MySQL error number
         */
        public function error () {
                return $this->err;
        }
    
    public function insertData($tablename,$fieldarray=array(),$columnname='pmunique_id')
    {
        $insertquery=$this->createInsertquerybasedonparams($tablename,$fieldarray,$columnname);
        
        $bindarray=$this->getBindarray($fieldarray);
        
        $exactquery=$this->PrepareInsertExactQuery($insertquery,$bindarray);
        //$starttime=microtime(true);
        $this->query($insertquery,$bindarray); 
		//$queryexecutiontime=(microtime(true)-$starttime);
        $this->lastinsertedid=$this->last_id();  
                
    }
    
    public function updateData($tablename,$fieldarray=array(),$conditionarray=array())
    {
        $updatequery=$this->createUpdatequerybasedonparams($tablename,$fieldarray,$conditionarray);
        $resultarray = array_merge($fieldarray, $conditionarray);
        $bindarray=$this->getBindarray($resultarray);
        
        $exactquery=$this->PrepareExactQuery($updatequery,$bindarray);
        $this->query($updatequery,$bindarray);
		
    }
    
    
    public function deleteData($tablename,$conditionarray=array())
    {
        $deletequery=$this->createDeletebasedonparams($tablename,$conditionarray);
        $bindarray=$this->getBindarray($conditionarray);
        $this->query($deletequery,$bindarray);
		        
    }
    
    public function createSelectquerybasedonparams($tables="users",$fieldarray=array(),$fields='*')
    {
        $sqlquery="select ".$fields." from ".$tables;
        if(count($fieldarray)>0)
        {
            $sqlquery.=" where ";
            foreach($fieldarray as $key=>$value)
                {
                        $sqlquery.=$key."=:".$key." and ";
                }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-4);
        }
        return $sqlquery;
    }
    
    public function createInsertquerybasedonparams($tables="users",$fieldarray=array(),$columnname='pmunique_id')
    {
        $sqlquery="insert into ".$tables."(";
        if(count($fieldarray)>0)
        {
            foreach($fieldarray as $key=>$value)
                {
                        $sqlquery.=$key.",";
                }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-1);
            $sqlquery.=")";
        }
        $sqlquery.=" values(";
        if(count($fieldarray)>0)
        {
            foreach($fieldarray as $key=>$value)
            {
                $sqlquery.=":".$key.",";
            }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-1);
            $sqlquery.=")";
        }
        return $sqlquery;
                
    }
    
    public function getBindarray($fieldarray=array())
    {
        $bindarray=array();
        foreach($fieldarray as $key=>$value)
        {
                $bindarray[":".$key]=$value;
        }
        return $bindarray;
    }
    
    public function createUpdatequerybasedonparams($tables="users",$fieldarray=array(),$conditionarray=array())
    {
        $sqlquery="update ".$tables." set ";
        if(count($fieldarray)>0)
        {
            foreach($fieldarray as $key=>$value)
                {
                        $sqlquery.=$key."=:".$key.",";
                }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-1);
        }
        if(count($conditionarray)>0)
        {
            $sqlquery.=" where ";
            foreach($conditionarray as $key=>$value)
                {
                        $sqlquery.=$key."=:".$key." and ";
                }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-4);
        }
        
        return $sqlquery;
        
    }
    
    public function createDeletebasedonparams($tables="users",$conditionarray=array())
    {
        $sqlquery="delete from ".$tables." ";
        if(count($conditionarray)>0)
        {
            $sqlquery.=" where ";
            foreach($conditionarray as $key=>$value)
                {
                        $sqlquery.=$key."=:".$key." and ";
                }
            $sqlquery=substr($sqlquery,0,strlen($sqlquery)-4);
        }
        
        return $sqlquery;
    }
    
    /**
        *       Returns an array which represents a column from the result set 
        *
        *       @param  string $query
        *       @param  array  $params
        *       @return array
        */      
                public function column($query,$params = null)
                {
                        $this->Init($query,$params);
                        $Columns = $this->sQuery->fetchAll(PDO::FETCH_NUM);             
                        
                        $column = null;

                        foreach($Columns as $cells) {
                                $column[] = $cells[0];
                        }

                        return $column;
                        
                }       
       /**
        *       Returns an array which represents a row from the result set 
        *
        *       @param  string $query
        *       @param  array  $params
        *       @param  int    $fetchmode
        *       @return array
        */      
                public function row($query,$params = null,$fetchmode = PDO::FETCH_ASSOC)
                {                               
                        $this->Init($query,$params);
                        return $this->sQuery->fetch($fetchmode);                        
                }
       /**
        *       Returns the value of one single field/column
        *
        *       @param  string $query
        *       @param  array  $params
        *       @return string
        */      
                public function single($query,$params = null)
                {
                        $this->Init($query,$params);
                        return $this->sQuery->fetchColumn();
                }
       /**      
        * Writes the log and returns the exception
        *
        * @param  string $message
        * @param  string $sql
        * @return string
        */
        private function ExceptionLog($message , $sql = "")
        {
                $exception  = 'Unhandled Exception. <br />';
                $exception .= $message;
                $exception .= "<br /> You can find the error back in the log.";

                if(!empty($sql)) {
                        # Add the Raw SQL to the Log
                        $message .= "\r\nRaw SQL : "  . $sql;
                }
                        # Write into log
                        $this->log->write($message);

                return $exception;
        }  
        
        public function __destruct()
        {
            // Disconnect from DB
            $this->conn = null;
        } 
}
?>
