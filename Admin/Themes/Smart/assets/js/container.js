/**
 * main-container容器内数据解析
 * buzhidao
 * 2015-07-28
 */

/**
 * formsearch处理
 */
$("form[name=formsearch]").on('submit', function(event) {
    event.preventDefault();

    var url = $(this).attr("action");
    var param = $(this).serialize();

    var location_href = url + '&' + param;
    window.location.hash = location_href;
    loadURL(location_href, MainContainerObj);
});

/**
 * datalist表格数据解析
 */
var DataListTableContainerClass = function () {
    var DataListTableContainerObj = $("#Datalist_Table_Container");
    var DataListTableToolBarObj = $("#Datalist_Table_ToolBar");

    //启用、禁用
    var Enable = function () {
        var itemObj = DataListTableContainerObj.find("tbody a[name=enable]");
        itemObj.on('click', function(event) {
            event.preventDefault();
            
            var url = $(this).attr("href");
            var params = {};
            $.post(url, params, ajaxCallback, 'json');
        });
    }();

    //checkbox全选
    var CheckAll = function () {
        DataListTableContainerObj.find("thead div.checkbox input[name=checkboxall]").on('click', function () {
            var ckallflag = $(this).is(':checked');
            if (ckallflag) {
                DataListTableContainerObj.find("tbody div.checkbox input.checkboxitem").each(function () {
                    if (!$(this).is(':disabled')) $(this).prop("checked", true);
                });
            } else {
                DataListTableContainerObj.find("tbody div.checkbox input.checkboxitem").prop("checked", false);
            }
        });
    }();

    //获取选中的checkbox的id
    var GetCheckBoxItems = function () {
        var checkboxitems = new Array();
        DataListTableContainerObj.find("tbody div.checkbox input.checkboxitem").each(function () {
            if ($(this).is(':checked')) checkboxitems.push($(this).attr("value"));
        });
        return checkboxitems;
    };

    //单条删除
    var SingleDelete = function () {
        var DeleteObj = DataListTableContainerObj.find("tbody a[name=delete]");
        DeleteObj.on('click', function (event) {
            event.preventDefault();

            var url = $(this).attr("href");
            var params = {};
            $.post(url, params, ajaxCallback, 'json');
        });
    }();

    //批量删除
    var MultiDelete = function () {
        var DeleteObj = DataListTableToolBarObj.find("a[name=delete]");
        DeleteObj.on('click', function () {
            var checkboxitems = GetCheckBoxItems();
            //检查是否有数据选中
            if (checkboxitems.length == 0) {
                alertPanelShow('error', '请选择至少一条数据！');
                return false;
            }

            var url = $(this).attr("href");
            var params = {};
            params[$(this).attr("checkboxitemkey")] = checkboxitems;
            $.post(url, params, ajaxCallback, 'json');

            return false;
        });
    }();
}();

/**
 * formnewedit 解析处理
 */
var FormNewEditClass = function () {
    var FormNewEditObj = $("form[name=formnewedit]");

    //form提交解析
    FormNewEditObj.on('submit', function (event) {
        event.preventDefault();

        var url = $(this).attr('action');
        var formdata = $(this).serialize();
        $.post(url, formdata, ajaxCallback, 'json');
    });
}();
