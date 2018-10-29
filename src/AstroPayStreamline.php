<?php

/**
 * Example of using the class:
 * require_once 'AstroPayStreamline.class.php';
 * $aps = new AstroPayStreamline();
 * $banks = $apd->get_banks_by_country('BR', 'json');
 *
 * Available functions:
 * - newinvoice($invoice, $amount, $bank, $country, $iduser, $cpf, $name, $email, $currency, $description, $bdate, $address, $zip, $city, $state, $return_url, $confirmation_url)
 * - get_status($invoice)
 * - get_exchange($country = 'BR', $amount = 1) {befault: $country = BR | $amount = 1}
 * - get_banks_by_country($country, $type) {befault: $country = BR | $type = 'json'}
 *
 */

/**
 * Class of AstroPay Streamline
 *
 * @author Santiago del Puerto (santiago@astropay.com)
 * @version 1.0
 *
 */

class AstroPayStreamline {

	/**************************
	 * Merchant configuration *
	 **************************/
	private $x_login = '';
	private $x_trans_key = '';

	private $x_login_for_webpaystatus = '';
	private $x_trans_key_for_webpaystatus = '';

	private $secret_key = '';

	private $sandbox = true;
	/*********************************
	 * End of Merchant configuration *
	 *********************************/



	/*****************************************************
	 * ---- PLEASE DON'T CHANGE ANYTHING BELOW HERE ---- *
	 *****************************************************/



	private $url = array(
		'newinvoice' => '',
		'status' => '',
		'exchange' => '',
		'banks' => ''
	);
	private $errors = 0;

	public function __construct(){
		$this->errors = 0;

		$this->url['newinvoice'] = 'https://apd-api.astropay.com/api_curl/streamline/newinvoice/';
		$this->url['status'] = 'https://apd-api.astropay.com/apd/webpaystatus';
		$this->url['exchange'] = 'https://apd-api.astropay.com/apd/webcurrencyexchange';
		$this->url['banks'] = 'https://apd-api.astropay.com/api_curl/apd/get_banks_by_country';

		if ($this->sandbox){
			$this->url['newinvoice'] = 'https://sandbox.astropaycard.com/api_curl/streamline/newinvoice';
			$this->url['status'] = 'https://sandbox.astropaycard.com/apd/webpaystatus';
			$this->url['exchange'] = 'https://sandbox.astropaycard.com/apd/webcurrencyexchange';
			$this->url['banks'] = 'https://sandbox.astropaycard.com/api_curl/apd/get_banks_by_country';
		}
	}

	public function newinvoice($invoice, $amount, $bank, $country, $iduser, $cpf, $name, $email, $currency='', $description='', $bdate='', $address='', $zip='', $city='', $state='', $return_url='', $confirmation_url=''){

		$params_array = array(
			//Mandatory
			'x_login' => $this->x_login,
			'x_trans_key' => $this->x_trans_key,
			'x_invoice' => $invoice,
			'x_amount' => $amount,
			'x_bank' => $bank,
			'type'=>'json',
			'x_country' => $country,
			'x_iduser' => $iduser,
			'x_cpf' => $cpf,
			'x_name' => $name,
			'x_email' => $email,
      'x_version' => '1.1',
		);

		//Optional
		if(!empty($currency)){
			$params_array['x_currency']=$currency;
		}
		if(!empty($description)){
			$params_array['x_description']=$description;
		}
		if(!empty($bdate)){
			$params_array['x_bdate']=$bdate;
		}
		if(!empty($address)){
			$params_array['x_address']=$address;
		}
		if(!empty($zip)){
			$params_array['x_zip']=$zip;
		}
		if(!empty($city)){
			$params_array['x_city']=$city;
		}
		if(!empty($state)){
			$params_array['x_state']=$state;
		}
		if(!empty($return_url)){
			$params_array['x_return']=$return_url;
		}
		if(!empty($confirmation_url)){
			$params_array['x_confirmation']=$confirmation_url;
		}


		$message = $invoice .'V' . $amount .'I' . $iduser .'2' . $bank .'1' . $cpf .'H' . $bdate .'G' . $email .'Y' . $zip .'A' . $address .'P' . $city .'S' . $state . 'P';
		$control = strtoupper(hash_hmac('sha256', pack('A*', $message), pack('A*', $this->secret_key)));
		$params_array['control'] = $control;

		$response = $this->curl($this->url['newinvoice'], $params_array);
		return $response;
	}

	public function get_status($invoice){
		$params_array = array(
			//Mandatory
			'x_login' => $this->x_login_for_webpaystatus,
			'x_trans_key' => $this->x_trans_key_for_webpaystatus,
			'x_invoice' => $invoice,
      'x_version' => '1.1',
      'type' => 'json'
		);

		$response = $this->curl($this->url['status'], $params_array);
		return $response;
	}

	public function get_exchange($country = 'BR', $amount = 1){
		$params_array = array(
			//Mandatory
			'x_login' => $this->x_login_for_webpaystatus,
			'x_trans_key' => $this->x_trans_key_for_webpaystatus,
			'x_country' => $country,
			'x_amount' => $amount,
      'x_version' => '1.1',
		);

		$response = $this->curl($this->url['exchange'], $params_array);
		return $response;
	}

	public function get_banks_by_country($country = 'BR', $type = 'json'){
		$params_array = array(
			//Mandatory
			'x_login' => $this->x_login,
			'x_trans_key' => $this->x_trans_key,
			'country_code' => $country,
			'type' => $type,
      'x_version' => '1.1'
		);

		$response = $this->curl($this->url['banks'], $params_array);
		return $response;
	}


	/**
	 * END OF PUBLIC INTERFACE
	 */
	private function curl($url, $params_array) {
		$params = array();
		foreach ($params_array as $key => $value){
			$params[] = "{$key}={$value}";
		}
		$params = join('&', $params);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$response = curl_exec($ch);
		if (($error = curl_error($ch)) != false) {
			$this->errors++;

			if ($this->errors >= 5){
				die("Error in curl: $error");
			}

			sleep(1);
			$this->curl($url, $params_array);
		}
		curl_close($ch);

		$this->errors = 0;
		return $response;
	}

}
