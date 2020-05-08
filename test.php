<?php

require_once "app/bootstrap.php";

$HtmlPage = new \HtmlPage();
?>
	<h3 class="title">Lookup Patient And MyChart Account</h3>
	<form action="" method="POST" id="lookup-mychart-form">
		<input style="width: 300px" type="text" name="mrn" placeholder="enter a medical record number (i.e. 202500)" value="<?php print($mrn) ?>">
		<button type="submit">Check</button>
	</form>
	<button id="test-button">test</button>
	<pre id="results"></pre>

	<div x-data="app()">
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
<!-- <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script> -->
<script type="module" src="<?= $module->getUrl('assets/js/alpine/alpine.min.js') ?>"></script>
<script nomodule src="<?= $module->getUrl('assets/js/alpine/alpine-ie11.min.js') ?>" defer></script>

<script>
	var base_url = '<?= $module->getBaseUrl() ?>'
	var api_client= axios.create({
		baseURL: base_url,
		headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}, // header for ajax detection
	})

	function app() {
        return {
            show: false,
            onSubmit() {
				var mrn_field = this.$refs.mrn
				var results_element = this.$refs.results
				var mrn = mrn_field.value
				var promise = this.LookupPatientAndMyChartAccount(mrn)
				promise.then(function(response) {
					var data = response.data
					results_element.innerHTML = JSON.stringify(data)
				})
			},
			LookupPatientAndMyChartAccount(mrn) {
				var query_params = new URLSearchParams()
				query_params.append('page', 'api')
				query_params.append('route', '/LookupPatientAndMyChartAccount')
				var url = `${base_url}&${query_params.toString()}`
				var params = new URLSearchParams()
				params.append('mrn', mrn)
				return api_client.post(url, params)
			}
        }
    }
	
(function(document, window){

	var test_button_selector = '#test-button'
	var form_selector = '#lookup-mychart-form'
	var mrn_field_selector = 'input[name="mrn"]'
	var results_selector = '#results'
	
	var base_url = '<?= $module->getBaseUrl() ?>'
	var api_client= axios.create({
		baseURL: base_url,
		headers: {common: {'X-Requested-With': 'XMLHttpRequest'}}, // header for ajax detection
    })

	/**
	 * make a request to the test endpoint
	 *
	 * @return void
	 */
	function test() {
		try {
			var query_params = new URLSearchParams()
			query_params.append('page', 'api')
			query_params.append('route', '/test')
			api_client.get('', {params:query_params})
		}catch(error) {
			console.log(error)
		}
	}

	function LookupPatientAndMyChartAccount(mrn) {
		var query_params = new URLSearchParams()
		query_params.append('page', 'api')
		query_params.append('route', '/LookupPatientAndMyChartAccount')
		var url = `${base_url}&${query_params.toString()}`
		var params = new URLSearchParams()
		params.append('mrn', mrn)
		return api_client.post(url, params)
	}

	/**
	 * set the event listener and init the app
	 *
	 * @return void
	 */
	function init() {
		var form = document.querySelector(form_selector)
		var mrn_field = document.querySelector(mrn_field_selector)
		var results_container = document.querySelector(results_selector)
		
		// manage form submission
		form.addEventListener('submit', function(e) {
			e.preventDefault()
			var mrn = mrn_field.value
			if(mrn.trim()=='') {
				alert('you must provide an MRN')
				return
			}
			var promise = LookupPatientAndMyChartAccount(mrn)
			promise.then(function(response) {
				var data = response.data
				results_container.innerHTML = JSON.stringify(data)
			})
		})

		// test button
		var test_button = document.querySelector(test_button_selector)
		test_button.addEventListener('click', test)
	}


    document.addEventListener("DOMContentLoaded", function() {
		// init()
	})
}(document, window))
</script>