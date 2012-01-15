<?php
function REST_verb_is($verb)
{
	$verb = strtoupper($verb);
	if ( isset($_GET['verb']) ) {
		return strtoupper($_GET['verb']) == $verb;
	}
	return isset($_SERVER['REQUEST_METHOD']) ? ( strtoupper($_SERVER['REQUEST_METHOD']) == $verb ) : 'GET' == $verb;
}

class REST_Response {
	private $headers  = array('status' => 200);
	private $callback = '';
	private $status   = '';
	private $data     = null;
	private $message  = null;
	
	public function __construct($callback = '')
	{
		if ( !empty($callback) ) {
			$this->setCallback($callback);
		}
	}
	
	public function setCallback($callback) 
	{
		$this->callback = $callback;
	}
	
	public function header($key, $value)
	{
		$this->headers[$key] = $value;
	}
	
	public function setMessage($message)
	{
		$this->message = $message;
	}
	
	public function setStatus($status)
	{
		$this->status = $status;
	}
	
	public function setData($data)
	{
		$this->data = $data;
	}
	
	public function error($message = 'Unspecified error', $data = null)
	{
		$this->setStatus('error');
		$this->setMessage($message);
		if ( $data !== 'null' ) {
			$this->setData($data);
		}
		$this->sendResponse();
	}
	
	public function success($data, $message = null)
	{
		$this->setStatus('success');
		$this->setData($data);
		if ( $message !== null ) {
			$this->setMessage($message);
		}
		$this->sendResponse();
	}

	public function sendResponse()
	{
		$envelope = new stdClass;
		$envelope->meta   = $this->headers;
		$envelope->status = $this->status;
		
		if ( $this->data !== null ) {
			$envelope->data = $this->data;
		}
		
		if ( !empty($this->message) ) {
			$envelope->message = $this->message;
		}
		
		if ( !empty($this->callback) ) {
			header('Content-Type: text/javascript');
			$envelope->meta = $this->headers;
			$json = $this->callback . '(' . json_encode($envelope) . ');';
			header('Content-Length: ' . strlen($json) );
			echo $json;
			exit;
		}
		
		header('Content-Type: application/json');
		unset($envelope->meta);
		foreach ( $this->headers AS $k => $v ) {
			header($k . ': ' . $v);
		}
		
		$json = json_encode($envelope);
		header('Content-Length: ' . strlen($json) );
		echo $json;
		exit;
	}
}

function REST_request($verb, $url, $data = null, $decodeJson = true)
{
	$url = 'http://' . $_SERVER['SERVER_NAME'] . $url;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Session-ID: ' . session_id() ) );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	switch ( strtoupper($verb) ) {
		case 'POST':
			break;
			
		case 'PUT':
			break;
			
		case 'DELETE':
			break;
			
		default:
			if ( is_array($data) ) {
				$url .= '?' . http_build_query($data);
			} else if ( !empty($data) ) {
				$url .= '?' . $data;
			}
	}
		
	$ret = curl_exec($ch);
	curl_close($ch);
	if ( !$decodeJson ) {
		return $ret;
	}
	return json_decode($ret);	
}