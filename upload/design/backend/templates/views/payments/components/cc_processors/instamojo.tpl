<div class="control-group">
    <label class="control-label" for="instamojo_api_key">API key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_api_key]" id="merchant_id" value="{$processor_params.instamojo_api_key}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_auth_token">Auth Token:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_auth_token]" id="password" value="{$processor_params.instamojo_auth_token}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_private_salt">Private Salt:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_private_salt]" id="instamojo_private_salt" value="{$processor_params.instamojo_private_salt}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_payment_url">Payment URL:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_payment_url]" id="instamojo_payment_url" value="{$processor_params.instamojo_payment_url}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_custom_field">Custom Field:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][instamojo_custom_field]" id="instamojo_custom_field" value="{$processor_params.instamojo_custom_field}"   size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="instamojo_currency_code">Currency Code:</label>
    <div class="controls">
        <input type="text" placeholder="INR" name="payment_data[processor_params][instamojo_currency_code]" id="instamojo_currency_code" value="{$processor_params.instamojo_currency_code}"   size="60">
    </div>
</div>