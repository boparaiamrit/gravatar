<?php
/**
 * Created by PhpStorm.
 * User: boparaiamrit
 * Date: 8/1/16
 * Time: 4:15 PM
 */

namespace Boparaiamrit\GravatarProfile;


use Boparaiamrit\GravatarProfile\Exceptions\InvalidEmailException;
use Bugsnag\Client as Bugsnag;
use GuzzleHttp\Client;
use Illuminate\Config\Repository as Config;

class GravatarClient
{
	protected $Client;
	
	function __construct(Config $Config, Bugsnag $Bugsnag)
	{
		$baseUri = $Config->get('gravatar-profile.base_uri');
		
		$this->Client = new Client(['base_uri' => $baseUri]);
	}
	
	private function checkEmail($email)
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			throw new InvalidEmailException ('Please specify a valid email address');
	}
	
	private function hashEmail($email)
	{
		return md5(strtolower(trim($email)));
	}
	
	private function buildRoute($email)
	{
		return $email . '.json';
	}
	
	public function getUserProfile($email)
	{
		$this->checkEmail($email);
		
		$email = $this->hashEmail($email);
		$route = $this->buildRoute($email);
		
		$response = $this->Client->get($route);
		$content  = $response->getBody()->getContents();
		
		$data = \GuzzleHttp\json_decode($content, true);
		
		return array_get($data, 'entry.0');
	}
}