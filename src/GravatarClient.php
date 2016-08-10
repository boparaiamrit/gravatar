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
	
	protected $email;
	protected $profile;
	
	protected $domainMapper = [
		'twitter' => 'twitter.com'
	];
	
	function __construct(Config $Config, Bugsnag $Bugsnag)
	{
		$baseUri = $Config->get('gravatar-profile.base_uri');
		
		$this->Client = new Client(['base_uri' => $baseUri]);
	}
	
	private function checkEmail()
	{
		if (!filter_var($this->email, FILTER_VALIDATE_EMAIL))
			throw new InvalidEmailException ('Please specify a valid email address');
	}
	
	private function hashEmail()
	{
		$this->email = md5(strtolower(trim($this->email)));
	}
	
	private function buildRoute()
	{
		return $this->email . '.json';
	}
	
	public function fetchProfile()
	{
		$this->checkEmail();
		$this->hashEmail();
		
		$route = $this->buildRoute();
		
		$response = $this->Client->get($route);
		
		$content = $response->getBody()->getContents();
		
		$content = \GuzzleHttp\json_decode($content, true);
		
		$this->profile = array_get($content, 'entry.0', []);
		
		return $this->profile;
	}
	
	
	public function getProfile()
	{
		if (empty($this->profile)) {
			return $this->fetchProfile();
		}
		
		return $this->profile;
	}
	
	public function resetProfile()
	{
		$this->profile = null;
		
		return true;
	}
	
	public function getUsername()
	{
		return array_get($this->getProfile(), 'preferredUsername');
	}
	
	public function getThumbnail()
	{
		return array_get($this->getProfile(), 'thumbnailUrl');
	}
	
	private function getAccounts()
	{
		return array_get($this->getProfile(), 'accounts', []);
	}
	
	public function getDomain($social)
	{
		return array_get($this->domainMapper, $social);
	}
	
	public function getSocialAccount($social = null)
	{
		$accounts = $this->getAccounts();
		$domain   = $this->getDomain($social);
		
		$socialAccount = [];
		foreach ($accounts as $account) {
			if (array_has($account, 'domain') and array_get($account, 'domain') == $domain) {
				$socialAccount = $account;
				break;
			}
		}
		
		return $socialAccount;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}
	
	/**
	 * @param mixed $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		$this->resetProfile();
	}
}