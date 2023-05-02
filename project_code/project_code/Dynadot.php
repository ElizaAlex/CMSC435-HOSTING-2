<?php
class Registrar_Adapter_Dynadot extends Registrar_AdapterAbstract
{

    /**
     * All of the global config settings needed to do API calls
     */
    public $config = array(
        'key'   => null
    );

    /** 
     * The way this registrar will check if a value exists
     * @param $val 
     * the value we will be checking
     * @return bool 
     * whether or not the value exists
    */
    private function _valueExists($val)
    {
        return isset($val) && !empty($val);
    }

    /**
     * constructor to fill in all of the config items
     * 
     * IMPORTANT: all config items must be filled in with some value
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     */
    public function __construct($options)
    {

        if (!extension_loaded('curl')) {
            throw new Registrar_Exception('CURL extension is not enabled');
        }

        if($this->_valueExists($options['key'])) {
            $this->config['key'] = $options['key'];
            unset($options['key']);
        } else {
            throw new Registrar_Exception('Domain registrar "Dyandot" is not configured properly. Please update configuration parameter "Dynadot key" at "Configuration -> Domain registration".');
        }
  
    }

    /**
     * This defines the user interface on the admin panel for input of config options
     * 
     * IMPORTANT: in the form field all options should match the __construct function
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     */
    public static function getConfig()
    {
         return array(
            'label'     =>  'Manages domains on Dynadot via API.',
            'form'  => array(
                'key' => array('password', array(
                            'label' => 'key',
                            'description'=> 'key'
                        ),
                     ),
            ),
        );
    }



    /**
     * All the top level domains that Dynadot supports
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     */
    public function getTlds()
    {
        return array(
            '.org', '.xyz', '.online', '.co', '.live', '.me', '.com', '.io', '.shop', '.net', '.biz', '.us', '.bond', '.info
            ', '.club', '.design', '.art', '.site', '.store', '.homes', '.space', '.sbs', '.icu', '.click', '.property', '
            .beauty', '.digital', '.cc', '.in', '.co.uk', '.life', '.today', '.tech', '.eu', '.pro', '.fun', '.moe', '
            .global', '.top', '.la', '.im', '.gdn', '.mx', '.tv', '.mobi', '.tel', '.cam', '.one', '.cn', '.blog', '.monster
            ', '.asia', '.vip', '.ws', '.link', '.games', '.work', '.lat', '.website', '.sucks', '.uk', '.sx', '.press', '
            .fund', '.bet', '.sex', '.vc', '.social', '.porn', '.xxx', '.run', '.gold', '.sexy', '.ninja', '.earth',
             '.red', '.name', '.family', '.win', '.news', '.rocks', '.adult', '.vin', '.osaka', '.ca', '.wine', '.de', '
             .cloud', '.rent', '.pub', '.co.in', '.stream', '.mba', '.com.mx', '.com.cn', '.ooo', '.pw', '.be', '.so', '.at'
             , '.ren', '.guru', '.world', '.forsale',  '.pizza', '.group', '.nyc', '.limited', '.network', '.help', '.toys'
             , '.lt', '.sc', '.nl', '.bz', '.pl', '.fm', '.ph', '.lc', '.uno', '.singles', '.graphics', '.diet', '.com.co', 
             '.kaufen', '.business', '.org.uk', '.vision', '.software', '.deals', '.band', '.vegas', '.wang', '.reviews', 
             '.holdings', '.domains', '.discount', '.city', '.market', '.events', '.media',  '.clothing', '.tips', '
             .photography', '.organic', '.mn', '.ag', '.bike', '.plumbing', '.ventures', '.camera', '.equipment', '
             .estate', '.gallery', '.lighting', '.contractors', '.land', '.technology', '.construction', '.directory', '
             .kitchen', '.diamonds', '.enterprises', '.voyage', '.shoes', '.careers', '.photos', '.recipes', '.limo', '.cab'
             , '.company', '.computer', '.center', '.systems', '.academy', '.management', '.menu', '.berlin', '.training', '
             .solutions', '.support', '.builders', '.email', '.education', '.institute', '.repair', '.camp', '.glass', '
             .ruhr', '.ceo', '.solar', '.coffee', '.international', '.house', '.florist', '.holiday', '.marketing', '
             .rich', '.tattoo', '.buzz', '.gift', '.guitars', '.pics', '.photo', '.viajes', '.farm', '.codes', '.onl', '
             .pink', '.shiksha', '.boutique', '.kim', '.blue', '.cheap', '.zone', '.build', '.cool', '.watch', '.kiwi', '
             .agency', '.bargains', '.actor', '.best', '.dance', '.wien', '.wiki', '.works', '.expert', '.luxury', '
             .democrat', '.immobilien', '.futbol', '.moda', '.foundation', '.exposed', '.vacations', '.villas', '.flights',
              '.rentals', '.cruises', '.condos', '.tienda', '.properties', '.maison', '.nagoya', '.productions', '.partners'
              , '.dating', '.bid', '.trade', '.webcam', '.qpon', '.archi', '.consulting', '.cards', '.catering', '.cleaning'
              , '.community', '.parts', '.industries', '.supplies', '.supply', '.tools', '.tokyo', '.christmas', '.koeln', '
              .university', '.taxi', '.career', '.fail', '.vet', '.cricket', '.wtf', '.hosting', '.cymru', '.haus', '.money'
              , '.auction', '.plus', '.soccer', '.claims', '.vodka', '.scot', '.jetzt', '.creditcard', '.pet', '.mortgage', 
              '.rehab', '.bio', '.loan', '.church', '.reisen', '.country', '.tube', '.associates', '.financial', '.horse', 
              '.moscow', '.surgery', '.coupons', '.gifts', '.chat', '.faith', '.sale', '.shopping', '.app', '.host', 
              '.wales', '.tours', '.okinawa', '.black', '.gmbh', '.memorial', '.schule', '.surf', '.gratis', '.bingo', 
              '.accountants', '.taipei', '.furniture', '.dentist', '.capital', '.juegos', '.coach', '.bar', '.ac', 
              '.finance', '.auto', '.hospital', '.style', '.download', '.yokohama', '.immo', '.promo', '.garden', '.party',
               '.cologne', '.miami', '.desi', '.attorney', '.beer', '.eco', '.investments', '.lease', '.direct', '.movie', 
               '.how', '.doctor', '.republican', '.tickets', '.poker', '.ngo', '.sh', '.ltd', '.casino', '.tennis', '.gripe'
               , '.dental', '.rip', '.clinic', '.london', '.science', '.voting', '.fashion', '.accountant', '.energy', 
               '.fans', '.car', '.restaurant', '.show', '.salon', '.dog', '.fish', '.fishing', '.exchange', '.fitness', 
               '.mom', '.pictures', '.audio', '.cx', '.review', '.yoga', '.fyi', '.express', '.loans', '.guide', 
               '.apartments', '.services', '.love', '.school', '.army', '.football', '.cars', '.report', '.reise', '.studio
               ', '.team', '.healthcare', '.golf', '.racing', '.place', '.town', '.cafe', '.flowers', '.blackfriday', 
               '.quebec', '.game', '.tires', '.ski', '.rodeo', '.lgbt', '.date', '.engineer', '.green', '.men', '.navy', 
               '.casa', '.lawyer', '.hiphop', '.wedding', '.credit', '.soy', '.irish', '.video', '.college', '.ink', '.care'
               , '.legal', '.insure', '.engineering', '.sarl', '.airforce', '.hockey', '.delivery', '.cooking', '.fit', 
               '.jewelry', '.rest', '.degree', '.gives', '.lol', '.theater', '.tax', '.cash', '.vote', '.voto', '.boston', 
               '.boats', '.pr', '.it', '.inc', '.autos', '.fan', '.page', '.dev', '.luxe', '.ai', '.charity', '.llc', 
               '.baby', '.travel', '.yachts', '.realestate', '.health', '.lv', '.fo', '.contact', '.lotto', '.gay', '.cyou'
               , '.srl', '.gd', '.gg', '.vg', '.ltda', '.dk', '.je', '.nz', '.skin', '.makeup', '.quest', '.hair', '.cfd
               ', '.basketball', '.tw', '.spa', '.co.za', '.ong');
    }


    /**
     * Checks if a given domain is available
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     * the domain to be checked
     * 
     * @return bool
     *  bool that says whether or not domain is available
     */
    public function isDomainAvailable(Registrar_Domain $domain)
    {
        if(!in_array($domain->getTLD(),$this->getTlds())){
            throw new Registrar_Exception("The TLD ".($domain->getTLD())." is not supported by this domain registrar");
        }
        $cmd = 'search';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array('domain0')
        );

        $apiResult = $this->_callApi($cmd, $params);

        return (bool)((string)($apiResult->SearchResponse->SearchHeader->Available) == 'yes');
        
    }

    /**
     * Checks if a given domain can be transferred
     * 
     * STATUS:
     *  Completed: no
     *  Tested: no
     * 
     * TODO:
     *  - make the process more precise, like checking the date purchased etc. 
     *  to get an accurate result
     * 
     * @param Registrar_Domain $domain
     *  the domain to be checked, 
     * 
     * @return bool
     *  bool that says whether or not domain can be transferred
     */
    public function isDomaincanBeTransferred(Registrar_Domain $domain)
    {
        if(!in_array($domain->getTLD(),$this->getTlds())){
            throw new Registrar_Exception("The TLD ".($domain->getTLD())." is not supported by this domain registrar");
        }
        
        return !$this->isDomainAvailable($domain);
    }

    /**
     * modifies the nameservers of a given domain 
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain for which the name servers will be modified
     * 
     * @return bool
     *  bool that says whether or not the nameservers were updated
     */
    public function modifyNs(Registrar_Domain $domain)
    {
        $cmd = 'set_ns';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array('domain'),
            optional: array(
                'ns0',
                'ns1',
                'ns2',
                'ns3'
            )
        );

        $apiResult = $this->_callApi($cmd,$params);
        return (bool)((string)($apiResult->SetNsHeader->SuccessCode) == '0');
    }

    /**
     * modifies the contact information associated with a domain name
     * this function assumes the contacts were already created in dynadot
     * 
     * STATUS:
     *  Completed: no
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain for which the contact information will be modified
     * 
     * @return bool
     *  bool that says whether or not the contact information was updated
     */
    public function modifyContact(Registrar_Domain $domain)
    {
        // collect contacts, ignoring repeats based on the id
        $contactIds = array();
        foreach($this->contactTypeTranslation as $localContact => $apiContact){
            $contactInfo = $domain->{"get".$localContact}();
            if(!array_key_exists($contactInfo->getId(),$contactIds)){
                $contactIds[$contactInfo->getId()] = $contactInfo;
            }
        }
        
        // do API call to modify contact and collect results
        $isSuccess = true;
        foreach($contactIds as $id => $contactInfo){
            $cmd = 'edit_contact';
            $params = $this->_paramatrizeContactInfo($contactInfo);
            $apiResult = $this->_callApi($cmd,$params);

            $isSuccess = 
                $isSuccess && 
                (bool)((string)($apiResult->EditContactHeader->SuccessCode) == '0');
        }

        return $isSuccess;
    }


    /**
     * Transfers a domain to Dynadot
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain that will be transferred
     * 
     * @return bool
     *  bool that says whether or not the transfer was successful
     */
    public function transferDomain(Registrar_Domain $domain)
    {
        $cmd = 'transfer';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
                'auth'
            ),
            optional: array(
                'registrant_contact',
                'admin_contact',
                'technical_contact',
                'billing_contact'
            )
        );
        $apiResult = $this->_callApi($cmd,$params);

        return (bool)((string)($apiResult->TransferHeader->SuccessCode) == '0');
    }



    public function getNsDetails(Registrar_Domain $domain){

        $cmd = 'get_ns';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
            )
        );
        $apiResult = $this->_callApi($cmd,$params);


        $nameservers = $apiResult->NsContent->Host;
        foreach(array(0,1,2,3) as $i){
            $ns = $nameservers[$i];
            if($this->_valueExists($ns)){
                $domain->{"setNs".($i + 1)}($ns);
            }
        }
        return $domain;
    }
    
    /**
     * gets the Domain for a given domain name
     * 
     * this loads the following data into the Registrar_Domain class:
     *  - domain registration date
     *  - domain expiration date
     *  - if domain privacy is enabled
     *  - domain nameservers
     *  - contact info for the domain name
     *  - EPP for the domain 
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain we are retrieving details for
     * 
     * @return Registrar_Domain
     *  the domain with all of the details updated
     */
    public function getDomainDetails(Registrar_Domain $domain)
    {
        $cmd = 'domain_info';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
            ),
        );
        $apiResult = $this->_callApi($cmd,$params);

        $domainInfo = $apiResult->DomainInfoContent->Domain;

        $domain->setExpirationTime((int)($domainInfo->Expiration))
            ->setRegistrationTime((int)($domainInfo->Registration))
            ->setLocked((bool)($domainInfo->Locked))
            ->setPrivacyEnabled((string)($domainInfo->Privacy) == 'partial' ||(string)($domainInfo->Privacy) == 'full');

        

        $loadedContacts = array();

        // load contact info
        foreach($this->contactTypeTranslation as $localContact => $apiContact){
            //grab the ID and check that it exists
            $id = $domainInfo->Whois->{$apiContact}->ContactId;

            if(!$this->_valueExists($id)){
                continue;
            }

            if(array_key_exists($id,$loadedContacts)){
                $domain->{"set".$localContact}($loadedContacts[$id]);
                continue;
            }

            // do an API call to get information about id
            $cmd = 'get_contact';
            $params = array(
                'contact_id' => $id,
            );
            $apiResult = $this->_callApi($cmd,$params);

            // get the local contact info class, default to registrar,
            // and then to a new class
            $localContactInfo = 
                $domain->{"get".$localContact}()??
                $domain->getContactRegistrar()??
                new Registrar_Domain_Contact();
            
            // fill in info in class
            $localContactInfo = $this->_fillInContactInfo(
                $localContactInfo,
                $apiResult->GetContactContent->GetContact->Contact
            );
            
            $loadedContacts[$id] = $localContactInfo;
            // set contact in domain class
            $domain->{"set".$localContact}($localContactInfo);
        }

        //$domain = $this->getNsDetails($domain);

        return $domain;
    }


    /**
     * does domain deletion for a given domain
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     */
    public function deleteDomain(Registrar_Domain $domain)
    {
        $cmd = 'delete';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array('domain'),
        );

        $apiResult = $this->_callApi($cmd,$params);

        return (bool)((string)($apiResult->DeleteHeader->SuccessCode) == '0');
    }

    
    /**
     * completes domain registration with Dynadot
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain we are registering
     * 
     * 
     * @return bool
     *  whether not the domain was registered
     */
    public function registerDomain(Registrar_Domain $domain)
    {
        $cmd = 'register';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
                'duration'
            ),
            optional: array(
                'registrant_contact',
                'admin_contact',
                'technical_contact',
                'billing_contact',
                'language',
                'premium'
            )
        );
        
        // for some reason Dynadot forces you to use get when registering
        $apiResult = $this->_callApi($cmd,$params, method: 'GET');

        return (bool)((string)($apiResult->RegisterHeader->SuccessCode) == '0');
    }

    /**
     * renews a domain with Dynadot
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain to be renewed
     * 
     * @return bool
     *  whether not the domain was renewed successfully
     */
    public function renewDomain(Registrar_Domain $domain)
    {
        $cmd = 'renew';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
                'duration'
            ),
        );
        
        $apiResult = $this->_callApi($cmd,$params, method: 'GET');
        return (bool)((string)($apiResult->RenewHeader->SuccessCode) == '0');
        
    }

    /**
     * enables privacy protection for a domain
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain privacy protection will be enabled for
     * 
     * @return bool
     *  whether enabling protection was successful
     */
    public function enablePrivacyProtection(Registrar_Domain $domain)
    {
        $cmd = 'set_privacy';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
            ),
        );
        $params['option'] = 'full';

        $apiResult = $this->_callApi($cmd,$params);
        return (bool)((string)($apiResult->SetPrivacyHeader->SuccessCode) == '0');
    }

    /**
     * disables privacy protection for a domain
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain privacy protection will be enabled for
     * 
     * @return bool
     *  whether disabling protection was successful
     */
    public function disablePrivacyProtection(Registrar_Domain $domain)
    {
        $cmd = 'set_privacy';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
            ),
        );
        $params['option'] = 'off';

        $apiResult = $this->_callApi($cmd,$params);
        return (bool)((string)($apiResult->SetPrivacyHeader->SuccessCode) == '0');
    }
    
    
    /**
     * gets the EPP/Auth code for the given domain 
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain for which we will retrieve the EPP
     * 
     * @return 
     *  the EPP code
     */
    public function getEpp(Registrar_Domain $domain){
        $value = $domain->getEPP();

        if($this->_valueExists($value)){
            return $value;
        }

        $cmd = 'get_transfer_auth_code';

        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'domain',
            ),
        );
        
        $apiResult = $this->_callApi($cmd, $params);
        $value = (int)($apiResult->GetTransferAuthCodeHeader->AuthCode);
        $domain->setEPP($value);
        return $value;
    }



    /**
     * locks the domain to prevent unwanted changes to it
     *  
     * STATUS:
     *  Completed: no
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain privacy protection will be enabled for
     * 
     * @return bool
     *  whether locking the domain was successful
     */
    public function lock(Registrar_Domain $domain)
    {
        throw new Registrar_Exception('Domain Registrar cannot process locking the domain');
    }

    /**
     * unlocks the domain so it can be changed
     *  
     * STATUS:
     *  Completed: no
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain privacy protection will be enabled for
     * 
     *  - the domain name must be filled in
     * 
     * @return bool
     *  whether unlocking the domain was successful
     */
    public function unlock(Registrar_Domain $domain)
    {
        throw new Registrar_Exception('Domain Registrar cannot process unlocking the domain');
    }


    public function isTestEnv()
    {
        return $this->_testMode;
    }






    /**
     * Api URL
     * 
     * @return string
     */
    private function _getApiUrl()
    {
        if($this->isTestEnv()) {
            /*return 'https://api.dynadot.com/api3.xml'; */
            throw new Registrar_Exception('No sandbox mode for Dynadot is available. Disable test mode to use Dynadot Registrar.');
        }
        // also have the option of using 'https://api.dynadot.com/api3.json'
        return 'https://api.dynadot.com/api3.xml';
    }


    /**
     * Runs an api command and returns parsed data.
     * @param string $command
     * @return array
     */
    protected function _callApi($command, $params, $method = 'GET'){

        if ($this->isTestEnv()) error_log('preforming '.$method.' API call with command: '.$command);

        // Set authentication parameter
        $params['key'] = $this->config['key'];

        // Set api command parameter 
        $params['command'] = $command;


        // inititialize cURL session
        $ch = curl_init();

        // Set URL we want to connect to 
        curl_setopt($ch, CURLOPT_URL, $this->_getApiUrl());

        // we want to get response from Dyndot Server
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_HEADER, 0);


        if($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $this->_getApiUrl());
        } else {
            /* Forms URL if not a post request*/
            $url = $this->_getApiUrl();
            $count = 0;

            foreach ($params as $key => $value) {
                if($count === 0) {
                    $url = $url . '?' . $key . "=" . $value;
                } else {
                    $url = $url . '&' . $key . "=" . $value;
                }

                $count = $count + 1;
            }
            curl_setopt($ch, CURLOPT_URL, $url);
         
        }

        // execute API call
        $result = curl_exec($ch);

        // print error if needed
        if ($result === false) {
            $e = new Registrar_Exception(sprintf('CurlException: "%s"', curl_error($ch)));
            $this->getLog()->err($e);
            curl_close($ch);
            throw $e;
        }
        
        // close cURL session
        curl_close($ch);
        
        $xml = simplexml_load_string($result);

        if ($this->isTestEnv()) error_log('result for '.$command.' is '.print_r($xml,1));

        

        $cmdTag = $this->_makeXmlTag($command);
        $headerInfo = $xml->{$cmdTag."Header"};

        if($command == 'search'){
            $headerInfo = $xml->SearchResponse->SearchHeader;
        }

        if(!$this->_valueExists($headerInfo)){
            throw new Registrar_Exception(
                "unknown error in API request: '".$command."'\n".print_r($xml,1)
            );
        }
        if(
            ($this->_valueExists($headerInfo->Status) && $headerInfo->Status == "error") ||
            ($this->_valueExists($headerInfo->ResponseCode) && $headerInfo->ResponseCode != '0') ||
            ($this->_valueExists($headerInfo->SuccessCode) && $headerInfo->SuccessCode != '0')
        ){

            $msg = "DYNADOT REGISTRAR ADAPTER ERROR!\nCommand: ".$command;
            if($this->_valueExists($headerInfo->Status)){
                $msg .= ("\nStatus: ".($headerInfo->Status));
            }
            if($this->_valueExists($headerInfo->ResponseCode)){
                $msg .= ("\nResponseCode: ".($headerInfo->ResponseCode));
            }
            if($this->_valueExists($headerInfo->SuccessCode)){
                $msg .= ("\nSuccessCode: ".($headerInfo->SuccessCode));
            }
            if($this->_valueExists($headerInfo->Error)){
                $msg .= ("\nERROR: ".($headerInfo->Error));
            }

            throw new Registrar_Exception($msg);
        }
        
        return $xml;
    }

    // cmd words are in snakecase, xml tags are in camel case with first letter capitalized
    private function _makeXmlTag($cmd){
        return implode('',array_map('ucfirst',explode('_',$cmd)));
    }

    /**
     * create the parameter for the API call, if a required value is not found a 
     * descriptive error is thrown, if an optional value is not found then the parameter
     * is not added to the array
     */
    private function _createParamArray(Registrar_Domain $domain, array $required, array $optional = array())
    {

        $params = array();

        // this variable converts the api parameter to a value retrieved from the domain
        $APIParamToValue = array(
            'duration' => $domain->getRegistrationPeriod(),
            'language' => (null !== $domain->getContactRegistrar())? 
                        $domain->getContactRegistrar()->getIdnLanguageCode():
                        null,

            'domain' => $domain->getName(),
            'domain0' => $domain->getName(),
    
            'registrant_contact' => (null !== $domain->getContactRegistrar())? 
                            $domain->getContactRegistrar()->getID():
                            null,
            'admin_contact' => (null !== $domain->getContactAdmin())? 
                            $domain->getContactAdmin()->getID():
                            null,
            'technical_contact' => (null !== $domain->getContactTech())? 
                            $domain->getContactTech()->getID():
                            null,
            'billing_contact' => (null !== $domain->getContactBilling())? 
                            $domain->getContactBilling()->getID():
                            null,
            
            'ns0' => $domain->getNs1(),
            'ns1' => $domain->getNs2(),
            'ns2' => $domain->getNs3(),
            'ns3' => $domain->getNs4(),

            'auth' => $domain->getEPP(),
            'premium' => 0,
        );

        // this anonymous function is used to check to make sure that the api parameter is 
        // accounted for in the APIParamToValue array
        $assertInArray = function($key) use ($APIParamToValue){
            if(!array_key_exists($key,$APIParamToValue)){
                throw new Registrar_Exception("APIParamToValue array does not have method to process ".$key);
            }
        };

        foreach($required as $reqParam){
            $assertInArray($reqParam);

            $value = $APIParamToValue[$reqParam];
            if(!$this->_valueExists($value)){
                throw new Registrar_Exception("required parameter '".$reqParam."' did not have an associated value");
            }
            $params[$reqParam] = $value;
        }

        foreach($optional as $opParam){
            $assertInArray($opParam);

            $value = $APIParamToValue[$opParam];
            if($this->_valueExists($value)){
                $params[$opParam] = $value;
            }
        }

        return $params;
    }

    /**
     * List of contact types for the domain Registrar
     */
    private $contactTypeTranslation = array(
        'ContactRegistrar' => 'Registrant',
        'ContactAdmin' => 'Admin',
        'ContactTech' => 'Technical',
        'ContactBilling' => 'Billing',
    );

    /**
    * translates common terms from the local fossbilling usage to the API usage
    */    
    private $contactFieldTranslation = array(
        'Id' =>'ContactId',
        'Name' =>'Name',
        'Email' => 'Email',
        'City' => 'City',
        'Country' => 'Country',
        'State' => 'State',
        'Zip' => 'ZipCode',
        'Tel' => 'PhoneNum',
        'TelCc' => 'PhoneCc',
        'Fax' => 'FaxNum',
        'FaxCc' => 'FaxCc',
        'Company' => 'Organization',
        'Address1' => 'Address1',
        'Address2' => 'Address2',
    );

    private function _fillInContactInfo(Registrar_Domain_Contact $domainContact, $infoClass){
        foreach($this->contactFieldTranslation as $localfield => $apifield)
        {
            (string)$value = $infoClass->{$apifield};
            if($this->_valueExists($value)){
                $domainContact->{"set".$localfield}($value);
            }
        }
        return $domainContact;
    }

    private function _paramatrizeContactInfo(Registrar_Domain_Contact $domainContact, array $params = array()){

        $required = array(
            'contact_id' => 'Id',
            'name' => 'Name',
            'phonenum' => 'Tel',
            'phonecc' => 'TelCc',
            'address1' => 'Address1',
            'city' => 'City',
            'state' => 'State',
            'zip' => 'Zip',
            'country' => 'Country'
        );

        $optional = array(
            'organization' => 'Company',
            'faxnum' => 'Fax',
            'faxcc' => 'FaxCc',
            'address2' => 'Address2'
        );

        foreach($required as $reqApi => $reqLocal){

            $value = $domainContact->{"get".($reqLocal)}();

            if(!$this->_valueExists($value)){
                throw new Registrar_Exception("required parameter '".$reqApi."' did not have an associated value.
                likely the '".$reqLocal."' field was not filled in");
            }
            $params[$reqApi] = $value;
        }

        foreach($optional as $opApi => $opLocal){

            $value = $domainContact->{"get".($opLocal)}();
            if($this->_valueExists($value)){
                $params[$opApi] = $value;
            }
        }

        return $params;
    }
    
}
