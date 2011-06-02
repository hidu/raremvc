<?php
/**
 *使用pdo 进行session存储 
 * @author duwei
 *
 */
class rDbSession{
    
    /**
     * @var PDO
     */
    protected  $pdo=null;
    protected $options=null;
    protected  static $instance=null;
    protected  $lifeTime=1440;//session有效期
    
    protected  function __construct($pdo,$options){
        $this->pdo=$pdo;
        if(!$options)$options=array();
        $options = array_merge(array(
          'db_table'    => 'session',
          'db_id_col'   => 'sess_id',
          'db_data_col' => 'sess_data',
          'db_time_col' => 'sess_time',
          'lifetime'  => ini_get('session.gc_maxlifetime'),
        ), $options);
       $this->options=$options;
       $this->lifeTime=$options['lifetime'];
       
       session_set_save_handler(array($this, 'open'),
                                array($this, 'close'),
                                array($this, 'read'),
                                array($this, 'write'),
                                array($this, 'destroy'),
                                array($this, 'gc'));

        session_start();
    }
    
    /**
     * @param PDO $pdo
     * @param array $options
     * @return rDbSession
     */
    public static function getInstance($pdo,$options=null){
        if(self::$instance==null){
            self::$instance=new self($pdo, $options);
        }
        return self::$instance;
    }

    public function close(){
        return true;
    }

    public function open($path = null, $name = null){
        if (is_null($this->pdo)){
            throw new Exception('没有数据库链接！');
        }
        return true;
     }
     

    public function gc($lifetime=0){
        $db_table    = $this->options['db_table'];
        $db_time_col = $this->options['db_time_col'];
        if($lifetime==0)$lifetime=$this->lifeTime;
        
        $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_time_col.' < '.(time() - $lifetime);
        $this->pdo->query($sql);
        return true;
    }
    
   public function destroy($id) {
      $db_table  = $this->options['db_table'];
      $db_id_col = $this->options['db_id_col'];

      $sql = 'DELETE FROM '.$db_table.' WHERE '.$db_id_col.'= ?';

      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(1, $id, PDO::PARAM_STR);
      $stmt->execute();
    return true;
  }
  

    public function read($id){
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        $sql = 'SELECT '.$db_data_col.' FROM '.$db_table.' WHERE '.$db_id_col.'=?';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_STR, 255);

        $stmt->execute();
        $sessionRows = $stmt->fetchAll(PDO::FETCH_NUM);
        if (count($sessionRows) == 1){
            return $sessionRows[0][0];
        }else{
            $sql = 'INSERT INTO '.$db_table.'('.$db_id_col.', '.$db_data_col.', '.$db_time_col.') VALUES (?, ?, ?)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $id, PDO::PARAM_STR);
        $stmt->bindValue(2, '', PDO::PARAM_STR);
        $stmt->bindValue(3, time(), PDO::PARAM_INT);
        $stmt->execute();

        return '';
        }
    }

    public function write($id, $data){
        $db_table    = $this->options['db_table'];
        $db_data_col = $this->options['db_data_col'];
        $db_id_col   = $this->options['db_id_col'];
        $db_time_col = $this->options['db_time_col'];

        $sql = 'UPDATE '.$db_table.' SET '.$db_data_col.' = ?, '.$db_time_col.' = '.time().' WHERE '.$db_id_col.'= ?';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(1, $data, PDO::PARAM_STR);
        $stmt->bindParam(2, $id, PDO::PARAM_STR);
        $stmt->execute();
        return true;
    }
    
}