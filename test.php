<?php

require_once "app/bootstrap.php";

$HtmlPage = new \HtmlPage();
?>
	<h3 class="title">Lookup Patient And MyChart Account</h3>

	<div x-data="MyChartApp()">
		<form id="mychart-form" class="form-inline" action="" method="POST" @submit.prevent="onSubmit()">
			<input class="form-control" x-model="mrn" type="text" name="mrn" placeholder="enter a medical record number (i.e. 202500)" value="<?php print($mrn) ?>">
			<button class="btn btn-primary ml-2" type="submit" :disabled="mrn.trim()==''">Check</button>
		</form>
		<pre  id="results" x-html="response_html" class="mt-2"></pre>
	</div>
<style>
.title {
	color:#800000;
}
#mychart-form input[name="mrn"] {
	width: 300px;
}
#results {
	min-height: 300px;
	max-width: 600px;
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
		setResponseHtml(html) {
			this.response_html = html
		},
		onSubmit() {
			var mrn = this.mrn
			if(mrn.trim()=='') return
			var promise = LookupPatientAndMyChartAccount(mrn)
			var self = this // reference to self
			self.setResponseHtml('loading...') // reset results
			promise.then(function(response) {
				var data = response.data
				self.setResponseHtml(JSON.stringify(data, null, '\t'))
			}).catch(function(error) {
				var message = error.message || "error processing the request"
				self.setResponseHtml(message)
			})
		},
	}
}
</script>