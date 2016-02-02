$(function() {
    var params = {
        params_count: 0,
        $param_cont: $('#param-rows-container'),
        $template: $('#param-row-template'),
        $add_btn: $('#add-param-btn'),
        addParam: function() {
            var $row;
            if (this.params_count < 5) {
                $row = $(this.$template.html());
                $row.appendTo(this.$param_cont);
                this.params_count += 1;
            } 
            if (this.params_count >= 5) {
                this.$add_btn.hide();
            }
            return $row;
        },
        removeParam: function($row) {
            $row.remove();
            this.params_count -= 1;
            this.$add_btn.show();
        },
        init: function() {
            var that = this;
            $('#add-param-btn').click(function() {
                that.addParam();
            });
            $('#param-rows-container').on('click', '.remove-param-btn', function() {
                that.removeParam($(this).closest('.param-row'));
            });            
        }
    };
    
    var form = {
        data: {},
        selector: '#url-form',
        $form: $('#url-form'),
        $no_response: $('#no-response'),
        $status_lbl: $('#status-display'),        
        $status_code: $('#status-display > span'),
        $response_cont: $('#response-container'),
        $error_lbl: $('#error-display'),
        $error_msg: $('#error-display > span'),
        $loading: $('#response-wrapper .loading'),
        init: function() {
            var that = this;
            this.clear();
            $(document).on('beforeSubmit', this.selector, function () {
                that.doSubmit();
                return false;
            });
        },
        doSubmit: function() {
            var that = this;
            this.beforeSubmit();

            $.ajax({
                url: '',
                method: 'POST',
                data: that.$form.serialize(),
                dataType: 'json'
            }).done(function(data) {
                if (!data || !data.meta) {
                    that.showError('Unknown server error');
                    return;
                }
                that.data = data.data;
                history.add(data.data, true);
                if (data.meta.status === 'error') {
                    that.showError(data.data.error);
                    return;   
                }
                that.displayStatus();
                if (!data.data.response) {
                    that.showError('No response');
                } else {
                    that.showResponse();
                }

            }).fail(function( _jqXHR, _textStatus, errorThrown ) {
                that.showError(errorThrown);
            }).always(function() {
                that.afterSubmit();
            });
        },
        beforeSubmit: function() {
            this.$form.find(':submit').prop('disabled', true);
            this.clear();
            this.$loading.show();
            this.$no_response.hide();
        },
        afterSubmit: function() {
            this.$form.find(':submit').prop('disabled', false);
            this.$loading.hide();
        },
        clear: function() {
            this.$status_lbl.hide();
            this.$error_lbl.hide();
            this.$response_cont.hide();
            this.$status_code.html('');
            this.$response_cont.html('');
        },
        fill: function(data) {
            var name, $row;
            form.$form.find('[name="UrlForm[url]"]').val(data.url);
            form.$form.find('[name="UrlForm[method]"]').prop('checked', false);
            form.$form.find('[name="UrlForm[method]"][value="' + data.method + '"]').prop('checked', true);
            $('.param-row').each(function() {
                params.removeParam(this);
            });
            if (typeof data.params === 'object') {
                for(name in data.params) {
                    $row = params.addParam();
                    $row.find('[name="UrlForm[param_names][]"]').val(name);
                    $row.find('[name="UrlForm[param_values][]"]').val(data.params[name]);
                }
            }
        },
        displayStatus: function() {
            this.$status_lbl.show();
            this.$status_code.html(this.data.status_code);
        },
        showResponse: function() {
            this.$response_cont.show().html(this.data.response);
        },
        showError: function(msg) {
            this.$error_lbl.show();
            this.$error_msg.html(msg);
        }
    };
    
    var history = {
        count: 0,
        $request_histroy: $('#request-history'),
        $loading: $('#request-wrapper .panel-body, #request-wrapper .loading'),
        $no_history: $('#no-history'),
        row_template: $('#history-row-template').html(),
        init: function() {
            var that = this;
            this.load();
            this.$request_histroy.on('click', '.repeat-request-btn', function() {
                that.repeat($(this).closest('tr').data('query'));
            });
        },
        load: function() {
            var that = this;
            this.beforeLoad();
            $.ajax({
                method: 'GET',
                url: 'ajax/request-history',
                cache: false
            }).done(function(data) {
                var i, history;
                if (data && data.meta && data.meta.status !== 'error') {
                    history = data.history;
                    if (history.length) {
                        for(i in history) {
                            that.add(history[i]);
                        }
                    } else {
                        that.showNoHistory();
                    }
                }
            }).always(function() {
                that.afterLoad();
            });
        },
        beforeLoad: function() {
            this.$loading.show();
            this.$no_history.hide();
        },
        afterLoad: function() {
            this.$loading.hide();
        },
        showNoHistory: function() {
            this.$no_history.show();
        },
        
        add: function(data, highlight) {
            var html, $row;
            
            html = this.render(this.row_template, {
                '%date%': data.date,
                '%method%': data.method,
                '%url%': data.url,
                '%url-title%': data.url,
                '%params%': this.paramsToString(data.params)
            });
            $row = $(html).prependTo(this.$request_histroy.find('tbody'));
            $row.data('query', {
                method: data.method,
                url: data.url,
                params: data.params
            });
            
            if (this.count >= 10) {
                this.$request_histroy.find('tr:last').remove();
            } else {
                this.count += 1;
            }
            
            this.$request_histroy.find('tr').removeClass('info');
            if (highlight) {
                $row.addClass('info');
            }
        },
        repeat: function(data) {
            form.fill(data);
            form.doSubmit();
        },
        paramsToString: function(params) {
            var name, html = '';
            if (typeof params === 'object') {
                for (name in params) {
                    html += name + '=' + params[name] + '<br />';
                }
            }
            return html;
        },
        render: function(template, data) {
            var id;
            for (id in data) {
                template = template.replace(id, data[id]);
            }
            return template;
        }
    };
    
    params.init();
    form.init();
    history.init();
});