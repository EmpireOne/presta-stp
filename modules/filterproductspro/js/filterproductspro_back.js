
$(function(){
    //Ocultar filas del formulario de filtros, excepto al del {buscador}
    $('#form_filter tbody tr:gt(0), #form_filter tfoot, #save_dependency_options, #data_list').hide();
    //Crear controles jQuery UI
    $('#tabs').tabs({
        cookie : {
            expires : 1
        }
    });
    $(':button').button();
    $('#add_filter').button({
        icons:{
            primary:'ui-icon-plusthick'
        }
    });
    $('#options_custom_add_product').button({
        icons:{
            primary:'ui-icon-plusthick'
        },
        text:false
    });
    $('.button_save').button({
        icons:{
            primary:'ui-icon-disk'
        }
    });
    $('.button_clean').button({
        icons:{
            primary:'ui-icon-document'
        }
    });
    $('.button_refresh').button({
        icons:{
            primary:'ui-icon-refresh'
        }
    });
    
    //Desplegar formulario
    $('#frmFilterProductsPro').fadeIn(200);
        
    //Autoseleccionar valores
    $('#searchers, #searchers_dependency_filters, #searchers_dependency_options, #searchers_options_custom').val('');
    
    //Eventos Buscador
    $('#save_searcher').click(function(){
        Searcher.saveSearcher();
    });
    
    $('#clear_searcher').click(function(){
        Searcher.cleanSearchersForm();
    });
        
    //Eventos Filtros y Opciones
    $('#add_filter').click(function(){        
        FilterOptions.addQuitFilter();
    });
        
    $('#searchers').change(function(){
        FilterOptions.getFiltersListBySearcher();
    });
    
    $('#sort_options_filter').click(function() {
        FilterOptions.sortOptionsFilter();
    });
    
    $('#save_filter').click(function(){
        FilterOptions.saveFilter();
    });
    
    $('#clear_filter').click(function(){
        FilterOptions.cleanFilterForm({update: true});
    });
    
    $('#types').change(function(){
        FilterOptions.changeType();
    }).trigger('change');
    
    $('#criterions').change(function(){
        FilterOptions.changeCriterion();
    }).trigger('change');

    $('input[id^="column_value_lang_"]').blur(function(){
        FilterOptions.saveLanguageForColumn($(this));
    });
        
    $('#features').change(function(){
        FilterOptions.changeFeature();
    });
    
    $('#attributes_group').change(function(){
        FilterOptions.changeAttributeGroup();
    });
    
    $('#div_categories_treeview .toolbar > a').click(function(){
        FilterOptions.observeCategoriesSelectionToolbar();
    });
    
    //Eventos Herramientas
    $('#reindex_categories').click(function(){
        Tools.reindexCategories();
    });
    
    $('#renindex_products').click(function(){
        Tools.reindexProducts();
    });
    
    $('#save_configuration').click(function(){
        Tools.saveConfiguration();
    });
    
    //Eventos de Dependencia de Filtros
    $('#searchers_dependency_filters').change(function(){
        DependencyFilters.getFiltersListBySearcher();
    });
    
    $('#save_dependency_filters').click(function(){
        DependencyFilters.save();
    }).hide();
    
    //Eventos de Dependencia de Opciones
    $('#searchers_dependency_options').change(function(){
        DependencyOptions.getFiltersListBySearcher();
    });
    
    $('#filters_dependency_options').change(function(){
        $('#dependency_options-page').val('0');
        DependencyOptions.getDataFilter(0);
    });
    
    $('#save_dependency_options').click(function(){
        DependencyOptions.save('');
    });
    
    $('#dependency_options-load_more_options').click(function(){        
        $('#dependency_options-page').val( parseInt($('#dependency_options-page').val()) + 1 );        
        DependencyOptions.getDataFilter(parseInt($('#dependency_options-page').val()));
    });
    
    //Eventos de Opciones Customizadas
    OptionsCustom.init();
    
    $('#searchers_options_custom').change(function(){
        OptionsCustom.getFiltersListBySearcher();
    });
    
    $('#filters_options_custom').change(function(){        
        $('#options_custom-page').val('0');
        OptionsCustom.getOptionsByFilter(0);
    });
    
    $('#save_option_custom').click(function(){
        OptionsCustom.save();
    });
    
    $('#clear_custom_option').click(function(){
        OptionsCustom.clearForm();
    });
    
    $('#options_custom_add_product').click(function(){
        OptionsCustom.addProduct();
    });
    
    $('#options_custom-load_more_options').click(function(){        
        $('#options_custom-page').val( parseInt($('#options_custom-page').val()) + 1 );        
        OptionsCustom.getOptionsByFilter(parseInt($('#options_custom-page').val()));
    });            
    
    //Eventos de Rango de Precios
    $('#conditions').change(function(){
        RangePrice.changeCondition();
    });
    
    $('#save_range_price').click(function(){
        RangePrice.save();
    });
    
    $('#clear_range_price').click(function(){
        RangePrice.clearForm();
    });
});

//Funcionalidad de la pestaña 'Buscador'
var Searcher = {
    saveSearcher: function(){
        var id_searcher = $('#id_searcher').val();
        var internal_name = $('#internal_name').val();
        var public_names = Util._getValuesLang('#public_name_lang_');
        var position = $('#position').val();
        var instant_search = $('#instant_search_on').is(':checked') ? 1 : 0;
        var hide_filter_category = $('#hide_filter_category_on').is(':checked') ? 1 : 0;
        var hide_filter_manufacturer = $('#hide_filter_manufacturer_on').is(':checked') ? 1 : 0;
        var hide_filter_supplier = $('#hide_filter_supplier_on').is(':checked') ? 1 : 0;
        var type_filter_category = $('#type_filter_category').val();
        var filter_categories = $('#filter_categories').val();
        var active = $('#searcher_active_on').is(':checked') ? 1 : 0;
        
        if(!Util._checkValueLang({el: $(':text[id^="public_name_lang_"]:visible:eq(0)'), siblings_expr: 'div.public_name_lang', force_def_lang: true})){
            alert(Msg.errors.invalid_public_name_searcher);
            return;
        }
                
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=updateSearcher' + 
                '&id_searcher=' + id_searcher +
                '&internal_name=' + internal_name +
                '&public_names=' + public_names +
                '&position=' + position +
                '&instant_search=' + instant_search +
                '&hide_filter_category=' + hide_filter_category +
                '&hide_filter_manufacturer=' + hide_filter_manufacturer +
                '&hide_filter_supplier=' + hide_filter_supplier +
                '&type_filter_category=' + type_filter_category +
                '&filter_categories=' + filter_categories +
                '&active=' + active ,
            beforeSend: function()
            {
                $('#tab_searcher > table:eq(0)').addOverlay();
                $('#save_searcher').disableButton(Msg.processing);
            },
            success: function(json)
            {               
                if(json.message_code == 0){                    
                    Searcher.getSearchers();
                    Searcher.cleanSearchersForm();
                    
                    $('#div_loading_searcher').removeAttr('class').addClass('conf').html(json.message);
                }else{
                    $('#div_loading_searcher').removeAttr('class').addClass('error').html(json.message);
                }                                                                                      
                
                $('#save_searcher').enableButton(Msg.save);
            },
            complete: function(){
                $('#tab_searcher > table:eq(0)').delOverlay();                
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_searcher').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    editSearcher: function(id_searcher){
        $('#id_searcher').val('');
        if(id_searcher != null && id_searcher != undefined && id_searcher > 0){
            var img_edit = $('#edit_' + id_searcher).parent().html();        
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=loadSearcher' + 
                    '&id_searcher=' + id_searcher,
                beforeSend: function(){
                    $("#div_loading_searcher").removeAttr('class').html('');
                    $('#delete_' + id_searcher).remove();
                    $('#edit_' + id_searcher).replaceWith('<img id="edit_' + id_searcher + '" src="' + filterproductspro_img + 'loader.gif" />');
                },
                success: function(json)
                {
                    if(json.message_code == 0){
                        $('#save_searcher').enableButton(Msg.update);
                        $('#id_searcher').val(id_searcher);
                        
                        $('#internal_name').val(json.data.internal_name);
                        $('#position').val(json.data.position);
                        
                        $(':radio[name="instant_search"][value="' + parseInt(json.data.instant_search) + '"]').attr('checked', true);
                        $(':radio[name="hide_filter_category"][value="' + parseInt(json.data.hide_filter_category) + '"]').attr('checked', true);
                        $(':radio[name="hide_filter_manufacturer"][value="' + parseInt(json.data.hide_filter_manufacturer) + '"]').attr('checked', true);
                        $(':radio[name="hide_filter_supplier"][value="' + parseInt(json.data.hide_filter_supplier) + '"]').attr('checked', true);
                        $(':radio[name="searcher_active"][value="' + parseInt(json.data.active) + '"]').attr('checked', true);
                        
                        $('#type_filter_category').val(json.data.type_filter_category);
                        $('#filter_categories').val(json.data.filter_categories);                        
                        
                        Util._setValuesLang('#public_name_lang_', json.data.names);
                    }
                    else
                        $('#div_loading_searcher').removeAttr('class').addClass('error').html(json.message);
                },
                complete: function(){
                    $('#edit_' + id_searcher).replaceWith(img_edit);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_searcher').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            });
        }
    },
    deleteSearcher: function(id_searcher){
        if(id_searcher != null && id_searcher != undefined && id_searcher > 0){
            if(confirm(Msg.confirm_delete)){
                var img_del = $('#delete_' + id_searcher).parent().html();
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: true,
                    cache: false,
                    dataType : 'json',
                    data: 'action=deleteSearcher' + 
                        '&id_searcher=' + id_searcher,
                    beforeSend: function()
                    {
                        $("#div_loading_searcher").removeAttr('class').html('');
                        $('#edit_' + id_searcher).remove();
                        $('#delete_' + id_searcher).replaceWith('<img id="delete_' + id_searcher + '" src="' + filterproductspro_img + 'loader.gif" />');
                    },
                    success: function(json)
                    {
                        if(json.message_code == 0){
                            $('#delete_' + id_searcher).parent().parent().addClass('strike').fadeOut(500, function(){
                                $(this).remove();
                        
                                if($('#searchers_list tbody tr').length <= 0)
                                    $('#searchers_list tfoot').fadeIn(200);
                            });
                            
                            //Eliminar el buscador de la lista de buscadores en las demas tabs y lanzar evento  {change}
                            $('#searchers option[value="' + id_searcher + '"], #searchers_dependency_filters option[value="' + id_searcher + '"], '+
                            '#searchers_dependency_options option[value="' + id_searcher + '"], #searchers_options_custom option[value="' + id_searcher + '"]').remove();
                            $('#searchers, #searchers_dependency_filters, #searchers_dependency_options, #searchers_options_custom').trigger('change');
                        }
                        else{
                            alert(json.message);
                        }                                                                                                                                                    
                    },
                    complete: function(){
                        $('#delete_' + id_searcher).replaceWith(img_del);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
        }
    },
    getSearchers: function(){
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=getSearchersList',
            beforeSend: function()
            {
                $('#searchers_list tbody').empty().append('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
            },
            success: function(json)
            {                
                $('#searchers_list tbody').empty();
                //Eliminar los items de la lista de buscadores de las pestañas
                $('#searchers option:gt(0), #searchers_dependency_filters option:gt(0), #searchers_dependency_options option:gt(0), #searchers_options_custom option:gt(0)').remove();

                if(json.message_code == 0){
                    $.each(json.data, function(i, data){
                        var html = '<tr>' +
                                        '<td>' + data.id_searcher + '</td>' +
                                        '<td>' + data.internal_name + '</td>' +
                                        '<td>' + data.name + '</td>' +
                                        '<td>' + data.position + '</td>' +
                                        '<td>' + ((parseInt(data.instant_search) == 1) ? '<img src="' + filterproductspro_img + 'enabled.gif" />' : '<img src="' + filterproductspro_img + 'disabled.gif" />') + '</td>' +
                                        /*'<td>' + ((parseInt(data.hide_filter_category) == 1) ? '<img src="' + filterproductspro_img + 'enabled.gif" />' : '<img src="' + filterproductspro_img + 'disabled.gif" />') + '</td>' +*/
                                        '<td>' + ((parseInt(data.active) == 1) ? '<img src="' + filterproductspro_img + 'enabled.gif" />' : '<img src="' + filterproductspro_img + 'disabled.gif" />') + '</td>' +
                                        '<td class="actions">' +
                                            '<img id="edit_' + data.id_searcher + '" src="' + filterproductspro_img + 'edit.png" onclick="Searcher.editSearcher(' + data.id_searcher + ')" />' +
                                            '<img id="delete_' + data.id_searcher + '" src="' + filterproductspro_img + 'delete.png" onclick="Searcher.deleteSearcher(' + data.id_searcher + ')" />' +
                                        '</td>' +
                                    '</tr>';

                        $('#searchers_list tbody').append(html);
                        
                        //Actualizar la lista de buscadores de la pestaña 'Filtros y Opciones'
                        var option = '<option value="' + data.id_searcher + '">' + data.internal_name + '</option>';
                        $('#searchers, #searchers_dependency_filters, #searchers_dependency_options, #searchers_options_custom').append(option);
                    });
                    
                    if((json.data).length > 0)
                        $('#searchers_list tfoot').fadeOut(200);
                    else
                        $('#searchers_list tfoot').fadeIn(200);
                    
                }else{
                    $('#div_loading_searcher').removeAttr('class').addClass('error').html(json.message);
                }                                                                                                                                           
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_searcher').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    cleanSearchersForm: function(){
        $('#id_searcher').val('');
        $('#internal_name').val('');
        Util._cleanValuesLang('public_name_lang_');
        $('#position option:eq(0)').attr('selected', true);
        $('#searcher_active_on, #instant_search_off, #hide_filter_category_off, #hide_filter_manufacturer_off, #hide_filter_supplier_off').attr('checked', true);
        $('#type_filter_category').val('');
        $('#filter_categories').val('');
        $('#save_searcher').enableButton(Msg.save);
    }    
};

//Funcionalidad de la pestaña 'Filtro y Opciones'
var FilterOptions = {
    addQuitFilter: function(params){
        var p = $.extend({},{
            force: false
        },params);
                
        if((!p.force && $.isEmpty($('#searchers').val())) || (p.force && !$('#form_filter tbody tr:gt(0)').is(':visible')))
            return;
        
        $('#form_filter tbody tr:gt(0), #form_filter tfoot, #data_list .content').slideToggle(100);
        if($('#criterions').val() == GLOBALS.Criterions.Category && $.isEmpty($('#id_filter').val())){
            $('#div_categories_treeview').slideToggle(100);
        }
        else
            $('#div_categories_treeview').hide();
        $('#add_filter').find('span.ui-button-icon-primary').toggleClass('ui-icon-plusthick ui-icon-minusthick');
        $('#types').trigger('change');
        
        if ($('#criterions').val() != GLOBALS.Criterions.Custom)
            $('#tr_clone_filter_custom').hide();
    },
    changeType: function(){
        var type = $('#types').val();
        if(type != GLOBALS.Types.Select){
            $('#row_multi_option').show();
        }
        else{
            $('#row_multi_option').hide();
        }
    },
    changeCriterion: function(){
        var criterion = $('#criterions').val();
        
        $('#data_list .content').empty();
        $('#tab_filter_options .category, #tab_filter_options .feature, #tab_filter_options .attribute_group, #tab_filter_options .custom').hide();
        $('#features, #attributes_group').attr('disabled', true);
        $('#num_columns').attr('disabled', false);
        $('#tr_clone_filter_custom').hide();        
        
        if(criterion == GLOBALS.Criterions.Category){
            $('#save_filter').hide();
            $('#tab_filter_options .category').fadeIn(200);
                        
            if(!$.isEmpty($('#id_filter').val())){
                $('#div_categories_treeview').hide();
                $('#save_filter').show();
            }
            else if(!$.isEmpty($('#searchers').val())){
                $('#div_categories_treeview').show();
                $('#save_filter').hide();
            }
        }
        else{
            $('#save_filter').show();
            $('#div_categories_treeview').hide();
        }
                
        if(criterion == GLOBALS.Criterions.Feature){
            $('#tab_filter_options .feature').fadeIn(200);
            $('#features').attr('disabled', (!$.isEmpty($('#id_filter').val()) ? true : false));
            this.changeFeature();
        }
                     
        if(criterion == GLOBALS.Criterions.Attribute){
            $('#tab_filter_options .attribute_group').fadeIn(200);
            $('#attributes_group').attr('disabled', (!$.isEmpty($('#id_filter').val()) ? true : false));
            this.changeAttributeGroup();
        }
                     
        if(criterion == GLOBALS.Criterions.Custom){
            $('#tab_filter_options .custom').fadeIn(200);
            $('#num_columns').attr('disabled', true);
            $('#tr_clone_filter_custom').show();
        }
                        
        if($.inArray(criterion, new Array(GLOBALS.Criterions.Category, GLOBALS.Criterions.Manufacturer, GLOBALS.Criterions.Supplier, GLOBALS.Criterions.Price, GLOBALS.Criterions.Custom)) > -1)
            $('#data_list').stop().fadeOut(100);
        else
            $('#data_list').stop().fadeIn(100);
    },
    changeFeature: function(){
        var id_feature = $('#features').val();
                
        if(!$.isEmpty(id_feature)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=getValuesByFeature' + 
                    '&id_feature=' + id_feature,
                beforeSend: function()
                {
                    $('#data_list .content').empty();
                    $('#div_loading_filter').removeAttr('class').empty();
                    $('#filters_list .content').addOverlay();    
                },
                success: function(json)
                {               
                    if(json.message_code == 0){
                        var data_list = $('<ul></ul>').attr({id: 'data_list_items'}).addClass('sortable data_list_items').appendTo($('#data_list .content'));
                        
                        $.each(json.data, function(i, value){
                            var item = $('<li></li>')
                                .attr({id: value.id_feature_value})
                                .addClass('ui-state-default')           
                                .append('<span class="ui-icon ui-icon-carat-1-e"></span>')                         
                                .append('<span class="title">' + value.value + '</span>')
                                .appendTo(data_list);
                        });
                        
                        if((json.data).length == 0)
                            $('#data_list .content').html(Msg.without_results);
                    }else{
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    }
                },
                complete: function(){
                    $('#filters_list .content').delOverlay();                
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    changeAttributeGroup: function(){
        var id_attr_group = $('#attributes_group').val();
                
        if(!$.isEmpty(id_attr_group)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=getAttributesByAttributeGroup' + 
                    '&id_attr_group=' + id_attr_group,
                beforeSend: function()
                {
                    $('#data_list .content').empty();
                    $('#div_loading_filter').removeAttr('class').empty();
                    $('#filters_list .content').addOverlay();    
                },
                success: function(json)
                {               
                    if(json.message_code == 0){
                        var data_list = $('<ul></ul>').attr({id: 'data_list_items'}).addClass('sortable data_list_items').appendTo($('#data_list .content'));
                        
                        $.each(json.data, function(i, attr){
                            var item = $('<li></li>')
                                .attr({id: attr.id_attribute})
                                .addClass('ui-state-default')           
                                .append('<span class="ui-icon ui-icon-carat-1-e"></span>')                         
                                .append('<span class="title">' + attr.name + '</span>')
                                .append(attr.color ? $('<span class="color"></span>').css({backgroundColor: attr.color}) : '')
                                .appendTo(data_list);
                        });
                        
                        if((json.data).length == 0)
                            $('#data_list .content').html(Msg.without_results);
                    }else{
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    }
                },
                complete: function(){
                    $('#filters_list .content').delOverlay();                
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    _stopSortableFilters : function(event, ui){          
        var order_filters = $('#sortable_filters').sortable('toArray');
        
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=updateFiltersPosition' + 
                '&order_filters=' + order_filters,
            beforeSend: function()
            {
                $('#filters_list .content').addOverlay();                
                $(':button').attr('disabled', true);
            },
            success: function(json)
            {               
                if(json.message_code == 0){
                    
                }else{
                    $.each(json.errors, function(i, error){
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(error + '<br />');
                    });
                }

                $(':button').attr('disabled', false);
            },
            complete: function(){
                $('#filters_list .content').delOverlay();                
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    
    sortOptionsFilter: function(event) {
        var id_filter = $('#id_filter_sort').val();
        
        if(id_filter != '') {
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: {
                    action: 'sortOptionsFilter',
                    id_filter: id_filter,
                    sort_asc: sort_asc
                },
                beforeSend: function()
                {
                    $('#sort_options_filter').attr('disabled', true);
                },
                success: function(json)
                {
                    if (json.message_code == 0) {
                        FilterOptions.getOptionsByFilter(id_filter)
                        sort_asc = !sort_asc;
                    }
                    else 
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                        
                        
                    $('#sort_options_filter').attr('disabled', false);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    
    getFiltersListBySearcher: function(event){
        var id_searcher = $('#searchers').val();
        
        $('#columns_list .column_filter_text_wrapper').appendTo($('#flags_for_columns'));
        
        if(!$.isEmpty(id_searcher)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getFiltersListBySearcher' + 
                    '&id_searcher=' + id_searcher,
                beforeSend: function()
                {
                    $('#tab_filter_options table:eq(0)').addOverlay();
                    $('#filters_list .content, #options_list .content').empty();                    
                    $(':button').attr('disabled', true);
                },
                success: function(json)
                {               
                    if(json.message_code == 0){
                        $('#filters_list .content').empty();
                        var items = $('<ul></ul>').attr({id: 'sortable_filters'}).addClass('sortable').appendTo($('#filters_list .content'));
                        $.each(json.data, function(i, filter){
                            var item = $('<li></li>')
                                .attr({id: filter.id_filter})
                                .addClass('ui-state-default')
                                .append('<span class="ui-icon ui-icon-arrow-4-diag"></span>')
                                .append('<span onclick="FilterOptions.editFilter(' + filter.id_filter + ',event)" class="ui-icon ui-icon-pencil tools ui-corner-all" title="' + Msg.edit + '"></span>')
                                .append(parseInt(filter.active) == 1 ? '<span onclick="FilterOptions.activeFilter(' + filter.id_filter + ',0,event)" class="active ui-icon ui-icon-check tools ui-corner-all" title="' + Msg.disable + '"></span>' : '<span onclick="FilterOptions.activeFilter(' + filter.id_filter + ',1,event)" class="active ui-icon ui-icon-cancel tools ui-corner-all" title="' + Msg.enabled + '"></span>')
                                .append('<span onclick="FilterOptions.deleteFilter(' + filter.id_filter + ',event)" class="ui-icon ui-icon-trash tools ui-corner-all" title="' + Msg.del + '"></span>')
                                .append('<span class="title">' + filter.name + '</span>')
                                .append('<span onclick="FilterOptions.getOptionsByFilter(' + filter.id_filter + ')" class="ui-icon ui-icon-wrench tools ui-corner-all" title="' + Msg.configure + '"></span>')
                                .appendTo(items);
                        });    
                        
                        $('#options_list .content').html(Msg.options_empty);
                        $('#columns_list .content').html(Msg.columns_none);
                        
                        if((json.data).length == 0){
                            $('#filters_list .content').html(Msg.filters_empty);                            
                        }
                        else if((json.data).length > 1){
                            $('#sortable_filters').sortable({
                                placeholder: 'ui-state-highlight',
                                stop: FilterOptions._stopSortableFilters
                            });
                            $('#sortable_filters').disableSelection();
                        }
                    }else{
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    }
                    
                    $(':button').attr('disabled', false);
                },
                complete: function(){
                    $('#tab_filter_options table:eq(0)').delOverlay();                
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        else{
            FilterOptions.addQuitFilter({force: true});
            $('#filters_list .content').html(Msg.searcher_null);
            $('#options_list .content').html(Msg.options_null);
            $('#columns_list .content').html(Msg.columns_none);
        }
    },
    saveFilter: function(){
        var id_filter = $('#id_filter').val();
        var id_searcher = $('#searchers').val();
        var names = Util._getValuesLang('#filter_name_lang_');
        var criterion = $('#criterions').val();
        var id_filter_custom_clone = $('#clone_filter_custom').val();
        
        /*
         * {level_depth}
         * Puede representar varios valores, ya que en la BD se almacena para diferentes conceptos
         * Para {Category} contiene un {Bool} indicando si el filtro incluye las subcategorias en la busqueda
         * Para {Feature} contiene el {Id_Feature} seleccionado
         * Para {Attribute} contiene el {Id_Attribute} seleccionado
         * *Para los criterios que no tienen ningún valor dependiente en {level_depth} se establece en {-1} 
         */
        var level_depth = $('#include_subcategories_search_on').is(':visible') ? ($('#include_subcategories_search_on').is(':checked') ? 1 : 0) : -1;
        level_depth = $('#features').is(':visible') ? $('#features').val() : level_depth;
        level_depth = $('#attributes_group').is(':visible') ? $('#attributes_group').val() : level_depth;
        
        var type = $('#types').val();
        var multi_option = $('#multioption_filter_options_on').is(':checked') ? 1 : 0;
        var num_columns = $('#num_columns').val();
        var search_ps = $('#use_engine_ps_search_on').is(':checked') ? 1 : 0;
        
        if(!Util._checkValueLang({el: $(':text[id^="filter_name_lang_"]:visible:eq(0)'), siblings_expr: 'div.filter_name_lang', force_def_lang: true})){
            alert(Msg.errors.invalid_name_filter);
            return;
        }
        
        //---------------------------------------------------------------------
        //Exclusivo para el criterio 'Category'(C)
        var categories_selected = [];
        $('input[name="categoryBox[]"]:checked').each(function(i, elem){
            if(!$.isEmpty($(elem).val()))
                categories_selected.push($(elem).val());
        });
        
        if($.isEmpty(id_filter) && criterion == GLOBALS.Criterions.Category && categories_selected.length == 0){
            alert(Msg.errors.empty_categories_selected);
            return;
        }
        //---------------------------------------------------------------------
                
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: false,
            cache: false,
            dataType : 'json',
            data: 'action=updateFilter' + 
                '&id_filter=' + id_filter +
                '&id_searcher=' + id_searcher +
                '&names=' + names +
                '&criterion=' + criterion +
                '&type=' + type +
                '&multi_option=' + multi_option +
                '&level_depth=' + level_depth +
                '&num_columns=' + num_columns +
                '&search_ps=' + search_ps +
                '&categories_selected=' + categories_selected +
                '&id_filter_custom_clone=' + id_filter_custom_clone,
            beforeSend: function()
            {
                $('#tab_filter_options > table:eq(0)').addOverlay();
                $('#save_filter').disableButton(Msg.processing);
            },
            success: function(json)
            {               
                if(json.message_code == 0){                    
                    FilterOptions.cleanFilterForm({update: true});
                    FilterOptions.getFiltersListBySearcher();
                    
                    //Autoseleccionar el filtro y cargar sus opciones
                    if(id_filter != undefined && id_filter != null && id_filter == ''){
                        var id = json.data.id;
                        if(id != undefined){
                            $('#sortable_filters li#' + id).addClass('selected_filter');
                            FilterOptions.getOptionsByFilter(id);
                        }
                    }
                    
                    $('#add_filter').trigger('click');
                    
                    $('#div_loading_filter').removeAttr('class').addClass('conf').html(json.message);
                }else{
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                }
            },
            complete: function(){
                $('#save_filter').enableButton(Msg.save);
                $('#tab_searcher > table:eq(0)').delOverlay();                
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#save_filter').enableButton(Msg.save);
                $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    editFilter: function(id_filter, event){
        //Forzar al navegador a reconocer el parametro falso {event}
        event = event || window.event;
        
        if(!$.isEmpty(id_filter)){
            var filter_name = $('#filters_list li[id="' + id_filter + '"] > span.title').html();
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=loadFilter' + 
                    '&id_filter=' + id_filter,
                beforeSend: function(){
                    $('#types').attr('disabled', false);
                    $('#filters_list li[id="' + id_filter + '"]').addOverlay();
                    $('#filters_list li[id="' + id_filter + '"] span.tools').hide();
                    $('#filters_list li[id="' + id_filter + '"] > span.title').append('&nbsp;' + Msg.is_loading);
                    $("#div_loading_filter").removeAttr('class').html('');                    
                },
                success: function(json)
                {
                    if(json.message_code == 0){
                        $('#save_filter').enableButton(Msg.update);
                        $('#id_filter').val(id_filter);                                                
                        
                        if(json.data.has_dependencies && json.data.type == GLOBALS.Types.Select){
                            $('#types').attr('disabled', true);
                        }
                                                
                        //Asignar valores y lanzar eventos {change}
                        $('#filters_list li[id="' + id_filter + '"] > span.title').html(filter_name);
                        $('#searchers').val(json.data.id_searcher);
                        Util._setValuesLang('#filter_name_lang_', json.data.names);
                        $('#criterions').val(json.data.criterion).trigger('change');
                        $('#types').val(json.data.type);
                        $('#features, #attributes_group').val(json.data.level_depth);
                        $('#num_columns').val(json.data.num_columns);
                        if(parseInt(json.data.multi_options) == 1)
                            $('#multioption_filter_options_on').attr('checked', true);
                        else
                            $('#multioption_filter_options_off').attr('checked', true);
                        
                        if(parseInt(json.data.search_ps) == 1)
                            $('#use_engine_ps_search_on').attr('checked', true);
                        else
                            $('#use_engine_ps_search_off').attr('checked', true);
                                                
                        //Mostrar formulario
                        if(!$('#save_filter').is(':visible'))
                            $('#add_filter').trigger('click');
                        else
                            $('#types').trigger('change'); 
                        
                        //Deshabilitar controles no editables
                        $('#searchers, #criterions, .category > :radio, #features, #num_columns').attr('disabled', true);
                    }
                    else
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                },
                complete: function(){
                    $('#filters_list li[id="' + id_filter + '"]').delOverlay();
                    $('#filters_list li[id="' + id_filter + '"] span.tools').fadeIn(10);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            });        
        }
        else
            $('#id_filter').val('');
        
        //Detener el evento y evitar que sea capturado por un selector de descendencia mas alta
        event.stopPropagation();
    },
    deleteFilter: function(id_filter, event){
        //Forzar al navegador a reconocer el parametro falso {event}
        event = event || window.event;
        $(':button').attr('disabled', true).addOverlay();
        
        if(!$.isEmpty(id_filter)){
            if(confirm(Msg.confirm_delete)){
                var filter_name = $('#filters_list li[id="' + id_filter + '"] > span.title').html();

                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: false,
                    cache: false,
                    dataType : 'json',
                    data: 'action=deleteFilter' + 
                        '&id_filter=' + id_filter,
                    beforeSend: function(){
                        $('#filters_list li[id="' + id_filter + '"]').addOverlay();
                        $('#filters_list li[id="' + id_filter + '"] span.tools').hide();
                        $('#filters_list li[id="' + id_filter + '"] > span.title').append('&nbsp;' + Msg.is_deleting);
                        $("#div_loading_filter").removeAttr('class').html('');     
                        $('#columns_list .column_filter_text_wrapper').appendTo($('#flags_for_columns'));
                    },
                    success: function(json)
                    {
                        if(json.message_code == 0){
                            $('#filters_list li[id="' + id_filter + '"]').delOverlay().fadeOut(500,function(){                             
                                //Si el filtro removido es el que esta selecciona, se eliminan las opciones                                
                                var id_filter_selected = $('#sortable_filters li.selected_filter').attr('id'); 
                                //Si el filtro removido es el que esta cargado, se limpia el formulario
                                var id_filter_loaded = $('#id_filter').val();
                                
                                if(!$.isEmpty(id_filter_loaded) && id_filter_loaded == id_filter){
                                    $('#id_filter').val('');
                                    Util._cleanValuesLang('filter_name_lang_');
                                    $('#criterions option:eq(0), #types option:eq(0)').attr('selected', true).trigger('change');
                                    $('#types option:eq(0), #num_columns option:eq(0)').attr('selected', true)
                                    $('#multioption_filter_options_on').attr('checked', true);
                                    $('#save_filter').enableButton(Msg.save);
                                    
                                    $('#searchers, #criterions, #num_columns').attr('disabled', false);
                                }
                                                                
                                if(!$.isEmpty(id_filter_selected) && id_filter_selected == id_filter){
                                    $('#options_list .content').html(Msg.options_empty);
                                    $('#columns_list .content').html(Msg.columns_none);
                                    $('#columns_list .content').html(Msg.columns_none);
                                }
                                
                                //Eliminar filtro del select de dependencias de opciones
                                var opt = $('#filters_dependency_options option[value="' + id_filter + '"]');
                                if((opt).length){
                                    $(opt).remove();
                                    $('#filters_dependency_options option:eq(0)').attr('selected', true).trigger('change');
                                }
                                
                                //Eliminar filtro del select de opciones customizadas
                                opt = $('#filters_options_custom option[value="' + id_filter + '"]');
                                if((opt).length){
                                    $(opt).remove();
                                    $('#filters_options_custom option:eq(0)').attr('selected', true).trigger('change');
                                }
                                
                                //Eliminar item
                                $(this).remove();
                                
                                //Si no hay filtros, mostrar mensaje
                                if($('#sortable_filters li').length == 0)
                                    $('#filters_list .content').html(Msg.filters_empty);
                                //Si la cantidad de filtros < 1, se deshabilita el widget
                                else if($('#sortable_filters li').length <= 1)
                                    $('#sortable_filters').sortable('option','disabled', true);
                            });
                        }
                        else
                            $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    },
                    complete: function(){
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                    }
                });  
            }
        }
        else
            $('#id_filter').val('');
              
        //Habilitar botones
        $(':button').attr('disabled', false).delOverlay();    
              
        //Detener el evento y evitar que sea capturado por un selector de descendencia mas alta
        event.stopPropagation();
    },
    activeFilter: function(id_filter, active, event){
        //Forzar al navegador a reconocer el parametro falso {event}
        event = event || window.event;
        
        if(!$.isEmpty(id_filter) && (active == 1 || active == 0)){
            var filter_name = $('#filters_list li[id="' + id_filter + '"] > span.title').html();
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=activeFilter' + 
                    '&id_filter=' + id_filter +
                    '&active=' + active,
                beforeSend: function(){
                    $('#filters_list li[id="' + id_filter + '"]').addOverlay();
                    $('#filters_list li[id="' + id_filter + '"] span.tools').hide();
                    $('#filters_list li[id="' + id_filter + '"] > span.title').append('&nbsp;' + Msg.is_updating);
                    $("#div_loading_filter").removeAttr('class').empty();                    
                },
                success: function(json)
                {
                    if(json.message_code == 0){                        
                        //FilterOptions.getFiltersListBySearcher();                        
                        if(active == 0){
                            var span = '<span onclick="FilterOptions.activeFilter(' + id_filter + ',1,event)" class="active ui-icon ui-icon-cancel tools ui-corner-all" title="' + Msg.enabled + '"></span>';
                        }
                        else{
                            var span = '<span onclick="FilterOptions.activeFilter(' + id_filter + ',0,event)" class="active ui-icon ui-icon-check tools ui-corner-all" title="' + Msg.disable + '"></span>';
                        }
                                                
                        $('#filters_list li[id="' + id_filter + '"] span.active').replaceWith(span);
                    }
                    else
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    
                    $('#filters_list li[id="' + id_filter + '"] > span.title').html(filter_name);
                },
                complete: function(){
                    $('#filters_list li[id="' + id_filter + '"]').delOverlay();
                    $('#filters_list li[id="' + id_filter + '"] span.tools').fadeIn(10);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            }); 
        }
        
        //Detener el evento y evitar que sea capturado por un selector de descendencia mas alta
        event.stopPropagation();
    },
    _stopSortableOptions: function(event, ui){
        //El evento de ordenamiento es la lista de opciones
        if($(ui.item).parent().attr('id') == 'sortable_options'){
            var order_options = $('#sortable_options').sortable('toArray');
        
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=updateOptionsPosition' + 
                    '&order_options=' + order_options,
                beforeSend: function()
                {
                    $('#options_list .content').addOverlay();                
                    $(':button').attr('disabled', true);
                },
                success: function(json)
                {               
                    if(json.message_code == 0){

                    }else{
                        $.each(json.errors, function(i, error){
                            $('#div_loading_filter').removeAttr('class').addClass('error').html(error + '<br />');
                        });
                    }

                    $(':button').attr('disabled', false);
                },
                complete: function(){
                    $('#options_list .content').delOverlay();                
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        //El evento de ordenamiento es las columnas
        else{
            var column = $(ui.item).parent().parent();
            var id_col = parseInt($(column).attr('id').replace(/column_filter_/, ''));
            id_col = isNaN(id_col) ? 0 : id_col;
            
            if(id_col != null && id_col > 0){
                var order_options_column = $(column).children('ul.sortable_connect').sortable('toArray');
                
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: true,
                    cache: false,
                    dataType : 'json',
                    data: 'action=updateOptionsColumnPosition' + 
                        '&id_col=' + id_col +
                        '&order_options_column=' + order_options_column,
                    beforeSend: function()
                    {
                        $(column).addOverlay();                
                        $(column).find(':button').attr('disabled', true);
                    },
                    success: function(json)
                    {               
                        if(json.message_code == 0){
                        }else{
                            $.each(json.errors, function(i, error){
                                $('#div_loading_options_column').removeAttr('class').addClass('error').html(error + '<br />');
                            });
                        }
                                        
                        $(column).find(':button').attr('disabled', true);
                    },
                    complete: function(){
                        $(column).delOverlay();               
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_options_column').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                    }
                });
            }
        }
         
    },
    activeOption: function(id_option, active, event){
        //Forzar al navegador a reconocer el parametro falso {event}
        event = event || window.event;
        
        if(!$.isEmpty(id_option) && (active == 1 || active == 0)){
            var option_value = $('li[id="' + id_option + '"].option_filter > span.title').html();
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=activeOption' + 
                    '&id_option=' + id_option +
                    '&active=' + active,
                beforeSend: function(){
                    $('li[id="' + id_option + '"].option_filter').addOverlay();
                    $('li[id="' + id_option + '"].option_filter span.tools').hide();
                    $('li[id="' + id_option + '"].option_filter > span.title').append('&nbsp;' + Msg.is_updating);
                    $("#div_loading_filter").removeAttr('class').empty();                    
                },
                success: function(json)
                {
                    if(json.message_code == 0){                        
                        if(active == 0){
                            var span = '<span onclick="FilterOptions.activeOption(' + id_option + ',1,event)" class="ui-icon ui-icon-cancel tools ui-corner-all" title="' + Msg.enabled + '"></span>';
                        }
                        else{
                            var span = '<span onclick="FilterOptions.activeOption(' + id_option + ',0,event)" class="ui-icon ui-icon-check tools ui-corner-all" title="' + Msg.disable + '"></span>';
                        }
                                                
                        $('li[id="' + id_option + '"].option_filter span.tools').replaceWith(span);
                    }
                    else
                        $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
                    
                    $('li[id="' + id_option + '"].option_filter > span.title').html(option_value);
                },
                complete: function(){
                    $('li[id="' + id_option + '"].option_filter').delOverlay();
                    $('li[id="' + id_option + '"].option_filter span.tools').fadeIn(10);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            }); 
        }
        
        //Detener el evento y evitar que sea capturado por un selector de descendencia mas alta
        event.stopPropagation();
    },
    createColumnsWidget: function(params){
        var p = $.extend({},{
            num_columns: 2,
            styles: {}
        },params);
        
        //Validaciones previas
        if(p.num_columns <= 1)
            return false;
                                
        //Limpiar el contenedor
        $('#columns_list .content').empty();
        
        //Limpiar los lenguajes de las columnas
        $('input[name^="column_value_lang_"]').val('');
        
        //Calcular medidas para las columnas     
        var w = ((100 / p.num_columns) - 1.5) + '%';

        $.extend(p.styles, {
            width: w
        }, p.styles);
        
        //Mostrar flags de lenguaje
        for(var i=1; i <= p.num_columns; i++){  
            $('#languages_for_' + i)
            .addClass('column_filter_text_wrapper')
            .css({
                width: w
            })
            .appendTo($('#columns_list .content'))
            .children('div[id^="column_value_"]').addClass('column_value');
        }
                
        $('#columns_list .content').append('<div class="ui-helper-clearfix">&nbsp;</div>');
                
        //Crear columnas y asingar valores del lenguaje
        for(var j=1; j <= p.num_columns; j++){
            $('<div><div>')
                .attr('id', 'column_filter_' + p.ids_columns[j-1])
                .html('<ul class="item_column ui-helper-reset sortable sortable_connect" id="sortable_column_items_' + p.ids_columns[j-1] + '"></ul>')
                .addClass('column_filter')
                .css(p.styles)
                .appendTo($('#columns_list .content'));
                
            var values = p.values_by_column[p.ids_columns[j-1]];
            
            Util._setValuesLang('#column_value_lang_' + j + '_', values);
        }
        
        $('#columns_list .content').append('<div class="ui-helper-clearfix">&nbsp;</div>');
        
        //Posicionar las opciones la columna indicata
        $.each(p.ids_columns, function(i, id_col){
            if(p.options_by_column[id_col] != undefined)
                $.each(p.options_by_column[id_col], function(j, id_option){
                    $('#sortable_options li[id="' + id_option + '"].option_filter').fadeOut(function(){
                        $('#column_filter_' + id_col + ' ul.sortable_connect').append($(this).fadeIn());
                    });
                });
        });
        
        //Crear los controles UI         
        $('.sortable_connect').sortable({
            placeholder: 'ui-state-highlight',
            stop: FilterOptions._stopSortableOptions,
            connectWith: '.sortable_connect'
        });        
        $('#sortable_options').disableSelection();
        
        return true;
    },
    getOptionsByFilter: function(id_filter){ 
        $('#id_filter_sort').val(id_filter);
        $('#filters_list').addOverlay();        
        $('#filters_list li[id="' + id_filter + '"]').addClass('selected_filter').siblings('li').removeClass('selected_filter');
        $('#columns_list .column_filter_text_wrapper').appendTo($('#flags_for_columns'));
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: false,
            cache: false,
            dataType : 'json',
            data: 'action=getOptionsByFilter' + 
                '&id_filter=' + id_filter + 
                '&dependencies=0',
            beforeSend: function(){
                $('#columns_list .content').empty();
                $("#div_loading_filter").removeAttr('class').empty();
                $('#options_list .content').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
            },
            success: function(json)
            {
                if(json.message_code == 0){    
                    $('#options_list .content').empty();                  
                    if((json.data).length != 0){
                        var btn_reindex = $('<button></button>')
                                            .attr({
                                                type: 'button',
                                                id: 'reindex_options_by_filter'
                                            })                                            
                                            .button()                                            
                                            .enableButton(null)
                                            .bind('click', {id_filter: id_filter}, FilterOptions.reindexByFilter)
                                            .css({display: ($.inArray(json.data.filter.criterion, new Array(GLOBALS.Criterions.Category, GLOBALS.Criterions.Custom, GLOBALS.Criterions.Price)) > -1) ? 'none' : 'block'})
                                            .appendTo($('#options_list .content'));
                                            
                        $('#options_list .content').append('<div class="ui-helper-clearfix">&nbsp;</div>');
                        
                        var items = $('<ul></ul>').attr({id: 'sortable_options'}).addClass('sortable sortable_connect').appendTo($('#options_list .content'));
                        
                        $.each(json.data.options, function(i, option){
                            var value = (json.data.filter.criterion == GLOBALS.Criterions.Price ? Util._getLabelOptionRangePrice(option.value) : option.value);
                                                        
                            var item = $('<li></li>')
                                .attr({id: option.id_option})
                                .addClass('ui-state-default option_filter')
                                .append('<span class="ui-icon ui-icon-arrow-4-diag"></span>')
                                .append(parseInt(option.active) == 1 ? '<span onclick="FilterOptions.activeOption(' + option.id_option + ',0,event)" class="ui-icon ui-icon-check tools ui-corner-all" title="' + Msg.disable + '"></span>' : '<span onclick="FilterOptions.activeOption(' + option.id_option + ',1,event)" class="ui-icon ui-icon-cancel tools ui-corner-all" title="' + Msg.enabled + '"></span>')
                                .append('<span class="title">' + value + '</span>')
                                .appendTo(items);
                        });
                        
                        var num_columns_filter = (json.data.filter.num_columns) ? json.data.filter.num_columns : 1;
                        var ids_columns = (json.data.filter.ids_columns) ? json.data.filter.ids_columns : {};
                        var options_by_column = (json.data.filter.options_by_column) ? json.data.filter.options_by_column : {};
                        var values_by_column = (json.data.filter.values_by_column) ? json.data.filter.values_by_column : {};
                                                    
                        if((json.data.options).length == 0){
                            $('#sortable_options, #options_list .content .ui-helper-clearfix').remove();
                            $('#options_list .content').append('<div class="msg-highlight">' + Msg.options_empty_reindex + '</div>');                                                        
                        }
                        else{
                            if(parseInt(json.data.filter.num_columns) == 1){
                                $('#columns_list .content').append('<div class="msg-highlight">' + Msg.columns_none + '</div>');
                            }
                            if(num_columns_filter > 1){
                                FilterOptions.createColumnsWidget({
                                    num_columns: num_columns_filter,
                                    ids_columns: ids_columns,
                                    options_by_column: options_by_column,
                                    values_by_column: values_by_column
                                });
                            }else{
                                $('.sortable_connect').sortable({
                                    placeholder: 'ui-state-highlight',
                                    stop: FilterOptions._stopSortableOptions
                                });        
                                $('#sortable_options').disableSelection();                                                                
                            }                                                        
                        }  
                        
                        if(num_columns_filter == 1)
                            $('#sort_options_filter').show();
                        else
                            $('#sort_options_filter').hide();                      
                        sort_asc = true;
                    }
                }
                else
                    $('#div_loading_filter').removeAttr('class').addClass('error').html(json.message);
            },
            complete: function(){
                $('#filters_list').delOverlay();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_filter').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
            }
        }); 
    },
    saveLanguageForColumn: function(input){
        if(Util._checkValueLang({el: input, siblings_expr: 'div.column_value'})){
            //Obtiene el prefijo para ser pasado como parametro a la funcion que retonar los valores de lenguaje
            var prefix_id = '#' + $(input).attr('id').split('_').slice(0,4).join('_') + '_';
            var values_for_column = Util._getValuesLang(prefix_id);
            
            //Posicion del div contenedor de los lenguajes en el DOM
            var index_dom = $(input).parent().parent().index();
            
            //Obtener columna a partir del {index_dom}
            var id_col = $('div.column_filter:eq(' + index_dom + ')').attr('id').replace(/column_filter_/, '');
            
            //Valor ingresado
            var text = $(input).val();
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=updateValuesColumn' + 
                    '&id_col=' + id_col +
                    '&values=' + values_for_column,
                beforeSend: function()
                {
                    $(input).val(Msg.processing).attr('disabled', true).addOverlay();
                },
                success: function(json)
                {               
                    if(json.message_code == 0){
                    }else{
                        $.each(json.errors, function(i, error){
                            $('#div_loading_options_column').removeAttr('class').addClass('error').html(error + '<br />');
                        });
                    }
                },
                complete: function(){
                    $(input).val(text).attr('disabled', false).delOverlay();               
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_options_column').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    reindexByFilter: function(params){
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=reindexByFilter' + 
                '&id_filter=' + params.data.id_filter,
            beforeSend: function()
            {
                $('#div_loading_options_column').removeAttr('class').empty();
                $(':button').attr('disabled', true).addOverlay();
                $('#reindex_options_by_filter').addOverlay().disableButton();
            },
            success: function(json)
            {               
                if(json.message_code == 0){
                    FilterOptions.getOptionsByFilter(params.data.id_filter);
                }else{
                    $('#div_loading_options_column').removeAttr('class').addClass('error').html(json.message);
                }
            },
            complete: function(){
                $(':button').attr('disabled', false).delOverlay();
                $('#reindex_options_by_filter').delOverlay().enableButton();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_options_column').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    observeCategoriesSelectionToolbar: function(){
        var categories_selected = $('input[name="categoryBox[]"]:checked');        
        $('#save_filter')[categories_selected.length == 0 ? 'hide' : 'show']();
        
    },
    cleanFilterForm: function(params){
        var p = $.extend({},{
            update: false
        },params);
                
        $(':button').attr('disabled', true);
        
        try{
            $('input[name="categoryBox[]"]:checked').each(function () { 
                $(this).removeAttr('checked');
                clickOnCategoryBox($(this));
            });
        }
        catch(Exc){}
        
        if(p.update)
            $('#searchers').trigger('change');
        else
            $('#searchers').val('').trigger('change');
        
        $('#id_filter').val('');
        Util._cleanValuesLang('filter_name_lang_');
        $('#criterions option:eq(0), #types option:eq(0)').attr('selected', true);
        $('#features option:eq(0), #attributes_group option:eq(0), #types option:eq(0), #num_columns option:eq(0)').attr('selected', true);
        $('#criterions, #types').trigger('change');
        $('#multioption_filter_options_on, #use_engine_ps_search_off, #include_subcategories_search_off').attr('checked', true);
        $('#save_filter').enableButton(Msg.save);
        $("#div_loading_filter").removeAttr('class').html('');
        //Habilitar controles no editables
        $('#searchers, #criterions, .category > :radio, #num_columns, #types').attr('disabled', false);        
        $(':button').attr('disabled', false);
    }
}

var Tools = {
    reindexCategories: function(){
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=reindexCategories',
            beforeSend: function()
            {
                $('#tab_tools :button').attr('disabled', true).addOverlay();
                $('#renindex_categories').disableButton(null);
            },
            success: function(json)
            {               
                if(json.message_code == 0){
                    $('#div_loading_tools').removeAttr('class').addClass('conf').html(json.message);
                }else{
                    $('#div_loading_tools').removeAttr('class').addClass('error').html(json.message);
                }                                                                                      
                
                $('#renindex_categories').enableButton(null);
            },
            complete: function(){                                
                $('#tab_tools :button').attr('disabled', false).delOverlay();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_tools').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    reindexProducts: function(){
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=reindexProducts',
            beforeSend: function()
            {
                $('#tab_tools :button').attr('disabled', true).addOverlay();
                $('#renindex_products').disableButton(null);
            },
            success: function(json)
            {               
                if(json.message_code == 0){
                    $('#div_loading_tools').removeAttr('class').addClass('conf').html(json.message);
                }else{
                    $('#div_loading_tools').removeAttr('class').addClass('error').empty();
                    $.each(json.errors, function(i, error){
                        $('#div_loading_tools').append(error + '<br/>');
                    });
                }                  
            },
            complete: function(){  
                $('#renindex_products').enableButton(null);
                $('#tab_tools :button').attr('disabled', false).delOverlay();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_tools').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    saveConfiguration: function(){
        var show_button_back_filters = $('#show_button_back_filters').is(':checked') ? 1 : 0;
        var show_button_expand_options = $('#show_button_expand_options').is(':checked') ? 1 : 0;
        var show_only_products_stock = $('#show_only_products_stock').is(':checked') ? 1 : 0;
        
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=saveConfiguration' +
                '&show_button_back_filters=' + show_button_back_filters + 
                '&show_button_expand_options=' + show_button_expand_options +
                '&show_only_products_stock=' + show_only_products_stock +
                '&id_content_results=' + $('#id_content_results').val(),
            beforeSend: function()
            {
                $('#tab_tools :button').attr('disabled', true).addOverlay();
                $('#renindex_products').disableButton(null);
            },
            success: function(json)
            {               
                if(json.message_code == 0){
                    $('#div_loading_tools').removeAttr('class').addClass('conf').html(json.message);
                }else{
                    $('#div_loading_tools').removeAttr('class').addClass('error').html(json.message);
                }                  
            },
            complete: function(){  
                $('#renindex_products').enableButton(null);
                $('#tab_tools :button').attr('disabled', false).delOverlay();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_tools').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    }
}

var DependencyFilters = {
    _data_filters: {},
    getControlSelect: function(params){
        var p = $.extend({}, {
            sender: null,
            exclude: new Array(),
            data: this._data_filters,
            empty_option: {
                avaible: true,
                value: "",
                label: Msg.choose
            },
            events:{
                change:DependencyFilters.changeVirtualSelect
            }
        }, params);
                                        
        var select = $('<select></select>').change(function(){
            if($.isFunction(p.events.change))
                p.events.change.apply(this, new Array(this));
        });
                
        if(p.empty_option.avaible)
            $(select).append('<option value="' + p.empty_option.value + '">' + p.empty_option.label + '</option>');
        
        if(p.sender == null)
            $(select).addClass('first');
                
        $.each($(p.data).data(), function(id_filter, name){
            if($.inArray(id_filter, p.exclude) == -1)
                $(select).append('<option value="' + id_filter + '">' + name + '</option>');
        });
        
        //Deshabilitar los {select} anteriores
        if($(p.sender).is('select')){
            //$('#dependencies_filters .content select').attr('disabled', true);
            //$(p.sender).parentsUntil('.content').last().find('select:not(:last)').attr('disabled', true);
            $(p.sender).parentsUntil('.content').last().find('select').attr('disabled', true).addClass('disabled');
        }
        
        $('#dependencies_filters .content .dependency_filter_item').removeClass('last');
        
        return $('<div></div>')
            .addClass('dependency_filter_item ')
            .append(select)
    },
    changeVirtualSelect: function(self){
        var id_filter = $(self).val();
        
        $(self).after('<span class="arrow"></span>'); 
        
        if(!$.isEmpty(id_filter)){
            var ids_filter_selected = new Array();
            $('#dependencies_filters select option:selected').each(function(i, select){                
                ids_filter_selected.push($(select).val());
            });
            
            $(self).parent().after(DependencyFilters.getControlSelect({exclude: ids_filter_selected, sender: self}));
                       
            if($(self).hasClass('first') && $('#dependencies_filters .content .block .dependency_filter_item .first:not(:disabled)').length == 0){
                $('#dependencies_filters .content').append(
                    $('<div></div>')
                        .addClass('block')
                        .append(DependencyFilters.getControlSelect({exclude: ids_filter_selected, sender: null}))
                );
            }
            
            if($(self).parentsUntil('.content').last().find('.delete').length == 0){
                $(self).parentsUntil('.content').last().append('<span class="delete" title="' + Msg.del + '" onclick="DependencyFilters.deleteDependecy(this)"></span>')
            }
            
            $('#dependencies_filters select:not(.disabled) option[value="' + id_filter + '"]').remove();
        }
        else{
            
        }
    },
    deleteDependecy: function(self){
        $(self).prev().fadeIn(500, function(){
            var div = $(this);
            var select = $(div).find('select');
            var prev = $(div).siblings('div').last();
            
            var num_blocks = $(self).parentsUntil('.content').last().find('select').length;
            var ids_filter_selected = new Array();
            $('#dependencies_filters select option:selected').each(function(i, option){                
                if($(prev).find('select').val() != $(option).val())
                    ids_filter_selected.push($(option).val());
            });
            $('#dependencies_filters select:not(.disabled)').append($(prev).find('select').find('option:selected').removeAttr('selected'));
                        
            if(num_blocks == 1){   
                //if(!$.isEmpty($(select).val())){                    
                    //$('#dependencies_filters select:not(.disabled)').append($(select).find('option:selected').removeAttr('selected').clone());    
                //}
                                
                $(div).replaceWith(DependencyFilters.getControlSelect({exclude: ids_filter_selected})).addClass('first');
                
                $(select).removeClass('disabled').attr('disabled', false).find('option:eq(0)').attr('selected', true);
                
                if($('#dependencies_filters .content .block .dependency_filter_item .first:not(:disabled)').length > 1){
                    $('#dependencies_filters .content .block .dependency_filter_item .first:not(:disabled):last').parentsUntil('.content').remove();
                }
            }
            else if(num_blocks == 2){
                $(prev).remove();
                $('#dependencies_filters .content .block .dependency_filter_item .first:not(:disabled):last').parentsUntil('.content').remove();
                $('#dependencies_filters .content .block').each(function(i, item){
                    if($(item).find('.dependency_filter_item').length == 1){
                       $(item).find('.dependency_filter_item').find('select').addClass('first'); 
                    }
                });
            }
            else{
                $(div).remove();                  
                $(prev).replaceWith(DependencyFilters.getControlSelect({exclude: ids_filter_selected, sender: self}));
                //$(prev).children('span.arrow').remove();
                //$(prev).children('select').attr('disabled', false);
                
                //if(!$.isEmpty($(select).val())){
                    //$('#dependencies_filters select:not(.disabled)').append($(select).find('option:selected').removeAttr('selected'));    
                //}
            }
        });
    },
    getFiltersListBySearcher: function(event){
        var id_searcher = $('#searchers_dependency_filters').val();
        
        if(!$.isEmpty(id_searcher)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getFiltersListBySearcher' + 
                    '&id_searcher=' + id_searcher,
                beforeSend: function()
                {
                    DependencyFilters._data_filters = {};
                    $('#dependencies_filters .content .block:eq(0)').empty();
                    $('#dependencies_filters .content .block:gt(0)').remove();
                    $('#div_loading_dependency_filters').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                },
                success: function(json)
                {        
                    try{
                        $('#div_loading_dependency_filters').empty();

                        if(json.message_code == 0){
                            $.each(json.data, function(i, filter){
                                /*if(filter.criterion != GLOBALS.Criterions.Custom)
                                    return;*/
                                jQuery.data(DependencyFilters._data_filters, filter.id_filter, filter.name);
                            });
                                                       
                            if((json.data).length == 0){
                                $('#save_dependency_filters').fadeOut();
                                $('#dependencies_filters .content .msg').fadeIn();
                            }
                            else{
                                $('#dependencies_filters .content .msg').hide();
                                $('#dependencies_filters .content .block:eq(0)').empty().append(DependencyFilters.getControlSelect());
                            }
                        }else{
                            $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(json.message);
                        }

                        $(':button').attr('disabled', false);
                    }
                    catch($Exc){}
                },
                complete: function(){
                    $.ajax({
                        type: 'POST',
                        url: filterproductspro_dir + 'actions.php',
                        async: false,
                        cache: false,
                        dataType : 'json',
                        data: 'action=getDependenciesFilters' + 
                                '&id_searcher=' + id_searcher,
                        beforeSend: function()
                        {
                            $('#div_loading_dependency_filters').removeAttr('class').empty();
                            $('#dependencies_filters .content').addOverlay();
                            $('#tab_dependency_filters :button').attr('disabled', true).addOverlay();
                            $('#save_dependency_filters').disableButton(null);
                        },
                        success: function(json)
                        {               
                            if(json.message_code == 0){
                                var index = 0;
                                $.each(json.data, function(id_filter_parent, childrens){
                                    if((childrens).length > 0){                                        
                                        $('#dependencies_filters .content .block:eq(' + index + ') select.first').val(id_filter_parent).trigger('change');                                         
                                        $.each(childrens, function(i, id_filter_children){
                                            $('#dependencies_filters .content .block:eq(' + index + ') select:eq(' + (i + 1) + ')').val(id_filter_children).trigger('change');
                                        });
                                        
                                        index++;
                                    }                                    
                                });
                            }else{
                                $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(json.message);
                            }                  
                        },
                        complete: function(){  
                            $('#dependencies_filters .content').delOverlay();
                            $('#save_dependency_filters').enableButton(null);
                            $('#tab_dependency_filters :button').attr('disabled', false).delOverlay();
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                        }
                    });
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        else{
            $('#save_dependency_filters').fadeOut();
            $('#dependencies_filters .content .block:eq(0)').empty();
            $('#dependencies_filters .content .block:gt(0)').remove();
            $('#dependencies_filters .content .msg').fadeIn();
        }
    },
    save: function(){        
        var data = {};
        var id_searcher = $('#searchers_dependency_filters').val();
        
        if($.isEmpty(id_searcher))
            return;
                
        $('#dependencies_filters .content .block .dependency_filter_item:not(:only-child) select.first').each(function(i, item){
            var id_filter_parent = $(item).val();
            
            if(!$.isEmpty(id_filter_parent)){
                var childrens = new Array();
                var block = $(item).parentsUntil('.block').last().parent();

                $(block).find('select:gt(0)').each(function(i, select){
                    var id_filter_children = $(select).val();
                    if(!$.isEmpty(id_filter_children))
                        childrens.push(id_filter_children);
                });
                
                jQuery.data(data, id_filter_parent, childrens);
            }
        });
                
        if($($(data).data()).length > 0){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=updateDependenciesFilters' +
                    '&id_searcher=' + id_searcher +
                    '&dependencies=' + JSON.stringify($(data).data()),
                beforeSend: function()
                {
                    $('#div_loading_dependency_filters').removeAttr('class').empty();
                    $('#dependencies_filters .content').addOverlay();
                    $('#tab_dependency_filters :button').attr('disabled', true).addOverlay();
                    $('#save_dependency_filters').disableButton(null);
                },
                success: function(json)
                {               
                    if(json.message_code == 0){
                        $('#div_loading_dependency_filters').removeAttr('class').addClass('conf').html(json.message);
                    }else{
                        if(!$.isEmpty((json.errors))){
                            $('#div_loading_dependency_filters').removeAttr('class').addClass('error').empty();
                            $.each(json.errors, function(i, error){
                                $('#div_loading_dependency_filters').append(error + '<br/>');
                            });
                        }
                        else
                            $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(json.message);
                    }                  
                },
                complete: function(){  
                    $('#dependencies_filters .content').delOverlay();
                    $('#save_dependency_filters').enableButton(null);
                    $('#tab_dependency_filters :button').attr('disabled', false).delOverlay();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    }
}

var DependencyOptions = {
    id_filter_child: null,
    getFiltersListBySearcher: function(){
        var id_searcher = $('#searchers_dependency_options').val();
        
        $('#filters_dependency_options option:gt(0)').remove();
        
        if(!$.isEmpty(id_searcher)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getFiltersListBySearcher' + 
                    '&id_searcher=' + id_searcher,
                beforeSend: function()
                {
                    $('#dependencies_options .content').addOverlay();                    
                    $('#dependencies_options .content').empty();
                    $('#div_loading_dependency_options').removeAttr('class').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                },
                success: function(json)
                {        
                    try{
                        $('#div_loading_dependency_options').empty();

                        if(json.message_code == 0){                            
                            $.each(json.data, function(i, filter){
                                if(filter.criterion != GLOBALS.Criterions.Custom)
                                    return;
                                
                                var option = '<option value="' + filter.id_filter + '">' + filter.name + '</option>';
                                $('#filters_dependency_options').append(option);
                            });
                            
                            $('#dependencies_options .content').html(((json.data).length == 0 ? Msg.filters_empty : Msg.choose_filter));                            
                        }else{
                            $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(json.message);
                        }

                        $(':button').attr('disabled', false);
                    }
                    catch($Exc){}
                },
                complete: function(){
                    $('#dependencies_options .content').delOverlay();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_dependency_filters').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        else{
            $('#filters_dependency_options').trigger('change');
        }
    },
    checkAllOptions: function(){
        var self = $(this);
        var divs = $(self).parent().siblings('div.option_child');
        
        $(divs).children(':checkbox').attr('checked', $(self).is(':checked'))
        $('#quantity_selected_' + $(self).attr('id').split('_')[2]).find('span').html($(divs).children(':checkbox:checked').length);
    },
    changeOptionChild: function(){
        var self = $(this);        
        var divs = $(self).parent().siblings('div.option_child').not('.check_all');
        
        $('#quantity_selected_' + $(self).attr('id').split('_')[2]).find('span').html(parseInt($(divs).children(':checkbox:checked').length) + ($(self).is(':checked') ? 1 : 0));
    },
    getDataFilter: function(page){
        var id_filter = $('#filters_dependency_options').val();        
        
        DependencyOptions.id_filter_child = null;
        
        if(!$.isEmpty(id_filter)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getDataFilterDependencyOptions' + 
                    '&id_filter=' + id_filter +
                    '&page=' + page,
                beforeSend: function()
                {                    
                    $('save_dependency_options').hide().disableButton(null);
                    $('#div_loading_dependency_options').removeAttr('class').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');               
                },
                success: function(json)
                {                    
                    $('#div_loading_dependency_options, #dependencies_options .content').empty();
                    
                    if(json.message_code == 0){
                        var accordion = $('<div></div>').attr({id: 'temp_accordion_dependency_options'}).hide();
                        
                        $('#dependency_options-is_parent').val(json.data.is_parent ? '1' : '0');
                        
                        $.each(json.data.options_list, function(i, option){ 
                            var _id = option.id_option + '_0';
                            var value_option = option.value;
                            
                            if (!json.data.is_parent){
                                if (option.ids_dependency != undefined){
                                    var arr_ids = option.ids_dependency.split(',');
                                    
                                    _id = option.id_option + '_' + option.id_dependency_option;
                                    value_option = option.str_dependency;
                                }                                
                            }
                            
                            var options = $('<div></div>').attr({id: 'content_' + _id});                            
                            
                            $('<div>')                                
                                .addClass('check_all option_child').css({display: 'block'})
                                .html(
                                    $('<input/>')                                        
                                        .attr({type: 'checkbox', id: 'check_all_' + option.id_option})
                                        .change(DependencyOptions.checkAllOptions)
                                        .after('<label for="check_all_' + option.id_option + '">' + Msg.checked_all + '</label>')
                                )
                                .prependTo(options);
                                                                              
                            $('<h3/>')
                                .attr({id: 'header_' + _id})
                                .append('<a href="#">' + Util._getLabelOptionRangePrice(value_option) + '</a>')
                                .append('<span id="quantity_selected_' + option.id_option + '" class="tool quantity_selected" style="display: none">(<span>0</span>)</span>')
                                .after(options)
                                .click(function(){                                    
                                    if (!$('#temp_accordion_dependency_options #content_' + _id).is(':visible'))
                                        DependencyOptions.getOptionsChild(option.id_option, _id);
                                })
                                .appendTo(accordion);
                        });                                            

                        $(accordion).accordion({
                            autoHeight: false,
                            collapsible: true,
                            active: -1,
                            changestart: function(event, ui){
                                $(ui.oldHeader).find(':button.check_all').removeClass('parent_open');
                                $(ui.newHeader).find(':button.check_all').addClass('parent_open');
                                
                                if ($(ui.oldHeader).length > 0){                                    
                                    var id_option = $(ui.oldHeader).attr('id').split('_')[1];
                                    var id_dependency_option = $(ui.oldHeader).attr('id').split('_')[2];
                              
                                    DependencyOptions.save(id_option + '_' + id_dependency_option, json.data.is_parent);                                        
                                }
                            }
                        });
                        
                        $(accordion).appendTo($('#dependencies_options .content')).fadeIn();                            
                                                              
                        //carga la paginacion.
                        //------------------------------------------------------------------                        
                        $('#dependency_options-pagination').empty();                        
                        if (json.data.count_options > 40){
                            content_pagination = $('#dependency_options-pagination');                        
                            for(var _page=0;_page <= Math.floor(json.data.count_options / 40);_page++){
                                var a = $('<a>')
                                                .attr({
                                                    id: 'dependency_options-a_page_' + _page,
                                                    href: '#', 
                                                    onclick: 'DependencyOptions.getDataFilter(' + _page + ')'
                                                })
                                                .html(_page);
                                $(a).appendTo(content_pagination);
                            }
                            $('<div class="clear"></div>').appendTo(content_pagination);
                            $('#dependency_options-a_page_' + page).css({
                                color: 'blue', 
                                textDecoration: 'none' 
                            });
                        }                        
                        //------------------------------------------------------------------                        
                        
                        if((json.data.options_list).length == 0){
                            $('#dependencies_options .content').html(Msg.options_null);
                        }
                    }
                    else{
                        $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(json.message);
                    }
                },
                complete: function(){
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        else{
            $('#dependency_options-pagination').empty();
            $('#save_dependency_options').hide().disableButton(null);
            $('#dependencies_options .content').html(!$.isEmpty($('#searchers_dependency_options').val()) ? Msg.choose_filter : Msg.choose_searcher);
        }
    },
    getOptionsChild: function(id_option_parent, _id){
        var id_filter_parent = $('#filters_dependency_options').val();
        if(!$.isEmpty(id_option_parent)){
            var id_dependency_option = _id.split('_')[1];
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getOptionsChild' + 
                    '&id_filter_parent=' + id_filter_parent + 
                    '&id_option_parent=' + id_option_parent + 
                    '&id_dependency_option=' + id_dependency_option,
                beforeSend: function()
                {
                },
                success: function(json)
                {                    
                    if(json.message_code == 0){
                        if((json.data.options_child).length > 0){                            
                            DependencyOptions.id_filter_child = json.data.id_filter_child;
                            $('#save_dependency_options').show().enableButton(null);
                        }
                        
                        //elimina las opciones que se tenian cargadas, menos el check all.
                        $('#dependencies_options #content_' + _id + ' >*:not(.check_all)').remove();
                        
                        var container = $('#dependencies_options #content_' + _id);
                                                
                        $(container).addClass('childs_loaded');
                        
                        var count_options_selected = 0;                        
                        $.each(json.data.options_child, function(i, option_child){
                            var id_checkbox = 'option_child_' + id_option_parent + '_' + option_child.id_option + '_0';
                            
                            if (option_child.selected){
                                count_options_selected += 1;
                                id_checkbox = 'option_child_' + id_option_parent + '_' + option_child.id_option + '_' + option_child.id_dependency_option;
                            }
                                                                                                              
                            $('<div>')
                                .addClass('option_child')                                    
                                .html(
                                    $('<input/>')
                                        .attr({id: id_checkbox, type: 'checkbox', name: 'cbg_options_child_' + id_option_parent, checked: option_child.selected})
                                        .click(DependencyOptions.changeOptionChild)                                        
                                        .after('<label for="' + id_checkbox + '">' + option_child.value + '</label>')
                                        .addClass(option_child.selected ? 'selected' : '')
                                )
                                .appendTo(container);                           
                        });
                        
                        $('#quantity_selected_' + id_option_parent).show().find('span').html(count_options_selected);                                                
                        
                        if((json.data.options_child).length == 0){
                            $('#dependencies_options .content').html(Msg.options_children_empty);
                        }                        
                    }else{
                        $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(json.message);
                    }
                },
                complete: function(){
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    //{_id} puede ser el id_option si es un filtro padre, o id_dependency_option si es un filtro hijo.
    save: function(_id){
        try{            
            if($('#temp_accordion_dependency_options') && !$.isEmpty(DependencyOptions.id_filter_child)){
                var dependency_option_checked = {};                
                var options_checked = {};
                var options_unchecked = {};
                
                var str_id_options_unchecked = new Array();
                
                var is_parent = $('#dependency_options-is_parent').val() == '0' ? false : true;
                var id_filter_parent = $('#filters_dependency_options').val();

                if (!$.isEmpty(_id))
                    var container = $('#temp_accordion_dependency_options #content_' + _id);
                else
                    var container = $('#temp_accordion_dependency_options .ui-accordion-content[id^="content_"]');

                var name_options = '';
                $(container).each(function(i, div){                                        
                    if($(div).hasClass('childs_loaded')){
                        var ids_dependency_option_checked = new Array();                        
                        var ids_options_checked = new Array();
                        var ids_options_unchecked = new Array();
                
                        var id_option_parent = $(div).attr('id').split('_')[1];
                        var id_dependency_option_parent = $(div).attr('id').split('_')[2];
                        
                        $(div).find('.option_child:not(.check_all) :checkbox:checked').each(function(i, item){
                            var id_option_child = parseInt($(item).attr('id').split('_')[3]);
                            var id_dependency_option_child = parseInt($(item).attr('id').split('_')[4]);                            
                            
                            if (is_parent)
                                ids_options_checked.push(id_option_child);
                            else{
                                ids_dependency_option_checked.push(id_option_child);
                            }
                        });
                        
                        $(div).find('.option_child:not(.check_all) :checkbox.selected').each(function(i, item){
                            if (!$(item).is(':checked')){
                                var id_option_child = parseInt($(item).attr('id').split('_')[3]);
                                
                                ids_options_unchecked.push(id_option_child);
                                if (name_options != '')
                                    name_options += ',';
                                name_options += $("label[for='" + $(item).attr('id') + "']").text();
                                
                                str_id_options_unchecked.push($(item).attr('id'));
                            }
                        });                                                                                                
                        
                        if (is_parent){
                            jQuery.data(options_checked, id_option_parent, ids_options_checked);
                            jQuery.data(options_unchecked, id_option_parent, ids_options_unchecked);                            
                        }else{
                            jQuery.data(dependency_option_checked, id_dependency_option_parent, ids_dependency_option_checked);
                            jQuery.data(options_unchecked, id_dependency_option_parent, ids_options_unchecked);                            
                        }                                                
                    }
                });
                
                if(name_options != '' && !confirm(Msg.save_dependencies + name_options))
                    return;
                                    
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: true,
                    cache: false,
                    dataType : 'json',
                    data: 'action=updateDependenciesOptions' +
                        '&id_filter_parent=' + id_filter_parent +
                        '&id_filter_child=' + DependencyOptions.id_filter_child +
                        '&options_checked=' + JSON.stringify($(options_checked).data()) + 
                        '&dependency_option_checked=' + JSON.stringify($(dependency_option_checked).data()) +
                        '&options_unchecked=' + JSON.stringify($(options_unchecked).data()),
                    beforeSend: function()
                    {
                        $('#div_loading_dependency_options').removeAttr('class').empty();
                        $('#dependencies_options .content').addOverlay();
                        $('#tab_dependency_options :button').attr('disabled', true).addOverlay();
                        $('#save_dependency_options').disableButton(null);
                    },
                    success: function(json)
                    {               
                        $('#div_loading_dependency_options').removeAttr('class').addClass((json.message_code == 0) ? 'conf' : 'error').html(json.message);
                        
                        //Elimina la clase "selected" del input y el label, para que no vuelva a mostrar el mensaje de advertencia anterior.                        
                        $.each(str_id_options_unchecked, function(i, str_id_option){
                            $('#dependencies_options #' + str_id_option).removeClass('selected');
                            $('#dependencies_options label[for=' + str_id_option + ']').removeClass('selected');
                        });
                        
                        //Pasa la lista de filtros a escoger filtro, para que se recarguen los cambios hechos de la dependencia de opciones.
                        $('#filters_options_custom').val('');
                        $('#filters_options_custom').trigger('change');
                    },
                    complete: function(){
                        $('#dependencies_options .content').delOverlay();
                        $('#save_dependency_options').enableButton(null);
                        $('#tab_dependency_options :button').attr('disabled', false).delOverlay();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_dependency_options').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                    }
                });
            }
        }
        catch($e){   
            console.log('ERROR: ' + $e);
        }
    }
}

var OptionsCustom = {
    init: function(){
        //Bloquear {input}
        $('.option_custom_name_lang > input, #options_custom_product').attr('disabled', true).addClass('disabled');
        $('#options_custom_add_product').hide();
        
        $('#options_custom_product').autocomplete('ajax_products_list.php', {
            delay: 100,
            minChars: 1,
            autoFill: true,
            max:20,
            matchContains: true,
            mustMatch:true,
            scroll:false,
            cacheLength:0,
            multipleSeparator:'||',
            formatItem: function(item) {
                return item[1]+' - '+item[0];
            }
        })
        .result(function(event, item){
            $('#options_custom_product_name').val(item[0]);//Guardar {name}
            $('#options_custom_id_product').val(item[1]);//Guardar {id_product}
            
            OptionsCustom.addProduct();
        });
    },
    getFiltersListBySearcher: function(){
        var id_searcher = $('#searchers_options_custom').val();
        
        $('#filters_options_custom option:gt(0)').remove();
        
        if(!$.isEmpty(id_searcher)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getFiltersListBySearcher' + 
                    '&id_searcher=' + id_searcher,
                beforeSend: function()
                {
                    $('#options_custom .content').addOverlay();                    
                    $('#options_custom .content').empty();
                    $('#div_loading_options_custom').removeAttr('class').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                },
                success: function(json)
                {        
                    try{
                        $('#div_loading_options_custom').empty();

                        if(json.message_code == 0){                            
                            $.each(json.data, function(i, filter){
                                if(filter.criterion != GLOBALS.Criterions.Custom)
                                    return;
                                
                                var option = '<option value="' + filter.id_filter + '">' + filter.name + '</option>';
                                $('#filters_options_custom').append(option);
                            });
                            
                            $('#options_custom .content').html(((json.data).length == 0 ? Msg.filters_empty : Msg.choose_filter));                            
                        }else{
                            $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);
                        }
                    }
                    catch($Exc){}
                },
                complete: function(){
                    $('#options_custom .content').delOverlay();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
        else{            
            $('#filters_options_custom').trigger('change');
        }
    },
    getOptionsByFilter: function(page){
        var id_filter = $('#filters_options_custom').val();
                
        if(!$.isEmpty(id_filter)){
            //Desbloquear todos los {input} de lang para {name_option_custom}
            $('.option_custom_name_lang > input').removeAttr('disabled').removeClass('disabled');
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=getOptionsByFilter' + 
                    '&id_filter=' + id_filter +                     
                    '&page=' + page,
                beforeSend: function(){
                    $('#options_custom_products .content').html(Msg.choose_option);
                    $("#div_loading_options_custom").removeAttr('class').empty();
                    $('#options_custom .content').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                },
                success: function(json)
                {
                    $('#options_custom .content').empty();
                    
                    if(json.message_code == 0){
                        var items = $('<ul></ul>').attr({id: 'list_options_custom'}).addClass('sortable').appendTo($('#options_custom .content'));

                        $.each(json.data.options, function(i, option){
                            var _id = option.id_option + '_0';
                            var value_option = option.value;
                            
                            if (!json.data.filter.is_parent){
                                if (option.ids_dependency != undefined){
                                    var arr_ids = option.ids_dependency.split(',');
                                    
                                    _id = option.id_option + '_' + option.id_dependency_option;
                                    value_option = option.str_dependency;
                                }                                
                            }
                                                        
                            var item = $('<li></li>')
                                .attr({id: _id})
                                .addClass('ui-state-default option_filter')
                                .append('<span class="ui-icon ui-icon-carat-1-e"></span>')
                                .append('<span onclick="OptionsCustom.deleteOption(' + option.id_option + ', \'' + _id + '\', event)" class="ui-icon ui-icon-trash tools ui-corner-all" title="' + Msg.del + '"></span>')
                                .append('<span onclick="OptionsCustom.edit(' + option.id_option + ', \'' + _id + '\', event)" class="ui-icon ui-icon-pencil tools ui-corner-all" title="' + Msg.edit + '"></span>')                                
                                .append('<span class="title">' + value_option + '</span>')
                                .appendTo(items);          
                                
                            if ((json.data.filter.is_parent && json.data.filter.is_last_dependency) || (json.data.filter.is_last_dependency && option.ids_dependency != undefined)){
                                item.append('<span onclick="OptionsCustom.getProductsByOption(\'' + _id + '\', event)" class="ui-icon ui-icon-wrench tools ui-corner-all" title="' + Msg.configure + '"></span>')
                            }                                                  
                        });                     

                        //carga la paginacion.
                        //------------------------------------------------------------------                        
                        $('#options_custom-pagination').empty();                        
                        if (json.data.count_options > 40){
                            content_pagination = $('#options_custom-pagination');                        
                            for(var _page=0;_page <= Math.floor(json.data.count_options / 40);_page++){
                                var a = $('<a>')
                                                .attr({
                                                    id: 'options_custom-a_page_' + _page,
                                                    href: '#', 
                                                    onclick: 'OptionsCustom.getOptionsByFilter(' + _page + ')'
                                                })
                                                .html(_page);
                                $(a).appendTo(content_pagination);
                            }
                            $('<div class="clear"></div>').appendTo(content_pagination);
                            $('#options_custom-a_page_' + page).css({
                                color: 'blue', 
                                textDecoration: 'none' 
                            });
                        }                       
                        //------------------------------------------------------------------
                            
                        //hace flotante el div donde se asocian los productos con las opciones. Solo si son mas de 30 opciones mostradas.                        
                        /*if ($('#list_options_custom li').length > 30){
                            var _top = $('#options_custom').offset().top;
                            var _left = $('#options_custom').offset().left;
                            $('#options_custom_products').css({
                                position: 'fixed',
                                width: 425,
                                top: 457,
                                left: _left + 450
                            });
                        }else{
                            $('#options_custom_products').css({
                                position: 'static'
                            });
                        }*/
                            
                        if((json.data.options).length == 0){
                            $('#options_custom .content').html(Msg.options_empty);
                        }
                    }
                },
                complete: function(){                    
                    $('#options_custom_product').attr('disabled', true).addClass('disabled');
                    $('#options_custom_add_product').hide();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            });
        }
        else{       
            //Bloquear los {input}
            $('#options_custom-pagination').empty();
            $('.option_custom_name_lang > input, #options_custom_product').attr('disabled', true).addClass('disabled');
            $('#options_custom_add_product').hide();
            $('#options_custom_products .content').html(Msg.choose_option);
            $('#options_custom .content').html(!$.isEmpty($('#searchers_options_custom').val()) ? Msg.choose_filter : Msg.choose_searcher);
            
            $('#options_custom_products').css({
                position: 'static'
            });
        }
    },
    getProductsByOption: function(_id, event){
        if(!$.isEmpty(_id)){
            var id_option = _id.split('_')[0];
            var id_dependency_option = _id.split('_')[1];
            var id_filter = $('#filters_options_custom').val();
            
            $('#options_custom').addOverlay();        
            $('#options_custom li[id="' + _id + '"]').addClass('selected_filter').siblings('li').removeClass('selected_filter');
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=getProductsByOptionCustom' +
                    '&id_filter=' + id_filter + 
                    '&id_option=' + id_option +  
                    '&id_dependency_option=' + id_dependency_option,
                beforeSend: function()
                {
                    $("#div_loading_options_custom").removeAttr('class').empty();
                    $('#options_custom_products .content').html('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                },
                success: function(json)
                {   
                    if(json.message_code == 0){
                        //Habilitar ingreso de productos
                        $('#options_custom_product').removeAttr('disabled').removeClass('disabled');
                        $('#options_custom_add_product').fadeIn();
                        
                        var ul = $('<ul/>').attr({id: 'list_products'}).appendTo($('#options_custom_products .content').empty()).addClass('sortable data_list_items');
                        
                        $.each(json.data, function(i, item){
                            $('<li></li>')
                                .attr({id: item.id_product})
                                .addClass('ui-state-default option_filter')
                                .append('<span onclick="OptionsCustom.deleteProduct(' + id_option + ',' + id_dependency_option + ',' + item.id_product + ',event)" class="ui-icon ui-icon-trash tools ui-corner-all" title="' + Msg.del + '"></span>')
                                .append('<span class="title">' + item.name + '</span>')
                                .appendTo(ul);
                        });
                    }
                    else
                        $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);
                },
                complete: function(){  
                    $('#options_custom').delOverlay();
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    edit: function(id_option, _id){
        if(!$.isEmpty(id_option)){
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: false,
                cache: false,
                dataType : 'json',
                data: 'action=loadOption' + 
                    '&id_option=' + id_option,
                beforeSend: function(){
                    $('#options_custom li[id="' + _id + '"]').addOverlay();
                    $('#options_custom li[id="' + _id + '"] span.tools').hide();                    
                    $("#div_loading_options_custom").removeAttr('class').empty();                    
                },
                success: function(json)
                {
                    try{
                        if(json.message_code == 0){
                            $('#save_option_custom').enableButton(Msg.update);
                            $('#id_custom_option').val(id_option);
                            $('#id_custom_option_criterion').val(json.data.option_criterion.id);
                            $('#id_element_custom_option').val(_id);

                            //Deshabilitar controles no editables
                            $('#searchers_options_custom, #filters_options_custom').attr('disabled', true);

                            //Asignar valores                            
                            Util._setValuesLang('#option_custom_name_lang_', json.data.option_criterion.value);
                        }
                        else
                            $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);
                    }
                    catch($Exc){}
                },
                complete: function(){
                    $('#options_custom li[id="' + _id + '"]').delOverlay();
                    $('#options_custom li[id="' + _id + '"] span.tools').fadeIn(10);                    
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                }
            });
        }
    },
    save: function(){
        var id_element_custom_option = $('#id_element_custom_option').val();
        var id_option = $('#id_custom_option').val();
        var id_option_criterion = $('#id_custom_option_criterion').val();
        var id_filter = $('#filters_options_custom').val();
        var id_searcher = $('#searchers_options_custom').val();
        var names = Util._getValuesLang('#option_custom_name_lang_');
                
        if(!Util._checkValueLang({el: $(':text[id^="option_custom_name_lang_"]:visible:eq(0)'), siblings_expr: 'div.option_custom_name_lang', force_def_lang: true})){
            alert(Msg.errors.invalid_name_option);
            return;
        }
   
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=updateOptionCustomName' +
                '&id_option=' + id_option +
                '&id_option_criterion=' + id_option_criterion +
                '&id_filter=' + id_filter +
                '&id_searcher=' + id_searcher +
                '&names=' + names,
            beforeSend: function()
            {
                $('#save_option_custom').disableButton(Msg.processing);
                $('#div_loading_options_custom').removeAttr('class').empty();
                $('#options_custom .content, #options_custom_products .content').addOverlay();
            },
            success: function(json)
            {   
                if(json.message_code == 0){
                    OptionsCustom.clearForm({fn: 'save'});
                    
                    OptionsCustom.getOptionsByFilter(parseInt($('#options_custom-page').val()));
                    
                    //Autoseleccionar opcion
                    if(!$.isEmpty(id_element_custom_option)){
                        $('#options_custom .content ul#list_options_custom li#' + id_element_custom_option).addClass('selected_filter');                        
                        OptionsCustom.getProductsByOption(id_element_custom_option);
                    }
                }
                
                $('#div_loading_options_custom').removeAttr('class').addClass((json.message_code == 0) ? 'conf' : 'error').html(json.message);
            },
            complete: function(){  
                $('#options_custom .content, #options_custom_products .content').delOverlay();
                $('#save_option_custom').enableButton(Msg.save);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
            }
        });
    },
    deleteOption: function(id_option, _id){
        if(!$.isEmpty(id_option)){
            if(confirm(Msg.confirm_delete_custom_option)){
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: false,
                    cache: false,
                    dataType : 'json',
                    data: 'action=deleteOptionCustom' + 
                        '&id_option=' + id_option,
                    beforeSend: function(){
                        $('#options_custom li[id="' + _id + '"]').addOverlay();
                        $('#options_custom li[id="' + _id + '"] span.tools').hide();
                        $('#options_custom li[id="' + _id + '"] > span.title').append('&nbsp;' + Msg.is_deleting);
                        $("#div_loading_options_custom").removeAttr('class').empty();
                    },
                    success: function(json)
                    {
                        if(json.message_code == 0){
                            OptionsCustom.getOptionsByFilter(parseInt($('#options_custom-page').val()));
                        }
                        else                         
                            $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);                        
                    },
                    complete: function(){},
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                    }
                });  
            }
        }
    },
    addProduct: function(){
        if($.isEmpty($('#options_custom_product').val()))
            $('#options_custom_id_product, #options_custom_product_name').val('');
        
        var id_product = $('#options_custom_id_product').val();
        var product_name = $('#options_custom_product_name').val();
        
        if(!$.isEmpty(id_product)){
            //Verificar si ya existen en la lista
            if($('#list_products > li[id="' + id_product + '"]').length){
                $('#options_custom_id_product, #options_custom_product_name, #options_custom_product').val('').last().focus();
                return;
            }
            
            var ul = $('#options_custom_products .content ul#list_products');
            ul = (ul.length ? ul : $('<ul/>').attr({id: 'list_products'}).appendTo($('#options_custom_products .content').empty())).addClass('sortable data_list_items');
            
            //Obtener {id_option} y {id_dependency_option}
            var _id = $('#list_options_custom li.selected_filter').attr('id');            
            var id_option = _id.split('_')[0];
            var id_dependency_option = _id.split('_')[1];
            var id_filter = $('#filters_options_custom').val();
            var id_searcher = $('#searchers_options_custom').val();
            
            if(!$.isEmpty(id_option) && !$.isEmpty(id_product)){
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: false,
                    cache: false,
                    dataType : 'json',
                    data: 'action=addProductOptionCustom' + 
                        '&id_searcher=' + id_searcher +
                        '&id_filter=' + id_filter + 
                        '&id_option=' + id_option +
                        '&id_dependency_option=' + id_dependency_option +
                        '&id_product=' + id_product,
                    beforeSend: function(){
                        $('#options_custom_products').addOverlay();
                        $('#options_custom_products :button').attr('disabled', true);
                        $("#div_loading_options_custom").removeAttr('class').empty();                    
                    },
                    success: function(json)
                    {
                        try{
                            if(json.message_code == 0){
                                $('<li></li>')
                                    .attr({id: id_product})
                                    .addClass('ui-state-default option_filter')
                                    .append('<span onclick="OptionsCustom.deleteProduct(' + id_option + ',' + id_dependency_option + ',' + id_product + ',event)" class="ui-icon ui-icon-trash tools ui-corner-all" title="' + Msg.del + '"></span>')
                                    .append('<span class="title">' + product_name + '</span>')
                                    .appendTo(ul);

                                $('#options_custom_id_product, #options_custom_product_name, #options_custom_product').val('').last().focus();
                            }
                            else
                                $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);
                        }
                        catch($Exc){}
                    },
                    complete: function(){
                        $('#options_custom_products').delOverlay();
                        $('#options_custom_products :button').removeAttr('disabled');
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                    }
                });
            }
        }
    },
    deleteProduct: function(id_option, id_dependency_option, id_product, event){
        if(!$.isEmpty(id_product) && !$.isEmpty(id_option)){
            if(confirm(Msg.confirm_delete)){
                var option_name = $('#options_custom_products li[id="' + id_product + '"] > span.title').html();

                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: false,
                    cache: false,
                    dataType : 'json',
                    data: 'action=deleteProductOptionCustom' + 
                        '&id_option=' + id_option +
                        '&id_dependency_option=' + id_dependency_option +
                        '&id_product=' + id_product,
                    beforeSend: function(){
                        $('#options_custom_products li[id="' + id_product + '"]').addOverlay();
                        $('#options_custom_products li[id="' + id_product + '"] span.tools').hide();
                        $('#options_custom_products li[id="' + id_product + '"] > span.title').append('&nbsp;' + Msg.is_deleting);
                        $("#div_loading_options_custom").removeAttr('class').empty();
                    },
                    success: function(json)
                    {
                        if(json.message_code == 0){
                            $('#options_custom_products li[id="' + id_product + '"]').delOverlay().fadeOut(500,function(){                                  
                                //Eliminar item
                                $(this).remove();
                            });
                        }
                        else{
                            $('#options_custom_products li[id="' + id_product + '"]').delOverlay();
                            $('#options_custom_products li[id="' + id_product + '"] span.tools').show();
                            $('#options_custom_products li[id="' + id_product + '"] > span.title').html(option_name);
                            $('#div_loading_options_custom').removeAttr('class').addClass('error').html(json.message);
                        }
                    },
                    complete: function(){
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#div_loading_options_custom').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);
                    }
                });  
            }
        }
    },
    clearForm: function(params){
        var p = $.extend({},{
            fn: 'index'
        },params);
        
        Util._cleanValuesLang('option_custom_name_lang_');
        $('#id_custom_option, #id_custom_option_criterion, #id_element_custom_option').val('');
        
        //posicion normal del div flotante
        $('#options_custom_products').css({
            position: 'static',
            top: 'auto',
            left: 'auto' 
        });
        
        var _fn = {
            index: function(){
                $('#div_loading_options_custom').removeAttr('class').empty();
                $('#searchers_options_custom, #filters_options_custom').val('').removeAttr('disabled').first().trigger('change');
            },
            save: function(){
                $('#searchers_options_custom, #filters_options_custom').removeAttr('disabled');
            }
        }
        
        try{
            _fn[p.fn]();
        }
        catch($Exp){}
        
        $('#save_option_custom').enableButton(Msg.save);
    }
}

var RangePrice = {
    changeCondition: function(){
        var condition = $('#conditions').val();
        
        if(!$.isEmpty(condition)){
            if(condition == GLOBALS.ConditionsRangePrice.Bt){
                $('#tab_range_price .range_price_first_value, #tab_range_price .range_price_second_value').fadeIn();
            }
            else{
                $('#tab_range_price .range_price_first_value').fadeIn();
                $('#tab_range_price .range_price_second_value').fadeOut();
            }
        }
        else{
            $('#tab_range_price .range_price_first_value, #tab_range_price .range_price_second_value').fadeOut();
        }
        
        return true;
        
        $.ajax({
            type: 'POST',
            url: filterproductspro_dir + 'actions.php',
            async: true,
            cache: false,
            dataType : 'json',
            data: 'action=getRangesPriceByCondition' + 
                '&condition=' + condition,
            beforeSend: function()
            {
                $('#ranges_price_list tbody').empty().append('<center><img src="' + filterproductspro_img + 'loader.gif" /></center>');
                $('#tab_range_price > table:eq(0)').addOverlay();
                $('#save_range_price').disableButton(Msg.processing);
            },
            success: function(json)
            {
                try{
                    $('#ranges_price_list tbody').empty();
                    if(json.message_code == 0){
                        $.each(json.data, function(i, item){
                            var data = item.value.split(',');
                            var condition = $('#conditions option[value="' + (!$.isEmpty(data[0]) ? data[0] : "") + '"]').html();
                            var first_value = parseFloat(data[1]);
                            var second_value = parseFloat(data[2]);
                                                        
                            $('#ranges_price_list tbody').append(
                                '<tr>' +
                                    '<td>' + (!$.isEmpty(condition) ? condition : Msg._undefined) + '</td>' +
                                    '<td>' + (!isNaN(first_value) ? first_value : '') + '</td>' +
                                    '<td>' + (!isNaN(second_value) ? first_value : '') + '</td>' +
                                    '<td class="actions">' + 
                                        '<img id="delete_range_price_' + item.id_option_criterion + '" src="' + filterproductspro_img + 'delete.png" onclick="RangePrice.deleteRangePrice(' + item.id_option_criterion + ')" title="' + Msg.del + '" />' +
                                    '</td>' +
                                '</tr>'
                            );
                        });

                        $('#ranges_price_list tfoot')[(json.data).length > 0 ? 'fadeOut' : 'fadeIn'](200);
                    }
                    else{
                        $('#div_loading_range_price').removeAttr('class').addClass('error').html(json.message);
                    }   
                }
                catch($Exc){}
            },
            complete: function(){
                $('#save_range_price').enableButton(Msg.save);
                $('#tab_range_price > table:eq(0)').delOverlay();       
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(XMLHttpRequest.responseText);
            }
        });
    },
    save: function(){
        var condition = $('#conditions').val();
        var first_value = $('#first_value').val();
        var second_value = $('#second_value').val();
        
        if(!$.isEmpty(condition) && !$.isEmpty(first_value)){
            if(condition == GLOBALS.ConditionsRangePrice.Bt && $.isEmpty(second_value))
                return;
            
            $.ajax({
                type: 'POST',
                url: filterproductspro_dir + 'actions.php',
                async: true,
                cache: false,
                dataType : 'json',
                data: 'action=saveRangePrice' + 
                    '&condition=' + condition +
                    '&first_value=' + first_value +
                    '&second_value=' + second_value ,
                beforeSend: function()
                {
                    $('#tab_range_price > table:eq(0)').addOverlay();
                    $('#save_range_price').disableButton(Msg.processing);
                },
                success: function(json)
                {            
                    try{
                        if(json.message_code == 0){
                            RangePrice.clearForm({fn: 'save'});
                            
                            var condition = $('#conditions option:selected').html();
                            $('#ranges_price_list tbody').append(
                                '<tr>' +
                                    '<td>' + condition + '</td>' +
                                    '<td>' + first_value + '</td>' +
                                    '<td>' + second_value + '</td>' +
                                    '<td class="actions">' + 
                                        '<img id="delete_range_price_' + json.data.option_criterion.id + '" src="' + filterproductspro_img + 'delete.png" onclick="RangePrice.deleteRangePrice(' + json.data.option_criterion.id + ')" />' +
                                    '</td>' +
                                '</tr>'
                            );  
                    
                            $('#ranges_price_list tfoot')[$('#ranges_price_list tbody tr').length > 0 ? 'fadeOut' : 'fadeIn'](200);
                        }     

                        $('#div_loading_range_price').removeAttr('class').addClass((json.message_code == 0) ? 'conf' : 'error').html(json.message);
                    }
                    catch($Exc){}
                },
                complete: function(){
                    $('#save_range_price').enableButton(Msg.save);
                    $('#tab_range_price > table:eq(0)').delOverlay();                
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    $('#div_loading_range_price').removeAttr('class').addClass('error').html(XMLHttpRequest.responseText);                
                }
            });
        }
    },
    deleteRangePrice: function(id_option_criterion){
        if(!$.isEmpty(id_option_criterion)){
            if(confirm(Msg.confirm_delete)){
                var img_del = $('#delete_range_price_' + id_option_criterion).parent().html();
                $.ajax({
                    type: 'POST',
                    url: filterproductspro_dir + 'actions.php',
                    async: true,
                    cache: false,
                    dataType : 'json',
                    data: 'action=deleteRangePrice' + 
                        '&id_option_criterion=' + id_option_criterion,
                    beforeSend: function()
                    {
                        $("#div_loading_range_price").removeAttr('class').html('');
                        $('#delete_range_price_' + id_option_criterion).replaceWith('<img id="delete_range_price_' + id_option_criterion + '" src="' + filterproductspro_img + 'loader.gif" />');
                    },
                    success: function(json)
                    {
                        if(json.message_code == 0){
                            $('#delete_range_price_' + id_option_criterion).parent().parent().addClass('strike').fadeOut(500, function(){
                                $(this).remove();
                        
                                $('#ranges_price_list tfoot')[$('#ranges_price_list tbody tr').length > 0 ? 'fadeOut' : 'fadeIn'](200);
                            });
                        }
                        else{
                            alert(json.message);
                        }                                                                                                                                                    
                    },
                    complete: function(){
                        $('#delete_range_price_' + id_option_criterion).replaceWith(img_del);
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert(XMLHttpRequest.responseText);
                    }
                });
            }
        }
    },
    clearForm: function(params){
        var p = $.extend({},{
            fn: 'index'
        },params);
                
        var _fn = {
            index: function(){
                $('#first_value, #second_vale').val('');
                $('#conditions').val('').trigger('change'); 
            },
            save: function(){
                $('#first_value, #second_value').val('');
            }
        }
        
        try{
            _fn[p.fn]();
        }
        catch($Exp){}
    }
}
//Funciones generales
var Util = {
    //Retorna un JSON con los valores de un campo para los idiomas disponibles, filtrado {id_lang: value}
    _getValuesLang: function(el){
        var values = '{'
        $.each(languages, function(i, lang){        
            var val = $(el + lang).val();
            values += '"' + lang + '"' + ':"' + val + '",';
        });
        values = (values.length > 1 ? values.slice(0,-1) : values) + '}';
        
        return values;
    },
    //Establece los valores de un campo para los idiomas disponibles, filtrado {id_lang: value}
     _setValuesLang: function(el, values){
        $.each(values, function(id_lang, name){
            $(el + id_lang).val(name);
        });
    },
    //Limpia los campos de los idiomas disponibles, filtrado por {id}
    _cleanValuesLang: function(prefix_id){
        $(':text[id^="' + prefix_id + '"]').val('');
    },
    //Verifica si hay algun valor para un lenguaje, partiendo del {input} que se pasa como parametro
    _checkValueLang: function(params){
        var p = $.extend({},{
            el: $(':text:eq(0)'),
            siblings_expr: '',
            force_def_lang: false
        }, params);
        
        if(p.force_def_lang){
            var el_def_lang = $(p.el).attr('id').split('_');
            el_def_lang = el_def_lang.splice(0, el_def_lang.length - 1).join('_') + '_' + id_default_language;
            
            if($.isEmpty($('#' + el_def_lang).val()))
                return false;
        }        
                
        var valid = false;
        if(!$.isEmpty($(p.el).val()))
            valid = true;
        else{
            var inputs = $(p.el).parent().siblings(p.siblings_expr).children('input');
            $.each(inputs, function(i, input){
                if(!$.isEmpty($(input).val()))
                    valid = true;
            });
        }
        
        return valid;
    },
    _getLabelOptionRangePrice: function(value){
        try{
            var data = value.split(',');
            
            if((data).length <= 1)
                return value;
            
            var condition = $('#conditions option[value="' + (!$.isEmpty(data[0]) ? data[0] : "") + '"]').html();
            var first_value = parseFloat(data[1]);
            var second_value = parseFloat(data[2]);

            condition = (!$.isEmpty(condition) ? condition : Msg._undefined);
            first_value = (!isNaN(first_value) ? first_value : '');
            second_value = (!isNaN(second_value) ? second_value : '');

            return condition + '&nbsp;' + first_value + (!$.isEmpty(second_value) ? '&nbsp;' + Msg.and + '&nbsp;' + second_value : '');
        }
        catch($Ex){
            return value;
        }
    }
}

//Extensiones, llamadas directamente como una funcion del core
jQuery.fn.extend({    
    addOverlay: function(){        
        return jQuery(this).addClass('overlay').fadeTo(0, .4);
    },
    delOverlay: function(){        
        return jQuery(this).fadeTo(100, 1).removeClass('overlay');
    },
    //Deshabilitar boton con la opcion de enviar un texto para setearlo, dado el caso que dentro del arreglo de {Msg}
    //Existe la llave como ID, se toma la propiedad {off} y se omite el texto enviado por parametro 
    disableButton: function(val){
        if(Msg[jQuery(this).attr('id')]){
            return jQuery(this).attr('disabled', true).find('span.ui-button-text').html(Msg[jQuery(this).attr('id')].off);
        }
        else   
            return jQuery(this).attr('disabled', true).find('span.ui-button-text').html(val);
    },
    //Habilitar boton con la opcion de enviar un texto para setearlo, dado el caso que dentro del arreglo de {Msg}
    //Existe la llave como ID, se toma la propiedad {on} y se omite el texto enviado por parametro 
    enableButton: function(val){
        if(Msg[jQuery(this).attr('id')]){
            return jQuery(this).attr('disabled', false).find('span.ui-button-text').html(Msg[jQuery(this).attr('id')].on).parent();
        }
        else   
            return jQuery(this).attr('disabled', false).find('span.ui-button-text').html(val).parent();
    }
});

jQuery.isEmpty = function(_var){
    return (_var == null || _var == undefined || typeof(_var) == 'undefined' || _var == '') ? true : false;
};
