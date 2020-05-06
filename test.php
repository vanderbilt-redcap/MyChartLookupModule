<?php

require_once "app/bootstrap.php";

$HtmlPage = new \HtmlPage();
$HtmlPage->PrintHeaderExt();

function mergeParams($defaultParams, $overrideParams=array())
{
	$params = array_merge(
		$defaultParams,
		array_intersect_key($overrideParams, $defaultParams)
	);
	return $params;
}

function postData($url, $access_token, $data=[], $settings=[])
{
	global $fhir_client_id;
	$default_settings = array(
		'headers' => array(
			'Accept' => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Authorization' => 'Bearer '.$access_token,
			'Epic-Client-ID' => $fhir_client_id,
		),
		'form_params' => $data,
	);
	$options = mergeParams($default_settings, $settings);

	$response = \HttpClient::request('POST', $url, $options);
	return $response->getBody();
}

function getBaseUrl()
{
	global $fhir_endpoint_base_url;
	$reg_exp = '/(?<base>.+?)api\/FHIR\/(?:DSTU2|STU3|R4)\/?$/i';
	$base_url = preg_replace($reg_exp, '\1', $fhir_endpoint_base_url);
	return $base_url;
}

function LookupPatientAndMyChartAccount($mrn)
{
	global $userid;
	try {
		$base_url = getBaseUrl();
		$user_id = \User::getUIIDByUsername($userid);
		$url_template = 'api/epic/2019/PatientAccess/Patient/LookupPatientAndMyChartAccount';
		$url = $base_url.$url_template;
		$token_manager = new \FhirTokenManager($user_id);
		$access_token = $token_manager->getAccessToken();
		$data = [
			'PersonID' => $mrn,
			'PersonIDType' => ['MRN'],
		];
		$response = postData($url, $access_token, $data);
		return json_decode($response);
	} catch (\Exception $e) {
		return $e->getMessage();
	}
}
// Your HTML page content goes here
$mrn = $_POST['mrn'] ?: '';
?>
<h3 style="color:#800000;">
Lookup Patient And MyChart Account
</h3>
<form action="" method="POST" >
	<input style="width: 300px" type="text" name="mrn" placeholder="enter a medical record number (i.e. 202500)" value="<?php print($mrn) ?>">
	<button type="submit">Check</button>
</form>
<p>
<?= getBaseUrl() ?>api/epic/2019/PatientAccess/Patient/LookupPatientAndMyChartAccount
</p>
<pre class="results">
<?php
if(!empty($mrn))
{
	$json = LookupPatientAndMyChartAccount($mrn);
	$response = json_encode($json, JSON_PRETTY_PRINT);
	print($response);
}
?>
</pre>
<style>
.results {
	min-height: 300px;
	max-width: 600px;
	white-space: pre-wrap;
}
</style>
<?php
// OPTIONAL: Display the footer
$HtmlPage->PrintFooterExt();