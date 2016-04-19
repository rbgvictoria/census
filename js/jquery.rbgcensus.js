var base_url = 'http://data.rbg.vic.gov.au/dev/rbgcensus';
var query_string = location.href.substr(location.href.indexOf('?') + 1);
var access_key = '';

$(function() {
    setPopover();
    if ($('form:eq(0)').attr('data-restricted-access-key')) {
        access_key = $('form:eq(0)').attr('data-restricted-access-key');
    }

    // auto-complete for name search
    $('input[name=taxon], input[name=q]').autocomplete({
        source: base_url + '/autocomplete/autocomplete_taxonname/' + access_key,
        minLength: 2
    });
    
    // auto-complete for common name search
    $('input[name=common_name]').autocomplete({
        source: base_url + '/autocomplete/autocomplete_common_name/' + access_key,
        minLength: 2
    });
    
    // auto-complete for family search
    $('input[name=family]').autocomplete({
        source: base_url + '/autocomplete/autocomplete_family/' + access_key,
        minLength: 2
    });
    
    // auto-complete for wgs region search
    $('input[name=wgs_fullname]').autocomplete({
        source: base_url + '/autocomplete/autocomplete_wgs',
        minLength: 2
    });
    
    bedFields();
    subprecinctDropDown();
    bedDropDown();
    
    // get beds for Cranbourne or Melbourne
    $('#search_location').change(function() {
        bedFields();
        subprecinctDropDown();
        bedDropDown();
    });
    
    $('#search_precinct').change(function() {
        subprecinctDropDown();
        bedDropDown();
    });
    
    $('#search_subprecinct').change(function() {
        bedDropDown();
    });
    
    // Prevent 'restricted' checkbox from being changed
    $('td [type=checkbox]').click(function(e) {
        e.preventDefault();
        return false;
    });
    
    /*
     * 
     */
    $('.result-table .taxon-name').parent('a').before('<span class="glyphicon glyphicon-triangle-bottom collapsible"></span>');
    $('.result-table .bed-link').before('<span class="glyphicon glyphicon-triangle-bottom collapsible"></span>');
    
    $('.result-table').on('click', '.collapsible', function(e) {
        $(this).parents('tbody').eq(0).find('tr:gt(0)').hide();
        $(this).removeClass('glyphicon-triangle-bottom').addClass('glyphicon-triangle-right');
        $(this).removeClass('collapsible').addClass('expandable');
    });
    
    $('.result-table').on('click', '.expandable', function(e) {
        $(this).parents('tbody').eq(0).find('tr').show();
        $(this).removeClass('glyphicon-triangle-right').addClass('glyphicon-triangle-bottom');
        $(this).removeClass('expandable').addClass('collapsible');
    });
    
    $('.result-table.search tbody:first tr:first td:first,\n\
        .result-table.bed tbody:first tr:first td:first,\n\
        .result-table.taxon tbody:first tr:first td:first').prepend('<div class="all"><a class="collapse-all" href="#">collapse all</a> | <a class="expand-all" href="#">expand all</a></div>');
    
    $('.result-table').on('click', '.collapse-all', function(e) {
        e.preventDefault();
        $('.result-table tbody').each(function() {
            $(this).find('tr:gt(0)').hide();
        });        
        $('.collapsible').removeClass('glyphicon-triangle-bottom').addClass('glyphicon-triangle-right');
        $('.collapsible').removeClass('collapsible').addClass('expandable');
    });
    
    $('.result-table').on('click', '.expand-all', function(e) {
        e.preventDefault();
        $('.result-table tbody').each(function() {
            $(this).find('tr').show();
        });        
        $('.expandable').removeClass('glyphicon-triangle-right').addClass('glyphicon-triangle-bottom');
        $('.expandable').removeClass('expandable').addClass('collapsible');
    });
    

});

var setPopover = function() {
    $('[data-toggle=popover]').popover({
        title: '<div class="text-right"><span onclick="$(&quot;[data-toggle=popover]&quot;).popover(&quot;hide&quot;);"><i class="fa fa-times"></i></span></div>',
        html: true
    }).on('shown.bs.popover', function () {
        if ($(this).attr('data-popover-width') !== undefined) {
            var popwidth = $(this).attr('data-popover-width');
            $(this).next('.popover').css({'width': popwidth + 'px', 'max-width': popwidth + 'px'});
        }
        else {
            $(this).next('.popover').css({'width':'276px', 'max-width':'276px'});
        }
    });

    $('body').on('click', function (e) {
        $('[data-toggle=popover]').each(function () {
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });    
};

var bedFields = function() {
    var location = $('#search_location').val();
    if (location === 'Cranbourne') {
        $('#search_grid').parents('.form-group').eq(0).hide();
        $('#search_precinct').parents('.form-group').eq(0).show();
        $('#search_subprecinct').parents('.form-group').eq(0).show();
        $('#search_grid').val('');
    }
    else { 
        if (location === 'Melbourne') {
            $('#search_grid').parents('.form-group').eq(0).show();
            $('#search_precinct').parents('.form-group').eq(0).hide();
            $('#search_subprecinct').parents('.form-group').eq(0).hide();
            $('#search_precinct').val('');
            $('#search_subprecinct').val('');
            
        }
        else {
            $('#search_grid').parents('.form-group').eq(0).show();
            $('#search_precinct').parents('.form-group').eq(0).show();
            $('#search_subprecinct').parents('.form-group').eq(0).show();
        }
    }
};

var bedDropDown = function() {
    var location = $('#search_location').val();
    var precinct = $('#search_precinct').val();
    var subprecinct = $('#search_subprecinct').val();
    var url = base_url + '/ajax/bed';
    $.ajax({
        url: url,
        data: {
            location: location,
            precinct: precinct,
            subprecinct: subprecinct,
            access_key: access_key
        },
        success: function(data) {
            var options = [];
            options.push('<option value=""></option>');
            $.each(data, function(index, item) {
                var option = '<option value="' + item + '">' + item + '</option>';
                options.push(option);
            });
            $('#search_bed').html(options.join(''));
            if (data.length === 1) {
                $('#search_bed').val(data[0]);
            }
        }
    });
};

var subprecinctDropDown = function() {
    var location = $('#search_location').val();
    var precinct = $('#search_precinct').val();
    var url = base_url + '/ajax/subprecinct';
    $.ajax({
        url: url,
        data: {
            location: location,
            precinct: precinct,
            access_key: access_key
        },
        success: function(data) {
            var options = [];
            options.push('<option value=""></option>');
            $.each(data, function(index, item) {
                var option = '<option value="' + item + '">' + item + '</option>';
                options.push(option);
            });
            $('#search_subprecinct').html(options.join(''));
            if (data.length === 1) {
                $('#search_subprecinct').val(data[0]);
            }
        }
    });
};