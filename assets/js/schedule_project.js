(function () {
    function getRandomColor() {
        var letters = '0123456789ABCDEF'.split('');
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    function getRandomInt(min, max) {
        var min = 1;
        var max = 100000000;
        return Math.floor(Math.random() * (max - min + 1) + min);
    }

    var setDatetimePicker = function (elem) {
        jQuery(elem).datetimepicker({
            timepicker: true,
            format: 'Y-m-d H:i',
            onChangeDateTime: function (dp, $input) {
                var project_id = $input.attr('data-project_id');

                for (var i = 0, len = scope.data.items.length; i < len; i++) {
                    var curItem = scope.data.items[i];

                    if (project_id == curItem.project_id) {
                        curItem.start_time = $input.val();
                        console.log(curItem.start_time);
                        break;
                    }
                }
            }
        });
    };

    var setDatetimePicker2 = function (elem) {
        jQuery(elem).datetimepicker({
            timepicker: true,
            format: 'Y-m-d H:i',
            onChangeDateTime: function (dp, $input) {
                var project_id = $input.attr('data-project_id');

                for (var i = 0, len = scope.data.items.length; i < len; i++) {
                    var curItem = scope.data.items[i];

                    if (project_id == curItem.project_id) {
                        curItem.end_time = $input.val();
                        console.log(curItem.end_time);

                        //if (project_id.match(/^t/)) {
                        //    curItem.project_id = '';
                        //}
                        break;
                    }
                }
            }
        });
    };

    if (typeof angular !== 'undefined') {
        window.myApp = angular.module("myapp", []);
    }

    var href = location + '';
    var regs = href.match(/workorders\/profile\/(\d+)/);

    if (regs) {

        var scope = null;

        jQuery(function () {

            setTimeout(function () {
                setDatetimePicker('.mydatetimepicker');
                setDatetimePicker2('.mydatetimepicker2');

            }, 200);

        });

        var estimateId = regs[1];

        var getNewItem = function (data) {
            return {
                //"project_id":"",
                "estimate_id": estimateId,
                "crew_id": "",
                "date": "0000-00-00 00:00",
                "start_time": "0000-00-00 00:00",
                "end_time": "0000-00-00 00:00",
                "project_color": getRandomColor(),
                "crew_assigned": "0",
                "estimated_hrs": "0",
                "employees1": data.employees.slice(0),
                "employees2": [],
                "myEmployee1": [],
                "myEmployee2": []
            };
        };

        var sortEmployees = function (a, b) {
            return a.origPos - b.origPos;
        };


        myApp.controller("MyController", function ($scope, $http) {

            scope = $scope;
            window.scopes = $scope;
            var estimate_id = estimateId;
            $http.get(baseUrl + 'schedule/get_projects/' + estimate_id)
                .success(function (data, status, headers, config) {
                    //$scope.myData.fromServer = data.title;
                    //console.log(data);

                    for (var i = 0, len = data.employees.length; i < len; i++) {
                        var curElem = data.employees[i];
                        curElem.emp_name = curElem.emp_name + '(' + curElem.emp_position + ')';
                    }

                    $scope.data = {
                        items: data.projects,
                        employees: data.employees
                        //employees1: data.employees,
                        //employees2: []
                    };

                    for (var i = 0, len = $scope.data.employees.length; i < len; i++) {
                        var employee = $scope.data.employees[i];
                        employee.origPos = i;
                    }

                    for (var i = 0, len = $scope.data.items.length; i < len; i++) {
                        var item = $scope.data.items[i];
                        var crew_assigned_arr = [];

                        item.start_time = (item.start_time + '').replace(/:\d\d$/, '');
                        item.end_time = (item.end_time + '').replace(/:\d\d$/, '');
                        item.date = (item.date + '').replace(/:\d\d$/, '');
                        //item.project_color = item.project_color || getRandomColor();

                        if (item.crew_assigned_str) {
                            crew_assigned_arr = item.crew_assigned_str.split(',');
                        }

                        item.employees1 = data.employees.slice(0);
                        item.employees2 = [];
                        item.myEmployee1 = [];
                        item.myEmployee2 = [];

                        for (var j = 0, len2 = item.employees1.length; j < len2; j++) {
                            var employee = item.employees1[j];

                            if (-1 !== crew_assigned_arr.indexOf(employee.employee_id)) {
                                item.myEmployee1.push(employee);
                            }
                        }
                        $scope.moveToAssignedCrew(item, false);
                    }
                })
                .error(function (data, status, headers, config) {
                    console.log("AJAX failed!");
                });

            $scope.updateItem = function (item) {
                //item.editMode = false;
                var color_item = $("input.mycolorpicker[data-project_id='" + item.project_id + "']");
                var color_item_val = $(color_item).val();
                //console.log(color_item_val);

                item.project_color = color_item_val;
                item.backColor = 'update_color';

                if ((item.project_id + '').match(/^t/)) {
                    item.project_id = '';
                }
                console.log(item);

                var send_data = jQuery.extend({}, item);
                var employees2_ids = [];

                for (var i = 0, len = send_data['employees2'].length; i < len; i++) {
                    employees2_ids.push(send_data['employees2'][i].employee_id);
                }
                send_data['crew_assigned_str'] = employees2_ids.join(',');

                delete send_data['$$hashKey'];
                delete send_data['editMode'];
                delete send_data['backColor'];
                delete send_data['employees1'];
                delete send_data['employees2'];
                delete send_data['myEmployee1'];
                delete send_data['myEmployee2'];
                delete send_data['origPos'];
                delete send_data['estimate_data'];
                delete send_data['estimate_total_data'];
                delete send_data['workorder_id'];
                //console.log(send_data);

                $http({
                    method: 'POST',
                    url: baseUrl + 'schedule/save_project/',
                    data: $.param(send_data),  // pass in data as strings
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                    .success(function (data, status, headers, config) {
                        //$scope.myData.fromServer = data.title;
                        //console.log(data);

                        if ('project_id' in data) {
                            item.project_id = data.project_id + '';
                        }
                        item.backColor = '';
                        item.editMode = false;
                    })
                    .error(function (data, status, headers, config) {
                        console.log("AJAX failed!");
                    });
            };

            $scope.editItem = function (item) {
                item.editMode = true;
            };

            $scope.cancelEditItem = function (item) {
                item.editMode = false;
            };

            $scope.addProject = function () {
                var newItem = getNewItem($scope.data);
                newItem.project_title = 'Project ' + ($scope.data.items.length + 1);
                newItem.project_id = 't' + getRandomInt();
                newItem.editMode = true;
                $scope.data.items.push(newItem);
                //console.log('new item');
                setTimeout(function () {
                    setDatetimePicker($('.mydatetimepicker[data-project_id]').last());
                    setDatetimePicker2($('.mydatetimepicker2[data-project_id]').last());
                    setMyColorpicker($('.mycolorpicker[data-project_id]').last());
                }, 0);

                //setDatetimePicker('.mydatetimepicker[data-project_id]');
                //setDatetimePicker2('.mydatetimepicker2[data-project_id]');
            };

            $scope.delProject = function () {
                var delItems = [];
                var delNewItems = [];

                for (var i = 0, len = $scope.data.items.length; i < len; i++) {
                    var item = $scope.data.items[i];

                    if (item.deleteMode) {
                        //console.log(item.start_time);

                        if (!item.project_id) {
                            delNewItems.push(i);
                        }
                        else {
                            delItems.push(item);
                        }
                    }
                }

                for (var i = delNewItems.length - 1; i >= 0; i--) {
                    $scope.data.items.splice(delNewItems[i], 1);
                }

                var delIds = [];

                for (var i = 0, len = delItems.length; i < len; i++) {
                    delIds.push(delItems[i].project_id);
                }
                var delIdsStr = delIds.join(',');
                var send_data = {delIdsStr: delIdsStr};

                if (delIdsStr) {
                    $http({
                        method: 'POST',
                        url: baseUrl + 'schedule/del_project/',
                        data: $.param(send_data),  // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                    })
                        .success(function (data, status, headers, config) {
                            //$scope.myData.fromServer = data.title;
                            //console.log(delIds);

                            for (var i = $scope.data.items.length - 1; i >= 0; i--) {
                                var item = $scope.data.items[i];

                                if (-1 !== delIds.indexOf(item.project_id)) {
                                    //console.log(item.project_id);
                                    $scope.data.items.splice(i, 1);
                                }
                            }
                        })
                        .error(function (data, status, headers, config) {
                            console.log("AJAX failed!");
                        });
                }
            };

            $scope.moveToAssignedCrew = function (item, editMode) {
                var add_arr = [];

                if (editMode === undefined) {
                    editMode = true;
                }

                for (var i = item.employees1.length - 1; i >= 0; i--) {
                    var subitem = item.employees1[i];

                    if (-1 !== item.myEmployee1.indexOf(subitem)) {
                        //subitem.origPos = i;
                        item.employees1.splice(i, 1);
                        add_arr.push(subitem);
                    }
                }
                item.myEmployee1 = [];
                add_arr.reverse();
                item.employees2 = jQuery.merge(item.employees2, add_arr);
                item.editMode = editMode;
                $scope.sortEmployees(item);
            };

            $scope.moveToMainCrew = function (item) {
                var add_arr = [];

                for (var i = item.employees2.length - 1; i >= 0; i--) {
                    var subitem = item.employees2[i];

                    if (-1 !== item.myEmployee2.indexOf(subitem)) {
                        item.employees2.splice(i, 1);
                        add_arr.push(subitem);
                    }
                }
                item.myEmployee2 = [];
                add_arr.reverse();
                item.employees1 = jQuery.merge(item.employees1, add_arr);
                item.editMode = true;
                $scope.sortEmployees(item);
            };

            $scope.sortEmployees = function (item) {
                item.employees1.sort(sortEmployees);
                item.employees2.sort(sortEmployees);
            };
        });
    }
}());
