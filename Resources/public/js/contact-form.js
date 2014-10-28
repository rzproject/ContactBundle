function rz_contact_us_form(options) {
    this.id = options.id;
    this.field_container = options.field_container;
    this.url = options.url;
    this.error_container = options.error_container;
    this.disabled = options.disabled;
    this.init();
}

rz_contact_us_form.prototype = {
    init: function() {
        var that = this;
        that.toggleFormState(that.disabled);

        if(!that.disabled && that.url ==='') {
            that.toggleFormState(true);
        }

        jQuery(sprintf('#%s', this.id)).submit(function(event) {
            that.submitForm(event, this);
            event.preventDefault();
            event.stopPropagation();
            return false;
        });
    },

    //submit form
    submitForm: function(event, form) {
        var that = this;
        event.preventDefault();
        event.stopPropagation();
        var formObj = jQuery(form);

        //jQuery.blockUI({ message:'Processing'});

        jQuery(formObj).ajaxSubmit({
            type: formObj.attr('method'),
            url: formObj.attr('action'),
            dataType: 'json',
            data: {_xml_http_request: true},
            success: function(data) {
                //cleanup messages
                that.resetErrors();
                that.resetForm();
                formObj.prepend(sprintf('<div class="alert alert-success"><strong>%s</strong></div>', data.message));
                //jQuery.unblockUI();
            },
            error: function(data) {
                var msg = JSON.parse(data.responseText);
                //cleanup messages
                that.resetErrors();
                //display error messages
                jQuery.each(msg.messages.fields, function(index, value){
                    jQuery( formObj )
                        .find( sprintf('%s[for="%s"]',that.error_container, index) )
                        .html( value );
                });
                formObj.prepend(sprintf('<div class="alert alert-error"><strong>%s</strong></div>', msg.message));
                jQuery.unblockUI();
            }
        });
    },

    resetErrors: function() {
        var that = this;
        //cleanup messages
        jQuery(sprintf('#%s', that.id)).find(sprintf("%s",that.error_container)).html( "" );
        jQuery(sprintf('#%s', that.id)).find('.popask-error').remove();
    },

    resetForm: function() {
        var that = this;
        jQuery(sprintf('#%s', that.id)).find("input[type=text], textarea, select").val("");
    },

    toggleFormState: function(disabled) {
        var that = this;
        jQuery(sprintf('#%s', that.id)).find("input[type=text], input[type=submit], textarea, select").attr("disabled", disabled);
    }

}