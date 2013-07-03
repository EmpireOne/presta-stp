
var options_prev = new Array();

$(function(){    
    CustomControls.init();

    $('.filterproductspro_seacher :button').click(function(){
        $(this).toggleClass('on off');
    });
    
    //Verificar si todos lo titulos de las columnas estan en blanco, si lo estan, se eliminan
    $('table.column_list').each(function(i, data){
        var del = true;
        $(this).contents().find('span.value_column').each(function(j, item){
            var value = $.trim($(item).html());
            if(value != null && value != undefined && value != '' && value != '&nbsp;')
                del = false;
        });
        if(del)
            $(this).contents().find('span.value_column').remove();
    });
    
    //Eventos
    FilterProducts.init();
    FilterProducts.assingDefaults();
    FilterProducts.registerEvents();
    
    CustomControls.clear();
    
    //Desplegar el form
    $('.filterproductspro_seacher').fadeIn();
});

var FilterProducts = {
    _id_searcher : null,
    _options : {
        select: new Array(),
        checkbox: new Array(),
        radio: new Array(),
        button: new Array()
    },
    _dependencies: {},
    init: function(){
        $.blockUI.defaults.message = '<img src="' + filterproductspro_img + 'block_loader.gif" />';
        $.blockUI.defaults.overlayCSS.backgroundColor = '#cecece';
        $.blockUI.defaults.css.border = 'none';
        $.blockUI.defaults.css.backgroundColor = 'transparent';
        
        $('.filterproductspro_seacher').find('select.filter_parent').attr('disabled', true);
    },
    assingDefaults: function(){
        $('div.filterproductspro_seacher div.block_content select[name="' + GLOBALS.Types.Select + '"]').val('');
        $('div.filterproductspro_seacher div.block_content input[type="' + GLOBALS.Types.Checkbox + '"]').attr('checked', false);
        $('div.filterproductspro_seacher div.block_content input[type="' + GLOBALS.Types.Radio + '"]').attr('checked', false);
    },
    resetFiltersDependency: function(select){
        if(select && $(select).is('select')){
            var id_filter = $(select).attr('id').split('_')[2];
            var id_filter_parent = $(select).attr('id').split('_')[3];
            
            var _select = $('select[id^="filter_select_"][id$="_' + id_filter + '"]');            

            if($(_select).length){
                var _id_filter = $(_select).attr('id').split('_')[2];
                var _id_filter_parent = $(_select).attr('id').split('_')[3];
            
                $(_select).attr('disabled', true).find('option:first').attr('selected', true);                                
                        
                //REESTABLESE EL (SELECT) A SUS VALORES INICIALES.
                $(_select).empty();                
                $('select#filter_backup_select_' + _id_filter + '_' + _id_filter_parent + '.filter_backup').find('option').clone().appendTo(_select); 
                    
                FilterProducts.resetFiltersDependency(_select);
            }else{
                //limpia el ultimo filtro de la dependencia, siempre y cuando no tenga un valor seleccionado direferente de null
                if ($(select).val() == ''){
                    
                    //REESTABLESE EL (SELECT) A SUS VALORES INICIALES.
                    $(select).empty();
                    $('select#filter_backup_select_' + id_filter + '_' + id_filter_parent + '.filter_backup').find('option').clone().appendTo(select);
                    
                    //ELIMINA LAS OPCIONES QUE NO TENGA QUE VER CON LAS OPCIONES SELECCIONADAS SEGUN LA DEPENDENCIA.
                    var opts = $(select).find('option');
                    var dependencies = $.data(FilterProducts._dependencies, $(select).attr('id').toString().split('_')[2]);
                                         
                    if(dependencies){
                        $.each(opts, function(i, opt){
                            var id_option = $(opt).attr('value').toString().split('_')[1];

                            if(id_option){
                                if($.inArray(parseInt(id_option), dependencies) < 0)                                        
                                    $(select).find('#option_' + id_option).remove();                                                                                                                
                            }
                        });
                    }
                }                
            }                        
        }
    },
    _changeSelects: function(select, id_searcher, search){
        FilterProducts._options.select = new Array();   
        
        if (FilterProducts._id_searcher != id_searcher){
            FilterProducts._options.checkbox = new Array();
            FilterProducts._options.radio = new Array();
            FilterProducts._options.button = new Array();  
            
            FilterProducts._id_searcher = id_searcher;  
        }
        
        var val_opt_selected = select != null ? (select.val() != '' ? select.val() : $(select).val()) : null;
        var option = (select) ? FilterProducts._getIdOptionFromControl(val_opt_selected) : null;
        var id_filter = (select) ? $(select).attr('id').split('_')[2] : null;   
        
        FilterProducts.resetFiltersDependency(select);
        
        $('div.filterproductspro_seacher' + (id_searcher ? '#searcher_' + id_searcher : '') +  ' div.block_content select[name="' + GLOBALS.Types.Select + '"]').find('option:selected').each(function(i, item){                
            var id_control = $(item).val();
            if(id_control != null && id_control != undefined && FilterProducts._getIdOptionFromControl(id_control) != null)
                FilterProducts._options.select.push(FilterProducts._getIdOptionFromControl(id_control));
        });    
                        
        if(option && !isNaN(option) && id_filter && !isNaN(id_filter)){
            $.ajax({                
                url: filterproductspro_dir + 'actions.php',
                type: 'POST',
                dataType: 'json',
                async: false,
                cache: false,
                data:{
                    action: 'getAvailableOptionsDependency',
                    id_filter: id_filter,
                    'options[]': FilterProducts.getOptions()
                },
                beforeSend: function(){
                    
                },
                success: function(json){ 
                    try{
                        if(json.message_code == 0){
                            var id_filter_child = (json.data.id_filter_child) ? json.data.id_filter_child : null;                             
                            if(id_filter_child){
                                var select_child = $('#searcher_' + id_searcher + ' select#filter_select_' + id_filter_child + '_' + id_filter);
                                var opts_child = $(select_child).find('option');
                                                                                                
                                if(json.data.cant_options_filter != json.data.options.length){                                                                                                                                            
                                    //SE BORRAN LAS OPCIONES DEL FILTRO HIJO Y SOLO SE AGREGAN CUYAS OPCIONES ESTEN DISPONIBLES.                                    
                                    $('#searcher_' + id_searcher + ' select#filter_select_' + id_filter_child + '_' + id_filter + ' option:not(:first)').remove();
                                    
                                    $.each(json.data.options, function(i, id_option){
                                        $('select[id^="filter_backup_select_' + id_filter_child + '"].filter_backup #option_' + id_option).clone().appendTo(select_child);                                       
                                    });                                                                                                                                      
                                }
                                
                                if($(select_child).is(':disabled'))
                                    $(select_child).removeAttr('disabled');

                                //Guardar dependencias por filtro
                                $.data(FilterProducts._dependencies, id_filter_child.toString(), json.data.options);
                            }else{
                                $('select[id^="filter_select_' + id_filter + '"] > option:gt(0)').addClass('unremovable');
                            }
                        } 
                        else
                            alert(json.message);
                    }
                    catch($Exc){
                        console.log('ERROR: ' + $Exc);
                    }
                },
                complete: function(){
                    
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    alert(XMLHttpRequest.responseText);
                }
            });                                    
        }
                
        if(search)
            FilterProducts.search({id_searcher: id_searcher, id_filter: id_filter});
    },
    _changeRadios: function(id_searcher, search){
        FilterProducts._options.radio = new Array();
        
        if (FilterProducts._id_searcher != id_searcher){
            FilterProducts._options.checkbox = new Array();
            FilterProducts._options.select = new Array();
            FilterProducts._options.button = new Array(); 
            
            FilterProducts._id_searcher = id_searcher;   
        }
            
        $('div.filterproductspro_seacher' + (id_searcher ? '#searcher_' + id_searcher : '') +  ' div.block_content div.filter_content input[type="' + GLOBALS.Types.Radio + '"]:checked').each(function(i, control){
            var id_control = $(control).attr('id');
            if(id_control != null && id_control != undefined)
                FilterProducts._options.radio.push(FilterProducts._getIdOptionFromControl(id_control));
        });

        if(search)
            FilterProducts.search({id_searcher: id_searcher});
    },
    _changeCheckboxs: function(id_searcher, search){
        FilterProducts._options.checkbox = new Array();
        
        if (FilterProducts._id_searcher != id_searcher){
            FilterProducts._options.select = new Array();
            FilterProducts._options.radio = new Array();
            FilterProducts._options.button = new Array(); 
            
            FilterProducts._id_searcher = id_searcher;   
        }
            
        $('div.filterproductspro_seacher' + (id_searcher ? '#searcher_' + id_searcher : '') +  ' div.block_content div.filter_content input[type="' + GLOBALS.Types.Checkbox + '"]:checked').each(function(i, item){
            var id_control = $(item).attr('id');        
            if(id_control != null && id_control != undefined)
                FilterProducts._options.checkbox.push(FilterProducts._getIdOptionFromControl(id_control));            
        });    

        if(search)
            FilterProducts.search({id_searcher: id_searcher});
    },
    _clickButtons: function(button, id_searcher, search){                
        FilterProducts._options.button = new Array();
        
        if (FilterProducts._id_searcher != id_searcher){
            FilterProducts._options.checkbox = new Array();
            FilterProducts._options.radio = new Array();
            FilterProducts._options.select = new Array();
            
            FilterProducts._id_searcher = id_searcher;
        }
        
        if(button != null && button != undefined){
            var is_multi_option = Boolean(parseInt($(button).attr('name').split('_').slice(1)[0]));

            if(!is_multi_option){
                $('div.filterproductspro_seacher' + (id_searcher ? '#searcher_' + id_searcher : '') +  ' div.block_content input[name="' + $(button).attr('name') + '"]').not($(button)).removeClass('on').addClass('off');
            }
        }

        $('div.filterproductspro_seacher' + (id_searcher ? '#searcher_' + id_searcher : '') +  ' div.block_content input[type="' + GLOBALS.Types.Button + '"].on').each(function(i, control){
            var id_control = $(control).attr('id');
            if(id_control != null && id_control != undefined)
                FilterProducts._options.button.push(FilterProducts._getIdOptionFromControl(id_control));
        });

        if(search)
            FilterProducts.search({id_searcher: id_searcher});
    },
    registerEvents: function(){
        //{1}
        //$(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1]
        //Busca en el DOM a partir del {select} donde se origna el evento hasta donde el padre tenga la clase {filterproductspro_seacher}
        //Ubica el puntero en el ultimo objeto que contiene el {div.searher_#}
        
        $('div.filterproductspro_seacher div.block_content select[name="' + GLOBALS.Types.Select + '"]').change(function(){            
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            FilterProducts._changeSelects($(this), id_searcher, true);
        });
        
        $('div.filterproductspro_seacher div.block_content input[type="' + GLOBALS.Types.Checkbox + '"]').change(function(){
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            FilterProducts._changeCheckboxs(id_searcher, true);
        });
        
        $('div.filterproductspro_seacher div.block_content input[type="' + GLOBALS.Types.Radio + '"]').change(function(){
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            FilterProducts._changeRadios(id_searcher, true);
        });
        
        $('div.filterproductspro_seacher div.block_content input[type="' + GLOBALS.Types.Button + '"]').click(function(){
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            FilterProducts._clickButtons($(this), id_searcher, true);
        });
        
        $('span.one_filter').click(function(){   
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            
            CustomControls.resetByFilter({
                filters: new Array($(this).attr('name')),
                id_searcher: id_searcher
            });
        });
        
        $('span.clear_all_filters').click(function(){                        
            var ids_filter = new Array();
            
            //Mueve el puntero 2 contenedores hacia atras para obtener el {searcher_{ID}}
            $(this).parent().parent().find('.filter_content').each(function(i, filter){
                ids_filter.push($(filter).attr('id'));
            });
                        
            var id_searcher = $(this).parents('.filterproductspro_seacher:last').attr('id').split('_')[1];//{1}
            
            CustomControls.resetByFilter({
                filters: ids_filter,
                id_searcher: id_searcher
            });
        });
    
        $('.go_search').click(function(){
            var id_searcher = $(this).attr('id').split('_')[2];
            if(id_searcher)
                FilterProducts.search({
                    id_searcher: id_searcher,
                    force_search: true
                });
        });
        
        $('.wrapper_name .expand').toggle(function(){
            var _self = $(this);
            _self.parent().siblings().not('.clear').not(':radio').not(':checkbox').not('.filter_backup').hide();
        },function(){
            var _self = $(this);
            _self.parent().siblings().not('.clear').not(':radio').not(':checkbox').not('.filter_backup').show();
        }).click(function(){
            $(this).toggleClass('off on');
        });
        $('.wrapper_name .expand').trigger('click');
    },
    getOptions: function(){
        try{
            var options = new Array();
            
            $.each(FilterProducts._options, function(key, data){
                $.each(data, function(i, id_option){
                    if ($.inArray(id_option, options) < 0)
                        options.push(id_option); 
                });
            });

            return options;
        }
        catch($Exc){
            return new Array();
        }
    },
    search: function(params){
        var p = $.extend({},{
            id_searcher: null,
            id_filter: null,
            force_search: false
        },params);
        
        var options = FilterProducts.getOptions();

        if(options.length)
            FilterProducts._unavailableOptions({options: options, id_searcher: p.id_searcher, id_filter: p.id_filter});
        else{
            $('#searcher_' + p.id_searcher + ' label[for^="option_"], #searcher_' + p.id_searcher + ' span[name^="option_"], #searcher_' + p.id_searcher + ' :button.fpp_button').fadeIn();
                        
            //REESTABLESE TODOS LOS (SELECT) A SUS VALORES INICIALES.            
            $('#searcher_' + p.id_searcher + ' select[id^="filter_backup_select_"].filter_backup').each(function(i, select){
                var _id_filter = $(select).attr('id').split('_')[3];
                var _id_filter_parent = $(select).attr('id').split('_')[4];
                
                var _select = $('#searcher_' + p.id_searcher + ' select#filter_select_' + _id_filter + '_' + _id_filter_parent);
                
                var val_id_option_selected = $(_select).val();
                
                $(_select).empty();
                $('select#filter_backup_select_' + _id_filter + '_' + _id_filter_parent + '.filter_backup').find('option').clone().appendTo(_select);
                
                $('#searcher_' + p.id_searcher + ' select#filter_select_' + _id_filter + '_' + _id_filter_parent + ' option[value="' + val_id_option_selected + '"]').attr('selected', 'true');
            });                          
        }
                
        //Prevenir la busqueda
        if((!p.force_search && $('#go_search_' + p.id_searcher).length)/* || (p.force_search && !options.length)*/)
            return;
                        
        $.ajax({            
            url: fpp_is_ps_15 ? baseDir + 'index.php?fc=module&module=filterproductspro&controller=search' : filterproductspro_dir + 'filterproductspro_search.php',
            type: 'POST',            
            async: true,
            cache: false,
            data:{
                'options[]': options,
                'p': 1,
                /*'orderby': '',
                'orderway': '',*/
                'id_category': id_category,                
                'id_manufacturer': id_manufacturer,                
                'id_supplier': id_supplier,                
                'id_searcher': p.id_searcher,
                'ajax': true
            },
            beforeSend: function(){
                FilterProducts._blockUI();
            },
            success: function(html){ 
                try{
                    $(id_content_results).fadeOut(250, function(){
                        //UBICA LOS RESULTADOS EN EL CENTER SIN ELIMINAR EL CONTENIDO DEL FILTRO.
                        $(id_content_results + '>*:not(.filterproductspro_seacher_home)').remove();
                        $(this).append(html).fadeIn(250, FilterProducts._overrideEvents);
                        
                        //RECARGA EL EVENTO CLICK AJAX DEL BOTON DE AÑADIR AL CARRITO DEL LISTADO DE PRODUCTOS.
                        $('.ajax_add_to_cart_button').unbind('click').click(function(){
                			var idProduct =  $(this).attr('rel').replace('ajax_id_product_', '');
                			if ($(this).attr('disabled') != 'disabled')
                				ajaxCart.add(idProduct, null, false, this);
                			return false;
                		});
                    });
                }
                catch($Exc){
                    console.log('ERROR: ' + $Exc);
                }
            },
            complete: function(){
                 FilterProducts._unblockUI();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                alert(XMLHttpRequest.responseText);
            }
        });
        
        return true;
    },
    _unavailableOptions: function(params){
        var p = $.extend({},{
            id_searcher: null,
            id_filter: null
        },params);
        
        if(!p.id_searcher)
            return;
        
        $.ajax({            
            url: filterproductspro_dir + 'actions.php',
            type: 'POST',
            dataType: 'json',
            async: false,
            cache: false,
            data:{
                action: 'getUnavailableOptionsByOptions',
                'options[]': p.options,
                id_searcher: p.id_searcher,
                id_filter: p.id_filter,
                'id_category': id_category,                
                'id_manufacturer': id_manufacturer,                
                'id_supplier': id_supplier
            },
            beforeSend: function(){
                FilterProducts._blockUI();
            },
            success: function(json){ 
                try{
                    if(json.message_code == 0){ 
                        var ids_options = new Array(); 
                        
                        var options_unavailable = new Array();
                        $.each(json.data, function(id_filter, data){
                            if (data.options != undefined)
                                $.each(data.options, function(i, id_option){
                                    options_unavailable.push(id_option);                                
                                });
                            if (data.options_select != undefined)
                                $.each(data.options_select, function(i, id_option){
                                    options_unavailable.push(id_option);                                
                                });
                        });                       

                        //---------------------------------------------------------------------------------
                        //Vuelve a mostrar las opciones anteriores al seleccion la opcion en cuestio.
                        //Al seleccionar una opcion se ocultas otras, cuando se vuelve a clickar en la opcion, debe volver a mostrar las opciones que estaban ocultas, esto lo hace este codigo.                        
                        var _options_prev = options_prev[p.id_searcher];
   
                        if (_options_prev != undefined && _options_prev != ''){
                            $.each(_options_prev, function(i, id_option_prev){                                
                                var option_to_show = false;
                                
                                $.each(options_unavailable, function(i, id_option){                                                                            
                                    if (id_option_prev == id_option){                                        
                                        option_to_show = true;
                                        
                                        return true;
                                    }
                                });
                            
                                if(!option_to_show){                                    
                                    $('#searcher_' + p.id_searcher + ' label[for="option_' + id_option_prev + '"], #searcher_' + p.id_searcher + ' span[name="option_' + id_option_prev + '"], #searcher_' + p.id_searcher + ' #' + id_option_prev).fadeIn();                                                                        
                                }                                    
                            });
                        }                                                                                                                                                
                        //---------------------------------------------------------------------------------
                                              
                        $.each(json.data, function(id_filter, data){
                            var selector = '#searcher_' + p.id_searcher + ' select[id^="filter_select_' + id_filter + '_"]';
                            var selector_backup = '#searcher_' + p.id_searcher + ' select[id^="filter_backup_select_' + id_filter + '_"]';
                            
                            //BORRA LAS OPCIONES DEL SELECT.
                            var val_option_selected = $(selector).val();
                            $(selector + ' option:not(:first)').remove();
                                                                                  
                            //OCULTA LOS OPCIONES NO DISPONIBLES.
                            if (data.options != undefined)
                                $.each(data.options, function(i, id_option){
                                    if (data.type == GLOBALS.Types.Button)
                                        if($('#searcher_' + p.id_searcher + ' #option_' + id_option).is(':button'))
                                            $('#option_' + id_option).fadeOut();
                                    
                                    if (data.type == GLOBALS.Types.Checkbox || data.type == GLOBALS.Types.Radio)
                                        $('#searcher_' + p.id_searcher + ' label[for="option_' + id_option + '"], #searcher_' + p.id_searcher + ' span[name="option_' + id_option + '"]').hide();
                                                                                                   
                                    ids_options.push(id_option);
                                });
                            
                            //AGREGA SOLA LAS OPCIONES DISPONIBLES. LOS SELECT FUNCIONAN DIFERENTE A LOS DEMAS, YA QUE SE TRAEN LAS OPCIONES DISPONIBLES.
                            
                            if (data.options_select != undefined)
                                $.each(data.options_select, function(i, id_option){                                
                                    var _option = $(selector_backup + '.filter_backup #option_' + id_option).clone();                                    
                                    $(selector).append(_option);
                                                                
                                    ids_options.push(id_option);
                                });
                            
                            $(selector + ' option[value=' + val_option_selected + ']').attr('selected', 'true');
                        });
                        
                        options_prev[p.id_searcher] = options_unavailable;
                    }
                    else
                        alert(json.message);
                }
                catch($Exc){
                    console.log('ERROR: ' + $Exc);
                }
            },
            complete: function(){
                FilterProducts._unblockUI();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                alert(XMLHttpRequest.responseText);
            }
        });
    },
    _getIdOptionFromControl: function(str){
        var val = str.split('_')[1];
        
        return (val != null && val != undefined && val != '' && typeof(val) != 'undefined') ? val : null;        
    },
    _overrideEvents: function(){
        var options = FilterProducts.getOptions();
        
        //Override enlaces de paginacion {a}
        $(this).find('ul.pagination a').click(function(event){
            var url = $(this).attr('href');

            if(url == null || url == undefined || url == ''){
                event.stopPropagation();
                event.preventDefault();
            }
            
            if ($('#selectPrductSort').length > 0)
                var splitData = $('#selectPrductSort').val().split(':');
            else
                var splitData = new Array('', '');
            
            FilterProducts._callAjaxOverrides(
                event, 
                url,
                'options=' + options +
                '&id_category=' + id_category +                       
                '&id_manufacturer=' + id_manufacturer +                 
                '&id_supplier=' + id_supplier +
                ($('#nb_item').val() != undefined && $('#nb_item').val() != '' ? '&n=' + $('#nb_item').val() : '') + 
                '&orderby=' + splitData[0] +
                '&orderway=' + splitData[1] +
                '&id_searcher=' + FilterProducts._id_searcher
            );
        });
        
        //{2}
        //A partir el control donde se origna el evento, se desplaza hacia atras hasta donde ubique un {form}
        
        //Override de los botones {submit}
        $(this).find('div#pagination :submit').click(function(event){
            var form = $(this).parents('form');//{2}
            var url = $(form).attr('action');
            
            if(url == null || url == undefined || url == ''){
                event.stopPropagation();
                event.preventDefault();
            }
            
            if ($('#selectPrductSort').length > 0)
                var splitData = $('#selectPrductSort').val().split(':');
            else
                var splitData = new Array('', '');
            
            FilterProducts._callAjaxOverrides(
                event, 
                url,
                'options=' + options +
                '&id_category=' + id_category +
                '&id_manufacturer=' + id_manufacturer +                 
                '&id_supplier=' + id_supplier +
                '&' + $(form).serialize() +
                '&orderby=' + splitData[0] +
                '&orderway=' + splitData[1] +
                '&id_searcher=' + FilterProducts._id_searcher
            );
        });
        
        //Override sort
        $('#selectPrductSort').removeAttr('onchange').unbind().change(function(event){
            
            var form = $(this).parents('form');//{2}            
            var url = $(form).attr('action');
                        
            if(url == null || url == undefined || url == ''){
                event.stopPropagation();
                event.preventDefault();
            }
            
            var splitData = new Array('position', 'asc');
                        
            if($(this).val().match(/[a-z]:[a-z0-9]/gi))
                splitData = $(this).val().split(':');
            else{
                url = '';
                var vars = $(this).val().split('?');//Parte la url por {?} para obtener las variables pasadas por GET
                vars = vars[1] = undefined ? vars[1] : vars[1].split('&');//Obtiene la 2da posicion donde se encuentran las variables y parte la cadena por {&}
                
                var orderby = vars[0] != undefined ? vars[0] : '';//Obtiene la 1da posicion la cual contiene la variable {orderby={valor}}
                var orderway = vars[1] != undefined ? vars[1] : '';//Obtiene la 2ra posicion la cual contiene la varibla {orderway={$valor}}
                
                if(orderby != '' && orderway != '')
                    splitData = new Array(orderby.split('=')[1], orderway.split('=')[1]);//Parte cada variable por {=} y obtiene la 2da posicion la cual posee el valor
            }
            
            FilterProducts._callAjaxOverrides(
                event, 
                url,
                'options=' + options +
                '&id_category=' + id_category +
                '&id_manufacturer=' + id_manufacturer +                 
                '&id_supplier=' + id_supplier +
                ($('#nb_item').val() != undefined && $('#nb_item').val() != '' ? '&n=' + $('#nb_item').val() : '') + 
                '&orderby=' + splitData[0] +
                '&orderway=' + splitData[1] +
                '&id_searcher=' + FilterProducts._id_searcher
            );
        });
    },
    _callAjaxOverrides: function(event, url, data){
        var extra_params = url.split('p=');

        $.ajax({
            url: (fpp_is_ps_15 ? baseDir + 'index.php?fc=module&module=filterproductspro&controller=search' + (extra_params[1] != undefined ? '&p=' + extra_params[1] : '') : filterproductspro_dir + 'filterproductspro_search.php' + (extra_params[1] != undefined ? '?p=' + extra_params[1] : '')),            
            type: 'POST',
            async: true,
            cache: false,
            data: data + '&ajax=true',
            beforeSend: function(){
                FilterProducts._blockUI();
            },
            success: function(html){
                try{
                    $(id_content_results).fadeOut(250, function(){
                        //UBICA LOS RESULTADOS EN EL CENTER SIN ELIMINAR EL CONTENIDO DEL FILTRO.
                        $(id_content_results + '>*:not(.filterproductspro_seacher_home)').remove();
                        $(this).append(html).fadeIn(250, FilterProducts._overrideEvents);
                        
                        //RECARGA EL EVENTO CLICK AJAX DEL BOTON DE AÑADIR AL CARRITO DEL LISTADO DE PRODUCTOS.
                        $('.ajax_add_to_cart_button').unbind('click').click(function(){
                			var idProduct =  $(this).attr('rel').replace('ajax_id_product_', '');
                			if ($(this).attr('disabled') != 'disabled')
                				ajaxCart.add(idProduct, null, false, this);
                			return false;
                		});                    
                    });
                }
                catch($Exc){
                    console.log('ERROR: ' + $Exc);
                }
            },
            complete: function(){
                 FilterProducts._unblockUI();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                alert(XMLHttpRequest.responseText);
            }
        });
        
        event.stopPropagation();
        event.preventDefault();
    },
    _blockUI: function(params){
        var p = $.extend({},{
            msg: null
        },params);
        
        $('div.filterproductspro_seacher, ' + id_content_results).block((p.msg != null ? {message: p.msg} : {}));
    },
    _unblockUI: function(){
        $('div.filterproductspro_seacher, ' + id_content_results).unblock();
    }
}

var CustomControls = {
    params: {},
    init: function(params){
        var p = $.extend({},{
            checkboxHeight: 25,
            radioHeight: 25            
        },params);
        
        this.params = p;
        
        $('.filterproductspro_seacher input.radio, .filterproductspro_seacher input.checkbox').each(function(i, item){
            var span = $('<span></span>').addClass(item.type).attr({name: $(item).attr('id')});
            
            if($(item).is(':checked'))
                $(span).css({
                    backgroundPosition: '0 -' + ((item.type == 'checkbox') ? (p.checkboxHeight * 2) : (p.radioHeight * 2)) + 'px'
                });
            
            $(item)
            .hide()
            .before(span)
            .click(function(){
                CustomControls.clear();                
            });
            
            $('label[for="' + $(item).attr('id') + '"]')
            .css({
                height: p.radioHeight
            })
            .click(function(){
                CustomControls.clear();
            });
            
            if(!$(item).attr('disabled')){
                $(span)
                    .mousedown(function(){CustomControls.pushed($(this))})
                    .mouseup(function(){CustomControls.check($(this))});
            }
        });
    
        $('.filterproductspro_seacher, .filterproductspro_seacher label').mouseup(function(){CustomControls.clear()});
    },
    pushed: function(el){
        var input = $(el).next();
        var type = $(input).attr('type');
                                
        if(type == 'checkbox')
            $(el).css('backgroundPosition', '0 -' + (this.params.checkboxHeight * ($(input).is(':checked') ? 3 : 1)) + 'px');
        else if(type == 'radio')
            $(el).css('backgroundPosition', '0 -' + (this.params.radioHeight * ($(input).is(':checked') ? 3 : 1)) + 'px');
    },
    check: function(el){
        var input = $(el).next();
        var type = $(input).attr('type');
        
        if($(input).is(':checked')) {
            $(el).css('backgroundPosition', '0 0');
            $(input).attr('checked', false).trigger('change');
        }
        else{
            if(type == 'checkbox')
                $(el).css('backgroundPosition', '0 -' + (CustomControls.params.checkboxHeight * 2) + 'px');
            else if(type == 'radio'){
                $(el).css('backgroundPosition', '0 -' + (CustomControls.params.radioHeight * 2) + 'px');
                //Des-seleccionar los otros radios
                $('input[name="' + $(input).attr('name') + '"]').not(input).prev().css('backgroundPosition', '0 0');
            }
            $(input).attr('checked', true).trigger('change');
        }
    },
    clear: function() {
        $('.filterproductspro_seacher input.radio, .filterproductspro_seacher input.checkbox').each(function(i, item){
            var type = $(item).attr('type');
            var checked = $(item).is(':checked');
            
            if(type == 'checkbox' || type == 'radio' ){
                $(item).next()[checked ? 'addClass' : 'removeClass']('on');
            }
            
            if(type == 'checkbox' && checked)
                $(item).prev().css('backgroundPosition', '0 -' + (CustomControls.params.checkboxHeight * 2) + 'px');
            else if(type == 'checkbox' && !checked)
                $(item).prev().css('backgroundPosition', '0 0');
            else if(type == 'radio' && checked)
                $(item).prev().css('backgroundPosition', '0 -' + (CustomControls.params.radioHeight * 2) + 'px');
            else if(type == 'radio')
                $(item).prev().css('backgroundPosition', '0 0');
        });
    },
    resetByFilter: function(params){    
        var p = $.extend({},{
            filters: new Array(),
            id_searcher: null
        },params);

        $.each(p.filters, function(i, id_filter){            
            if(id_filter != null && id_filter != undefined){
                var filter = $('#' + id_filter);
                
                if($(filter).length){
                    $(filter).find('select:not(.filter_backup)').each(function(i, select){                        
                        var _id_filter = $(select).attr('id').split('_')[2];            
                        var _select = $('select[id^="filter_select_"][id$="_' + _id_filter + '"]');                                                                                                   
 
                        //ELIMINA LAS OPCIONES QUE NO TENGA QUE VER CON LAS OPCIONES SELECCIONADAS SEGUN LA DEPENDENCIA.
                        var opts = $(select).find('option');
                        var dependencies = $.data(FilterProducts._dependencies, $(select).attr('id').toString().split('_')[2]);
                                             
                        if(dependencies){
                            $.each(opts, function(i, opt){
                                var id_option = $(opt).attr('value').toString().split('_')[1];

                                if(id_option){
                                    if($.inArray(parseInt(id_option), dependencies) < 0)                                        
                                        $(select).find('#option_' + id_option).remove();                                                                                                                
                                }
                            });
                        }
                    });
                    
                    //FilterProducts.resetFiltersDependency($(filter).find('select:not(.filter_backup)'));
                    
                    $(filter).find('select').val('');                    
                    $(filter).find(':checkbox:checked').attr('checked', false);
                    $(filter).find(':radio:checked').attr('checked', false);
                    $(filter).find(':button.on').removeClass('on').addClass('off');
                }
            }
        });
        
        FilterProducts._changeSelects(null, p.id_searcher, false);
        FilterProducts._changeRadios(p.id_searcher, false);
        FilterProducts._changeCheckboxs(p.id_searcher, false);        
        FilterProducts._clickButtons(null, p.id_searcher, false);
        
        FilterProducts.search({id_searcher: p.id_searcher});
        CustomControls.clear();
    }
}