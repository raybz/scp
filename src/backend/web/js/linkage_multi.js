// (function ($) {
//     "use strict";
    function IMultiSelect(args)
    {
        this.original = args.original;
        this.aim = args.aim;
        this.selected_values_id = args.selected_values_id;
        this.url = args.url;
        this.depend = args.depend;
    }

    IMultiSelect.prototype = {
        originalChangeEvent: function(){
            var aim = this.aim;
            var url = this.url;
            var _isArray = this.isArray;
            var depend = this.depend;
            $(this.original).on('change', function(){
                var originalArr = $(this).val();
                var dependVal = $(depend).val();
                var originals = '';
                if (_isArray(originalArr)) {
                    if (originalArr && originalArr.length > 0) {
                        originals = originalArr.join(',');
                    }
                } else {
                    originals = originalArr;
                }
                $.getJSON(url, {originals: originals, depends : dependVal}, function(data){
                    $(aim).empty();
                    var rData = data.data;
                    var options = '';
                    if (rData) {
                        var i = 0;
                        for (i; i < rData.length; i ++) {
                            options += '<option value="'+rData[i]['id']+'">' + rData[i]['name'] + '</option>';
                        }
                        $(aim).html(options);
                        $(aim).multiselect('rebuild');
                    }
                });
            });
        },
        common: function(){
            var originalArr = $(this.original).val();
            var originals = '';
            if (this.isArray(originalArr)) {
                if (originalArr && originalArr.length > 0) {
                    originals = originalArr.join();
                }
            } else {
                originals = originalArr;
            }

            var selectedGame = $(this.selected_values_id).val();
            var selectedArr  = [];
            if (selectedGame) {
                selectedArr = selectedGame.split(',');
            }
            var aim = this.aim;
            var url = this.url;
            $.getJSON(url, {originals: originals}, function(data){
                $(aim).empty();
                var rData = data.data;
                var options = '';
                if (rData) {
                    var i = 0;
                    for (i; i < rData.length; i ++) {
                        var ch = '';
                        if (selectedArr.length > 0) {
                            if ($.inArray(rData[i]['id'], selectedArr) > -1) {
                                ch = 'selected';
                            }
                        }
                        options += '<option value="'+rData[i]['id']+'" '+ch+'>' + rData[i]['name'] + '</option>';
                    }
                    $(aim).html(options);
                    $(aim).multiselect('rebuild');
                }
            });
        },
        isArray: function (o) {
            'use strict';
            return Object.prototype.toString.call(o)=='[object Array]';
        },
        start: function(){
            this.common();
            this.originalChangeEvent();
            $(this.aim).multiselect(
                {   includeSelectAllOption: true,
                    selectAllText: '全选',
                    filterPlaceholder: '请选择...',
                    nonSelectedText: '未选择',
                    enableFiltering: true,
                    numberDisplayed: 0
                }
            );
        }
    };
// })();
