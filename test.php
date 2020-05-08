<?php

require_once "app/bootstrap.php";

$HtmlPage = new \HtmlPage();
?>
	<h3 class="title">Lookup Patient And MyChart Account</h3>

	<div x-data="MyChartApp()">
		<form action="" method="POST" @submit.prevent="onSubmit()">
			<input x-ref="mrn" style="width: 300px" type="text" name="mrn" placeholder="enter a medical record number (i.e. 202500)" value="<?php print($mrn) ?>">
			<button type="submit">Check</button>
		</form>
		<pre  x-ref="results"></pre>
	</div>
<style>
.title {
	color:#800000;
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
		onSubmit() {
			var mrn_field = this.$refs.mrn
			var results_element = this.$refs.results
			var mrn = mrn_field.value
			var promise = LookupPatientAndMyChartAccount(mrn)
			promise.then(function(response) {
				var data = response.data
				results_element.innerHTML = JSON.stringify(data)
			})
		},
	}
}
</script>