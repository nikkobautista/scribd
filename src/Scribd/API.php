<?php
namespace Scribd;

use \Exception;

class API
{
    public $api_key;
    public $secret;
    private $url;
    public $session_key;
    public $my_user_id;
    private $error;

    /**
     * Scribd\API constructor
     * @param string $api_key API key
     * @param string $secret  API secret
     */
    public function __construct($api_key, $secret)
    {
        $this->api_key = $api_key;
        $this->secret = $secret;
        $this->url = "http://api.scribd.com/api?api_key=" . $api_key;
    }


    /**
    * Upload a document from a file
    * @param string $file : relative path to file
    * @param string $doc_type : PDF, DOC, TXT, PPT, etc.
    * @param string $access : public or private. Default is Public.
    * @param int $rev_id : id of file to modify
    * @return array containing doc_id, access_key, and secret_password if nessesary.
    */
    public function upload($file, $doc_type = null, $access = null, $rev_id = null)
    {
        $method = "docs.upload";
        $params['doc_type'] = $doc_type;
        $params['access'] = $access;
        $params['file'] = "@".$file;

        $result = $this->postRequest($method, $params);
        return $result;
    }


    /**
    * Upload a document from a Url
    * @param string $url : absolute URL of file 
    * @param string $doc_type : PDF, DOC, TXT, PPT, etc.
    * @param string $access : public or private. Default is Public.
    * @return array containing doc_id, access_key, and secret_password if nessesary.
    */
    public function uploadFromUrl($url, $doc_type = null, $access = null, $rev_id = null)
    {
        $method = "docs.uploadFromUrl";
        $params['url'] = $url;
        $params['access'] = $access;
        $params['rev_id'] = $rev_id;
        $params['doc_type'] = $doc_type;

        $data_array = $this->postRequest($method, $params);
        return $data_array;
    }

    /**
    * Get a list of the current users files
    * @return array containing doc_id, title, description, access_key, and conversion_status for all documents
    */
    public function getList($params = array())
    {
        $method = "docs.getList";

        $result = $this->postRequest($method, $params);
        return $result['resultset'];
    }

    /**
    * Get the current conversion status of a document
    * @param int $doc_id : document id
    * @return string containing DISPLAYABLE", "DONE", "ERROR", or "PROCESSING" for the current document.
    */
    public function getConversionStatus($doc_id)
    {
        $method = "docs.getConversionStatus";
        $params['doc_id'] = $doc_id;

        $result = $this->postRequest($method, $params);
        return $result['conversion_status'];
    }

    /**
    * Get settings of a document
    * @return array containing doc_id, title , description , access, tags, show_ads, license, access_key, secret_password
    */
    public function getSettings($doc_id)
    {
        $method = "docs.getSettings";
        $params['doc_id'] = $doc_id;

        $result = $this->postRequest($method, $params);
        return $result;
    }

    /**
    * Change settings of a document
    * @param array $doc_ids : document id
    * @param string $title : title of document
    * @param string $description : description of document
    * @param string $access : private, or public
    * @param string $license : "by", "by-nc", "by-nc-nd", "by-nc-sa", "by-nd", "by-sa", "c" or "pd"
    * @param string $access : private, or public
    * @param array $show_ads : default, true, or false
    * @param array $tags : list of tags
    * @return string containing DISPLAYABLE", "DONE", "ERROR", or "PROCESSING" for the current document.
    */
    public function changeSettings($doc_ids, $title = null, $description = null, $access = null, $license = null, $parental_advisory = null, $show_ads = null, $tags = null)
    {
        $method = "docs.changeSettings";
        $params['doc_ids'] = $doc_ids;
        $params['title'] = $title;
        $params['description'] = $description;
        $params['access'] = $access;
        $params['license'] = $license;
        $params['show_ads'] = $show_ads;
        $params['tags'] = $tags;

        $result = $this->postRequest($method, $params);
        return $result;
    }

    /**
    * Delete a document
    * @param int $doc_id : document id
    * @return 1 on success;
    */
    public function delete($doc_id)
    {
        $method = "docs.delete";
        $params['doc_id'] = $doc_id;

        $result = $this->postRequest($method, $params);
        return $result;
    }

    /**
    * Search the Scribd database
    * @param string $query : search query
    * @param int $num_results : number of results to return (10 default, 1000 max)
    * @param int $num_start : number to start from
    * @param string $scope : scope of search, "all" or "user"
    * @param integer $category_id : Restricts search results to only documents in a certain category.
    * @param string $language : Restricts search results to only documents in the specified language (in ISO 639-1 format).
    * @param boolean $simple : This option specifies whether or not to allow advanced search queries. 
    *                          When set to false, the API search behaves the same as the search on Scribd.com. 
    *                          When set to true, the API search allows advanced queries that contain filters 
    *                          such as title:"A Tale of Two Cities". Set to "true" by default.
    *                          
    * @return array of results, each of which contain doc_id, secret password, access_key, title, and description
    */
    public function search($query, $num_results = null, $num_start = null, $scope = null, $category_id = null, $language = null, $simple = true)
    {
        $method = "docs.search";
        $params['query'] = $query;
        $params['num_results'] = $num_results;
        $params['num_start'] = $num_start;
        $params['scope'] = $scope;
        $params['simple'] = $simple;
        
        if (!is_null($category_id)) {
            $params['category_id'] = $category_id;
        }

        if (!is_null($language)) {
            $params['language'] = $language;
        }

        $result = $this->postRequest($method, $params);

        return $result['result_set'];
    }

    /**
    * Login as a user
    * @param string $username : username of user to log in
    * @param string $password : password of user to log in
    * @return array containing session_key, name, username, and user_id of the user;
    */
    public function login($username, $password)
    {
        $method = "user.login";
        $params['username'] = $username;
        $params['password'] = $password;

        $result = $this->postRequest($method, $params);
        $this->session_key = $result['session_key'];
        return $result;
    }

    /**
    * Sign up a new user
    * @param string $username : username of user to create
    * @param string $password : password of user to create
    * @param string $email : email address of user
    * @param string $name : name of user
    * @return array containing session_key, name, username, and user_id of the user;
    */
    public function signup($username, $password, $email, $name = null)
    {
        $method = "user.signup";
        $params['username'] = $username;
        $params['password'] = $password;
        $params['name'] = $name;
        $params['email'] = $email;

        $result = $this->postRequest($method, $params);
        return $result;
    }

    /**
     * Sends a request
     * @param  string $method type of request
     * @param  array $params  array of parameters
     * @return array          result set
     */
    private function postRequest($method, $params)
    {
        $params['method'] = $method;
        $params['session_key'] = $this->session_key;
        $params['my_user_id'] = $this->my_user_id;


        $post_params = array();

        foreach ($params as $key => &$val) {
            if(!empty($val)) {
                if (is_array($val)) $val = implode(',', $val);
                if($key != 'file' && substr($val, 0, 1) == "@"){
                    $val = chr(32).$val;
                }
                $post_params[$key] = $val;
            }
        }

        $secret = $this->secret;
        $post_params['api_sig'] = $this->generate_sig($params, $secret);
        $request_url = $this->url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url );       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params );
        $xml = curl_exec( $ch );
        $result = simplexml_load_string($xml); 
        curl_close($ch);

        if($result['stat'] == 'fail') {
            //This is ineffecient.
            $error_array = (array)$result;
            $error_array = (array)$error_array;
            $error_array = (array)$error_array['error'];
            $error_array = $error_array['@attributes'];
            $this->error = $error_array['code'];

            throw new Exception($error_array['message'], $error_array['code']);

            return 0;
        }

        if($result['stat'] == "ok") {
            //This is shifty. Works currently though.
            $result = $this->convert_simplexml_to_array($result);
            if(gettype($result) == 'string' && urlencode($result) == "%0A%0A" && $this->error == 0) {
                $result = "1";
                return $result;
            } else {
                return $result;
            }
        }
    }

    /**
     * Generate the signature for a request
     * @param  array  $params_array  params array
     * @param  string $secret        client secret
     * @return string                signature
     */
    public static function generate_sig($params_array, $secret)
    {
        $str = '';

        ksort($params_array);
        // Note: make sure that the signature parameter is not already included in
        //       $params_array.
        foreach ($params_array as $k=>$v) {
        $str .= $k . $v;
        }
        $str = $secret . $str;

        return md5($str);
    }

    /**
     * Convert XML to an array
     * @param  SimpleXMLObject $sxml XML object to convert
     * @return array                 Converter XML
     */
    public static function convert_simplexml_to_array($sxml)
    {
        $arr = array();
        if ($sxml) {
            foreach ($sxml as $k => $v) {
                if(isset($arr[$k]) && $arr[$k]) {
                    $arr[$k." ".(count($arr) + 1)] = self::convert_simplexml_to_array($v);
                } else {
                    $arr[$k] = self::convert_simplexml_to_array($v);
                }
            }
        }

        if (sizeof($arr) > 0) {
            return $arr;
        } else {
            return (string)$sxml;
        }
    }
}
?>
