var today = new Date();
var chnDate = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
var chart;

Date.prototype.toYmd = function( ){
    var year = this.getFullYear();

    var month = this.getMonth()+1;
    if(month<10){
        month = '0'+month;
    }

    var date = this.getDate();
    if(date<10){
        date = '0'+date;
    }

    var date = [year,month,date]

    return date.join('-');
}

function getCategories( startFrom, durationDays ){
    var today = new Date();
    today.setDate(today.getDate()+1+startFrom);
    today.setHours(0);
    today.setMinutes(0);
    var startDateSec = today.setSeconds(0);
    var stopDateSec = startDateSec + durationDays*(1000*60*60*24);

    var dates = [];
    for( var i = startDateSec; i < stopDateSec; i+=(1000*60*60*24) ){
        var crntDay = new Date( i );
        //console.log(crntDay);
        var crntDayStr = crntDay.toYmd() + '<br />' + chnDate[crntDay.getDay()];
        dates.push( crntDayStr );
    }
    return dates;
}
var HCOpts = {
    'areaspline':{
        chart: {
            type: 'areaspline'
        },
        legend: {  //图列
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 150, //图例距离位置
            y: 100,
            floating: true,  //图例放图内？
            borderWidth: 1, //图例边框
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        xAxis: {
            categories: getCategories( -7, 7 ),
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' units',
            crosshairs: true
        },
        credits: {
            enabled: false
        }
    },
    'pie':{
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        }
    }
};


var statOpts = {
    'os':{
        title: {
            text: '操作系统比例'
        },
        tooltip: {
            pointFormat: '<b>共 {point.y} 台</b>'
        },
    },
    'users':{
        title:{
            text: '注册用户男女比例'
        },
        tooltip: {
            pointFormat: '<b>共 {point.y} 人</b>'
        }
    },
    'threads':{
        title:{
            text: '帖子和求助比例'
        },
        tooltip:{
            pointFormat: '<b>共 {point.y} 条</b>'
        },
    },
    'asks':{
        title: {
            text: '求助趋势分析图'
        },
        yAxis: {
            title: {
                text: '数量'
            },
            labels: {
                align: 'left',
                x: 10,
                y: 20
            }
        },
        // series: [{
        //     name: '求助数',
        //     data: point.data.asks
        // }, {
        //     name: '作品数',
        //     data: point.data.replies
        // }]
    }
}
$(function () {

});

var queries = {};
$(function () {
    $.each(document.location.search.substr(1).split('&'), function(c,q){
        var i = q.split('=');
        queries[i[0].toString()] = i[1].toString();
    });

    var type = queries['type'];
    var defOpts = {};
    var request_url = '';

    switch( type ){
        case 'os':
        case 'users':
        case 'threads':
            request_url = '/stat/sum_stats';
            defOpts = HCOpts.pie;
            break;
        case 'replies':
        case 'asks':
            request_url = '/stat/sum_analyze';
        default:
            defOpts = HCOpts.areaspline;
            break;
    }

    Highcharts.setOptions( defOpts );

    $.ajax({
        url: request_url,
        data: {'type': type},
        success: function(point) {
            var opt = statOpts[type];
            console.log(opt);
            opt['series'] = [{
                type: 'pie',
                name:'OS share',
                data: point.data
            }];
            chart = $('.hcharts.threads').highcharts( opt );
        },
        cache: false
    });

});
