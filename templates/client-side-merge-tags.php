<script type="text/javascript">
    gform.addFilter("gform_merge_tags", "add_merge_tags");
    function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
        mergeTags["custom"].tags.push({ tag: '{securesubmit_transaction_id}', label: 'SecureSubmit Transaction ID' });
        mergeTags["custom"].tags.push({ tag: '{securesubmit_authorization_code}', label: 'SecureSubmit Authorization Code' });

        return mergeTags;
    }
</script>
