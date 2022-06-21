<?php 
    class AddressBook
    {
        protected $db;
        protected $data=array();
        public function __construct($db){
               $this->db = $db->getDb(); 
        }

        /*  create new entry into addressbook */
        public function create(){
            if(($res = $this->checkContentType()) === "ok"){
                $json_status = $this->checkInput();
                if(empty($this->data)){
                    return $json_status;
                }

                $qry = 'insert into addresses(name,address,city,phone,business_phone,email,messanger,social_profile,website) values (:name,:address,:city,:phone,:business_phone,:email,:messanger,:social_profile,:website)';

                $sql_statement = $this->db->prepare($qry);
                $params = [
                    'name' => $this->data['name'], 
                    'address' => $this->data['address'], 
                    'city' => $this->data['city'], 
                    'phone' => $this->data['phone'], 
                    'business_phone' => $this->data['business'], 
                    'email' => $this->data['email'], 
                    'messanger' => $this->data['messanger'], 
                    'social_profile' => $this->data['social'], 
                    'website' => $this->data['website']
                ];
                if($sql_statement->execute($params))
                    return "succesfull";
                else{
                    return(json_encode([
                        'value' => 0,
                        'error' => 'DB operation error. Data can not be entered',
                        'data' => null,
                    ]));
                       
                }
            }
            else{
            return $res;            
            }
        }
     
    /* pdate addressbook entry    */
    public function update(){
                        
            if(($res = $this->checkContentType()) === "ok"){
                $json_status = $this->checkInput();
                if(empty($this->data)){
                    return $json_status;
                }
                /* validate id exists in DB */
                if(!$this->checkIfCorectId($this->data['id'])){
                    return(json_encode([
                        'value' => 0,
                        'error' => 'No valid ID in DB',
                        'data' => null,
                    ]));   
                };
                $qry = 'update  addresses set name=:name,address=:address,city=:city,phone=:phone,business_phone=:business_phone,email=:email,messanger=:messanger,social_profile=:social_profile,website=:website where id=:id';

                $sql_statement = $this->db->prepare($qry);
                $params = [
                    'id' => (int)$this->data['id'],
                    'name' => $this->data['name'], 
                    'address' => $this->data['address'], 
                    'city' => $this->data['city'], 
                    'phone' => $this->data['phone'], 
                    'business_phone' => $this->data['business'], 
                    'email' => $this->data['email'], 
                    'messanger' => $this->data['messanger'], 
                    'social_profile' => $this->data['social'], 
                    'website' => $this->data['website']
                ];              
            if($sql_statement->execute($params))
            return "succesfull";
        else{
            return(json_encode([
                'value' => 0,
                'error' => 'DB operation error. Data can not be updated',
                'data' => null,
            ]));
        }
        }
            else{
            return $res;            
            }             
        }


        /*  GET Method params   */
        public function get(){
            $sql_statement = $this->db->query('SELECT * FROM addresses order by name');
            $res = $sql_statement->fetchAll(PDO::FETCH_ASSOC);
                if(!empty($res)){
                    return json_encode($res);
                }
                else{
                    return(json_encode([
                        'value' => 0,
                        'error' => 'No data in DB',
                        'data' => null,
                    ]));            
                }
        }

        /* remove entry from addressbook */
        public function delete(){
              if(isset($_GET['del_id']) AND !empty($_GET['del_id'])){
                     /* validate id exists in DB */
                     if(!$this->checkIfCorectId($_GET['del_id'])){
                        return(json_encode([
                            'value' => 0,
                            'error' => 'No valid ID in DB',
                            'data' => null,
                        ]));   
                    };
                    $id = (int)$_GET['del_id'];
                    $qry = 'delete from addresses where id =:id';
                    $sql_statement = $this->db->prepare($qry);
                    $sql_statement->bindParam(':id',$id);
                    if($sql_statement->execute()){
                        return json_encode($this->get());
                    }
                }

            else{
                return(json_encode([
                    'value' => 0,
                    'error' => 'Entry can not be found and deleted',
                    'data' => null,
                ]));            
            }
        }


        /* search DB for contacts */
        public function search(){
            if(isset($_GET['search']) AND !empty($_GET['search'])){
                $search = $this->sanitizeSearchInput($_GET['search']);
                $search_string = $search.'%';
                $qry = 'select * from addresses where name like :search_string';
                $sql_statement = $this->db->prepare($qry);
                $sql_statement->bindValue(':search_string',$search_string,PDO::PARAM_STR);
                $sql_statement->execute();
                $res = $sql_statement->fetchAll(PDO::FETCH_ASSOC);
                if(!empty($res)){
                    return json_encode($res);
                }        
            }
            else{
                return(json_encode([
                    'value' => 0,
                    'error' => 'No data in DB',
                    'data' => null,
                ]));            
            }
        }

        public function checkContentType(){
            /*  php8+ */
            $content_type = trim($_SERVER["CONTENT_TYPE"] ?? ''); 
            
            /* Send error to Fetch API, if unexpected content type */
            if ($content_type !== "application/json;charset=UTF-8")
                return(json_encode([
                    'value' => 0,
                    'error' => 'Content-Type is not set as "application/json"',
                    'data' => null,
                ]));
            else return "ok";


        }
        
        /*  Section for sanitize and  filter input data */

        public function checkInput(){
           

                /* Receive the RAW post data. */
                $cnt = trim(file_get_contents("php://input"));

                /* $decoded can be used the same as you would use $_POST in $.ajax */
                $decoded_data = json_decode($cnt, true);

                /* Send error to Fetch API, if JSON is broken */
                if(!is_array($decoded_data))
                return(json_encode([
                    'value' => 0,
                    'error' => 'JSON bad formatted',
                    'data' => null,
                ]));

                /* filter data */ 
                $this->dataFilter($decoded_data);
                
                $this->data = $decoded_data;
        }

        public function dataFilter($decoded_data){
            foreach($decoded_data as $data){
                trim($data);
                filter_var($data,FILTER_SANITIZE_STRING);
            }

            $decoded_data['email'] = substr($decoded_data['email'],0,128);
            $decoded_data['name'] = substr($decoded_data['name'],0,64);
            $decoded_data['social_profile'] = substr($decoded_data['social_profile'],0,256);
            $decoded_data['website'] = substr($decoded_data['website'],0,128);
            $decoded_data['address'] = substr($decoded_data['address'],0,128);
            $decoded_data['city'] = substr($decoded_data['city'],0,32);
            $decoded_data['phone'] = substr($decoded_data['phone'],0,20);
            $decoded_data['business_phone'] = substr($decoded_data['business_phone'],0,20);
            $decoded_data['messanger'] = substr($decoded_data['messanger'],0,128);
            $decoded_data['social_profile'] = substr($decoded_data['social_profile'],0,256);
            $decoded_data['website'] = substr($decoded_data['website'],0,128);

            filter_var($decoded_data['email'],FILTER_SANITIZE_EMAIL);
            filter_var($decoded_data['social_profile'],FILTER_SANITIZE_URL);
            filter_var($decoded_data['website'],FILTER_SANITIZE_URL);
        }

        public function checkIfCorectId($id){
            $id = (int)$id;
            /* get all id's to insure received id exists. */
            $qry = 'select id from addresses';
            $sql_statement = $this->db->query($qry);
            $ids = $sql_statement->fetchAll();
            if(!empty($ids)){
               foreach($ids as $key => $value){
                if($value['id'] === $id)
                    return true;
               }
            }
               return false;         
        }

        public function sanitizeSearchInput($search){
            $search = trim($search);
            /* search by name so max length is name field in DB */
            $search = substr($search,0,64);
            return htmlspecialchars($search);
        }


        /*  EXPORT ADDRESSBOOK */
        public function exportAdressBook(){
            $sql_statement = $this->db->query('SELECT * FROM addresses order by name');
            $res = $sql_statement->fetchAll(PDO::FETCH_ASSOC);
            //$addressbook_file = fopen("addressbook.csv","w");
            header('Content-Type: text/csv'); 
            header('Content-Disposition: attachment; filename="addressbook.csv";'); 
            $addressbook_file = fopen('php://output', 'w'); 
                if(!empty($res)){
                     $attributes = array('ID', 'NAME', 'ADDRESS', 'CITY', 'PHONE', 'BUSINESS_PHONE', 'EMAIL', 'MESSANGER', 'SOCIAL PROFILE', 'WEB SITE'); 
                     fputcsv($addressbook_file, $attributes);
                    foreach($res as $k => $value){
                     $data = array($value['id'],$value['name'],$value['address'],$value['city'],$value['phone'],$value['business_phone'],$value['email'],$value['messanger'],$value['social_profile'],$value['website']);
                     fputcsv($addressbook_file, $data, ",");
                     }
                    fclose($addressbook_file);
                }
                else{
                    return(json_encode([
                        'value' => 0,
                        'error' => 'No data in DB',
                        'data' => null,
                    ]));            
                }            
        }
    }
?>