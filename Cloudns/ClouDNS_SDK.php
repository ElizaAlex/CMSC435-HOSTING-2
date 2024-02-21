<?php

namespace Box\Mod\ClouDNS;

class ClouDNS_SDK {

	protected $id;
	protected $password;
	protected $user_type;

	function __construct($auth_id, $auth_password, $is_subuser = false) {
		if ($is_subuser == false) {
			$this->user_type = 'auth-id';
		} else {
			if (is_numeric($auth_id)) {
				$this->user_type = 'sub-auth-id';
			} else {
				$this->user_type = 'sub-auth-user';
			}
		}
		$this->id = $auth_id;
		$this->password = $auth_password;
		
		error_log($this->id);
		error_log($this->password);

	}

	private function apiRequest($api_data, $api_url) {

		$init = curl_init();
		curl_setopt($init, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($init, CURLOPT_URL, 'https://api.cloudns.net/' . $api_url . '.json');
		curl_setopt($init, CURLOPT_POST, true);
		curl_setopt($init, CURLOPT_POSTFIELDS, $this->user_type . '=' . $this->id . '&auth-password=' . $this->password . $api_data);

		$content = curl_exec($init);

		curl_close($init);
		
		error_log((string) $content);

		return json_decode($content, true);
	}

	public function apiLogin() {
		$url = 'login/login';

		return $this->apiRequest(false, $url);
	}

	public function dnsRegisterDomainZone($domain_name, $zone_type, $ns = false, $master_ip = false) {

		$data = '&domain-name=' . urlencode($domain_name) . '&zone-type=' . urlencode($zone_type);

		if (is_array($ns)) {
			if (empty($ns)) {
				$data .= '&ns[]=';
			} else {
				foreach ($ns as $value) {
					$data .= '&ns[]=' . urlencode($value);
				}
			}
		}

		if (!empty($master_ip)) {
			$data .= '&master-ip=' . urlencode($master_ip);
		}
		$url = 'dns/register';

		return $this->apiRequest($data, $url);
	}

	public function dnsAvailableNameServers() {
		$url = 'dns/available-name-servers';

		return $this->apiRequest(false, $url);
	}

	public function dnsDeleteDomainZone($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/delete';

		return $this->apiRequest($data, $url);
	}

	public function dnsListZones($page, $rows_per_page, $search = false, $groupId = "") {
		$data = '&page=' . urlencode($page) . '&rows-per-page=' . urlencode($rows_per_page) .
            ($search !== false ? '&search=' . urlencode($search) : "") . ($groupId != "" ? '&group-id='.urlencode($groupId) : "");
		$url = 'dns/list-zones';

		return $this->apiRequest($data, $url);
	}

	public function dnsGetPagesCount($rows_per_page, $search = false) {
		$data = '&rows-per-page=' . urlencode($rows_per_page) . '&search=' . urlencode($search);
		$url = 'dns/get-pages-count';

		return $this->apiRequest($data, $url);
	}

	public function dnsGetZonesStatistics() {
		$url = 'dns/get-zones-stats';

		return $this->apiRequest(false, $url);
	}

	public function dnsGetMailForwardsStatistics() {
		$url = 'dns/get-mail-forwards-stats';

		return $this->apiRequest(false, $url);
	}

	public function dnsGetZoneInformation($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/get-zone-info';

		return $this->apiRequest($data, $url);
	}

	public function dnsUpdateZone($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/update-zone';

		return $this->apiRequest($data, $url);
	}

	public function dnsUpdateStatus($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/update-status';

		return $this->apiRequest($data, $url);
	}

	public function dnsIsUpdated($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/is-updated';

		return $this->apiRequest($data, $url);
	}

	public function dnsChangeZonesStatus($domain_name, $status = false) {
		$data = '&domain-name=' . urlencode($domain_name) . '&status=' . urlencode($status);
		$url = 'dns/change-status';

		return $this->apiRequest($data, $url);
	}

	public function dnsListRecords($domain_name, $host = false, $type = false) {
		$data = '&domain-name=' . urlencode($domain_name);
		if (!empty($host)) {
			$data .= '&host=' . urlencode($host);
		}
		if (!empty($type)) {
			$data .= '&type=' . urlencode($type);
		}

		$url = 'dns/records';

		return $this->apiRequest($data, $url);
	}

	public function dnsAddRecord($domain_name, $record_type, $host, $record, $ttl, $priority = false, $weight = false, $port = false, $frame = false, $frame_title = false, $frame_keywords = false, $frame_description = false, $save_path = false, $redirect_type = false, $mail = false, $txt = false, $algorithm = false, $fptype = false, $status = true, $geodns_location = false, $caa_flag = false, $caa_type = false, $caa_value = false) {

		$data = '&domain-name=' . urlencode($domain_name) . '&record-type=' . urlencode($record_type) . '&host=' . urlencode($host) . '&record=' . urlencode($record) . '&ttl=' . urlencode($ttl) .
			'&priority=' . urlencode($priority) . '&weight=' . urlencode($weight) . '&port=' . urlencode($port) . '&frame=' . urlencode($frame) . '&frame-title=' . urlencode($frame_title) .
			'&frame-keywords=' . urlencode($frame_keywords) . '&frame-description=' . urlencode($frame_description) . '&save-path=' . urlencode($save_path) .
			'&redirect-type=' . urlencode($redirect_type) . '&mail=' . urlencode($mail) . '&txt=' . urlencode($txt) . '&algorithm=' . urlencode($algorithm) . '&fptype=' . urlencode($fptype) .
			'&status=' . urlencode($status) . '&geodns-location=' . urlencode($geodns_location) . '&caa_flag=' . urlencode($caa_flag) . '&caa_type=' . urlencode($caa_type) . '&caa_value=' . urlencode($caa_value);
		$url = 'dns/add-record';

		return $this->apiRequest($data, $url);
	}

	public function dnsDeleteRecord($domain_name, $record_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id);
		$url = 'dns/delete-record';

		return $this->apiRequest($data, $url);
	}

	public function dnsModifyRecord($domain_name, $record_id, $host, $record, $ttl, $priority = false, $weight = false, $port = false, $frame = false, $frame_title = false, $frame_keywords = false, $frame_description = false, $save_path = false, $redirect_type = false, $mail = false, $txt = false, $algorithm = false, $fptype = false, $status = false, $geodns_location = false, $caa_flag = false, $caa_type = false, $caa_value = false) {

		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id) . '&host=' . urlencode($host) . '&record=' . urlencode($record) . '&ttl=' . urlencode($ttl) .
			'&priority=' . urlencode($priority) . '&weight=' . urlencode($weight) . '&port=' . urlencode($port) . '&frame=' . urlencode($frame) . '&frame-title=' . urlencode($frame_title) .
			'&frame-keywords=' . urlencode($frame_keywords) . '&frame-description=' . urlencode($frame_description) . '&save-path=' . urlencode($save_path) .
			'&redirect-type=' . urlencode($redirect_type) . '&mail=' . urlencode($mail) . '&txt=' . urlencode($txt) . '&algorithm=' . urlencode($algorithm) . '&fptype=' . urlencode($fptype) .
			'&status=' . urlencode($status) . '&geodns-location=' . urlencode($geodns_location) . '&caa_flag=' . urlencode($caa_flag) . '&caa_type=' . urlencode($caa_type) . '&caa_value=' . urlencode($caa_value);
		$url = 'dns/mod-record';

		return $this->apiRequest($data, $url);
	}

	public function dnsCopyRecords($domain_name, $from_domain, $delete_current_records = false) {
		$data = '&domain-name=' . urlencode($domain_name) . '&from-domain=' . urlencode($from_domain) .
			'&delete-current-records=' . urlencode($delete_current_records);
		$url = 'dns/copy-records';

		return $this->apiRequest($data, $url);
	}

	public function dnsImportRecords($domain_name, $format, $content, $delete_existing_records = false) {
		$data = '&domain-name=' . urlencode($domain_name) . '&format=' . urlencode($format) .
			'&content=' . urlencode($content) . '&delete-existing-records=' . urlencode($delete_existing_records);
		$url = 'dns/records-import';

		return $this->apiRequest($data, $url);
	}

	public function dnsExportRecordsBIND($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/records-export';

		return $this->apiRequest($data, $url);
	}

	public function dnsGetAvailableRecords($zone_type) {
		$data = '&zone-type=' . urlencode($zone_type);
		$url = 'dns/get-available-record-types';

		return $this->apiRequest($data, $url);
	}

	public function dnsGetAvailableTTL() {
		$url = 'dns/get-available-ttl';

		return $this->apiRequest(false, $url);
	}

	public function dnsGetSOA($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/soa-details';

		return $this->apiRequest($data, $url);
	}

	public function dnsModifySOA($domain_name, $primary_ns, $admin_mail, $refresh, $retry, $expire, $default_ttl) {
		$data = '&domain-name=' . urlencode($domain_name) . '&primary-ns=' . urlencode($primary_ns) .
			'&admin-mail=' . urlencode($admin_mail) . '&refresh=' . urlencode($refresh) .
			'&retry=' . urlencode($retry) . '&expire=' . urlencode($expire) . '&default-ttl=' . urlencode($default_ttl);
		$url = 'dns/modify-soa';

		return $this->apiRequest($data, $url);
	}
	
	public function dnsGetDynamicURL ($domain_name, $record_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id);
		$url = 'dns/get-dynamic-url';
		
		return $this->apiRequest($data, $url);
	}

	public function dnsDisableDynamicURL($domain_name, $record_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id);
		$url = 'dns/disable-dynamic-url';

		return $this->apiRequest($data, $url);
	}

	public function dnsChangeDynamicURL($domain_name, $record_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id);
		$url = 'dns/change-dynamic-url';

		return $this->apiRequest($data, $url);
	}

	public function dnsImportViaTransfer($domain_name, $server) {
		$data = '&domain-name=' . urlencode($domain_name) . '&server=' . urlencode($server);
		$url = 'dns/axfr-import';

		return $this->apiRequest($data, $url);
	}

	public function dnsChangeRecordStatus($domain_name, $record_id, $status = false) {
		$data = '&domain-name=' . urlencode($domain_name) . '&record-id=' . urlencode($record_id) .
			'&status=' . urlencode($status);
		$url = 'dns/change-record-status';

		return $this->apiRequest($data, $url);
	}

	public function dnsAddMasterServer($domain_name, $master_ip) {
		$data = '&domain-name=' . urlencode($domain_name) . '&master-ip=' . urlencode($master_ip);
		$url = 'dns/add-master-server';

		return $this->apiRequest($data, $url);
	}

	public function dnsDeleteMasterServer($domain_name, $master_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&master-id=' . urlencode($master_id);
		$url = 'dns/delete-master-server';

		return $this->apiRequest($data, $url);
	}

	public function dnsListMasterServer($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/master-servers';

		return $this->apiRequest($data, $url);
	}

	public function dnsMailForwardsStats() {
		$url = 'dns/get-mail-forwards-stats.json';

		return $this->apiRequest(false, $url);
	}
	
	public function dnsAvailableMailForwards() {
		$url = 'dns/get-mailforward-servers';

		return $this->apiRequest(false, $url);
	}

	public function dnsAddMailForward($domain_name, $box, $host, $destination) {
		$data = '&domain-name=' . urlencode($domain_name) . '&box=' . urlencode($box) . '&host=' . urlencode($host) .
			'&destination=' . urlencode($destination);
		$url = 'dns/add-mail-forward';

		return $this->apiRequest($data, $url);
	}

	public function dnsDeleteMailForward($domain_name, $mail_forward_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&mail-forward-id=' . urlencode($mail_forward_id);
		$url = 'dns/delete-mail-forward';

		return $this->apiRequest($data, $url);
	}

	public function dnsModifyMailForward($domain_name, $box, $host, $destination, $mail_forward_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&box=' . urlencode($box) . '&host=' . urlencode($host) .
			'&destination=' . urlencode($destination) . '&mail-forward-id=' . urlencode($mail_forward_id);
		$url = 'dns/modify-mail-forward';

		return $this->apiRequest($data, $url);
	}

	public function dnsListMailForwards($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/mail-forwards';

		return $this->apiRequest($data, $url);
	}

	public function dnsAddCloudDomain($domain_name, $cloud_domain_name) {
		$data = '&domain-name=' . urlencode($domain_name) . '&cloud-domain-name=' . urlencode($cloud_domain_name);
		$url = 'dns/add-cloud-domain';

		return $this->apiRequest($data, $url);
	}

	public function dnsDeleteCloudDomain($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/delete-cloud-domain';

		return $this->apiRequest($data, $url);
	}

	public function dnsChangeCloudMaster($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/set-master-cloud-domain';

		return $this->apiRequest($data, $url);
	}

	public function dnsListCloudDomains($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/list-cloud-domains';

		return $this->apiRequest($data, $url);
	}

	public function dnsAllowNewIP($domain_name, $ip) {
		$data = '&domain-name=' . urlencode($domain_name) . '&ip=' . urlencode($ip);
		$url = 'dns/axfr-add';

		return $this->apiRequest($data, $url);
	}

	public function dnsDeleteAllowedIP($domain_name, $id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&id=' . urlencode($id);
		$url = 'dns/axfr-remove';

		return $this->apiRequest($data, $url);
	}

	public function dnsListAllowedIP($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/axfr-list';

		return $this->apiRequest($data, $url);
	}

	public function dnsHourlyStatistics($domain_name, $year, $month, $day) {
		$data = '&domain-name=' . urlencode($domain_name) . '&year=' . urlencode($year) .
			'&month=' . urlencode($month) . '&day=' . urlencode($day);
		$url = 'dns/statistics-hourly';

		return $this->apiRequest($data, $url);
	}

	public function dnsDailyStatistics($domain_name, $year, $month) {
		$data = '&domain-name=' . urlencode($domain_name) . '&year=' . urlencode($year) . '&month=' . urlencode($month);
		$url = 'dns/statistics-daily';

		return $this->apiRequest($data, $url);
	}

	public function dnsMonthlyStatistics($domain_name, $year) {
		$data = '&domain-name=' . urlencode($domain_name) . '&year=' . urlencode($year);
		$url = 'dns/statistics-monthly';

		return $this->apiRequest($data, $url);
	}

	public function dnsYearlyStatistics($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/statistics-yearly';

		return $this->apiRequest($data, $url);
	}

	public function dnsLast30DaysStatistics($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/statistics-last-30-days';

		return $this->apiRequest($data, $url);
	}

	public function dnsGetParkedTemplates() {
		$url = 'dns/get-parked-templates';

		return $this->apiRequest(false, $url);
	}

	public function dnsGetParkedSettings($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/get-parked-settings';

		return $this->apiRequest($data, $url);
	}

	public function dnsModifyParkedSettings($domain_name, $template, $title = false, $description = false, $keywords = false, $contact_form = false) {
		$data = '&domain-name=' . urlencode($domain_name) . '&template=' . urlencode($template) .
			'&title=' . urlencode($title) . '&description=' . urlencode($description) .
			'&keywords=' . urlencode($keywords) . '&contact-form=' . urlencode($contact_form);
		$url = 'dns/set-parked-settings';

		return $this->apiRequest($data, $url);
	}

	public function dnsListGeoDNSLocations($domain_name) {
		$data = '&domain-name=' . urlencode($domain_name);
		$url = 'dns/get-geodns-locations';

		return $this->apiRequest($data, $url);
	}

	public function dnsAddGroup($domain_name, $name) {
		$data = '&domain-name=' . urlencode($domain_name) . '&name=' . urlencode($name);
		$url = 'dns/add-group';

		return $this->apiRequest($data, $url);
	}

	public function dnsChangeGroup($domain_name, $group_id) {
		$data = '&domain-name=' . urlencode($domain_name) . '&group-id=' . urlencode($group_id);
		$url = 'dns/change-group';

		return $this->apiRequest($data, $url);
	}

	public function dnsListGroups() {
		$url = 'dns/list-groups';

		return $this->apiRequest(false, $url);
	}

	public function dnsDeleteGroup($group_id) {
		$data = '&group-id=' . urlencode($group_id);
		$url = 'dns/delete-group';

		return $this->apiRequest($data, $url);
	}

	public function dnsRenameGroup($group_id, $new_name) {
		$data = '&group-id=' . urlencode($group_id) . '&new-name=' . urlencode($new_name);
		$url = 'dns/rename-group';

		return $this->apiRequest($data, $url);
	}

}