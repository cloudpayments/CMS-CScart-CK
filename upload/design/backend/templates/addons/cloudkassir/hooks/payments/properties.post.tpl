<div class="control-group">
    <label class="control-label" for="elm_payment_cloudkassir_{$id}">{__("cloudkassir.payment_send_cloudkassir")}:</label>
    <div class="controls">
        <input type="checkbox" name="payment_data[cloudkassir_payment_id]" {if $cloudkassir_payment_id == 'Y'}checked="checked"{/if} id="elm_payment_cloudkassir_{$id}" value="Y">
    </div>
</div>