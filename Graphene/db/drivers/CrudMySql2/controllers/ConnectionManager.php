<?php
namespace Graphene\db\drivers\mysql;
use \Log;
use Graphene\controllers\exceptions\GraphException;
use Graphene\controllers\exceptions\ExceptionCodes;
use \PDO;
use \PDOStatement;

class ConnectionManager {
    public function __construct($configManager){
        $this->configManager = $configManager;
    }

    /**
     * @return PDO|null
     */
    public function getConnection(){
        if($this->connection === null)$this->connection =$this->connect();
        return $this->connection;
    }
    public function getPrefix(){
        return $this->configManager->getPrefix();
    }
    private function connect(){
        try {
            $this->connection = new PDO(
                'mysql:host=' . $this->configManager->getUrl() . ';dbname=' . $this->configManager->getDbName(),
                $this->configManager->getUserName(),
                $this->configManager->getPassword()
            );
            Log::debug('mySql connection success as: '.$this->configManager->getUserName());
            return $this->connection;
        } catch (Exception $e) {
            Log::debug('mySql connection fails: '.$e->getMessage());
            $this->connection = null;
            $ex = new GraphException('Error on mysql connection: ' . $e->getMessage(), ExceptionCodes::DRIVER_CONNECTION, 500);
            ExceptionConverter::throwException($ex);
        }
    }

    /**
     * @param $query
     * @return array
     * @throws GraphException
     */
    public function query($query){
        $this->queryCounter++;
        //echo $query."\n";
        $res = $this->connection->query($query);
        $err = $this->connection->errorInfo();
        if (strcasecmp($err[0], '00000') != 0) {
            Log::err("\n".'MySql exception: ' . $err[2] . "\n" . 'Query no' . $this->queryCounter . "\n__________\n" . $query."\n");
            ExceptionConverter::throwException($err);
            //throw new GraphException('mySql exception on query no.' . $this->queryCounter . ', see log for more info', ExceptionCodes::DRIVER_CREATE, 500);
        }else if ($res instanceof PDOStatement) {
            $results = array();
            $i = 0;
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                foreach ($row as $rk => $rv) {$results[$i][$rk] = $rv;}
                $i ++;
            }
            return $results;
        }else{
            Log::err('Unexpected result for query '.$this->queryCounter. "\n__________\n" . $query."\n");
            throw new GraphException('mySql exception on query no.' . $this->queryCounter . ', see log for more info', ExceptionCodes::DRIVER_CREATE, 500);
        }
    }
    private $queryCounter = 0;
    /**
     * @var PDO
     */
    private $connection    = null;
    /**
     * @var ConfigManager
     */
    private $configManager = null;
}