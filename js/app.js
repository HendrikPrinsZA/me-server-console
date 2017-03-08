function getJson(obj) { var v, ret = []; for (var key in obj) { if (obj.hasOwnProperty(key)) { v = obj[key]; if (v === 'toString') { continue; }; if (Ember.typeOf(v) === 'function') { continue; }; ret.push(key); } }; return obj.getProperties ? obj.getProperties(ret) : obj; };

function downloadFileInMemory(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

// Converts json deep arrays to ember objects
// Allows you to use get & set on deeper level array objects
function jsonToDeepEmberObject(data) {
    $.each(data, function(key, value) {
        if (value && value.constructor === Array) {
            if (value.length > 0) {
                var tempArr = Em.A();
                $.each(value, function(idx, record) { tempArr.pushObject(Ember.Object.create(record)); });
                data[key] = tempArr;
            }
        } else if (value && value.constructor === Object) {
            data[key] = jsonToDeepEmberObject(value);
        }
    });
    return Ember.Object.create(data);
}

App = Ember.Application.create({
    api: 'api'
});

App.Router.map(function() {
    this.route("index", {path: "/"});
    this.route("core", {path: "/core"});
    this.route("modules", {path: "/modules"});
    this.route("php", {path: "/php"});
    this.route("database", {path: "/database"});
    this.route("version", {path: "/version"});
    this.route("other", {path: "/other"});
    this.route("ezservermonitor", {path: "/ezservermonitor"});
    this.route("apachestatus", {path: "/apachestatus"});
});

App.ApplicationRoute = Ember.Route.extend({
    model: function() {
        return $.getJSON('config/specification.json').then(function(response) {
            return jsonToDeepEmberObject(response);
        });
    },

    renderTemplate: function(controller, model) {
        var self = this;

        self.render();

        $.ajax({type: 'GET', url: App.api + '/authentication/checkstatus', dataType: 'json'}).success(function(reply) {
            model.set('sessionStatus', reply);
        }).fail(function(reply) { }).always(function(reply) {
            controller.set('startupStatus', {
                total: model.requirements.length,
                passed: 0
            });
            self.send('checkCoreRequirements');
        });

    },

    actions: {

        downloadDatabaseFile: function() {
            $.ajax({type: 'GET', url: App.api + '/tools/database', dataType: 'json'}).success(function(reply) {
                alert('Error');
                console.log(reply);
            }).fail(function(reply) {
            }).always(function(reply) {
            });
        },

        downloadServerFile: function(type) {
            var endpoint = null;
            switch(type) {
                case 'phpini':
                    endpoint = App.api + '/action/phpini';
                break;
                case 'phpinfo':
                    endpoint = App.api + '/action/phpinfo';
                break;
                case 'phperror':
                    endpoint = App.api + '/action/phperror';
                break;
            }

            if (endpoint) {
                window.open(endpoint);
            }
        },

        checkCoreRequirements: function() {
            var self = this,
                controller = self.get('controller'),
                model = controller.get('model'),
                requirements = model.get('requirements'),
                coreRequirementsCheckedAll = true;

            // Bypass Checks
            // controller.set('applicationData.coreRequirementsCheckedAll', true); return true;
            

            if (!controller.get('applicationData.currentRequirement')) {
                controller.set('applicationData.currentRequirement', Ember.Object.create({status: {success: true}}));
            }

            if (controller.get('applicationData.currentRequirement.status.success') || controller.get('applicationData.currentRequirement.status.skipped')) {
                $.each(requirements, function(idx, record) {


                    if (record.get('status.success') || record.get('status.skipped')) { } else {

                        controller.send('checkCoreRequirement', record, function() {
                            if (
                                (idx === (requirements.length - 1)) && 
                                (record.get('status.success') || record.get('status.skipped'))
                            ) {
                                controller.set('applicationData.coreRequirementsCheckedAll', true);
                            } else {
                                controller.set('startupStatus.passed', controller.get('startupStatus.passed')+1);
                                controller.send('checkCoreRequirements');
                            }
                        }); 
                        return false;
                    }
                });
            }
        },

        checkCoreRequirement: function(record, callback) {
            var self = this,
                controller = this.get('controller');

            controller.set('applicationData.currentRequirement', record);

            var refresh = record.get('refresh');
            if (refresh) {
                record.set('status', {loading: true});
                $.ajax({url: refresh.url, data: refresh.data, dataType: 'json'}).then(function(data) {
                    if (data.success) {

                        var passed = false;

                        if (!isNaN(parseFloat(data.value))) {
                            // Numeric value
                            var value = parseFloat(data.value),
                                minValue = parseFloat(record.get('values.minimum')),
                                recValue = parseFloat(record.get('values.recommended'));

                            if (value >= minValue) {
                                passed = true;
                            }

                        } else {
                            // Textual value
                            if (data.value === record.get('values.minimum')) {
                                passed = true;
                            }
                        }

                        record.set('values.server', data.value);
                        if (passed) {
                            record.set('status', {success: true});
                        } else {
                            record.set('status', {failed: true});
                        }

                        // If server responds with title OR description, override the current
                        if (data.title && data.title.length > 0) {
                            record.set('display.title', data.title);
                        }
                        if (data.description && data.description.length > 0) {
                            record.set('display.description', data.description);
                        }

                        if (typeof callback === "function") {
                            callback();
                        }
                    }
                }).fail(function(error) {
                    record.set('values.server', error.status+": "+error.statusText);
                    record.set('status', {failed: true});
                }).always(function() {

                });
            }
        },

        skipCoreRequirement: function(record) {
            var self = this;
            record.set('status', {skipped: true});
            self.send('checkCoreRequirements');
        },

        retryCoreRequirement: function(record) {
            var self = this;
            record.set('status', {retry: true});
            self.send('checkCoreRequirement', record);
        },

        logout: function() {
            var self = this;
               
            $.ajax({url: App.api + '/authentication/logout', type: 'GET', dataType: 'json'}).success(function(reply) {
                if (reply.success) {
                    location.reload();
                }
            }).fail(function(reply) {
            }).always(function(reply) {
            });
            
        },

        doAction: function(action) {

            if (action.type === 'open') {

                window.open(action.endpoint);

            } else if (action.type === 'get') {

                $.ajax({type: 'get', url: action.endpoint, data: {}, dataType: 'json'}).success(function(reply) {
                    // obj.set('value', reply.value);
                }).fail(function(reply) {
                    // obj.set('value', '-1');
                }).always(function(reply) {

                });

            } else {

                $.ajax({type: 'post', url: action.endpoint, data: {}, dataType: 'json'}).success(function(reply) {
                    // obj.set('value', reply.value);
                }).fail(function(reply) {
                    // obj.set('value', '-1');
                }).always(function(reply) {

                });

            }
        }
    }
});

App.ApplicationController = Ember.Controller.extend({
    applicationData: {
        currentRequirement: null,
        coreRequirementsCheckedAll: false
    },

    oustandingRequirements: Ember.computed.filter('model.requirements.@each.status', function(record) {
        if (record.status.failed || record.status.skipped) {
            return true;
        }
    })
});

App.IndexRoute = Ember.Route.extend({
    // model: return $.ajax({'url': App.api + '/environment', 'type': 'GET', 'dataType': 'json'}); // Disabled, not guaranteed that the server is in an OK state therefore the model will be set in renderTemplate

    renderTemplate: function(controller, model) {
        var self = this;
        $.ajax({'url': App.api + '/environment', 'type': 'GET', 'dataType': 'json'}).success(function(reply) {
            model.setProperties(reply);

            // TEMP GET RID OF THIS SECTION
            model.set('tempFunction', {
                'function': 'extension_loaded',
                'parameter': 'xml'
            });
            // TEMP GET RID OF THIS SECTION
        }).fail(function(reply) {
        }).always(function(reply) {
            self.render();
        });
    },

    actions: {
        // TEMP GET RID OF THIS SECTION
        doTempAction: function() {
            var self = this,
                params = getJson(self.currentModel.tempFunction);

            console.log(params);
            $.ajax({'url': App.api + '/mediator', 'type': 'GET', 'data': params, 'dataType': 'json'}).success(function(reply) {
                console.log(reply);
            });
        }
        // TEMP GET RID OF THIS SECTION
    }
});

App.IndexController = Ember.Controller.extend({
    needs: ['application'],
    oustandingRequirements: Ember.computed.alias('controllers.application.oustandingRequirements'),
});

App.CoreRoute = Ember.Route.extend({
    needs: ['application']
});

App.ModulesRoute = Ember.Route.extend({
    needs: ['application']
});


App.RequirementsTableComponent = Ember.Component.extend({
    filterByTypes: '[]',

    filteredRecords: Ember.computed.filter('model.requirements.@each.type', function(record) {
        var filterByTypes = $.parseJSON(this.filterByTypes);
        if ($.inArray(record.type, filterByTypes) > -1) {
            return true;
        }
    }),

    actions: {

        sendAction: function(action, params) {
            var self = this;
            self.sendAction(action, params);
        },

        refreshAll: function() {
            var self = this,
                model = self.get('model');

            $.each(self.get('filteredRecords'), function(idx, record) {
                self.sendAction("checkCoreRequirement", record);
            });
        },

        downloadFile: function() {
            var csvData = this.get('model.requirements');

            csvText = csvData.map(function(record){
                var values = [];
                if (record.display !== null && record.display.title !== null) {
                    values.push(record.display.title);
                }
                if (record.values !== null && record.values.minimum !== null) {
                    values.push(record.values.minimum);
                }
                if (record.values !== null && record.values.server !== null) {
                    values.push(record.values.server);
                }
                return '"' + values.join('","') + '"';
            }).join('\n');

            downloadFileInMemory('variables_'+moment().format('YYYYMMDD_HHmmss')+'.csv', csvText);
        }

    }

});

App.VariablesTableComponent = Ember.Component.extend({
    attributeBindings: ['endpoint'],
    didInsertElement: function() {
        var self = this, 
            grid = self.$('table');

        var table = grid.DataTable({
            createdRow: function (row, data, index) { },
            ajax: {
                type: 'GET',
                url: App.api + self.endpoint,
                dataSrc: function (data) { return data.records; }
            },
            columns: [
                {title: '', data: 'tag'},
                {title: 'Variable Name', data: 'title'},
                {title: 'Priority', data: 'priority'},
                {title: 'Expected Value', data: 'recValue'},
                {title: 'Server Value Value', data: 'value'}
            ],
            rowClickHandler: function(tr, data, index) { }
        });


        // Refresh Button
        self.$('.dataTables_filter').append('<button type="button" title="Refresh" class="btn btn-primary btn-sm refresh-button" style="margin-left: 10px;"><i class="fa fa-refresh"></i></button>');
        self.$('button.refresh-button').on('click', function () { table.ajax.reload(); });

        // Download Button
        self.$('.dataTables_filter').append('<button type="button" title="Download" class="btn btn-primary btn-sm download-button" style="margin-left: 10px;"><i class="fa fa-download"></i></button>');
        self.$('button.download-button').on('click', function () { 
            var csvText = table.data().map(function(record){
                var values = [];
                if (record.title !== null) {
                    values.push(record.title);
                }
                if (record.recValue !== null) {
                    values.push(record.recValue);
                }
                if (record.value !== null) {
                    values.push(record.value);
                }
                return '"' + values.join('","') + '"';
            }).join('\n');

            downloadFileInMemory('variables_'+moment().format('YYYYMMDD_HHmmss')+'.csv', csvText);
        });
    },
    actions: {
        rowclick: function(data) {
            this.sendAction('rowclick', data);
        }
    }
});

App.AuthenticationFormComponent = Ember.Component.extend({
    needs: ['application'],
    actions: {

        authenticationSetup: function(params) {
            var self = this,
                model = self.get('model'),
                applicationController = self.get('controllers.application');

            self.set('errors', []);
            $.ajax({url: App.api + '/authentication/setup', data: getJson(model), type: 'POST', dataType: 'json'}).success(function(reply) {
                
                if (reply.success) {
                    location.reload();
                } else {
                    self.set('errors', reply.errors);
                }

            }).fail(function(reply) {
            }).always(function(reply) {
            });
        },

        authenticationLogin: function(params) {
            var self = this,
                model = self.get('model'),
                applicationController = self.get('controllers.application');

            self.set('errors', []);
            $.ajax({url: App.api + '/authentication/login', data: getJson(model), type: 'POST', dataType: 'json'}).success(function(reply) {
                if (reply.success) {
                    location.reload();
                } else {
                    self.set('errors', reply.errors);
                }
            }).fail(function(reply) {
            }).always(function(reply) {
            });
        }

    }

});

App.ProgressBarComponent = Ember.Component.extend({
    layout: Ember.HTMLBars.compile('<div class="progress-bar-component"></div><span style="float:right; margin-top:-20px;">{{model.passed}} / {{model.total}}</span>'),
    didInsertElement: function() {
        var self = this;
        self.$(".progress-bar-component").progressbar();
        self.$(".progress-bar-component").progressbar('setMaximum', self.get('model.total'));
        self.$(".progress-bar-component").progressbar('setDangerMarker', 40);
        self.$(".progress-bar-component").progressbar('setWarningMarker', 70);
        self.$(".progress-bar-component").progressbar('setPosition', self.get('model.passed'));
    }
});
Ember.Handlebars.helper('progress-bar', App.ProgressBarComponent.extend());

App.PieChartComponent = Ember.Component.extend({
    layout: Ember.HTMLBars.compile('<div class="flot-chart"><div class="flot-chart-content" style="width:100px;height:100px;"></div></div>'),
    didInsertElement: function() {
        console.log('App.PieChartComponent->didInsertElement');
        var self = this;

        var data = [{
            label: "Series 0",
            data: 1
        }, {
            label: "Series 1",
            data: 3
        }, {
            label: "Series 2",
            data: 9
        }, {
            label: "Series 3",
            data: 20
        }];

        $.plot(self.$('div.flot-chart-content'), data, {
            series: {
                pie: {
                    show: true,
                    radius: 1,
                    label: {
                        show: false,
                        radius: 2/3,
                        formatter: labelFormatter,
                        threshold: 0.1
                    }
                }
            },
            legend: {
                show: false
            }
        });

        function labelFormatter(label, series) {
            return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
        }
    }
});
Ember.Handlebars.helper('pie-chart', App.PieChartComponent.extend());

App.PasswordInputComponent = Ember.Component.extend({
    layout: Ember.HTMLBars.compile(
        '<div id="pwd-container" class="password-input">'+
            '{{input type="password" id="thePasswordfield" class=inputClass placeholder=inputPlaceholder value=inputValue}}'+
            '<i class="fa fa-eye password-input-peek"></i>'+
        '</div>'
    ),
    didInsertElement: function() {
        var self = this;
        self.$('input').on("keyup", function() {
            if ($(this).val()) {
                self.$(".password-input-peek").show();
            } else {
                self.$(".password-input-peek").hide();
            }
        });

        // Disable Chrome autocomplete
        // setTimeout(function(){
        //     self.$('input').attr('type','password');
        // }, 300);

        self.$('.password-input-peek').on('mousedown touchstart', function(){
            self.$('input').attr('type','text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        }).on('mouseup mouseout touchend touchcancel', function(){
            self.$('input').attr('type','password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        });

        // https://github.com/ablanco/jquery.pwstrength.bootstrap
        self.$('input').pwstrength({
            common: {
                debug: false,
                minChar: 8
            },
            ui: {
                showVerdicts: false,
                showErrors: false,
                showProgressBar: false
            },
            rules: {
                activated: {
                    wordSequences: false,
                    wordLength: true,
                    wordLowercase: true,
                    wordUppercase: true,
                    wordOneNumber: true,
                    wordOneSpecialChar: true
                }
            }
        });
    }
});
Ember.Handlebars.helper('password-input', App.PasswordInputComponent.extend());