import * as echarts from 'echarts';

function parseData(element) {
    try { return JSON.parse(element.getAttribute(element.hasAttribute('data-poll-timeline') ? 'data-poll-timeline' : 'data-poll-audience') || '[]'); }
    catch { return []; }
}

function initPollCharts() {
    document.querySelectorAll('[data-poll-timeline]').forEach((element) => {
        const data = parseData(element);
        const chart = echarts.init(element, null, { renderer: 'canvas' });
        chart.setOption({
            tooltip: { trigger: 'axis' },
            legend: { data: ['بازدید', 'پاسخ'], bottom: 0 },
            grid: { top: 20, right: 18, bottom: 55, left: 45 },
            xAxis: { type: 'category', data: data.map(item => item.date), axisLabel: { fontFamily: 'inherit', interval: 4 } },
            yAxis: { type: 'value', minInterval: 1 },
            series: [
                { name: 'بازدید', type: 'line', smooth: true, symbol: 'none', data: data.map(item => item.views), lineStyle: { color: '#0ea5e9', width: 3 }, areaStyle: { color: 'rgba(14,165,233,.08)' } },
                { name: 'پاسخ', type: 'line', smooth: true, data: data.map(item => item.responses), lineStyle: { color: '#f97316', width: 3 }, itemStyle: { color: '#f97316' } },
            ],
        });
        new ResizeObserver(() => chart.resize()).observe(element);
    });

    document.querySelectorAll('[data-poll-audience]').forEach((element) => {
        const data = parseData(element);
        const chart = echarts.init(element, null, { renderer: 'canvas' });
        chart.setOption({
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}٪)' },
            legend: { bottom: 0 },
            series: [{ type: 'pie', radius: ['50%', '72%'], center: ['50%', '45%'], label: { fontFamily: 'inherit', formatter: '{b}\n{d}٪' }, data: [
                { name: 'اعضای سایت', value: data.registered || 0, itemStyle: { color: '#8b5cf6' } },
                { name: 'مهمانان', value: data.guests || 0, itemStyle: { color: '#f59e0b' } },
            ] }],
        });
        new ResizeObserver(() => chart.resize()).observe(element);
    });
}

document.addEventListener('DOMContentLoaded', initPollCharts);
