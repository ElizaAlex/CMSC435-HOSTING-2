<?php
class Registrar_Adapter_NameCheap extends Registrar_AdapterAbstract
{

    /**
     * All of the global config settings needed to do API calls
     */
    public $config = array(
        'ApiUser'   => null,
        'ApiKey'   => null,
        'UserName'   => null,
        'ClientIp' => null,
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

        if($this->_valueExists($options['ApiUser'])) {
            $this->config['ApiUser'] = $options['ApiUser'];
            unset($options['ApiUser']);
        } else {
            throw new Registrar_Exception('Domain registrar "Namecheap" is not configured properly. Please update configuration parameter "Namecheap ApiUser" at "Configuration -> Domain registration".');
        }

        if($this->_valueExists($options['ApiKey'])) {
            $this->config['ApiKey'] = $options['ApiKey'];
            unset($options['ApiKey']);
        } else {
            throw new Registrar_Exception('Domain registrar "Namecheap" is not configured properly. Please update configuration parameter "Namecheap ApiKey" at "Configuration -> Domain registration".');
        }

        if($this->_valueExists($options['ClientIp'])) {
            $this->config['ClientIp'] = $options['ClientIp'];
            unset($options['ClientIp']);
        } else {
            throw new Registrar_Exception('Domain registrar "Namecheap" is not configured properly. Please update configuration parameter "ClientIp" at "Configuration -> Domain registration".');
        }

        if($this->_valueExists($options['UserName'])) {
            $this->config['UserName'] = $options['UserName'];
            unset($options['UserName']);
        } else {
            throw new Registrar_Exception('Domain registrar "Namecheap" is not configured properly. Please update configuration parameter "Namecheap UserName" at "Configuration -> Domain registration".');
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
            'label'     =>  'Manages domains on Namcheap via API.',
            'form'  => array(
 
                'ApiUser' => array('text', array(
                            'label' => 'API user',
                            'description'=> 'API user'
                        ),
                     ),

 
                'ApiKey' => array('password', array(
                        'label' => 'API key',
                        'description'=> 'API key'
                            ),
                        ),
 
                 'UserName' => array('text', array(
                    'label' => 'Namcheap username doing command (typically the same as api user)',
                    'description'=> 'Namecheap username'
                ),
             ),

                'ClientIp' => array('text', array(
                    'label' => 'IPv4 Server IP address',
                    'description'=> 'IPv4 Server IP address required for api access'
                ),
             ),
 
            ),
        );

         

    }



    /**
     * All the top level domains that Namecheap supports
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     */
    public function getTlds()
    {
        return array(
            '.com', '.net', '.org', '.io',
            '.co', '.ai', '.co.uk', '.ca',
            '.dev', '.me', '.de', '.app',
            '.in', '.is', '.eu', '.gg',
            '.to', '.ph', '.nl', '.id',
            '.inc', '.website', '.xyz', '.club',
            '.online', '.info', '.store', '.best',
            '.live', '.us', '.tech', '.pw',
            '.pro', '.uk', '.tv', '.cx',
            '.mx', '.fm', '.cc', '.world',
            '.space', '.vip', '.life', '.shop',
            '.host', '.fun', '.biz', '.icu',
            '.design', '.art');
    }


    /**
     * Checks if a given domain is registered
     * 

     * STATUS:
     *  Completed: yes
     *  Tests: 
     *     Test #1:
     *      Input --> registered Domain
     *      output --> text error that domain is registered
     *      Result --> Success
     *      Doucmentation --> Test #1 in Testing document
     *     Test #2:
     *      Input --> unregistered Domain
     *      output --> successful order, registered domain on sandbox account
     *      Result --> Success
     *      Doucmentation --> Test #2 in Testing document  
     * @param Registrar_Domain $domain
     * the domain to be checked, 
     * 
     * - the domain name needs to be filled in
     * 
     * @return bool
     *  bool that says whether or not domain is available
     */
    public function isDomainAvailable(Registrar_Domain $domain){
        
        if(!in_array($domain->getTLD(),$this->getTlds())){
            throw new Registrar_Exception("The TLD ".($domain->getTLD())." is not supported by this domain registrar");
        }

        $cmd = 'namecheap.domains.check';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainList',
            ),
        );

        $apiResult = $this->_callApi($cmd, $params);
        
        if((string)($apiResult->CommandResponse->DomainCheckResult['IsPremiumName']) == 'true'){
            throw new Registrar_Exception(
                'The top domain '.($domain->getName()).' is a premium domain and is '.
                'not supported for purchase or transfer through this Registrar Adapter'
            );
        }
        return (bool)((string)($apiResult->CommandResponse->DomainCheckResult['Available']) == 'true');
    }

    /**
     * All of the domains that Namecheap says they can transfer
     */
    private $transferableTLDs = array(
        '.biz','.ca','.cc','.co','.co.uk','.com','.com.es','.com.pe',
        '.es','.in','.info','.me','.me.uk','.mobi','.net','.net.pe',
        '.nom.es','.org','.org.es','.org.pe','.org.uk','.pe','.tv','.us'
    );

    /**
     * Checks if a given domain can be transferred
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain to be checked, 
     * 
     *  - the domain name and the TLD must be filled in
     * 
     * @return bool
     *  bool that says whether or not domain can be transferred
     */
    public function isDomaincanBeTransferred(Registrar_Domain $domain)
    {
        if(!in_array($domain->getTLD(),$this->getTlds())){
            throw new Registrar_Exception("The TLD ".($domain->getTLD())." is not supported by this domain registrar");
        }

        $tld = $domain->getTld();

        if (!in_array($tld, $this->transferableTLDs)){
            throw new Registrar_Exception('The TLD "'.($tld).'" is not supported for domain transfer by this domain registrar');
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
     *  - the domain SLD and TLD must be filled in
     *  - the domain nameservers must be filled in
     * 
     * @return bool
     *  bool that says whether or not the nameservers were updated
     */
    public function modifyNs(Registrar_Domain $domain)
    {
        $cmd = 'namecheap.domains.dns.setCustom';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'SLD',
                'TLD',
                'Nameservers'
            ),
        );

        $apiResult = $this->_callApi($cmd,$params,method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->DomainDNSSetCustomResult["Updated"]) == 'true');
    }



    private function _handleExtendedAttributes(Registrar_Domain $domain, $params){
        

        /* Get client contact information  */
        $c = $domain->getContactRegistrar();

        /* Handle .us top level domain */
        if($domain->getTLD() == '.us') {
            
            /* foreign organization */
            if($this->_valueExists($c->getCompany())) {
                /*  bsuiness for business purposes */
                $params['RegistrantNexus'] = 'C21'; 
                $params['RegistrantPurpose'] = 'P1'; 
            } else if ($c->getCountry() == 'US' && !$this->_valueExists($c->getCompany())) {
                /*  US permanent resident for personal  purposes */
                $params['RegistrantNexus'] = 'C12'; 
                $params['RegistrantPurpose'] = 'P3'; 
            }  else {
                throw new Registrar_Exception("Cannot register with this TLD without information of business or permanent resident status");
            }

        } else if ($domain->getTLD() == '.eu') {

            /* agree to eu policies */
            throw new Registrar_Exception("In order to register a .eu domain, you must 
            agree to EU polcies that we cannot agree to on your behalf.");
            
            //$params['EUAgreeWhoisPolicy'] = 'YES'; 
            //$params['EUAgreeDeletePolicy'] = 'YES'; 
         
        } else if ($domain->getTLD() == '.nu') {

            /* may also insert swedish ID*/
            $params['NUOrgNo'] = $c->getCompanyNumber();
         
        } else if ($domain->getTLD() == '.ca') {

            throw new Registrar_Exception("In order to register a .ca domain, you must 
            agree to CIRA polcies that we cannot agree to on your behalf.");

         
        } else if ($domain->getTLD() == '.co.uk' || 
                   $domain->getTLD() == '.me.uk' ||
                   $domain->getTLD() == '.org.uk') {

            throw new Registrar_Exception("Legal status as UK resident needed 
                 to register with this TLD");
         
        }  else if ($domain->getTLD() == '.com.au') {
            $params['COMAURegistrantId'] = $c->getCompanyNumber();
            $params['COMAURegistrantIdType'] = 'ACN';
        } else if ($domain->getTLD() == '.net.au') {
            $params['NETAURegistrantId'] = $c->getCompanyNumber();
            $params['NETAURegistrantIdType'] = 'ACN';
        } else if ($domain->getTLD() == '.org.au') {
            $params['ORGAURegistrantId'] = $c->getCompanyNumber();
            $params['ORGAURegistrantIdType'] = 'ACN';
        } else if ($domain->getTLD() == '.es' || 
                   $domain->getTLD() == '.nom.es' ||
                   $domain->getTLD() == '.com.es' ||
                   $domain->getTLD() == '.org.es') {
            throw new Registrar_Exception("To register this domain, you must agree to a 
                 legal contract that we cannot agree to on your behalf.");

            
        }  else if ($domain->getTLD() == '.de') {
            throw new Registrar_Exception("You must make an agreement to for the renewal 
                terms of your domain to register with this TLD")  ;
        } else if ($domain->getTLD() == '.fr') {
            
            if($this->_valueExists($c->getCompany())) {
                throw new Registrar_Exception("You provide legal information
                    for your company to register with this TLD");
            } else {
                throw new Registrar_Exception("You provide a place 
                    of birth to register with this TLD");
            }
        } 

        return $params;
    }

    /**
     * modifies the contact information associated with a domain name
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain for which the contact information will be modified
     * 
     *  - the domain name must be filled in
     *  - the domain registrar contact must be filled in
     *  - optionally the other contacts can be filled in, 
     *  but will default to the registrar if not
     * 
     * @return bool
     *  bool that says whether or not the contact information was updated
     */
    public function modifyContact(Registrar_Domain $domain)
    {
        $cmd = 'namecheap.domains.setContacts';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array_merge(
                array(
                    'DomainName'
                ),
                $this->_getRequiredContactFields()
            ),
            optional: $this->_getOptionalContactFields(),
            needContactInfo: true
        );

        $params = $this->_handleExtendedAttributes($domain,$params);

        $apiResult = $this->_callApi($cmd,$params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->DomainSetContactResult["IsSuccess"]) == 'true');
    }

    /**
     * completes domain registration with namecheap
     *  
     * STATUS:
     *  Completed: yes
     *  Tests: 
     *     Test #1:
     *      Input --> registered Domain
     *      output --> text error that domain is registered
     *      Result --> Success
     *      Doucmentation --> Test #1 in Testing document
     *     Test #2:
     *      Input --> unregistered Domain
     *      output --> successful order, registered domain on sandbox account
     *      Result --> Success
     *      Doucmentation --> Test #2 in Testing document  
     *
     * 
     * @param Registrar_Domain $domain
     *  the domain we are registering
     * 
     *  - the domain name must be filled in
     *  - the registration period must be filled in
     *  - the registrar contact information must be filled in 
     *  - optionally the other contact information can be filled in 
     *  they will default to registrar contact if not
     * 
     * @return bool
     *  whether not the domain was registered
     */
    public function registerDomain(Registrar_Domain $domain)
    {

        $cmd = 'namecheap.domains.create';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array_merge(
                array(
                    'DomainName',
                    'Years'
                ),
                $this->_getRequiredContactFields()
            ),
            optional: array_merge(
                array(
                    'IdnCode',
                    'Nameservers',
                    'IsPremiumDomain',
                    'AddFreeWhoisguard'
                ),
                $this->_getOptionalContactFields()
            ),
            needContactInfo: true
        );

        $params = $this->_handleExtendedAttributes($domain,$params);

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->DomainCreateResult['Registered']) == 'true');
    }



    /**
     * Transfers a domain to Namecheap
     * 
     * This only works for some TLDs, an error is thrown if the TLD is not one of the valid ones
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain that will be transferred
     * 
     *  - the domain name must be filled in
     *  - the TLD must be filled in
     * 
     * @return bool
     *  bool that says whether or not the transfer was successful
     */
    public function transferDomain(Registrar_Domain $domain)
    {
        
        if(!in_array($domain->getTld(), $this->transferableTLDs)){
            throw new Registrar_Exception("Top Level Domain ".($domain->getTld())." cannot be transferred with Namecheap");            
        }

        $cmd = 'namecheap.domains.transfer.create';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
                'Years',
                'EPPCode'
            ),
            optional: array(
                'AddFreeWhoisguard'
            )
        );

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');

        return (bool)((string)($apiResult->CommandResponse->DomainTransferCreateResult['Transfer']) == 'true');
    }


    public function getContactDetails(Registrar_Domain $domain){

        $cmd = 'namecheap.domains.getContacts';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
            ),
        );
        $apiResult = $this->_callApi($cmd, $params);

        $domain = $this->_fillContactFromApiResult($domain,$apiResult->CommandResponse->DomainContactsResult);

        return $domain;
    }

    
    /**
     * gets the Domain for a given domain name
     * 
     * this loads the following data into the Registrar_Domain class:
     *  - domain registration date
     *  - domain expiration date
     *  - if domain privacy is enabled
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     * 
     * @param Registrar_Domain $domain
     *  the domain we are retrieving details for
     * 
     *  - the domain name must be filled in
     *  - the SLD and TLD must be filled in
     * 
     * @return Registrar_Domain
     *  the domain with all of the details updated
     */
    public function getDomainDetails(Registrar_Domain $domain)
    {

        // get basic Domain info
        $cmd = 'namecheap.domains.getinfo';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
            ),
        );

        $apiResult = $this->_callApi($cmd, $params);

        $domainInfo = $apiResult->CommandResponse->DomainGetInfoResult;

        $domain
            ->setExpirationTime(strtotime((string)$domainInfo->DomainDetails->ExpiredDate))
            ->setRegistrationTime(strtotime((string)$domainInfo->DomainDetails->CreatedDate))
            ->setPrivacyEnabled((bool)((string)$domainInfo->Whoisguard['Enabled'] == 'true'));

        // set a custom parameter in the registrar domain, the hope is that this persists when 
        // the privacy protection functions are called
        $domain->WhoisGuardID = (int)$domainInfo->Whoisguard->ID;

        
        // get nameserver Info
        $nameserverInfo = $domainInfo->DnsDetails->Nameserver;
        foreach(array(0,1,2,3) as $i)
        {
            if($this->_valueExists($nameserverInfo[$i]))
            {
                $domain->{"setNs".($i+1)}((string)$nameserverInfo[$i]);
            }
        }
        
        //get contact info
        $domain = $this->getContactDetails($domain);


        return $domain;
    }


    /**
     * Namecheap does not support domain deletion so this is not implemented
     * 
     * STATUS:
     *  Completed: yes
     *  Tested: not needed
     */
    public function deleteDomain(Registrar_Domain $domain)
    {
        throw new Registrar_Exception('Namecheap API does not support deletion');
    }

    
    /**
     * renews a domain with namecheap
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: basic
     * 
     * @param Registrar_Domain $domain
     *  the domain to be renewed
     * 
     *  - the domain name must be filled in
     *  - the registration period must be filled in
     * 
     * @return bool
     *  whether not the domain was registered
     */
    public function renewDomain(Registrar_Domain $domain)
    {
        $cmd = 'namecheap.domains.renew';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
                'Years'
            ),
        );

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');

        $renewSuccess = (bool)((string)($apiResult->CommandResponse->DomainRenewResult['Renew']) == 'true');

        // if we successfuly renew then also renew whoisgaurd
        if($renewSuccess){
            $cmd = 'namecheap.whoisguard.renew';
            $params = $this->_createParamArray(
                domain: $domain,
                required: array(
                    'DomainName',
                    'Years'
                ),
            );
            $params['WhoisguardID'] = $this->getWhoIsGuardId($domain);
            $apiResult = $this->_callApi($cmd, $params, method: 'POST');

            $renewSuccess = (bool)((string)($apiResult->CommandResponse->WhoisguardRenewResult['Renew']) == 'true');
        }

        return $renewSuccess;
    }



    private function getWhoIsGuardId(Registrar_Domain $domain){
        $whoisGuardID = $domain->WhoisGuardID;

        if(!$this->_valueExists($whoisGuardID)){
            // API call to get Whoisguard id
            $cmd = 'namecheap.domains.getinfo';
            $params = $this->_createParamArray(
                domain: $domain,
                required: array(
                    'DomainName',
                ),
            );
            $apiResult = $this->_callApi($cmd, $params);
            $whoisGuardID = (int)($apiResult->CommandResponse->DomainGetInfoResult->Whoisguard->ID);
            $domain->WhoisGuardID = $whoisGuardID;
        }

        return $whoisGuardID;
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
     *  - the domain name must be filled in
     *  - the email in the registrar contact must be filled in 
     * 
     * @return bool
     *  whether enabling protection was successful
     */
    public function enablePrivacyProtection(Registrar_Domain $domain)
    {

        $cmd = 'Namecheap.Whoisguard.enable';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'ForwardedToEmail',
            ),
        );
        $params['WhoisguardID'] = $this->getWhoIsGuardId($domain);

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->WhoisguardEnableResult['IsSuccess']) == 'true');
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
     *  - the domain name must be filled in
     *  - the email in the registrar contact must be filled in 
     * 
     * @return bool
     *  whether disabling protection was successful
     */
    public function disablePrivacyProtection(Registrar_Domain $domain)
    {

        $cmd = 'Namecheap.Whoisguard.disable';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'ForwardedToEmail',
            ),
        );
        $params['WhoisguardID'] = $this->getWhoIsGuardId($domain);

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->WhoisguardDisableResult['IsSuccess']) == 'true');
    }
    
    
    /**
     * gets the EPP/Auth code for the given domain 
     * currently no way has been found to support this through the api
     *
     * STATUS:
     *  Completed: no
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain for which we will retrieve the EPP
     * 
     * @return 
     *  the EPP code
     */
    public function getEpp(Registrar_Domain $domain)
    {

        throw new Registrar_Exception('Namecheap API does not support retrieval of EPP code');
    }

    /**
     * locks the domain to prevent unwanted changes to it
     *  
     * STATUS:
     *  Completed: yes
     *  Tested: no
     * 
     * @param Registrar_Domain $domain
     *  the domain privacy protection will be enabled for
     * 
     *  - the domain name must be filled in
     * 
     * @return bool
     *  whether locking the domain was successful
     */
    public function lock(Registrar_Domain $domain)
    {
        $cmd = 'namecheap.domains.setRegistrarLock';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
            ),
        );
        $params['LockAction'] = 'LOCK';

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->DomainSetRegistrarLockResult['IsSuccess']) == 'true');
    }

    /**
     * unlocks the domain so it can be changed
     *  
     * STATUS:
     *  Completed: yes
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
        $cmd = 'namecheap.domains.setRegistrarLock';
        $params = $this->_createParamArray(
            domain: $domain,
            required: array(
                'DomainName',
            ),
        );
        $params['LockAction'] = 'UNLOCK';

        $apiResult = $this->_callApi($cmd, $params, method: 'POST');
        return (bool)((string)($apiResult->CommandResponse->DomainSetRegistrarLockResult['IsSuccess']) == 'true');
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
            return 'https://api.sandbox.namecheap.com/xml.response';
        }
        return 'https://api.namecheap.com/xml.response';
    }


    /**
     * Runs an api command and returns parsed data.
     * @param string $command
     *  the command to be run
     * @param array $params 
     *  the parameters for the call
     */
    private function _callApi($command, $params, $method = 'GET')
    {

        if ($this->isTestEnv()) error_log('preforming '.$method.' API call with command: '.$command);

        // Set Global Paramters parameters
        $params['ApiUser'] = $this->config['ApiUser'];
        $params['ApiKey'] = $this->config['ApiKey'];
        $params['UserName'] = $this->config['UserName'];
        $params['ClientIp'] = $this->config['ClientIp'];

        # set command
        $params['Command'] = $command;

        // inititialize cURL session
        $ch = curl_init();

        // Set URL we want to connect to
        

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        // we want to get response from Dyndot Server
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $this->_getApiUrl());
        } else {
            /* Forms URL if not a post request*/
            $url = $this->_getApiUrl();
            $count = 0;

            foreach ($params as $key => $value) {
                if($count == 0) {
                    $url = $url . '?' . $key . "=" . $value;
                } else {
                    $url = $url . '&' . $key . "=" . $value;
                }

                $count = $count + 1;
            }
            curl_setopt($ch, CURLOPT_URL, $url);
         
        }

        // execute API call
        $data = curl_exec($ch);


        // print error if curl does not run properly
        if ($data === false) {
            $e = new Registrar_Exception(sprintf('CurlException: "%s"', curl_error($ch)));
            $this->getLog()->err($e);
            curl_close($ch);
            throw $e;
        }

        
        
        // close cURL session
        curl_close($ch);

        // get xml response
        $xml = simplexml_load_string($data);

        if ($this->isTestEnv()) error_log('result for '.$command.' is '.print_r($xml,1));

        /* Throws registrar exception if XML retured contains an error message */
        $error = $xml->Errors->Error;
        
        if($this->_valueExists($error)) {
            $apiRequest = $xml->RequestedCommand;
            throw new Registrar_Exception(
                'error in API request "'.$apiRequest.'"'.$error
            );
        }

        
        return $xml;
    }



    private function _getRequiredContactFields(){
        $apiContactTypes = array(
            'Registrant',
            'Admin',
            'Tech',
            'AuxBilling'
        );
        $apiRequiredContactFields = array(
            'FirstName',
            'LastName',
            'Address1',
            'City',
            'StateProvince',
            'PostalCode',
            'Country',
            'Phone',
            'EmailAddress',
        );
        $params = array();
        foreach($apiContactTypes as $contact){
            foreach($apiRequiredContactFields as $field){
                $params[] = ($contact.$field);
            }
        }
        return $params;
    }

    private function _getOptionalContactFields(){
        $apiContactTypes = array(
            'Registrant',
            'Admin',
            'Tech',
            'AuxBilling'
        );
        $apiOptionalContactFields = array(
            'OrganizationName',
            'JobTitle',
            'Address2',
            'Fax',
        );
        $params = array();
        foreach($apiContactTypes as $contact){
            foreach($apiOptionalContactFields as $field){
                $params[] = ($contact.$field);
            }
        }
        return $params;
    }


    /**
     * List of contact types for the domain Registrar
     */
    private function getContactTypeTranslation() {
        return array(
            'ContactRegistrar' => 'Registrant',
            'ContactAdmin' => 'Admin',
            'ContactTech' => 'Tech',
            'ContactBilling' => 'AuxBilling',
        );
    }

    /**
    * translates common terms from the local fossbilling usage to the API usage
    */    
    private function getContactFieldTranslation(){
        return array(
            'FirstName' =>'FirstName',
            'LastName' =>'LastName',
            'Email' => 'EmailAddress',
            'City' => 'City',
            'Country' => 'Country',
            'State' => 'StateProvince',
            'Zip' => 'PostalCode',
            'Company' => 'OrganizationName',
            'Address1' => 'Address1',
            'Address2' => 'Address2',
            'JobTitle' => 'JobTitle',
        );
    }

    /**
     * create the parameter for the API call, if a required value is not found a 
     * descriptive error is thrown, if an optional value is not found then the parameter
     * is not added to the array
     */
    private function _createParamArray(
        Registrar_Domain $domain, 
        array $required, 
        array $optional = array(), 
        bool $needContactInfo = false
    )
    {

        $params = array();

        // this variable converts the api parameter to a value retrieved from the domain
        $APIParamToValue = array(
            'DomainList' => $domain->getName(),
            'DomainName' => $domain->getName(),
            'SLD' => $domain->getSld(), 
            'TLD' => $domain->getTld(false),

            'Years' => $domain->getRegistrationPeriod(),
            'ForwardedToEmail' => (null !== $domain->getContactRegistrar())? 
                            $domain->getContactRegistrar()->getEmail():
                            null,

            'IdnCode' => (null !== $domain->getContactRegistrar())? 
                            $domain->getContactRegistrar()->getIdnLanguageCode():
                            null,
            'EPPCode' => $domain->getEpp(),
            'IsPremiumDomain' => false,
            'AddFreeWhoisguard' => 'Yes'
        );


        // collapse the nameserver values into a list and add that to the params
        $nsList = array();
        foreach(array("getNs1","getNs2","getNs3","getNs4") as $ns)
        {
            $nsValue = $domain->{$ns}();
            if($this->_valueExists($nsValue))
            {
                $nsList[] = $nsValue;
            }
        }
        $APIParamToValue["Nameservers"] = implode(',',$nsList);




        // load the contact info only if flagged as needed 
        if($needContactInfo){
            foreach($this->getContactTypeTranslation() as $localContact => $apiContact){
                // get the contact data stored by fossbilling, defaulting to the Registrar data
                $localContactData = $domain->{"get".$localContact}()??
                                    $domain->getContactRegistrar();
                // fill contact params except Phone and fax
                foreach($this->getContactFieldTranslation() as $localField => $apiField){
                    $APIParamToValue[$apiContact.$apiField] = $localContactData->{"get".$localField}();
                }

                // phone and fax need special formatting
                $APIParamToValue[$apiContact."Phone"] = null;
                if($this->_valueExists($localContactData->getTelCc()) && $this->_valueExists($localContactData->getTel())){
                    $APIParamToValue[$apiContact."Phone"] = "+".$localContactData->getTelCc().".".$localContactData->getTel();
                }
                
                $APIParamToValue[$apiContact."Fax"] = null;
                if($this->_valueExists($localContactData->getFaxCc()) && $this->_valueExists($localContactData->getFax())){
                    $APIParamToValue[$apiContact."Fax"] = "+".$localContactData->getFaxCc().".".$localContactData->getFax();
                }
            }
        }

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
     * transfers the contact data from the api result to the Registrar_Domain class
     *
     * @param Registrar_Domain $domain
     * the domain Data
     * @param $apiResult
     * the DomainContactsResult of the result from api call 
     * @return Registrar_Domain
     * filled in with contact information
     */
    private function _fillContactFromApiResult(Registrar_Domain $domain, $apiResult){


        /**
         * go through each contact type to fill in contact details
         */
        foreach($this->getContactTypeTranslation() as $localContact => $apiContact)
        {
            // get the locally known contact info, default to registrar, and then default to 
            // a new contact class
            $localContactInfo = $domain->{"get".$localContact}()??
                                $domain ->getContactRegistrar()??
                                new Registrar_Domain_Contact();

            // get api contact info, default to registrant info
            $apiContactInfo =   $apiResult->{$apiContact}??
                                $apiResult->Registrant;
            
            // go through each contact parameter, and if it is defined in 
            // the result then set it in the domain contact
            foreach($this->getContactFieldTranslation() as $localField => $apiField)
            {
                // if the value exists set it in the local info
                $paramValue = (string)($apiContactInfo->{$apiField});
                if($this->_valueExists($paramValue)){
                    $localContactInfo->{"set".$localField}($paramValue);
                }
            }

            // fax and phone are special, at least for namecheap and 
            // need to be exploded to get country code
            $paramValue = (string)($apiContactInfo->Phone);
            if($this->_valueExists($paramValue)){
                $phone = explode('.',$paramValue);
                $localContactInfo   ->setTelCc((explode('+',$phone[0])[1]))
                                    ->setTel($phone[1]);
            }

            $paramValue = (string)($apiContactInfo->Fax);
            if($this->_valueExists($paramValue))
            {
                $fax = explode('.',$paramValue);
                $localContactInfo   ->setFaxCc((explode('+',$fax[0])[1]))
                                    ->setFax(($fax[1]));
            }


            // set contact in the domain
            $domain->{"set".$localContact}($localContactInfo);
        }

        return $domain;
    }
}