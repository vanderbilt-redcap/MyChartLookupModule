<?php
namespace Vanderbilt\MyChartLookup;

use FhirTokenManager;
use Vanderbilt\MyChartLookup\App\Endpoints\LookupPatientAndMyChartAccountEndpoint;

require_once "app/bootstrap.php";

$HtmlPage = new \HtmlPage();

?>
<h3 class="title">Lookup Patient And MyChart Account</h3>

<p>Check the MyChart status for a specific MRN.</p>
<p>Possible values are:</p>
<ul>
	<li><span><strong>0 or empty</strong>: No MyChart account</span></li>
	<li><span><strong>1</strong>: Activated</span></li>
	<li><span><strong>2</strong>: Inactivated</span></li>
	<li><span><strong>3</strong>: Pending Activation</span></li>
	<li><span><strong>4</strong>: Non Standard MyChart Status</span></li>
	<li><span><strong>5</strong>: Patient Declined</span></li>
	<li><span><strong>6</strong>: Activation Code Generated, but Disabled</span></li>
</ul>

<div x-data="MyChartApp()" class="card">
	<div class="card-body">
		<h5 class="card-title">Check the status for a patient</h5>
		<form id="mychart-form" class="form-inline" action="" @reset.prevent="onReset()">
			<input class="form-control" x-model="mrn" type="text" name="mrn" placeholder="enter a medical record number (i.e. 202500)" value="<?php print($mrn) ?>">
			<button class="btn btn-primary ml-2" type="button" :disabled="loading || mrn.trim()==''"@click="lookup()">Check</button>
			<button class="btn btn-info ml-2" type="reset" :disabled="loading || response_html.trim()==''">Reset</button>
		</form>
		<div id="results-container" class="mt-2" x-show="loading || response_html">
			<span x-show="loading">Loading <i class="fas fa-spinner fa-spin"></i></span>
			<div class="results" x-html="response_html"></div>
		</div>
	</div>

</div>

<div x-data="BatchUpdateApp()" class="card mt-2">
	<div class="card-body">
		<h5 class="card-title">Batch update MyChart status for all records</h5>
		<form id="batch-update-form" class="form-inline" action="" @submit.prevent="onSubmit()" @reset.prevent="onReset()">
			<button class="btn btn-primary" type="submit" :disabled="loading">Batch update</button>
		</form>
		<div id="results-container" class="mt-2" x-show="loading || response_html">
			<span x-show="loading">Loading <i class="fas fa-spinner fa-spin"></i></span>
			<div class="results" x-html="response_html"></div>
		</div>
	</div>

</div>

<div>
</div>

<style>
.title {
	color:#800000;
}
#mychart-form input[name="mrn"] {
	width: 300px;
}
#results-container{
	/* min-height: 300px; */
	max-width: 300px;
    padding: 9.5px;
    margin: 0 0 10px;
    font-size: 13px;
    color: #333;
    word-break: break-all;
    word-wrap: break-word;
    background-color: #f5f5f5;
    border: 1px solid #ccc;
    border-radius: 4px;
}
#results-container .results {
	white-space: pre-wrap;
}
</style>

<script src="<?= $module->getUrl('assets/js/axios.min.js') ?>"></script>
<script type="module" src="<?= $module->getUrl('assets/js/alpine/alpine.min.js') ?>"></script>
<script nomodule src="<?= $module->getUrl('assets/js/alpine/alpine-ie11.min.js') ?>" defer></script>

<script>
/**
 * alpine app
 *
 * @return object
 */
function MyChartApp() {

	var base_url = '<?= $module->getBaseUrl() ?>'
	var api_client= axios.create({
		baseURL: base_url,
		headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}, // header for ajax detection
	})
	/**
	 * send a request to the LookupPatientAndMyChartAccount endpoint
	 */
	function LookupPatientAndMyChartAccount(mrn) {
		var query_params = new URLSearchParams()
		query_params.append('page', 'api')
		query_params.append('route', '/LookupPatientAndMyChartAccount')
		var url = `${base_url}&${query_params.toString()}`
		var params = new URLSearchParams()
		params.append('mrn', mrn)
		return api_client.post(url, params)
	}

	
	return {
		mrn: '',
		response_html: '',
		loading: false,
		setResponseHtml(html) {
			this.response_html = html
		},
		lookup() {
			var mrn = this.mrn
			if(mrn.trim()=='') return
			var promise = LookupPatientAndMyChartAccount(mrn)
			var self = this // reference to self
			self.loading = true
			self.setResponseHtml('') // reset results
			promise.then(function(response) {
				var data = response.data
				self.setResponseHtml(JSON.stringify(data, null, '\t'))
			}).catch(function(error) {
				var message = error.message || "error processing the request"
				self.setResponseHtml(message)
			}).finally(function() {
				self.loading = false
			})
		},
		onReset() {
			this.mrn = ''
			this.response_html = ''
		}
	}
}

function BatchUpdateApp() {
	var base_url = '<?= $module->getBaseUrl() ?>'
	var api_client= axios.create({
		baseURL: base_url,
		headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}, // header for ajax detection
	})
	/**
	 * send a request to the LookupPatientAndMyChartAccount endpoint
	 */
	function updateAll() {
		var query_params = new URLSearchParams()
		query_params.append('page', 'api')
		query_params.append('route', '/updateAll')
		var url = `${base_url}&${query_params.toString()}`
		return api_client.put(url)
	}
	return {
		response_html: '',
		loading: false,
		setResponseHtml(html) {
			this.response_html = html
		},
		onSubmit() {
			var promise = updateAll()
			var self = this // reference to self
			self.loading = true
			self.setResponseHtml('') // reset results
			promise.then(function(response) {
				var data = response.data
				self.setResponseHtml(JSON.stringify(data, null, '\t'))
			}).catch(function(error) {
				var message = error.message || "error processing the request"
				self.setResponseHtml(message)
			}).finally(function() {
				self.loading = false
			})
		},
	}
}
</script>