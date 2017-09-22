function IMultiSelect(args) {
    'use strict';
    this.original = args.original;
    this.aim = args.aim;
    this.selected_values_id = args.selected_values_id;
    this.url = args.url;
    this.append = args.append;
    this.append_show_max_length = args.append_show_max_length;
    this.depend = args.depend;
}

IMultiSelect.prototype = {
    originalChangeEvent: function () {
        'use strict';
        var aim = this.aim;
        var append = this.append;
        var url = this.url;
        var _isArray = this.isArray;
        var depend = this.depend;
        var _length = this.append_show_max_length;
        /*jshint -W117 */
        $(this.original).on('change', function () {
            var originalArr = $(this).val();
            var dependVal = $(depend).val();
            var originals = '';
            if (_isArray(originalArr)) {
                if (originalArr && originalArr.length > 0) {
                    if(originalArr.length > _length) {
                        originals = '';
                        $(append).hide();
                    } else {
                        $(append).show();
                        originals = originalArr.join(',');
                    }
                }
            } else {
                originals = originalArr;
            }
            $.getJSON(url, {originals: originals, depends: dependVal}, function (data) {
                var rData = data.data;
                var options = '';
                if (rData) {
                    var i = 0;
                    for (i; i < rData.length; i++) {
                        /*jshint -W069 */
                        options += '<option value="' + rData[i]['id'] + '">' + rData[i]['name'] + '</option>';
                    }
                    $(aim).empty().html(options).multiselect('rebuild');
                }
            });
        });
    },
    common: function () {
        'use strict';
        /*jshint -W117 */
        var originalArr = $(this.original).val();
        var originals = '';
        var append = this.append;
        var depend = this.depend;
        var dependVal = $(depend).val();
        var _length = this.append_show_max_length;
        if (this.isArray(originalArr)) {
            if (originalArr && originalArr.length > 0) {
                if(originalArr.length > _length) {
                    $(append).hide();
                } else {
                    $(append).show();
                }
                originals = originalArr.join();
            }
        } else {
            originals = originalArr;
        }

        var selectedGame = $(this.selected_values_id).val();
        var selectedArr = [];
        if (selectedGame) {
            selectedArr = selectedGame.split(',');
        }

        var aim = this.aim;
        var url = this.url;
        $.getJSON(url, {originals: originals, depends: dependVal}, function (data) {
            var rData = data.data;
            var options = '';
            if (rData) {
                var i = 0;
                for (i; i < rData.length; i++) {
                    var ch = '';
                    if (selectedArr.length > 0) {
                        /*jshint -W069 */
                        if ($.inArray(rData[i]['id'], selectedArr) > -1) {
                            ch = 'selected';
                        }
                    }
                    options += '<option value="' + rData[i]['id'] + '" ' + ch + '>' + rData[i]['name'] + '</option>';
                }
                $(aim).empty().html(options).multiselect('rebuild');
            }
        });
    },
    isArray: function (o) {
        'use strict';
        return Object.prototype.toString.call(o) == '[object Array]';
    },
    start: function () {
        'use strict';
        /*jshint -W117 */
        this.common();
        this.originalChangeEvent();
        $(this.aim).multiselect(
            {
                includeSelectAllOption: true,
                selectAllText: '全选',
                filterPlaceholder: '请选择...',
                nonSelectedText: '未选择',
                enableFiltering: true,
                numberDisplayed: 0
            }
        );
    }
};