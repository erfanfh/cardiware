import * as echarts from 'echarts/core';
import { LineChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import { SVGRenderer } from 'echarts/renderers';

echarts.use([LineChart, GridComponent, TooltipComponent, SVGRenderer]);

const element = document.getElementById('seller-income-chart');

if (element) {
    const data = JSON.parse(element.dataset.chart || '{}');
    const chart = echarts.init(element, null, { renderer: 'svg' });
    const dark = document.documentElement.classList.contains('dark');

    chart.setOption({
        animationDuration: 450,
        grid: { left: 16, right: 18, top: 24, bottom: 16, containLabel: true },
        tooltip: {
            trigger: 'axis',
            backgroundColor: dark ? '#1e293b' : '#fff',
            borderColor: dark ? '#334155' : '#e2e8f0',
            textStyle: { color: dark ? '#f8fafc' : '#0f172a', fontFamily: 'inherit' },
            formatter: (items) => `${items[0].axisValue}<br/><strong>${Number(items[0].value).toLocaleString('fa-IR')} تومان</strong>`,
        },
        xAxis: {
            type: 'category', data: data.labels || [], boundaryGap: false,
            axisLine: { lineStyle: { color: dark ? '#334155' : '#e2e8f0' } },
            axisTick: { show: false },
            axisLabel: { color: dark ? '#94a3b8' : '#64748b', interval: data.labels?.length > 30 ? 13 : data.labels?.length > 7 ? 5 : 0 },
        },
        yAxis: {
            type: 'value', min: 0,
            splitLine: { lineStyle: { color: dark ? '#273449' : '#f1f5f9' } },
            axisLabel: { color: dark ? '#94a3b8' : '#64748b', formatter: (value) => Number(value).toLocaleString('fa-IR') },
        },
        series: [{
            type: 'line', data: data.values || [], smooth: 0.3, symbol: 'circle', symbolSize: 6,
            showSymbol: (data.values || []).length <= 7,
            lineStyle: { width: 3, color: '#10b981' },
            itemStyle: { color: '#10b981' },
            areaStyle: { color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                { offset: 0, color: 'rgba(16,185,129,.28)' },
                { offset: 1, color: 'rgba(16,185,129,0)' },
            ]) },
        }],
    });

    new ResizeObserver(() => chart.resize()).observe(element);
}
