var bo_fade_opacity = 0.3;
var opt_prefix = 'opc_';


// Maintain correct id_customer in Edit customer group link
$('#' + opt_prefix + 'payment_customer_id').keyup(function () {
    var id_cust = $('#' + opt_prefix + 'payment_customer_id').val();
    var link = $('#edit_sim_cust').attr('href');
    if (id_cust > 0) {
        $('#edit_sim_cust').attr('href', link.replace(/id_customer=\d+/, "id_customer=" + id_cust));
    }
});


// Conditional fields
function setOpacityFields() {
    if ($('input[name=' + opt_prefix + 'page_fading]:checked').val() == 1) {
        $('input[name^=' + opt_prefix + 'fading_]').parent().prev('label').andSelf().css('opacity', 1);
    } else {
        $('input[name^=' + opt_prefix + 'fading_]').parent().prev('label').andSelf().css('opacity', bo_fade_opacity);
    }
    if (ps_guest_checkout)
        $('input[name=' + opt_prefix + 'display_password_msg]').parent().prev('label').andSelf().css('opacity', bo_fade_opacity);
}

// urobit page fading disabled ked je sticky header cart turned on
// a tiez v opc-configu ci kde sa optiony dostavaju von, aby page fading bol off ked je sticky header cart on


function setInlineValidationFields() {
    if ($('input[name=' + opt_prefix + 'inline_validation]:checked').val() == 1) {
        $('input[name=' + opt_prefix + 'validation_checkboxes]').parent().prev('label').andSelf().css('opacity', 1);
    } else {
        $('input[name=' + opt_prefix + 'validation_checkboxes]').parent().prev('label').andSelf().css('opacity', bo_fade_opacity);
    }
}

function setScrollSummary() {
    if ($('input[name=' + opt_prefix + 'scroll_summary]:checked').val() == 1) {
        $('input[name=' + opt_prefix + 'scroll_products]').parent().prev('label').andSelf().css('opacity', 1);
    } else {
        $('input[name=' + opt_prefix + 'scroll_products]').parent().prev('label').andSelf().css('opacity', bo_fade_opacity);
    }
}

function setHidePasswordBox() {
    if ($('input[name=' + opt_prefix + 'hide_password_box]:checked').val() == 1) {
        $('#'+opt_prefix + 'offer_password_top_off').click();
        $('input[name=' + opt_prefix + 'offer_password_top]').parent().prev('label').andSelf().css('opacity', bo_fade_opacity);
    } else {
        $('input[name=' + opt_prefix + 'offer_password_top]').parent().prev('label').andSelf().css('opacity', 1);
    }
}

// Realtime change
$('input[name=' + opt_prefix + 'page_fading]').change(function () {
    setOpacityFields();
});

$('input[name=' + opt_prefix + 'inline_validation]').change(function () {
    setInlineValidationFields();
});

$('input[name=' + opt_prefix + 'scroll_summary]').change(function () {
    setScrollSummary();
});

$('input[name=' + opt_prefix + 'hide_password_box]').change(function () {
    setHidePasswordBox();
});


// On page load - initial settings
setOpacityFields();
setInlineValidationFields();
setScrollSummary();
setHidePasswordBox();

function emailLangFile() {
    $.ajax({
        type:'POST',
        url:location.href,
        async:true,
        cache:false,
        dataType:"json",
        data:'ajax=true&emailLangFile=1',
        success:function (json) {
            // display Thank you
            $('#emailLangFile').html('Thank you!');
        }
    });
}

$('input[name=submitSupportEmail]').click(submitSupportEmail);

function submitSupportEmail() {
    $.ajax({
        type:'POST',
        url:location.href,
        async:true,
        cache:false,
        dataType:"json",
        data:'submitSupportEmail=1&'+$("#submitSupportEmailForm").serialize(),
        success:function (json) {
            // display Thank you
            $('#submitSupportEmailForm').html('<p class="thank_you">Thank you!</p><p>We have sent you confirmation email, please check your inbox. In case you have not received anything from us, contact us directly at prestamodules@gmail.com.</p>');
        }
    });
}

function displaySupport() {
    $('div#opc-support').slideDown();
    $('#display_support_anchor').hide();
}

function toggle_settings_anchor(collapseStr, expandStr, settingsGroup) {
    if ($(settingsGroup+':visible').length > 0) {
        $(settingsGroup).slideToggle('slow');
        $(settingsGroup+'_anchor>span').html(expandStr);
    } else {
        $(settingsGroup).slideToggle('slow');
        $(settingsGroup+'_anchor>span').html(collapseStr);
    }
}


$('a[id$="_settings_anchor"]').each(function() {
    $(this).click(function() {
        var x = $(this).attr('id');
        toggle_settings_anchor('[-]', '[+]', '#'+x.substring(0, x.length-'_anchor'.length));
    })
});

function setTwoColumnOptions(el) {
    $('#opc_sample_values_off, #opc_scroll_header_cart_off, #opc_cart_summary_bottom_on, #opc_offer_password_top_on, #opc_remove_ref_on').click();
    el.html("saving...");
    el.fadeTo("slow", 0.6, function() {$('input[name=submitOPC]')[0].click()});
}